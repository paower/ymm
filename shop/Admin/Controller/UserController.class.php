<?php
namespace Admin\Controller;

use Think\Page;

/**
 * 用户控制器
 * 
 */
class UserController extends AdminController
{


		/**
		 * 用户列表
		 * 
		 */
		 public function index()
		{


				 // 搜索
				$keyword    = I('keyword', '', 'string');
				$querytype  = I('querytype','userid','string');
				$status     = I('status');
				if($keyword){
						$condition = $keyword ;
						$map[$querytype] = $condition;
				}


				 //按日期搜索
				$date=date_query('reg_date');
				if($date){
					$where=$date;
					if(isset($map))
						$map=array_merge($map,$where);
					else
						$map=$where;
				}

				if($level!=''){
						$map['a.level']=$level;
				}

				// 获取所有用户
				$user   = M('user a');
				if(!isset($map)){
						$map=true;
				}


				// //按日期搜索
				// $date=date_query('reg_date');
				// if($date){
				//     $where=$date;
				//     if($map)
				//         $map=array_merge($map,$where).' and sex==0';
				//     else
				//         $map=$where.' and sex==0';
				// }

				// if($status=='0' || $status=='1'){
				//      $map['a.status']=$status;
				// }
				//  //$map=$map.' sex=0';
				// // 获取所有用户
				// $user   = M('user a');

				//========排序=========
				$order_str='a.userid desc';

				//========排序=========

				//分页
				$table=$user->join('ysk_store b on a.userid=b.uid','left');
				$p=getpage($table,$map,15);
				$page=$p->show();

				$data_list     = $table
					->field('a.userid,a.username,a.email,a.yinbi,a.account,a.mobile,a.reg_date,a.status,a.pid,b.cangku_num,b.fengmi_num,b.recharge_num,b.cangku2_num')
					->where($map)
					->order($order_str)
					->select();
				$yue_sum = 0;
				$jifen_sum = 0;
				$count = 0;
				$store =  M('store');
				$count = $user->count();
				$yue_sum = $store->sum('cangku_num');
				$jifen_sum = $store->sum('fengmi_num');
				$cangku2_sum = $store->sum('cangku2_num');
				$recharge_sum = $store->sum('recharge_num');
				 //取管理员会员列表的权限
				$uids= is_login();
				$hylbs="1,2,3,4,5";
				$auth_id    = M('admin')->where(array('id'=>$uids))->getField('auth_id');
				if($auth_id<>1){
				$auth_id    = M('admin')->where(array('id'=>$uids))->getField('auth_id');
				$hylbs    = M('group')->where(array('auth_id'=>$auth_id))->getField('hylb');

				}
				$hylb=explode(",",$hylbs);
				$this->assign('hylb',$hylb);
				$this->assign('list',$data_list);
				$this->assign('yue_sum',$yue_sum);
			 $this->assign('count',$count);
				$this->assign('jifen_sum',$jifen_sum);
				$this->assign('cangku2_sum',$cangku2_sum);
				$this->assign('recharge_sum',$recharge_sum);
				$this->assign('table_data_page',$page);
				$this->display();
		}

		//快速充值记录
		public function recharge2(){
			$data = M('wxrecharge')->where('result_code = 1')->join('ysk_user u on ysk_wxrecharge.uid = u.userid','left')->select();
			$this->assign('list',$data);
			$this->display();
		}
		
		/**
		 * 后台充值记录
		 */
		public function recharge(){
				// 获取所有用户
				$tranmoney   = M('tranmoney t');  
				// 搜索
				$querytype  = I('querytype','','string');
				$keyword    = I('keyword', '', 'string');
				if($querytype){
						if($querytype == 1){
								$map['t.get_type'] = array('eq', 11);
						}else{
								$map['t.get_type'] = array('eq', 12);
						}
				}else{
						$map['t.get_type'] = array('in', '11,12');
				}
				if($keyword){
						$map['t.get_id'] = array('eq', $keyword);
				}
				$order_str='t.id desc';
				//分页
				$table=$tranmoney->join('ysk_user u on t.get_id = u.userid','left');
				$p=getpage($table,$map,15);
				$page=$p->show();
				$data_list     = $table
						->field('u.userid,u.username,u.account,u.mobile,t.get_nums,t.createtime,t.get_type')
						->where($map)
						->order($order_str)
						->select();
				$yue_add = 0;
				$yue_red = 0;
				$jifen_add = 0;
				$jifen_red = 0;
				$yue_data = $tranmoney->where(array('get_type'=>11))->select();
				if(!empty($yue_data)){
					foreach($yue_data as $k=>$v){
							if($v['get_nums'] < 0){
									$yue_red += $v['get_nums'];
								}elseif($v['get_nums'] > 0){
									$yue_add += $v['get_nums'];
								}
						}
				}
				$jifen_data = $tranmoney->where(array('get_type'=>12))->select();
				if(!empty($jifen_data)){
					foreach($jifen_data as $k=>$v){
							if($v['get_nums'] < 0){
									$jifen_red += $v['get_nums'];
								}elseif($v['get_nums'] > 0){
									$jifen_add += $v['get_nums'];
								}
						}
				}
				if(!empty($data_list)){
						foreach ($data_list as $k=>$v){
								if($v['get_type'] == 11){
										$data_list[$k]['name'] = '余额';
								}else if($v['get_type'] == 12){
										$data_list[$k]['name'] = 'JM';
								}
								if($v['get_nums'] < 0 ){
										$data_list[$k]['type'] = '减少';
								}else{
										$data_list[$k]['type'] = '增加';
								}
						}
				}
				$this->assign('yue_add',$yue_add);
				$this->assign('yue_red',$yue_red);
				$this->assign('jifen_add',$jifen_add);
				$this->assign('jifen_red',$jifen_red);
				$this->assign('list',$data_list);
				$this->assign('table_data_page',$page);
				$this->display();
		}


		/**
		 * 用户充值记录
		 */

		public function userrechar(){
			$recharge = M('recharge');

			//分页
			$table=$recharge->alias('r')->join('ysk_user u ON r.uid=u.userid');
			$map = '';
			$p=getpage($table,$map,15);
			$page=$p->show();
			$data_list = $table->order('examine asc,id desc')->select();
			$this->assign('list',$data_list);
			$this->assign('table_data_page',$page);
			$this->display();
		}


