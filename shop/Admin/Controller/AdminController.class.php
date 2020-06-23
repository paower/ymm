<?php
// +----------------------------------------------------------------------
// | #
// +----------------------------------------------------------------------
// | .
// +----------------------------------------------------------------------
// | ##
// +----------------------------------------------------------------------
namespace Admin\Controller;

use Think\Controller;

/**
 * 后台公共控制器
 * 为什么要继承AdminController？
 * 因为AdminController的初始化函数中读取了顶部导航栏和左侧的菜单，
 * 如果不继承的话，只能复制AdminController中的代码来读取导航栏和左侧的菜单。
 * @author jry <598821125@qq.com>
 */
class AdminController extends Controller
{
	/**
	 * 初始化方法
	 * @author jry <598821125@qq.com>
	 */
	protected function _initialize()
	{

		// 登录检测
		if (!is_login()) {
			//还没登录跳转到登录页面
			$this->redirect('Admin/Pubss/login');
		}

		// 权限检测
		$current_url = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
		if ('Admin/Index/index' !== $current_url) {

			if (!D('Admin/Group')->checkMenuAuth()) {
				$this->error('权限不足！', U('Admin/Index/index'));
			}
		}

		// 日分红
		
		// 判断是否开启分红
		$reward = M('reward')->where(array('id'=>1))->field('xf_price,fh_status')->find();
		if($reward['fh_status'] == 1){
			$store = M('store')->where('xiaofei_num > 0')->select();
			$starttime = strtotime(date('Y-m-d'));
			foreach($store as $k => $v){
				if($v['bonus_time'] < $starttime){
					set_time_limit (0);
					ignore_user_abort(true);
					if($v['is_bonus'] == 1){
						M('store')->where(array('uid'=>$v['uid']))->setField('is_bonus',0);
					}elseif($v['is_bonus'] == 0){
						// $beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
						// $endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
						// $end_time=$start_time+60*60*24;
						// $tranmoney = M('tranmoney')->where(array('get_type'=>array('in','24'),'get_time'=>array('EGT',$beginYesterday),'get_time'=>array('ELT',$endYesterday)))->sum('get_nums');
						// $order_detail = M('order_detail as b');
						// $order = $order_detail->join('ysk_order a on b.order_id = a.order_id')->select();
						// foreach ($order as $key => $value) {
						// 	if($value['time']>=$beginYesterday && $value['time']<=$endYesterday && $value['com_id']==184 && $value['goods_status']==1){
						// 		$tranmoney = M('order_detail as b')->join('ysk_order a on b.order_id = a.order_id')
						// 		->where(array('b.com_id'=>array('in','184'),'a.time'=>array('EGT',$beginYesterday),'a.time'=>array('ELT',$endYesterday)))->sum('jifen_nums');
						// 	};
						// }
						$order_detail = M('order_detail')->where(array('com_id'=>array('in','184'),'goods_status'=>array('in','1')))->sum('jifen_nums');
						// if ($tranmoney['get_time']>=$start_time&&$tranmoney['get_time']<=$end_time) {
						// 	$liutongl = 
						// }
						/*$ren = $find->join('ysk_user a on b.user_id = a.userid')->field('a.username,a.vip_grade,b.user_id,b.content,b.create_time,b.status')->order('create_time desc')->select();*/
						$data['fenhong_num'] = $v['fenhong_num'] + $reward['xf_price']*100/ $order_detail*$v['xiaofei_num'];
							
						
						$data['is_bonus'] = 1;
						$data['bonus_time'] = time();
						
						// 添加分红记录
						$arr = array(
							'get_id' => $v['uid'],
							'pay_id' => 0,
							'get_nums' => $reward['xf_price']*100/ $order_detail*$v['xiaofei_num'],
							'get_time' => time(),
							'get_type' => 77,
							'now_nums' => $data['fenhong_num'],
							'now_nums_get' => $data['fenhong_num'],
						);

						M('tranmoney')->add($arr);
						
						M('store')->where(array('uid'=>$v['uid']))->save($data);
						$jian = M('store')->where(array('uid'=>$v['uid']))->find();
						$tranmoney = M('tranmoney')->where(array('get_id'=>$v['uid'],'get_type'=>77))->order('get_time desc')->find();
						if($jian['xiaofei_num']-$tranmoney['get_nums']>=0){
							$jianq['xiaofei_num'] = $jian['xiaofei_num']-$tranmoney['get_nums'];
						}else{
							$jianq['xiaofei_num'] = $jian['xiaofei_num']-$jian['xiaofei_num'];
						}
						$wc = M('store')->where(array('uid'=>$v['uid']))->save($jianq);
					}
				}
			}
		}

		//自动收货
		$time = time();
        //  AND deadline <> 0
        $where = 'deadline <='.$time.' AND status = 2';
        $order_res = M('order')->where($where)->field('order_id')->select();
        if($order_res){
            foreach($order_res as $k => $v){
                $this->deadline_Confirmad($v['order_id']);
            }
        }
		
	 
		// 获取左侧导航
		$this->getMenu();
	}


