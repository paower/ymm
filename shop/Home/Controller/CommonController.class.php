<?php
/**
 * 本程序仅供娱乐开发学习，如有非法用途与本公司无关，一切法律责任自负！
 */
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
	public function _initialize(){
		//判断网站是否关闭
		$close=is_close_site();
		if($close['value']==0){
		  success_alert($close['tip'],U('Login/logout'));
		}
		//验证用户登录
		$this->is_user();

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
						$order_detail = M('order_detail')->where(array('com_id'=>array('in','184'),'goods_status'=>array('in','1')))->sum('jifen_nums');
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
		$qishu = M('wbao_detail')->where(array('uid'=>$uid,'is_dj'=>0))->select();/*dump($qishu);exit;*/
		// $zuori = M('wbao_detail')->where(array('uid'=>$uid,'is_dj'=>0))->sum('num');
		$times = time();
		// dump(date('Y-m-d',$times));
		// $times = strtotime('+1 day');
		
		$zuori = 0;
		foreach ($qishu as $k => $v) {
			$diff = round(($times-$v['create_time'])/3600/24);
			$timess = M('assets')->where(array('id'=>$v['qishu']))->find();

			if ($v['qishu']==1 && $diff>=1 && $diff<=$timess['time']) {
				for($i=1;$i<=$diff;$i++){
					if($i == $diff){
						$time = $v['create_time']+$diff*86400;
						$data = array('uid'=>$uid,'time'=>$time,'shouyi'=>$v['lixi']/$timess['time']);
						$lxjl = M('lixi')->add();
					}
				}
				$zuori = $zuori+$v['lixi']/$timess['time'];

			}elseif($v['qishu']==2 && $diff >=1 && $diff<=30*$timess['time']){
				for($i=1;$i<=$diff;$i++){
					if($i == $diff){
						$time = $v['create_time']+$diff*86400;
						$data = array('uid'=>$uid,'time'=>$time,'shouyi'=>$v['lixi']/30/$timess['time']);
						$lxjl = M('lixi')->add();
					}
				}
				$zuori = $zuori+$v['lixi']/30/$timess['time'];

			}elseif($v['qishu']==3 && $diff >=1 && $diff<=30*$timess['time']){
                for($i=1;$i<=$diff;$i++){
                    if($i == $diff){
                        $time = $v['create_time']+$diff*86400;
                        $data = array('uid'=>$uid,'time'=>$time,'shouyi'=>$v['lixi']/30/$timess['time']);
                        $lxjl = M('lixi')->add();
                    }
                }
				$zuori = $zuori+$v['lixi']/30/$timess['time'];

			}elseif($v['qishu']==4 && $diff >=1 && $diff<=30*$timess['time']){
                for($i=1;$i<=$diff;$i++){
                    if($i == $diff){
                        $time = $v['create_time']+$diff*86400;
                        $data = array('uid'=>$uid,'time'=>$time,'shouyi'=>$v['lixi']/30/$timess['time']);
                        $lxjl = M('lixi')->add();
                    }
                }
				$zuori = $zuori+$v['lixi']/30/$timess['time'];

			}elseif($v['qishu']==5 && $diff >=1 && $diff<=30*$timess['time']){
                for($i=1;$i<=$diff;$i++){
                    if($i == $diff){
                        $time = $v['create_time']+$diff*86400;
                        $data = array('uid'=>$uid,'time'=>$time,'shouyi'=>$v['lixi']/30/$timess['time']);
                        $lxjl = M('lixi')->add();
                    }
                }
				$zuori = $zuori+$v['lixi']/30/$timess['time'];

			}
		}/*dump($zuori);exit;*/



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

	}


 protected function is_user(){
		$userid=user_login();
		$user=M('user');
		if(!$userid){
			$this->redirect('Login/login');
			exit();
		}

		//判断12小时后必须重新登录
		$in_time=session('in_time');
		$time_now=time();
		$between=$time_now-$in_time;
		if($between > 3600 * 24 * 5){
			$this->redirect('Login/logout');
		}

		$where['userid']=$userid;
		$u_info=$user->where($where)->field('status,session_id')->find();
		//判断用户是否锁定
		$login_from_admin=session('login_from_admin');//是否后台登录
		if($u_info['status']==0 && $login_from_admin!='admin'){
			if(IS_AJAX){
				ajaxReturn('你账号已锁定，请联系管理员',0);
			}else{
				success_alert('你账号已锁定，请联系管理员',U('Login/logout'));
				exit();
			}
		}

		//判断用户是否在他处已登录
		$session_id=session_id();
		// if($session_id != $u_info['session_id'] && empty($login_from_admin)){

			// if(IS_AJAX){
				// ajaxReturn('您的账号在他处登录，您被迫下线',0);
			// }else{
				// success_alert('您的账号在他处登录，您被迫下线',U('Login/logout'));
				// exit();
			// }
		// }
		//记录操作时间
		// session('in_time',time());
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
			$com_id=M('order_detail')->where(array('order_id' => $oid))->field('com_id,com_num')->select();
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


}