		public function userrechar_examine(){
			
			$id = I('id');
			$arr = [
				'examinetime'=>time(),
				'examine' => 1
			];
			$res = M('recharge')->where(array('id'=>$id))->save($arr);
			$user = M('recharge')->where(array('id'=>$id))->field('uid,money')->find();
			$res2 = M('store')->where(array('uid'=>$user['uid']))->setInc('recharge_num',$user['money']);

			if($res !== false && $res2 !== false){
				//判断是否为首次充值
				$rechargeData= M('recharge')->where(array('uid'=>$user['uid']))->order('createtime asc')->field('id')->find();
				if($id == $rechargeData['id']){
					//获取会员数据
					$level_info = M('vip_level')->select();
					//判断父级会员消费金额
					$pid = M('user')->where(array('userid'=>$user['uid']))->getField('pid');
					if($pid!=0){
						$ppay_money = M('user')->where("userid = $pid")->getField('pay_money');
						//判断父级会员等级
						foreach ($level_info as $k => $v) {
							if($ppay_money>=$v['pay_money'] && $plevel<$v['level']){
								$plevel = $v['level'];
							}
						}
						switch ($plevel) {
							case '1':
								# code...
								$rechar_firstReward =  M('config')->where(array('name'=>'rechar_firstReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'reward_to_shopEta'))->getField('value');
								break;
							case '2':
								$rechar_firstReward =  M('config')->where(array('name'=>'level2_rechar_firstReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level2_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level2_reward_to_shopEta'))->getField('value');
								break;
							case '3':
								$rechar_firstReward =  M('config')->where(array('name'=>'level3_rechar_firstReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level3_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level3_reward_to_shopEta'))->getField('value');
								break;
							case '4':
								$rechar_firstReward =  M('config')->where(array('name'=>'level4_rechar_firstReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level4_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level4_reward_to_shopEta'))->getField('value');
								break;
							case '5':
								$rechar_firstReward =  M('config')->where(array('name'=>'level5_rechar_firstReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level5_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level5_reward_to_shopEta'))->getField('value');
								break;
								case '6':
								$rechar_firstReward =  M('config')->where(array('name'=>'level6_rechar_firstReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level6_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level6_reward_to_shopEta'))->getField('value');
								break;
							case '7':
								$rechar_firstReward =  M('config')->where(array('name'=>'level7_rechar_firstReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level7_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level7_reward_to_shopEta'))->getField('value');
								break;
							case '8':
								$rechar_firstReward =  M('config')->where(array('name'=>'level8_rechar_firstReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level8_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level8_reward_to_shopEta'))->getField('value');
								break;
							case '9':
								$rechar_firstReward =  M('config')->where(array('name'=>'level9_rechar_firstReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level9_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level9_reward_to_shopEta'))->getField('value');
								break;
						}
						//奖励总金额
						$reward = $user['money'] * $rechar_firstReward;
						//修改用户余额
						M('store')->where(array('uid'=>$pid))->setInc('fengmi_num',$reward * $reward_to_rechar);
						M('store')->where(array('uid'=>$pid))->setInc('cangku_num',$reward * $reward_to_shopEta);
						//兑换卡奖励记录插入数据库
						$data['pid'] = $pid;
						$data['money'] = $reward * $reward_to_rechar;
						$data['createtime'] = time();
						$data['uid'] = $id;
						$where['uid'] = $pid;
						$data['yue'] = M('store')->where($where)->getField('fengmi_num');
						M('tuijian_reward')->add($data); 
						//商家jm奖励记录插入数据库
						$data['pid'] = $pid;
						$data['money'] = $reward * $reward_to_shopEta;
						$data['createtime'] = time();
						$data['uid'] = $id;
						$where['uid'] = $pid;
						$data['yue'] = M('store')->where($where)->getField('cangku_num');
						M('shangjiajm_reward')->add($data);
					}
					//判断父父级会员消费金额
					$gid = M('user')->where(array('userid'=>$pid))->getField('pid');
					if($gid!=0){
						$gpay_money = M('user')->where("userid = $gid")->getField('pay_money');
						//判断父父级会员等级
						foreach ($level_info as $k => $v) {
							if($gpay_money>=$v['pay_money'] && $plevel<$v['level']){
								$glevel = $v['level'];
							}
						}
						switch ($glevel) {
							case '1':
								# code...
								$rechar_secondReward =  M('config')->where(array('name'=>'rechar_secondReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'reward_to_shopEta'))->getField('value');
								break;
							case '2':
								$rechar_secondReward =  M('config')->where(array('name'=>'level2_rechar_secondReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level2_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level2_reward_to_shopEta'))->getField('value');
								break;
							case '3':
								$rechar_secondReward =  M('config')->where(array('name'=>'level3_rechar_secondReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level3_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level3_reward_to_shopEta'))->getField('value');
								break;
							case '4':
								$rechar_secondReward =  M('config')->where(array('name'=>'level4_rechar_secondReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level4_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level4_reward_to_shopEta'))->getField('value');
								break;
							case '5':
								$rechar_secondReward =  M('config')->where(array('name'=>'level5_rechar_secondReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level5_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level5_reward_to_shopEta'))->getField('value');
								break;
								case '6':
								$rechar_secondReward =  M('config')->where(array('name'=>'level6_rechar_secondReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level6_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level6_reward_to_shopEta'))->getField('value');
								break;
							case '7':
								$rechar_secondReward =  M('config')->where(array('name'=>'level7_rechar_secondReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level7_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level7_reward_to_shopEta'))->getField('value');
								break;
							case '8':
								$rechar_secondReward =  M('config')->where(array('name'=>'level8_rechar_secondReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level8_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level8_reward_to_shopEta'))->getField('value');
								break;
							case '9':
								$rechar_secondReward =  M('config')->where(array('name'=>'level9_rechar_secondReward'))->getField('value');
								$reward_to_rechar =  M('config')->where(array('name'=>'level9_reward_to_rechar'))->getField('value');
								$reward_to_shopEta =  M('config')->where(array('name'=>'level9_reward_to_shopEta'))->getField('value');
								break;
						}
						//奖励总金额
						$reward = $user['money'] * $rechar_secondReward;
						//修改用户余额
						M('store')->where(array('uid'=>$gid))->setInc('fengmi_num',$reward * $reward_to_rechar);
						M('store')->where(array('uid'=>$gid))->setInc('cangku_num',$reward * $reward_to_shopEta);
						//兑换卡奖励记录插入数据库
						$data['pid'] = $gid;
						$data['money'] = $reward * $reward_to_rechar;
						$data['createtime'] = time();
						$data['uid'] = $id;
						$where['uid'] = $gid;
						$data['yue'] = M('store')->where($where)->getField('fengmi_num');
						M('tuijian_reward')->add($data); 
						//商家jm奖励记录插入数据库
						$data['pid'] = $gid;
						$data['money'] = $reward * $reward_to_shopEta;
						$data['createtime'] = time();
						$data['uid'] = $id;
						$where['uid'] = $gid;
						$data['yue'] = M('store')->where($where)->getField('cangku_num');
						M('shangjiajm_reward')->add($data);
					}
				}

				//判断是否为二次充值
				$uid=$user['uid'];
				$recharge2Data = M('recharge')->where("uid=$uid and examine=1")->count();
				if($recharge2Data==2){

					//获取会员数据
					$level_info = M('vip_level')->select();
					//判断父级会员消费金额
					$pid = M('user')->where(array('userid'=>$user['uid']))->getField('pid');
					if($pid!=0){
						$ppay_money = M('user')->where("userid = $pid")->getField('pay_money');
						//判断父级会员等级
						foreach ($level_info as $k => $v) {
							if($ppay_money>=$v['pay_money'] && $plevel<$v['level']){
								$plevel = $v['level'];
							}
						}
						switch ($plevel) {
							case '1':
								# code...
								$rechar2_firstReward =  M('config')->where(array('name'=>'rechar2_firstReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'reward2_to_shopEta'))->getField('value');
								break;
							case '2':
								$rechar2_firstReward =  M('config')->where(array('name'=>'level2_rechar2_firstReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level2_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level2_reward2_to_shopEta'))->getField('value');
								break;
							case '3':
								$rechar2_firstReward =  M('config')->where(array('name'=>'level3_rechar2_firstReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level3_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level3_reward2_to_shopEta'))->getField('value');
								break;
							case '4':
								$rechar2_firstReward =  M('config')->where(array('name'=>'level4_rechar2_firstReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level4_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level4_reward2_to_shopEta'))->getField('value');
								break;
							case '5':
								$rechar2_firstReward =  M('config')->where(array('name'=>'level5_rechar2_firstReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level5_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level5_reward2_to_shopEta'))->getField('value');
								break;
								case '6':
								$rechar2_firstReward =  M('config')->where(array('name'=>'level6_rechar2_firstReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level6_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level6_reward2_to_shopEta'))->getField('value');
								break;
							case '7':
								$rechar2_firstReward =  M('config')->where(array('name'=>'level7_rechar2_firstReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level7_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level7_reward2_to_shopEta'))->getField('value');
								break;
							case '8':
								$rechar2_firstReward =  M('config')->where(array('name'=>'level8_rechar2_firstReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level8_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level8_reward2_to_shopEta'))->getField('value');
								break;
							case '9':
								$rechar2_firstReward =  M('config')->where(array('name'=>'level9_rechar2_firstReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level9_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level9_reward2_to_shopEta'))->getField('value');
								break;
						}
						//奖励总金额
						$reward2 = $user['money'] * $rechar2_firstReward;
						//修改用户余额
						M('store')->where(array('uid'=>$pid))->setInc('fengmi_num',$reward2 * $reward2_to_rechar);
						M('store')->where(array('uid'=>$pid))->setInc('cangku_num',$reward2 * $reward2_to_shopEta);
						//兑换卡奖励记录插入数据库
						$data['pid'] = $pid;
						$data['money'] = $reward2 * $reward2_to_rechar;
						$data['createtime'] = time();
						$data['uid'] = $id;
						$where['uid'] = $pid;
						$data['yue'] = M('store')->where($where)->getField('fengmi_num');
						M('tuijian_reward')->add($data); 
						//商家jm奖励记录插入数据库
						$data['pid'] = $pid;
						$data['money'] = $reward2 * $reward2_to_shopEta;
						$data['createtime'] = time();
						$data['uid'] = $id;
						$where['uid'] = $pid;
						$data['yue'] = M('store')->where($where)->getField('cangku_num');
						M('shangjiajm_reward')->add($data);
					}
					//判断父父级会员消费金额
					$gid = M('user')->where(array('userid'=>$pid))->getField('pid');
					if($gid!=0){
						$gpay_money = M('user')->where("userid = $gid")->getField('pay_money');
						//判断父父级会员等级
						foreach ($level_info as $k => $v) {
							if($gpay_money>=$v['pay_money'] && $plevel<$v['level']){
								$glevel = $v['level'];
							}
						}
						switch ($glevel) {
							case '1':
								# code...
								$rechar2_secondReward =  M('config')->where(array('name'=>'rechar2_secondReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'reward2_to_shopEta'))->getField('value');
								break;
							case '2':
								$rechar2_secondReward =  M('config')->where(array('name'=>'level2_rechar2_secondReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level2_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level2_reward2_to_shopEta'))->getField('value');
								break;
							case '3':
								$rechar2_secondReward =  M('config')->where(array('name'=>'level3_rechar2_secondReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level3_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level3_reward2_to_shopEta'))->getField('value');
								break;
							case '4':
								$rechar2_secondReward =  M('config')->where(array('name'=>'level4_rechar2_secondReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level4_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level4_reward2_to_shopEta'))->getField('value');
								break;
							case '5':
								$rechar2_secondReward =  M('config')->where(array('name'=>'level5_rechar2_secondReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level5_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level5_reward2_to_shopEta'))->getField('value');
								break;
								case '6':
								$rechar2_secondReward =  M('config')->where(array('name'=>'level6_rechar2_secondReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level6_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level6_reward2_to_shopEta'))->getField('value');
								break;
							case '7':
								$rechar2_secondReward =  M('config')->where(array('name'=>'level7_rechar2_secondReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level7_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level7_reward2_to_shopEta'))->getField('value');
								break;
							case '8':
								$rechar2_secondReward =  M('config')->where(array('name'=>'level8_rechar2_secondReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level8_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level8_reward2_to_shopEta'))->getField('value');
								break;
							case '9':
								$rechar2_secondReward =  M('config')->where(array('name'=>'level9_rechar2_secondReward'))->getField('value');
								$reward2_to_rechar =  M('config')->where(array('name'=>'level9_reward2_to_rechar'))->getField('value');
								$reward2_to_shopEta =  M('config')->where(array('name'=>'level9_reward2_to_shopEta'))->getField('value');
								break;
						}
						//奖励总金额
						$reward2 = $user['money'] * $rechar2_secondReward;
						//修改用户余额
						M('store')->where(array('uid'=>$gid))->setInc('fengmi_num',$reward2 * $reward2_to_rechar);
						M('store')->where(array('uid'=>$gid))->setInc('cangku_num',$reward2 * $reward2_to_shopEta);
						//兑换卡奖励记录插入数据库
						$data['pid'] = $gid;
						$data['money'] = $reward2 * $reward2_to_rechar;
						$data['createtime'] = time();
						$data['uid'] = $id;
						$where['uid'] = $gid;
						$data['yue'] = M('store')->where($where)->getField('fengmi_num');
						M('tuijian_reward')->add($data); 
						//商家jm奖励记录插入数据库
						$data['pid'] = $gid;
						$data['money'] = $reward2 * $reward2_to_shopEta;
						$data['createtime'] = time();
						$data['uid'] = $id;
						$where['uid'] = $gid;
						$data['yue'] = M('store')->where($where)->getField('cangku_num');
						M('shangjiajm_reward')->add($data);
					}

				}
				//判断是否是二次以后充值
				$uid=$user['uid'];
				$recharge2Data = M('recharge')->where("uid=$uid and examine=1")->count();
				if($recharge2Data>=3){
					//获取会员数据
					$level_info = M('vip_level')->select();
					//判断父级会员消费金额
					$pid = M('user')->where(array('userid'=>$user['uid']))->getField('pid');
					if($pid!=0){
						$ppay_money = M('user')->where("userid = $pid")->getField('pay_money');
						//判断父级会员等级
						foreach ($level_info as $k => $v) {
							if($ppay_money>=$v['pay_money'] && $plevel<$v['level']){
								$plevel = $v['level'];
							}
						}
						switch ($plevel) {
							case '1':
								# code...
								$rechar3_firstReward =  M('config')->where(array('name'=>'rechar3_firstReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'reward3_to_shopEta'))->getField('value');
								break;
							case '2':
								$rechar3_firstReward =  M('config')->where(array('name'=>'level2_rechar3_firstReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level2_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level2_reward3_to_shopEta'))->getField('value');
								break;
							case '3':
								$rechar3_firstReward =  M('config')->where(array('name'=>'level3_rechar3_firstReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level3_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level3_reward3_to_shopEta'))->getField('value');
								break;
							case '4':
								$rechar3_firstReward =  M('config')->where(array('name'=>'level4_rechar3_firstReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level4_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level4_reward3_to_shopEta'))->getField('value');
								break;
							case '5':
								$rechar3_firstReward =  M('config')->where(array('name'=>'level5_rechar3_firstReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level5_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level5_reward3_to_shopEta'))->getField('value');
								break;
							case '6':
								$rechar3_firstReward =  M('config')->where(array('name'=>'level6_rechar3_firstReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level6_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level6_reward3_to_shopEta'))->getField('value');
								break;
							case '7':
								$rechar3_firstReward =  M('config')->where(array('name'=>'level7_rechar3_firstReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level7_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level7_reward3_to_shopEta'))->getField('value');
								break;
							case '8':
								$rechar3_firstReward =  M('config')->where(array('name'=>'level8_rechar3_firstReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level8_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level8_reward3_to_shopEta'))->getField('value');
								break;
							case '9':
								$rechar3_firstReward =  M('config')->where(array('name'=>'level9_rechar3_firstReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level9_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level9_reward3_to_shopEta'))->getField('value');
								break;
						}
						//奖励总金额
						$reward3 = $user['money'] * $rechar3_firstReward;
						//修改用户余额
						M('store')->where(array('uid'=>$pid))->setInc('fengmi_num',$reward3 * $reward3_to_rechar);
						M('store')->where(array('uid'=>$pid))->setInc('cangku_num',$reward3 * $reward3_to_shopEta);
						//兑换卡奖励记录插入数据库
						$data['pid'] = $pid;
						$data['money'] = $reward3 * $reward3_to_rechar;
						$data['createtime'] = time();
						$data['uid'] = $id;
						$where['uid'] = $pid;
						$data['yue'] = M('store')->where($where)->getField('fengmi_num');
						M('tuijian_reward')->add($data); 
						//商家jm奖励记录插入数据库
						$data['pid'] = $pid;
						$data['money'] = $reward3 * $reward3_to_shopEta;
						$data['createtime'] = time();
						$data['uid'] = $id;
						$where['uid'] = $pid;
						$data['yue'] = M('store')->where($where)->getField('cangku_num');
						M('shangjiajm_reward')->add($data);
					}
					//判断父父级会员消费金额
					$gid = M('user')->where(array('userid'=>$pid))->getField('pid');
					if($gid!=0){
						$gpay_money = M('user')->where("userid = $gid")->getField('pay_money');
						//判断父父级会员等级
						foreach ($level_info as $k => $v) {
							if($gpay_money>=$v['pay_money'] && $plevel<$v['level']){
								$glevel = $v['level'];
							}
						}
						switch ($glevel) {
							case '1':
								# code...
								$rechar3_secondReward =  M('config')->where(array('name'=>'rechar3_secondReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'reward3_to_shopEta'))->getField('value');
								break;
							case '2':
								$rechar3_secondReward =  M('config')->where(array('name'=>'level2_rechar3_secondReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level2_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level2_reward3_to_shopEta'))->getField('value');
								break;
							case '3':
								$rechar3_secondReward =  M('config')->where(array('name'=>'level3_rechar3_secondReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level3_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level3_reward3_to_shopEta'))->getField('value');
								break;
							case '4':
								$rechar3_secondReward =  M('config')->where(array('name'=>'level4_rechar3_secondReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level4_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level4_reward3_to_shopEta'))->getField('value');
								break;
							case '5':
								$rechar3_secondReward =  M('config')->where(array('name'=>'level5_rechar3_secondReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level5_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level5_reward3_to_shopEta'))->getField('value');
								break;
							case '6':
								$rechar3_secondReward =  M('config')->where(array('name'=>'level6_rechar3_secondReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level6_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level6_reward3_to_shopEta'))->getField('value');
								break;
							case '7':
								$rechar3_secondReward =  M('config')->where(array('name'=>'level7_rechar3_secondReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level7_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level7_reward3_to_shopEta'))->getField('value');
								break;
							case '8':
								$rechar3_secondReward =  M('config')->where(array('name'=>'level8_rechar3_secondReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level8_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level8_reward3_to_shopEta'))->getField('value');
								break;
							case '9':
								$rechar3_secondReward =  M('config')->where(array('name'=>'level9_rechar3_secondReward'))->getField('value');
								$reward3_to_rechar =  M('config')->where(array('name'=>'level9_reward3_to_rechar'))->getField('value');
								$reward3_to_shopEta =  M('config')->where(array('name'=>'level9_reward3_to_shopEta'))->getField('value');
								break;
						}
						//奖励总金额
						$reward3 = $user['money'] * $rechar3_secondReward;
						//修改用户余额
						M('store')->where(array('uid'=>$gid))->setInc('fengmi_num',$reward3 * $reward3_to_rechar);
						M('store')->where(array('uid'=>$gid))->setInc('cangku_num',$reward3 * $reward3_to_shopEta);
						//兑换卡奖励记录插入数据库
						$data['pid'] = $gid;
						$data['money'] = $reward3 * $reward3_to_rechar;
						$data['createtime'] = time();
						$data['uid'] = $id;
						$where['uid'] = $gid;
						$data['yue'] = M('store')->where($where)->getField('fengmi_num');
						M('tuijian_reward')->add($data); 
						//商家jm奖励记录插入数据库
						$data['pid'] = $gid;
						$data['money'] = $reward3 * $reward3_to_shopEta;
						$data['createtime'] = time();
						$data['uid'] = $id;
						$where['uid'] = $gid;
						$data['yue'] = M('store')->where($where)->getField('cangku_num');
						M('shangjiajm_reward')->add($data);
					}
				}
				$this->success('审核成功');
			}else{
				$this->error('审核失败');
			}
		}


		/**
		 * 用户提现
		 */

		public function withdrawal(){


			$trans = M('trans t')->join('ysk_user u ON t.payout_id=u.userid');
			$where = array('trans_type'=>1);
			//分页

			$p=getpage($trans,$where,15);
			$page=$p->show();

			$list = $trans->order('pay_state asc,id desc')->select();
			foreach($list as $k => $v){
				if($v['card_type'] == 'wx'){
					$list[$k]['with_account'] = M('uwx')->where(array('id'=>$v['card_id']))->getField('wx_num');
				}else{
					$list[$k]['with_account'] = M('ualipay')->where(array('id'=>$v['card_id']))->getField('alipay_num');
				}
			}
			$this->assign('table_data_page',$page);
			$this->assign('list',$list);
			$this->display();
		}


		public function upload(){
			if(empty($_FILES)){
				$this->error("请选择上传文件！");
			}else{
				$upload = new \Think\Upload();// 实例化上传类
				$upload->maxSize   = 3145728 ;// 设置附件上传大小
				$upload->exts      = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
				$upload->rootPath  = './Uploads/'; // 设置附件上传根目录
				$upload->savePath  = ''; // 设置附件上传（子）目录
				// 上传文件
				$inf  =   $upload->upload();
				if(!$inf) {// 上传错误提示错误信息
					$this->error($upload->getError());
				}else{// 上传成功 获取上传文件信息
					return $inf;
				}
			}
		}

		//后台提现不通过
		public function with_purch_no(){
			$id = (int)I('id');
			M()->startTrans();
			$res = M('trans')->where("id = $id")->setField('pay_state',4);
			$payout_id = M('trans')->where("id = $id")->getField('payout_id');
			$pay_nums = M('trans')->where("id = $id")->getField('pay_nums');
            $sell_type = M('trans')->where("id = $id")->getField('sell_type');
			//手续费
			if($sell_type == 1){
            	$transfer_ratio = M('config')->where(array('name'=>'transfer_ratio'))->getField('value');
			}elseif($sell_type == 2){
				$transfer_ratio = M('config')->where(array('name'=>'cash2_ratio'))->getField('value');
			}
            $num = $pay_nums / $transfer_ratio;
            if($sell_type == 1){
            	$sqlname = 'cangku_num';
            }elseif($sell_type == 2){
            	$sqlname = 'cangku2_num';
            }
            $res2 = M('store')->where("uid = $payout_id")->setInc($sqlname,$num);
            if($res !== false && $res2 !== false){
            	M()->commit();
            	echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
            	echo "<script>alert('订单不通过成功');location.href='".U('user/withdrawal')."';</script>";
            }else{
            	M()->rollback();
            	echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
            	echo "<script>alert('订单不通过失败');location.href='".U('user/withdrawal')."';</script>";
            }
		}

		/*
		* 后台买入
		*/


		public function with_purch(){
			if(IS_POST){
				$id = (int)I('id');
				if($_FILES['files']['tmp_name'])
				{
					$inf=$this->upload();
					
					$data['trans_img']=$inf['files']['savepath'].$inf['files']['savename'];
					
				}
				$data['pay_state'] = 2;
				$data['payin_id'] = 1;
				$res = M('trans')->where(array('id'=>$id))->save($data);
				if($res !== false){
					echo "<script>alert('确认订单成功');location.href='".U('user/withdrawal')."';</script>";
				}else{
					echo "<script>alert('确认订单失败');</script>";
				}
			}
			
			$id = I('id');
			$detail = M('trans t')->join('ysk_user u ON t.payout_id=u.userid')->where(array('t.id'=>$id))->field('t.id,t.payout_id,u.username,u.mobile,t.pay_nums')->find();
			$wx = M('uwx')->where(array('userid'=>$detail['payout_id'],'is_default'=>1))->find();
			$alipay = M('ualipay')->where(array('userid'=>$detail['payout_id']))->order('id asc')->find();

			$this->assign('detail',$detail);
			$this->assign('wx',$wx);
			$this->assign('alipay',$alipay);
			$this->display();
		}



		/**
		 * 用户记录
		 */
		public function record(){
				// 获取所有用户
				$tranmoney   = M('tranmoney t');  
				// 搜索
				$querytype  = I('querytype','','string');
				$keyword    = I('keyword', '', 'string');
				if($querytype){
						if($querytype == 1){
								$map['t.get_type'] = array('eq', 11);
						}else{
								$map['t.get_type'] = array('eq', 12);
						}
				}else{
						$map['t.get_type'] = array('in', '0');
				}
				if($keyword){
						$map['t.get_id'] = array('eq', $keyword);
				}
				$order_str='t.id desc';
				//分页
				$table=$tranmoney->join('ysk_user u on t.get_id = u.userid','left');
				$p=getpage($table,$map,15);
				$page=$p->show();
				$data_list = $table
						->field('u.userid,u.username,u.account,u.mobile,t.get_nums,t.pay_id,t.createtime,t.get_type')
						->where($map)
						->order($order_str)
						->select();

				
				// var_dump($data_list);
				// exit;
				if(!empty($data_list)){
						foreach($data_list as $k => $v){
								if($v['get_type'] == 0){
										$data_list[$k]['type'] = '转账';
										$payuser = M('user')->field('userid,username,account,mobile')->where(array('userid'=>$v['pay_id']))->find();
										$data_list[$k]['payusername']  = $payuser['username'];
										$data_list[$k]['payaccount']  = $payuser['account'];
										$data_list[$k]['paymobile']  = $payuser['mobile'];
								}
						}
				}
				$this->assign('list',$data_list);
				$this->assign('table_data_page',$page);
				$this->display();
		}
		/**
		 * 新增用户
		 * 
		 */
		public function add()
		{
				if (IS_POST) {

					 $admin_kucun=M('admin_kucun');//平台仓库表
						#查询平台总充值了多少水果
					 $kucun_info=$admin_kucun->order('id')->find();
					 $less_num=$kucun_info['less_num'];
					 $kucun_id=$kucun_info['id'];
					 if ($less_num < 300) {
								$this->error('平台库存不足'); 
					 }


						// 提交数据
						$user_object = D('User');

						$data        = $user_object->create();
						if(!$data){
								$this->error($user_object->getError());
						}
						$parent=I('post.paccount');
						if(empty($parent)){
								$this->error('上级不能为空');
						}
						$where['account']=$parent;
						$p_info=$user_object->where($where)->field('userid,pid,username,mobile')->find();
						if(empty($p_info)){
								$this->error('上级账号错误或不存在');
						}

						$pid=$p_info['userid']; //上级ID

						$data['pid']=$p_info['userid'];
						$gid=$p_info['pid'];//上上级ID
						if($gid){
								$data['gid']=$gid;
						}

						//登录密码加密
						$salt= substr(md5(time()),0,3);
						$data['login_pwd']=$user_object->pwdMd5($data['login_pwd'],$salt);
						$data['login_salt']=$salt;
						//交易密码加密
						$data['safety_pwd']=$user_object->pwdMd5($data['safety_pwd'],$salt);
						$data['safety_salt']=$salt;

						$user_object->startTrans();
						if ($data) {
								$result = $user_object->add($data);
								if ($result) {
										$uid=$result;
										//为新会员创建仓库和土地
										if(!D('Home/Store')->CreateCangku(300,$result)){
												$user_object->rollback();
												$this->error('仓库创建失败');
										}

										//判断他直推的人是多少奖励稻草人
										$count=$user_object->where(array('pid'=>$pid))->count(1); 
										if($count>=10){

												if($count>=10 && $count<20){
													$ren=1;
												}
												if($count>=20 && $count<30){
													$ren=2;
												}
												if($count>=30 && $count<40){
													$ren=3;
												}
												if($count>=40){
													$ren=4;
												}
												if($ren){
													M('user_level')->where(array('uid'=>$pid))->setField('dcr_num',$ren);
												}
										}

										//给推荐人奖励20个种子
										$table=M('user_seed');
										$seed_where['uid']=$pid;
										$count=$table->where($seed_where)->count(1);
										if($count==0){
											$data['uid']=$pid;
											$data['zhongzi_num']=20;
											$table->where($seed_where)->add($data);
										}else{
											$table->where($where)->setInc('zhongzi_num',20);
										}


										
										//添加种子明细
										$zz['uid']=$pid;
										$zz['recommond_id']=$uid;
										$zz['recommond_account']=$data['account'];
										$zz['recommond_name']=$data['username'].'(后台注册)';
										$zz['seed_num']=20;
										$zz['time']=time();
										$hdzz=M('zhongzijiangli')->data($zz)->add();



										//减少系统总库存
										if(!$admin_kucun->where(array('id'=>$kucun_id))->setDec('less_num',300)){
												$user_object->rollback();
												$this->error('操作失败');
										}

										//把数据记录到流水明细
										 $m_info=session('user_auth');
										 $manage_id=$m_info['uid'];
										 $data['manage_id']=$manage_id;//管理者ID
										 $data['manage_name']=$m_info['username'];
										 $data['uid']=$result; //用户ID
										 $data['guozi_num']=300; //转账数量
										 $data['create_time']=time();
										 $data['before_cangku_num']=0; //转账前仓库数量
										 $data['after_cangku_num']=300; //转账后仓库数量
										 $data['ip']=get_client_ip();
										 $data['type']=1;
										 $data['content']='后台注册会员:'.$data['account'];
										 $data['username']=$data['username'];
										 $data['account']=$data['account'];
										 $jl=M('admin_zhuangz')->data($data)->add();



										$user_object->commit();
										$this->success('操作成功', U('index'));
								} else {
										$user_object->rollback();
										$this->error('操作失败', $user_object->getError());
								}
						} else {
								$this->error($user_object->getError());
						}
				} else {
							 
								$this->display();
				}
		}

		/**
		 * 编辑用户
		 * 
		 */
		public function edit($id)
		{
				if (IS_POST) {
						if(empty($_POST['login_pwd'])){
								unset($_POST['relogin_pwd']);
						}
						if(empty($_POST['safety_pwd'])){
								unset($_POST['resafety_pwd']);
						}


						// 提交数据
						$user_object = D('User');
						$data        = $user_object->create();

						//如果没有密码，去掉密码字段
						if(empty($data['login_pwd']) || trim($data['login_pwd'])==''){
								unset($data['login_pwd']);
						}
						else{
							$salt= substr(md5(time()),0,3);
							 $data['login_pwd']=$user_object->pwdMd5($data['login_pwd'],$salt);
							 $data['login_salt']=$salt;
						}
						if(empty($data['safety_pwd']) || trim($data['safety_pwd'])==''){
								unset($data['safety_pwd']);
						}
						else{
							$salt= substr(md5(time()),0,3);
							 $data['safety_pwd']=$user_object->pwdMd5($data['safety_pwd'],$salt);
							 $data['safety_salt']=$salt;
						}

						// if(empty($data['quanxian']) ){
						// 		$data['quanxian'] = '';
						// }
						// else{

						// 	$quanxian= join("-",$data['quanxian']);
						// 	 $data['quanxian']=$quanxian;
						// }


						$data['out_jm'] = I('out_jm');
						$data['to_jm'] = I('to_jm');
						$data['recharge'] = I('recharge');
						$data['tixian'] = I('tixian');
						$data['exchange'] = I('exchange');
						$data['pay_shangjiajm'] = I('pay_shangjiajm');
						$data['pay_xiaofeijm'] = I('pay_xiaofeijm');
						$data['pay_duihuan'] = I('pay_duihuan');
						if ($data) {
						 // var_dump($data);die;
								$result = $user_object
										->field('userid,account,quanxian,username,mobile,email,safety_pwd,safety_salt,login_pwd,login_salt,sex,out_jm,to_jm,recharge,tixian,exchange,pay_shangjiajm,pay_xiaofeijm,pay_duihuan')
										->save($data);
								if ($result) {
										$this->success('更新成功', U('index'));
								} else {
										$this->error('更新失败', $user_object->getError());
								}
						} else {
								$this->error($user_object->getError());
						}
				} else {

						// 获取账号信息
						$info = D('User')->find($id);
						unset($info['password']);
						$parent=D('User')->where(array('userid'=>$info['pid']))->getField('account');
						$info['parent']=$parent ? $parent :'无';
						$quanxian=explode("-",$info['quanxian']);
						$this->assign('info',$info);
						$this->assign('quanxian',$quanxian);
						//var_dump($quanxian);die;
						$this->display();
				}
		}

		/**
		 * 设置一条或者多条数据的状态
		 * 
		 */
		public function setStatus($model = CONTROLLER_NAME)
		{
				$ids = I('request.ids');
				if (is_array($ids)) {
						if (in_array('1', $ids)) {
								$this->error('超级管理员不允许操作');
						}
				} else {
						if ($ids === '1') {
								$this->error('超级管理员不允许操作');
						}
				}
				parent::setStatus($model);
		}


 /**
		 * 设置会员隐蔽的状态
		 * 
		 */
		public function setStatus1($model = CONTROLLER_NAME)
		{
				$id =(int)I('request.id');    
				$userid =(int)I('request.userid');    
				
				 $user_object = D('User');    
				$result=D('User')->where(array('userid'=>$userid))->setField('yinbi',$id);
				if ($result) {
										$this->success('更新成功', U('index'));
				 }else {
										$this->error('更新失败', $user_object->getError());
								}
		}



		 /**
		 * 编辑用户
		 * 
		 */
		public function AddFruits($id)
		{
				if (IS_POST) {
							
					 $dbst=M('store');
					 $dbazg=M('admin_zhuangz'); // 播发给用户记录表
					 $admin_kucun=M('admin_kucun');//平台仓库表
					 $uid=I('post.userid',0,'intval');
					 $cangku_num=I('post.cangku_num');
					 if(empty($cangku_num)){
								$this->error('数量不能为空');
					 }
					 if(!preg_match('/^[1-9]\d*$/',$cangku_num)){
							 $this->error('请输入整数');
					 }
						$opetype=I('post.opetype');

						if($opetype < 1){
								$this->error('请选择操作类型');
						}
						if($opetype == 1){
							$sqlname='cangku_num';
						}elseif($opetype == 2){
							$sqlname='fengmi_num';
						}elseif($opetype==3){
							$sqlname = 'recharge_num';
						}elseif($opetype==4){
							$sqlname = 'cangku2_num';
						}
					$dbst->startTrans();

					//判断库存是否还大于0
					$add_cangku=I('post.add_cangku');
					$des_cangku=I('post.des_cangku');
					#++++添加+++++
					if(!empty($add_cangku) && empty($des_cangku)){
						$before_cangku_num=$dbst->where('uid='.$uid)->getField($sqlname);
						$up=$dbst->where('uid='.$uid)->setInc($sqlname,$cangku_num);

							//添加余额记录
							$pay_n = M('store')->where(array('uid' => $uid))->getfield($sqlname);
							$jifen_dochange['now_nums'] = $pay_n;
							$jifen_dochange['now_nums_get'] = $pay_n;
							$jifen_dochange['is_release'] = 1;
							$jifen_dochange['pay_id'] = 0;
							$jifen_dochange['get_id'] = $uid;
							$jifen_dochange['get_nums'] = $cangku_num;
							$jifen_dochange['createtime'] = time();
							if($sqlname=="cangku_num"){
								$jifen_dochange['get_type'] = 11; //余额
							}else{
								$jifen_dochange['get_type'] = 12; //积分
							}

							$res_addres = M('tranmoney')->add($jifen_dochange);
								if ($up) {
									$dbst->commit();
									$this->success('修改成功');
								}else{
									$dbst->rollback();
									$this->error('修改失败');
								}

					}
					#++++减少+++++
					if(empty($add_cangku) && !empty($des_cangku))
					{
								$up=$dbst->where('uid='.$uid)->setDec($sqlname,$cangku_num);

							//添加积分记录
							$pay_n = M('store')->where(array('uid' => $uid))->getfield($sqlname);
							$jifen_dochange['now_nums'] = $pay_n;
							$jifen_dochange['now_nums_get'] = $pay_n;
							$jifen_dochange['is_release'] = 1;
							$jifen_dochange['pay_id'] = 0;
							$jifen_dochange['get_id'] = $uid;
							$jifen_dochange['get_nums'] = -$cangku_num;
							$jifen_dochange['createtime'] = time();
							if($sqlname=="cangku_num"){
							$jifen_dochange['get_type'] = 11; //余额
							}else{
							$jifen_dochange['get_type'] = 12; //积分
							}

							$res_addres = M('tranmoney')->add($jifen_dochange);

								if(!$up){
									$dbst->rollback();
								}
							if ($up) {
									$dbst->commit();
									$this->success('修改成功');

							}else{
									$dbst->rollback();
									$this->error('修改失败');
							} 

					}



					


				} else {

						// 获取账号信息
						$info = D('User')->field('userid,username,account')->find($id);
						$cangku_num=D('store')->where(array('uid'=>$info['userid']))->getField('cangku_num');
						$fengmi_num=D('store')->where(array('uid'=>$info['userid']))->getField('fengmi_num');
						$cangku2_num=D('store')->where(array('uid'=>$info['userid']))->getField('cangku2_num');
						$recharge_num = D('store')->where(array('uid'=>$info['userid']))->getField('recharge_num');
						$info['cangku_num']=$cangku_num;
						$info['fengmi_num']=$fengmi_num;
						$info['recharge_num'] = $recharge_num;
						$info['cangku2_num'] = $cangku2_num;
						
						$this->assign('info',$info);
						$this->display();
				}
		}

		//用户登录
		public function userlogin(){
				$userid=I('userid',0,'intval');
				$user=D('Home/User');
				$info=$user->find($userid);
				if(empty($info)){
						return false;
				}

				$login_id=$user->auto_login($info);
				if($login_id){
						session('in_time',time());
						session('login_from_admin','admin',10800);
						$this->redirect('Home/Index/index');
				}
		}
		
		
		
		
		//升级会员
		public function UpgradeMembers(){
			 // 搜索
				$keyword    = I('keyword', '', 'string');
				$querytype  = I('querytype','id','string');
				$status     = I('status');
				if($keyword){
						$condition = $keyword ;
						$map[$querytype] = $condition;
				}


				 //按日期搜索
				$date=date_query('pay_time');
				if($date){
						$where=$date;
						if(isset($map))
								$map=array_merge($map,$where);
						else
								$map=$where;
				}

//      if($level!=''){
//          $map['a.level']=$level;
//      }

				//获取所有用户
				$trans   = M('trans a');
				if(!isset($map)){
						$map=true;
				}
				$map['pay_nums']=1800;

		
				//========排序=========
				$order_str='a.id desc';

				//========排序=========

				//分页
				$table=$trans->join('ysk_store b on a.card_id=b.uid','left');
				$p=getpage($table,$map,15);
				$page=$p->show();
		
		 $data_list=$table->field('a.id,a.payout_id,a.payin_id,a.pay_nums,a.pay_state,a.pay_time,a.pay_no,a.card_id,a.trade_notes,a.trans_type,a.trans_img,a.get_moneytime,a.out_card')
						->where(array('pay_nums'=>1800))
						->order($order_str)
						->select();
					
				 //取管理员会员列表的权限
				$uids= is_login();
				$hylbs="1,2,3,4,5";
				$auth_id    = M('admin')->where(array('id'=>$uids))->getField('auth_id');
				if($auth_id<>1){
				$auth_id    = M('admin')->where(array('id'=>$uids))->getField('auth_id');
				$hylbs    = M('group')->where(array('auth_id'=>$auth_id))->getField('hylb');

				}
				$hylb=explode(",",$hylbs);
				$this->assign('hylb',$hylb);
				$this->assign('list',$data_list);
				
				$this->assign('table_data_page',$page);
				$this->display();
		}
		
		
		public function jiaoyi(){
			$id=I('get.id');
			$data['pay_state']=1;
			$data['card_id']=1;
			$data['payout_id']=1;
			$res=M('trans')->where(array('id'=>$id))->save($data);
			if($res){
				$this->success('已确认交易，等待用户打款');
			}else{
				$this->error('交易失败');
			}
		}
		
		
		public function queren(){
			$id=I('get.id');
			$data['pay_state']=3;
			$data['get_moneytime']=time();
			$res=M('trans')->where(array('id'=>$id))->save($data);
			
			$uid=M('trans')->where(array('id'=>$id))->getField('payin_id');
			
			if($res){
				M('store')->where(array('uid'=>$uid))->setInc('cangku_num',1800);
				
				$this->success('已确认，顶单完成');
			}else{
				$this->error('确认失败');
			}
		}
		
		public function userrecharge(){
			$data = M('recharge')->field('uid,pay_nums,pay_state,pay_time,pay_no,trans_img,id')->select();/*dump($data);exit;*/
			$id = I('get.id');
			if ($id) {
				$check = M('recharge')->where('id='.$id)->getField('pay_state');/*dump($check);exit;*/
				if ($check == 0) {
          $pay_state = I('get.pay_state');
          $uid = I('get.uid');
          $pay_nums = I('get.pay_nums');
					if ($pay_state == 1) {
						$tg = M('recharge')->where('id='.$id)->save(array('pay_state'=>1));

            $ck = M('store')->where(array('uid' => $uid))->getField('cangku_num');
            $cx['cangku_num'] = $ck+$pay_nums;
            $res_back = M('store')->where(array('uid'=>$uid))->save($cx);

						header('Location:'.U('User/userrecharge'));
						exit;
					}else{
						$btg = M('recharge')->where('id='.$id)->save(array('pay_state'=>2));
						header('Location:'.U('User/userrecharge'));
						exit;
					}
				}
			}/*dump($tg);dump($btg);exit;*/
			$this->assign('data',$data);
			$this->assign('id',$id);
			$this->assign('table_data_page',$page);
			$this->display();
		}
}