	//确认收货
	private function deadline_Confirmad($orderid)

	{

		$orders = M('order');

		M()->startTrans();
		$res_change = $orders->where(array('order_id' => $orderid))->setField('status', 3);

		if ($res_change) {
			
			$datas=$orders->where(array('order_id' => $orderid))->find();
			$uid=$datas['order_sellerid'];
			$money=$datas['buy_price'];
			
			if($uid != 0){

				$cangku_ratio = M('gerenshangpu gr')->where(array('gr.userid'=>$uid))->join('ysk_product_cate pc ON gr.shop_cate=pc.id')->getField('cangku_ratio');
				
				//平台按提成抽取费用
				$s_money = $money * $cangku_ratio;
				$p_money = $money - $s_money;

				M('store')->where(array('uid'=>1))->setInc('cangku_num',$p_money);
				$res=M('store')->where(array('uid'=>$uid))->setInc('cangku_num',$s_money);
			}else{
				$s_money = $money;
				//平台商品
				$res=M('store')->where(array('uid'=>1))->setInc('cangku_num',$money);
			}
				$cangku_num = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
				$tran['pay_id'] = 0;
				$tran['get_id'] = $datas['order_sellerid'];
				$tran['get_nums'] = $s_money;
				$tran['get_time'] = time();
				$tran['get_type'] = 36;
				$tran['now_nums'] = $cangku_num;
				$tran['now_nums_get'] = $cangku_num;
				M('tranmoney')->add($tran);

			//返還上級余额
			$com_id=M('order_detail')->where(array('order_id' => $orderid))->field('com_id,com_num')->select();
			$return_balance = 0;
			$is_meal = 0;
			foreach($com_id as $vo){
				//赠送积分
				if($vo['set_meal']==1){
					
					//赠送余额
					$to_balance = M('product_detail')->where(array('id'=>$vo['com_id']))->getField('return_balance');
					$to_balance = $to_balance * $vo['com_num'];
					$return_balance += $to_balance;
					$is_meal = 1;
				}
			}
			// in_array
			if($return_balance&&$return_balance > 0 && $is_meal == 1){
				
				//返还上级余额
				$cangku_num = M('store')->where(array('uid'=>$user['pid']))->getField('cangku2_num');
				$cang_res = M('store')->where(array('uid'=>$user['pid']))->setInc('cangku2_num',$return_balance);
				

				//写入记录
				$cang_tran['pay_id'] = $user['pid'];
				$cang_tran['get_nums'] = $return_balance;
				$cang_tran['get_time'] = time();
				$cang_tran['get_type'] = 35;
				$cang_tran['now_nums'] = $cangku_num + $return_balance;
				$cang_tran['now_nums_get'] = $cangku_num + $return_balance;
				$cang_tran['is_release'] = 1;
				$traInfo->add($cang_tran);
			}
			if($res){
				M()->commit();
			}else{
                $orders->where(array('order_id' => $orderid))->setField('status', 2);
                M()->rollback();
			}

		} else {
			M()->rollback();
		}

	}



