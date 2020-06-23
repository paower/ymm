<?php

namespace SAdmin\Controller;
use Think\Controller;
class CommonController extends Controller{


     /* 初始化,权限控制,菜单显示 */
     protected function _initialize(){

     	
        // 获取当前用户ID
        define('UID',is_login());
        if(!UID){// 还没登录 跳转到登录页面
            $this->redirect('Public/login');
        }
		/* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =   api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
      
		C($config); //添加配置
		
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


	/* 空操作，用于输出404页面 */
	public function _empty(){
		// $this->display('Public:404');die();
		die('空操作');
	}

	/**
	 *跳转控制
	 */
	public function osc_alert($status){

		if($status['status']=='back'){
			$this->error($status['message']);
			die;
		}elseif($status['status']=='success'){
			$this->success($status['message'],$status['jump']);
			die;
		}elseif($status['status']=='fail'){
			$this->error($status['message'],$status['jump']);
			die;
		}
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

}
?>