	// 后台主菜单
	public function getMenu(){
		$module_object = D('Admin/Menu');

		//选种的顶部菜单ID
		$_menu_tab=$module_object->getParentMenu();
		$_menu_tab['gid']? $_menu_tab['gid'] :$_menu_tab['gid']=1;
		// 获取所有导航
		$menu_list=$module_object->getAllMenu($_menu_tab['gid']);
		$menu_top=$module_object->getTopMenu();
		$select_url=$module_object->SelectMenu();

		//获取升级会员等待交易的信息
		$num = M('trans')->where(array('pay_nums'=>1800,'pay_state'=>0))->count();
		// dump($num);
		// exit;
		$this->assign(array(
				'_menu_list_g'  =>  $menu_top['g_menu'],//爷爷级
				'_menu_list_p'  =>  $menu_list['p_menu'],//父级
				'_menu_list_c'  =>  $menu_list['c_menu'],//子级
				'_menu_tab'     =>  $_menu_tab,
				'_select_url'    =>  $select_url,
				'num'           => $num,
			));
	}


	/**
	 * 设置一条或者多条数据的状态
	 * @param $script 严格模式要求处理的纪录的uid等于当前登陆用户UID
	 * @author jry <598821125@qq.com>
	 */
	public function setStatus($model = CONTROLLER_NAME, $script = false)
	{
		$ids    = I('request.ids');
		$status = I('request.status');
		if (empty($ids)) {
			$this->error('请选择要操作的数据');
		}
		$model_primary_key       = D($model)->getPk();
		$map[$model_primary_key] = array('in', $ids);
		if ($script) {
			$map['uid'] = array('eq', is_login());
		}
		switch ($status) {
			case 'forbid': // 禁用条目
				$data = array('status' => 0);
				$this->editRow(
					$model,
					$data,
					$map,
					array('success' => '禁用成功', 'error' => '禁用失败')
				);
				break;
			case 'resume': // 启用条目
				$data = array('status' => 1);
				$map  = array_merge(array('status' => 0), $map);
				$this->editRow(
					$model,
					$data,
					$map,
					array('success' => '启用成功', 'error' => '启用失败')
				);
				break;
			case 'recycle': // 移动至回收站
				$data['status'] = -1;
				$this->editRow(
					$model,
					$data,
					$map,
					array('success' => '成功移至回收站', 'error' => '删除失败')
				);
				break;
			case 'restore': // 从回收站还原
				$data = array('status' => 1);
				$map  = array_merge(array('status' => -1), $map);
				$this->editRow(
					$model,
					$data,
					$map,
					array('success' => '恢复成功', 'error' => '恢复失败')
				);
				break;
			case 'delete': // 删除条目
				$result = D($model)->where($map)->delete();
				if ($result) {
					$this->success('删除成功，不可恢复！');
				} else {
					$this->error('删除失败');
				}
				break;
			default:
				$this->error('参数错误');
				break;
		}
	}

	/**
	 * 对数据表中的单行或多行记录执行修改 GET参数id为数字或逗号分隔的数字
	 * @param string $model 模型名称,供M函数使用的参数
	 * @param array  $data  修改的数据
	 * @param array  $map   查询时的where()方法的参数
	 * @param array  $msg   执行正确和错误的消息
	 *                       array(
	 *                           'success' => '',
	 *                           'error'   => '',
	 *                           'url'     => '',   // url为跳转页面
	 *                           'ajax'    => false //是否ajax(数字则为倒数计时)
	 *                       )
	 * @author jry <598821125@qq.com>
	 */
	final protected function editRow($model, $data, $map, $msg)
	{
		$id = array_unique((array) I('id', 0));
		$id = is_array($id) ? implode(',', $id) : $id;
		//如存在id字段，则加入该条件
		$fields = D($model)->getDbFields();
		if (in_array('id', $fields) && !empty($id)) {
			$where = array_merge(
				array('id' => array('in', $id)),
				(array) $where
			);
		}
		$msg = array_merge(
			array(
				'success' => '操作成功！',
				'error'   => '操作失败！',
				'url'     => ' ',
				'ajax'    => IS_AJAX,
			),
			(array) $msg
		);
		$result = D($model)->where($map)->save($data);
		if ($result != false) {
			$this->success($msg['success'], $msg['url'], $msg['ajax']);
		} else {
			$this->error($msg['error'], $msg['url'], $msg['ajax']);
		}
	}

	/**
	 * 模块配置方法
	 * @author jry <598821125@qq.com>
	 */
	public function module_config()
	{
		if (IS_POST) {
			$id     = (int) I('id');
			$config = I('config');
			$flag   = D('Admin/Module')
				->where("id={$id}")
				->setField('config', json_encode($config));
			if ($flag !== false) {
				$this->success('保存成功');
			} else {
				$this->error('保存失败');
			}
		} else {
			$name        = MODULE_NAME;
			$config_file = realpath(APP_PATH . $name) . '/' . D('Admin/Module')->install_file();
			if (!$config_file) {
				$this->error('配置文件不存在');
			}
			$module_config = include $config_file;

			$module_info = D('Admin/Module')->where(array('name' => $name))->find($id);
			$db_config   = $module_info['config'];

			// 构造配置
			if ($db_config) {
				$db_config = json_decode($db_config, true);
				foreach ($module_config['config'] as $key => $value) {
					if ($value['type'] != 'group') {
						$module_config['config'][$key]['value'] = $db_config[$key];
					} else {
						foreach ($value['options'] as $gourp => $options) {
							foreach ($options['options'] as $gkey => $value) {
								$module_config['config'][$key]['options'][$gourp]['options'][$gkey]['value'] = $db_config[$gkey];
							}
						}
					}
				}
			}

			// 构造表单名
			foreach ($module_config['config'] as $key => $val) {
				if ($val['type'] == 'group') {
					foreach ($val['options'] as $key2 => $val2) {
						foreach ($val2['options'] as $key3 => $val3) {
							$module_config['config'][$key]['options'][$key2]['options'][$key3]['name'] = 'config[' . $key3 . ']';
						}
					}
				} else {
					$module_config['config'][$key]['name'] = 'config[' . $key . ']';
				}
			}

			//使用FormBuilder快速建立表单页面。
			$builder = new \Common\Builder\FormBuilder();
			$builder->setMetaTitle('设置') //设置页面标题
				->setPostUrl(U('')) //设置表单提交地址
				->addFormItem('id', 'hidden', 'ID', 'ID')
				->setExtraItems($module_config['config']) //直接设置表单数据
				->setFormData($module_info)
				->display();
		}
	}

	/**
	 * 扩展日期搜索map
	 * @param $map array 引用型
	 * @param string $field 搜索的时间范围字段
	 * @param string $type datetime  类型 或 timestamp 时间戳
	 * @param boolean $not_empty 是否允许空值搜索到
	 */
	public function extendDates(&$map, $field = 'update_time', $type = 'datetime', $not_empty = false)
	{
		$dates = I('dates', '', 'trim');
		if ($dates) {
			$start_date = substr($dates, 0, 10);
			$end_date   = substr($dates, 11, 10);
			if ($type == 'datetime') {
				$map[$field] = [
					['egt', $start_date . ' 00:00:00'],
					['lt', $end_date . ' 23:59:59'],
				];
				if ($not_empty) {
					$map[$field][] = ['exp', 'IS NOT NUll'];
				}
			} else {
				$map[$field] = [
					['egt', strtotime($start_date . ' 00:00:00')],
					['lt', strtotime($end_date . ' 23:59:59')],
				];
			}
		}
		// else {
		//     $start_date = datetime("-365 days", 'Y-m-d');
		//     $end_date   = datetime('now', 'Y-m-d');
		// }
	}
}
