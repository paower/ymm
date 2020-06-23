<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends CommonController
{
    //每天JM金额减少
    public function auto_jian_sign()
    {
        $id = M('user')->field('userid')->select();
        foreach ($id as $k => $v) {
            $uid = $v['userid'];
            $fengmi_num = M('store')->where(array('uid'=>$uid))->getField('fengmi_num');
            $reward = M('config')->where(array('name'=>'reward'))->getField('value');
            $add_cangku = number_format($fengmi_num * $reward,2);
            $store_data['fengmi_num'] = array('exp','fengmi_num - '.$add_cangku);
            M('store')->where(array('uid'=>$uid))->save($store_data);
        }
    }

    public function index()
    {

        $userid = session('userid');
        $where['userid'] = $userid;
        $uid = session('userid');


        $pic_array = $this->get_banner();
        
        $uinfo = M('user')->where($where)->field('img_head,userid,user_credit,is_reward,today_releas,quanxian,is_sign,sign_time')->find();
        $moneyinfo = M('store')->where(array('uid' => $userid))->field('cangku_num,fengmi_num,recharge_num,cangku2_num')->find();
        //今日可领取收益
        $time= date('Y-m-d',time());
        $time = strtotime($time);


        // 今日币升值
        $this->appreciation();
        


        // 检测今天是否签到
        $start = strtotime(date('Y-m-d'));
// dump(date('Y-m-d H:i:s',$start));exit;
        if($uinfo['sign_time'] < $start){
            $res = M('user')->where(array('userid'=>$userid))->setField('is_sign',0);
        }
        
        

       	// $is_release = M('tranmoney')->where(array('get_id'=>$userid,'get_type'=>14,'get_time'=>$time))->order('get_time desc')->limit(1)->getField('is_release');
       	// if(empty($is_release)){
       	// 	$is_release = 0;
       	// }
       	// $can_get =M('tranmoney')->where(array('get_id'=>$userid,'get_type'=>14))->order('get_time desc')->limit(1)->getField('get_nums');

        if($uinfo['is_sign'] == 0){
            $is_release = 0;
            $can_get = M('reward')->where(array('id'=>1))->getField('qd_price');
        }else{
            $is_release = 1;
        }

        // echo 

        //今日是否已经领取释放收益
        if (IS_AJAX) {
            if ($can_get > 0) {
            	// $data['is_release']=1;
                // $sa=M('tranmoney')->where(array('get_id'=>$userid,'get_type'=>14,'get_time'=>$time))->order('get_time desc')->limit(1)->save($data);
                
                $data['sign_time'] = time();
                $data['is_sign'] = 1;
                $res = M('user')->where(array('userid'=>$userid))->save($data);


	            if($res){    
	                $res = '签到成功';
	                
	                M('store')->where(array('uid'=>$userid))->setInc('fengmi_num',$can_get);
                    M('coins')->where(array('name'=>'MXC'))->setInc('yhcy_num',$can_get);
                    M('coins')->where(array('name'=>'MXC'))->setDec('sqyy_num',$can_get);

                    $sign['now_nums'] = $moneyinfo['fengmi_num'] + $can_get;
                    $sign['now_nums_get'] = $moneyinfo['fengmi_num'] + $can_get;
                    $sign['pay_id'] = $userid;
                    $sign['get_id'] = $userid;
                    $sign['get_nums'] = $can_get;
                    $sign['get_time'] = time();
                    $sign['get_type'] = 25;
                    $res_addres = M('tranmoney')->add($sign);
	                
	                ajaxReturn($res, 1, '/Index/index');
	            }
            }
        }
        $bili = M('coins')->where(array('name'=>'MXC'))->getField('bili');
        $where = 'id <> 1';
        $newinfo = M('news')->where($where)->limit(5)->select();
        $lastsy = M('wbao_detail')->where(array('uid'=>$uid,'is_dj'=>0))->sum('num');
        $this->assign(array(
            'uinfo' => $uinfo,
            'moneyinfo' => $moneyinfo,
            'is_release'=>$is_release,
            'can_get' => $can_get,
            'is_setnums' => $is_setnums,
            'pic_array'=>$pic_array,
            'bili'   => $bili,
            'newinfo' => $newinfo,
            'lastsy' => $lastsy,
        ));
        $this->display('/Index/index');
    }


  /*
  * 轮播私有方法链接数据库
  */
	private function get_banner()
	{
	    $user_object   = M('banner');
	    $data_list = $user_object->order('sort asc')->select();
	    return $data_list;
    }
    

    public function Dotrrela()
    {
        if (IS_AJAX) {
            $userid = session('userid');
            //是否存在当日转账释放红包
            $startime = date('Y-m-d');
            $endtime = date("Y-m-d", strtotime("+1 day"));
            $todaystime = strtotime($startime);
            $endtime = strtotime($endtime);
            $whereres['get_id'] = $userid;
            $whereres['is_release'] = 0;
            $whereres['get_type'] = 22;
            $whereres['get_time'] = array('between', array($todaystime, $endtime));
            $is_setnums = M('tranmoney')->where($whereres)->sum('get_nums') + 0;
            if ($is_setnums > 0) {
                $datapay['cangku_num'] = array('exp', 'cangku_num + ' . $is_setnums);
                $datapay['fengmi_num'] = array('exp', 'fengmi_num - ' . $is_setnums);
                $res_pay = M('store')->where(array('uid' => $userid))->save($datapay);//每日银积分释放金积分

                //添加释放记录
                $jifen_nums = $is_setnums;
                $jifen_dochange['pay_id'] = $userid;
                $jifen_dochange['get_id'] = $userid;
                $jifen_dochange['get_nums'] = $jifen_nums;
                $jifen_dochange['get_time'] = time();
                $jifen_dochange['get_type'] = 2;
                $res_addres = M('tranmoney')->add($jifen_dochange);
                //改成已释放
                $savedata['is_release'] = 1;
                $savedata['get_time'] = time();
                $is_setnums = M('tranmoney')->where($whereres)->save($savedata);
                if ($is_setnums) {
                    ajaxReturn('转账银积分释放成功', 1);
                } else {
                    ajaxReturn('转账银积分释放失败', 0);
                }
            }
        }
    }

    //金积分记录
    public function Bancerecord()
    {
        $traInfo = M('tranmoney');
        $uid = session('userid');
        $where['pay_id|get_id'] = $uid;
        $where['get_type'] = array('not in', '1,11,12,22,23');
        //分页
        $p = getpage($traInfo, $where, 50);
        $page = $p->show();
        $Chan_info = $traInfo->where($where)->order('id desc')->select();

        
        foreach ($Chan_info as $k => $v) {


            $Chan_info[$k]['get_timeymd'] = date('Y-m-d', $v['get_time']);
            $Chan_info[$k]['get_timedate'] = date('H:i:s', $v['get_time']);
            //转入转出
            if ($uid == $v['pay_id']) {
                $Chan_info[$k]['trtype'] = 1;
                    if($v['get_type']==5){//当自己是求购方支付金积分，因挂求购单时已支付了金积分，故不存在
                        unset($Chan_info[$k]);

                    }

            } else {
                $Chan_info[$k]['trtype'] = 2;
            }


        }
        if (IS_AJAX) {
            if (count($Chan_info) >= 1) {
                ajaxReturn($Chan_info, 1);
            } else {
                ajaxReturn('暂无记录', 0);
            }
        }
        $this->assign('page', $page);
        $this->assign('Chan_info', $Chan_info);
        $this->assign('uid', $uid);
        $this->display();
    }

    /**
     * 充值记录
     */
    public function recharge_record(){
        $uid = session('userid');
        $list = M('recharge')->where(array('uid'=>$uid))->select();
        $where['pay_id|get_id'] = $uid;
        $where['get_type'] = array('in', '37,30');
        $list2 = M('tranmoney')->where($where)->select();
        $where2['pid'] = $uid;
        //$list3 = M('tuijian_reward')->where($where2)->select();
        $list4 = array_merge($list,$list2);
        //$list4 = array_merge($list4,$list3);
        array_multisort(array_column($list4,'createtime'),SORT_DESC,$list4);
        $this->assign('list',$list4);
        // $this->assign('list2',$list2);
        // $this->assign('list3',$list3);
        $this->display();
    }

    /**
     * 余额记录
     */
    public function balance_record(){
        $type = I('type');
        $traInfo = M('tranmoney');
        $uid = session('userid');
        $where['pay_id|get_id'] = $uid;
        if($type == 0){
            $where['get_type'] = array('in', '40,36,2,3,4,5,6,7,8,9,10,13');
        }elseif($type==1){
            $where['get_type'] = array('in','41,39,31,34,35,38');
        }
        //分页
        $p = getpage($traInfo, $where, 50);
        $page = $p->show();
        $Chan_info = $traInfo->where($where)->order('createtime desc')->select();
        if($type==0){
			$list = M('shangjiajm_reward')->where("pid = $uid")->select();
			$Chan_info = array_merge($Chan_info,$list);
			array_multisort(array_column($Chan_info,'createtime'),SORT_DESC,$Chan_info);
		}
        $this->assign('list',$list);

        foreach ($Chan_info as $k => $v) {


            $Chan_info[$k]['get_timeymd'] = date('Y-m-d', $v['createtime']);
            $Chan_info[$k]['get_timedate'] = date('H:i:s', $v['createtime']);
            //转入转出
            if ($uid == $v['pay_id']) {
                $Chan_info[$k]['trtype'] = 1;
                    if($v['get_type']==5){//当自己是求购方支付金积分，因挂求购单时已支付了金积分，故不存在
                        unset($Chan_info[$k]);

                    }

            } else {
                $Chan_info[$k]['trtype'] = 2;
            }


        }
        if (IS_AJAX) {
            if (count($Chan_info) >= 1) {
                ajaxReturn($Chan_info, 1);
            } else {
                ajaxReturn('暂无记录', 0);
            }
        }
		// $Chan_info = array_merge($Chan_info,$list);
        // array_multisort(array_column($Chan_info,'createtime'),SORT_DESC,$Chan_info);
        $this->assign('Chan_info', $Chan_info);
        $this->assign('uid', $uid);
        $this->display();
    }

    /**
     * 积分记录
     */

    public function integral_record(){
        $traInfo = M('tranmoney');
        $uid = session('userid');
        $where['pay_id|get_id'] = $uid;
        $where['get_type'] = array('not in', '0,1,4,11,12,22,23,31,30,34,9,10,35,36,37,38,39,40');
        //分页
        $p = getpage($traInfo, $where, 50);
        $page = $p->show();
        $Chan_info = $traInfo->where($where)->order('id desc')->select();
        $list3 = M('tuijian_reward')->where("pid = $uid")->select();
        $Chan_info = array_merge($Chan_info,$list3);
        array_multisort(array_column($Chan_info,'createtime'),SORT_DESC,$Chan_info);
        foreach ($Chan_info as $k => $v) {


            $Chan_info[$k]['get_timeymd'] = date('Y-m-d', $v['createtime']);
            $Chan_info[$k]['get_timedate'] = date('H:i:s', $v['createtime']);
            //转入转出
            if ($uid == $v['pay_id']) {
                $Chan_info[$k]['trtype'] = 1;
                    if($v['get_type']==5){//当自己是求购方支付金积分，因挂求购单时已支付了金积分，故不存在
                        unset($Chan_info[$k]);

                    }

            } else {
                $Chan_info[$k]['trtype'] = 2;
            }


        }
        if (IS_AJAX) {
            if (count($Chan_info) >= 1) {
                ajaxReturn($Chan_info, 1);
            } else {
                ajaxReturn('暂无记录', 0);
            }
        }
        $this->assign('Chan_info', $Chan_info);
        $this->assign('uid', $uid);
        $this->display();
    }

    //转出
    public function Turnout()
    {
        $id = session('userid');
        $out_jm = M('user')->where("userid=$id")->getField('out_jm');
        //判断网站是否关闭
         // $close=is_close_out_jm();
         // if($close['value']==0){
         if($out_jm==0){
             success_alert('转出暂时关闭',U('index/index'));
         }
        if (IS_AJAX) {
            $uinfo = trim(I('uinfo'));
            //手机号码或者用户id
            $map['userid|mobile'] = $uinfo;

           // dump($map);die;
            $issetU = M('user')->where($map)->field('userid,username')->find();
            $userid = session('userid');

            if ($userid == $issetU['userid']) {
                ajaxReturn('您不能给自己转账哦~', 0);
            }
            if ($issetU) {
                $url = '/Index/Changeout/sid/' . $issetU['userid'];
                ajaxReturn($url, 1);
            } else {
                ajaxReturn('并不存在该用户哦~', 0);
            }
        }
        $this->display();
    }


    public function Changeout()
    {
        $sid = trim(I('sid'));
        $uinfo = M('user as us')->JOIN('ysk_store as ms')->where(array('us.userid' => $sid))->field('us.mobile,us.userid,us.img_head,us.username,ms.cangku_num')->find();
        $exchange_ratio = M('config')->where(array('name'=>'exchange_ratio'))->getField('value');
		$uid = session('userid');
		$money = M('store')->where(array('uid'=>$uid))->find();
        if (IS_AJAX) {
            $data = $_POST['post_data'];
            $trid = trim($data['zuid']);
            $paytype = trim($data['paytype']);
            $paynums = $data['paynums'];
            $mobila = trim($data['mobila']);
            $pwd = trim(I('pwd'));
            $uid = session('userid');
            $type = (int)trim($data['type']);


            $info2=$paynums%1;

            if($paynums<1){

                ajaxReturn('不得小于1',0);

              }

            if($info2){

                 ajaxReturn('请输入1的倍数',0);
            
                }

            M()->startTrans();

            //验证交易密码
            $minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
            $user_object = D('Home/User');
            $user_info = $user_object->Trans($minepwd['account'], $pwd);
            //验证手机号码后四位
            $is_setm['userid|mobile'] = $trid;
            $tmobile = M('user')->where($is_setm)->getfield('mobile');
            $tmobile = substr($tmobile, -4);
            if ($tmobile != $mobila) {
                ajaxReturn('您输入的手机号码后四位有误', 0);
            }
            if ($paynums <= 0) {
                ajaxReturn('您输入的转账金额有误哦~', 0);
            }
            if ($uid == $trid) {
                ajaxReturn('您不能给自己转账哦~', 0);
            }
            if($type==1){
                $mine_money = M('store')->where(array('uid' => $uid))->getfield('cangku2_num');
            }else{
                $mine_money = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
            }

            if ($mine_money < $paynums) {
                ajaxReturn('您余额暂无这么多哦~', 0);
            }
            // $tper = $paynums * 20 / 100;
            // $eper = $paynums * 80 / 100;
            if($type == 1 ){
                $datapay['cangku2_num'] = array('exp', 'cangku2_num - ' . $paynums);
            }else{
                $datapay['cangku_num'] = array('exp','cangku_num - '.$paynums);
            }
            // $datapay['fengmi_num'] = array('exp', 'fengmi_num + ' . $eper);
            $res_pay = M('store')->where(array('uid' => $uid))->save($datapay);//转出的人+80%银积分

            //收到cangku值为乘比例
            $cash_paynums = $paynums * $exchange_ratio;
            if($type == 1){
                $dataget['cangku2_num'] = array('exp', "cangku2_num + $cash_paynums");
            }else{
                $dataget['cangku_num'] = array('exp','cangku_num + '.$cash_paynums);
            }
            // $dataget['fengmi_num'] = array('exp', 'fengmi_num + ' . $tper);
            $res_get = M('store')->where(array('uid' => $trid))->save($dataget);//转入的人+20%银积分


			// $pay_ny = M('store')->where(array('uid' => $uid))->getfield('fengmi_num');
            // $get_ny = M('store')->where(array('uid' => $trid))->getfield('fengmi_num');

            //转入的人+20%银积分记录SSS
            // $changenums['pay_id'] = $uid;
            // $changenums['get_id'] = $trid;
            // $changenums['now_nums'] = $pay_ny;
            // $changenums['now_nums_get'] = $get_ny;
            // $changenums['get_nums'] = $tper;
            // $changenums['is_release'] = 1;
            // $changenums['get_time'] = time();
            // $changenums['get_type'] = 1;
            // M('tranmoney')->add($changenums);
 
            //转入的人+20%银积分记录EEE
//            $jifen_nums = $tper * 2 / 1000;
//            $jifen_dochange['pay_id'] = $trid;
//            $jifen_dochange['get_id'] = $trid;
//            $jifen_dochange['get_nums'] = $jifen_nums;
//            $jifen_dochange['get_time'] = time();
//            $jifen_dochange['get_type'] = 22;
//            M('tranmoney')->add($jifen_dochange);
            //对应20%银积分释放到金积分SSS
//            $jifen_donums['cangku_num'] = array('exp', "cangku_num + $jifen_nums");
//            $jifen_donums['fengmi_num'] = array('exp', 'fengmi_num - ' . $jifen_nums);
//            $res_get = M('store')->where(array('uid' => $trid))->save($jifen_donums);//转入的人+20%银积分


                //金积分转动奖---没有触发  
                $this->zhuand15($uid,$paynums);//转出方15层得到转动奖
                

                $this->zhuand15($trid,$eper);//转入方15层得到转动奖

            //判断用户等级
            $uChanlev = D('Home/index');
            $uChanlev->Checklevel($trid);
            //执行转账
            if($type == 1){
                $pay_n = M('store')->where(array('uid' => $uid))->getfield('cangku2_num');
                $get_n = M('store')->where(array('uid' => $trid))->getfield('cangku2_num');
            }else{
                $pay_n = M('store')->where(array('uid' => $uid))->getfield('cangku_num');
                $get_n = M('store')->where(array('uid' => $trid))->getfield('cangku_num');
            }
             

            $add_data['pay_id'] = $uid;
            $add_data['get_id'] = $trid;
            $add_data['get_nums'] = $paynums;
            if($type==1){
                $add_data['get_type'] = 39;
            }else{
                $add_data['get_type'] = 40;
            }
            $add_data['now_nums'] = $pay_n;                
            $add_data['now_nums_get'] =$get_n;                
            $add_data['is_release'] =1;                
            $add_data['createtime'] = time();

            $add_Dets = M('tranmoney')->add($add_data);
            if ($add_Dets&&$res_get&&$res_pay) {
                M()->commit();
                ajaxReturn('转账成功哦~', 1, '/Index/index');
            }else{
                M()->rollback();
                ajaxReturn('转账失败~', 0);
            }
        }
        $this->assign('exchange_ratio',$exchange_ratio);
        $this->assign('uinfo', $uinfo);
        $this->assign('money', $money);
        $this->display();
    }

    //金积分转银积分

    public function test()
    {
          $userid = session('userid');

       

    }


 public function get_between($input, $start, $end) {
    $substr = substr($input, strlen($start)+strpos($input, $start),(strlen($input) - strpos($input, $end))*(-1));
    return $substr;

}


    //管理奖和直推奖， 管理拿2-4代
    private function Manage_reward($uid,$paynums){

    $Lasts = D('Home/index');
    $Lastinfo = $Lasts->Getlasts($uid, 15, 'path');  
    if (count($Lastinfo) > 0) {

        $Manage_b = M('config')->where(array('group' => 6, 'status' => 1))->order('id asc')->select();//分享奖比例
        $Manage_a = M('config')->where(array('group' => 7, 'status' => 1))->order('id asc')->select();//管理奖比例
   
        foreach ($Lastinfo as $k => $v) {
  
            if (!empty($v)) {//当前会员信息
              

                    if($k==0) {//第一代，即为直推奖 

                        $u_Grade = M('user')->where(array('userid' => $v))->getfield('use_grade');
                        $direct_fee=0;
                        if($u_Grade>0)$direct_fee=(float)$Manage_b[$u_Grade-1]["value"];//判断是什么比例

                        $zhitui_reward = $direct_fee / 100 * $paynums;//直推的人所得分享奖
                        M('user')->where(array('userid' => $v))->setInc('releas_rate', $zhitui_reward);
                    }

                    if ($k>0&&$k<=3) {//2-4代,拿直推的人的分享奖*相应比例，即为管理奖
                         $t=$k-1; 
                         $zhitui_num = M('user')->where(array('pid' => $v))->count(1);//计算直推人数
                         $suoxu_num=(int)$Manage_a[$t]["tip"];
                        if($zhitui_num>=$suoxu_num){//直推人数满足条件

                            $My_reward=$Manage_a[$t]["value"]/100*$zhitui_reward;                          
                            $res_Incrate = M('user')->where(array('userid' => $v))->setInc('releas_rate', $My_reward);
                               
                        }
                    }                  
                   
                
            }//if
        }//foreach

    }
  }

    //区块奖和VIP奖   区块拿15层
    private function Addreas15($uid,$paynums){

    $Lasts = D('Home/index');
    $Lastinfo = $Lasts->Getlasts($uid, 15, 'path');
    if (count($Lastinfo) > 0) {
        $add_relinfo = M('config')->where(array('group' => 9, 'status' => 1))->order('id asc')->select();
        $vips = M('config')->where(array('group' => 10, 'status' => 1))->order('id asc')->select();
        $i = 0;
        $n = 0;
        foreach ($Lastinfo as $k => $v) {
            //查询当前自己等级
            if (!empty($v)) {

                    $zhitui_num = M('user')->where(array('pid' => $v))->count(1);//计算直推人数
                    $t=$k+1;
                    $tkey =0;
                    $daishu=array(3,6,9,12,15);
                    foreach ($daishu as $key1 => $value1) {
                     if($t>$value1)$tkey=$key1+1;
                    }
                    
                    $suoxu_num=(int)$add_relinfo[$tkey]["tip"];
                    if($zhitui_num>=$suoxu_num){//直推人数满足条件 得区块奖

                                $Lastone = $My_reward=$add_relinfo[$tkey]["value"]/100*$paynums; 
                                $res_Incrate = M('user')->where(array('userid' => $v))->setInc('releas_rate', $Lastone);

                                
                    }

                    //VIP奖，有集差，加速释放
                    $v_Grade = M('user')->where(array('userid' => $v))->getfield('vip_grade');

                    if(($v_Grade == 1 && $i == 0)||($v_Grade == 2 && $i == 0)){//VIP1奖

                            $u_get_money = $vips[0]['value'] / 100 * $paynums;
                            $res_Add = M('user')->where(array('userid' => $v))->setInc('releas_rate', $u_get_money);
                            $i++;
                            
                            
                    }elseif($v_Grade==2 && $i!=0 &&$n==0){//VIP2奖
                         $u_get_money = $vips[1]['value'] / 100 * $paynums;
                         $res_Add = M('user')->where(array('userid' => $v))->setInc('releas_rate', $u_get_money);
                         $n++;

                    }



               
            }//if
        }//foreach

     }
}


public function signin(){
    $uid = session('userid');
    $where = 'id <> 1';
    $newinfo = M('news')->where($where)->limit(5)->select();
    $fengmi_num = M('store')->where(array('uid'=>$uid))->getField('fengmi_num');
    
    $is_sign = M('sign')->where('uid='.$uid)->order('id desc')->find();

    $today = date('Y-m-d',time());
    $sign_time = date('Y-m-d',$is_sign['signtime']);
    if($today != $sign_time){
        $is_sign = 1;
    }else{
        $is_sign = 0;
    }

    $reward = M('config')->where(array('name'=>'reward'))->getField('value');
    
    
    $add_cangku = number_format($fengmi_num * $reward,2);
    
    $guanggao = M('guanggao')->where(array('status'=>1))->select();
	
	//dump($guanggao); die();
    $this->assign('guanggao',$guanggao);

    $this->assign('is_sign',$is_sign);
    $this->assign('sign_num',$add_cangku);
    $this->assign('newinfo',$newinfo);
    $this->display();
}

public function is_sign(){
    $uid = session('userid');

    $fengmi_num = M('store')->where(array('uid'=>$uid))->getField('fengmi_num');
    if($fengmi_num <= 0){
        $data['msg'] = '您还没获得积分';
        $data['code'] = 0;
        exit(json_encode($data));
    }
    $time = strtotime(date('Y-m-d'));
    $is_sign = M('sign')->where(array('uid'=>$uid,'signtime'=>array('egt',$time)))->count();
    
    if(!$is_sign){
        $arr = [
            'uid' => $uid,
            'signtime' => time()
        ];
        M()->startTrans();
        $res = M('sign')->add($arr);

        $fengmi_num = M('store')->where(array('uid'=>$uid))->getField('fengmi_num');

        $reward = M('config')->where(array('name'=>'reward'))->getField('value');
        
        $add_cangku = number_format($fengmi_num * $reward,2);
        
        if($fengmi_num != 0){
            // $store_data['fengmi_num'] = array('exp','fengmi_num - '.$add_cangku);
            $store_data['cangku2_num'] = array('exp','cangku2_num + '.$add_cangku);
            $res_store = M('store')->where(array('uid'=>$uid))->save($store_data);
        }else{
            $res_store = true;
        }
        

        $fengmi_num = M('store')->where(array('uid'=>$uid))->getField('fengmi_num');
        
        //积分减少记录
        $tran['pay_id'] = $uid;
        $tran['get_id'] = $uid;
        $tran['get_nums'] = -$add_cangku;
        $tran['createtime'] = time();
        $tran['get_type'] = 33;
        $tran['now_nums'] = $fengmi_num;
        $tran['now_nums_get'] = $fengmi_num;
        $feng_tran = M('tranmoney')->add($tran);

        $cangku_num = M('store')->where(array('uid'=>$uid))->getField('cangku2_num');
        $tranc['pay_id'] = $uid;
        $tranc['get_id'] = $uid;
        $tranc['get_nums'] = $add_cangku;
        $tranc['createtime'] = time();
        $tranc['get_type'] = 34;
        $tranc['now_nums'] = $cangku_num;
        $tranc['now_nums_get'] = $cangku_num;
        $cang_tran = M('tranmoney')->add($tranc);
        if($res&&$feng_tran&&$cang_tran&&$res_store){
            M()->commit();
            $data['msg'] = '签到成功,已获得'.$add_cangku.'Eta';
            $data['code'] = 0;
            exit(json_encode($data));
        }else{
            M()->rollback();
            $data['msg'] = '签到失败';
            $data['code'] = 0;
            exit(json_encode($data));
        }
    }else{
        $data['msg'] = '已签到';
        $data['code'] = 1;
        exit(json_encode($data));
    }
}



    //金积分转动奖  拿15层
    public function zhuand15($uid,$paynums)
    {
            $Lasts = D('Home/index');
            $Lastinfo = $Lasts->Getlasts($uid, 8, 'path');  
            if (count($Lastinfo) > 0) {
                    
                    $Manage_b = M('config')->where(array('group' => 8, 'status' => 1))->order('id asc')->select();//金积分转动奖比例   
                    foreach ($Lastinfo as $k => $v) {
              
                        if (!empty($v)) {//当前会员信息

                                    $u_Grade = M('user')->where(array('userid' => $v))->getfield('use_grade');
                                    $direct_fee=0;
                                    if($u_Grade>0)$direct_fee=(float)$Manage_b[$u_Grade-1]["value"];//判断是什么比例

                                    $zhuand_reward = $direct_fee / 100 * $paynums;//我得到转动奖的加速
                                    M('user')->where(array('userid' => $v))->setInc('releas_rate', $zhuand_reward);
                        }
                    }

            }


    }


    public function Turncords()
    {
        $traInfo = M('tranmoney');
        $uid = session('userid');
        $where['pay_id'] = $uid;
        $where['get_type'] = 0;
        //分页
        $p = getpage($traInfo, $where, 20);
        $page = $p->show();
        $Chan_info = $traInfo->where($where)->order('id desc')->select();
        foreach ($Chan_info as $k => $v) {
            $getinfos = M('user')->where(array('userid' => $v['get_id']))->Field('img_head,username')->find();
            $Chan_info[$k]['imghead'] = $getinfos['img_head'];
            $Chan_info[$k]['guname'] = $getinfos['username'];

        }
        if (IS_AJAX) {
            if (count($Chan_info) >= 1) {
                ajaxReturn($Chan_info, 1);
            } else {
                ajaxReturn('暂无记录', 0);
            }
        }
        $this->assign('page', $page);
        $this->assign('Chan_info', $Chan_info);
        $this->assign('uid', $uid);

        $this->display();
    }


    //根据关系进行分销
    public function Doprofit($uid, $paynums, $type)
    {
        $Lasts = D('Home/index');
        $Lastinfo = $Lasts->Getlasts($uid, 15, 'path');
        //数量的多少
        if($type == 1){
            $paynums = $paynums * 20/100;
        }else{
            $paynums = $paynums;

        }
        if (count($Lastinfo) > 0) {
            $this->Addreas($uid,$Lastinfo,$paynums,$type);//加速银积分释放
        }
    }

    //加速银积分释放【银积分基础释放， 1代银积分加速释放，2-15代，2代vip ，2-15代vip银积分】
    private function Addreas($uid,$Lastinfo,$paynums,$type){
        //银积分加速释放率
        $add_relinfo = M('config')->where(array('group' => 4, 'status' => 1))->order('id asc')->select();
        $i = 0;
        foreach ($Lastinfo as $k => $v) {
            $k = $k + 1;
            //查询当前自己等级
            if (!empty($v)) {
                //当前会员信息 等级字段
                $u_Grade = M('user')->where(array('userid' => $v))->getfield('use_grade');

                if ($u_Grade >= 1) {
                    if ($k == 1) {
                        $release_bili = $add_relinfo[1]['value'];
                    } else {
                        $release_bili = $add_relinfo[2]['value'];
                    }
                    $Lastone = $release_bili / 100 * $paynums;
                    //加速银积分释放
                    $res_Incrate = M('user')->where(array('userid' => $v))->setInc('releas_rate', $Lastone);

                    //增加银积分
                        $u_get_money = $add_relinfo[3]['value'] / 100 * $paynums;
                        if($u_Grade == 3 && $i == 0){
                            $res_Add = M('store')->where(array('uid' => $v))->setInc('fengmi_num', $u_get_money);//给上级会员加银积分
                            if ($res_Add) {
                                $earns['pay_id'] = $uid;
                                $earns['get_id'] = $v;
                                $earns['get_nums'] = $u_get_money;
                                $earns['get_level'] = $k;
                                $earns['get_types'] = $type;
                                $earns['get_time'] = time();
                                $res_Earn = M('moneyils')->add($earns);

                                // $jifendets['pay_id'] = $uid;
                                // $jifendets['get_id'] = $v;
                                // $jifendets['get_nums'] = $u_get_money;
                                // $jifendets['get_time'] = time();
                                // $jifendets['get_type'] = 1;
                                // M('tranmoney')->add($jifendets);
                                $i++;
                            }
                    }
                }
            }//if
        }//foreach
    }


    //转出记录
    public function Outrecords()
    {
        $traInfo = M('tranmoney');
        $uid = session('userid');
        $where['pay_id|get_id'] = $uid;
        $where['get_type'] = 0;
        //分页
        $p = getpage($traInfo, $where, 50);
        $page = $p->show();
        $Chan_info = $traInfo->where($where)->order('id desc')->select();
        foreach ($Chan_info as $k => $v) {
            $Chan_info[$k]['get_timeymd'] = date('Y-m-d', $v['get_time']);
            $Chan_info[$k]['get_timedate'] = date('H:i:s', $v['get_time']);
            //转入转出
            if ($uid == $v['pay_id']) {
                $Chan_info[$k]['trtype'] = 1;
            } else {
                $Chan_info[$k]['trtype'] = 2;
            }
        }
        if (IS_AJAX) {
            if (count($Chan_info) >= 1) {
                ajaxReturn($Chan_info, 1);
            } else {
                ajaxReturn('暂无记录', 0);
            }
        }
        $this->assign('page', $page);
        $this->assign('Chan_info', $Chan_info);
        $this->assign('uid', $uid);
        $this->display();
    }

   

//兑换银积分
    public function Exehange()
    {
        $id = session('userid');
        $exchange = M("user")->where("userid = $id")->getField("exchange");
        //判断兑换是否关闭
         // $close=is_close_exchange();
         // if($close['value']==0){
         if($exchange==0){
             success_alert('兑换暂时关闭',U('index/index'));
         }
        $uid = session('userid');
        $minems = M('store')->where(array('uid' => $uid))->find();
        $rechar_balance = M('config')->where(array('name'=>'rechar_balance'))->getField('value');
        $rechar_integral = M('config')->where(array('name'=>'rechar_integral'))->getField('value');
        // $bili=M('coins')->where(array('name'=>'MXC'))->getField('bili');
        // $bili = number_format($bili,4);
        if (IS_AJAX) {
            $dhnums = I('dhnums');
            $pwd = I('pwd');
            $type = I('post.type');
            if ($dhnums < 100) {
                $this->ajaxReturn('最少兑换数量为100哦~', 0);
            }
            if ($dhnums % 100 != 0) {
                $this->ajaxReturn('兑换数量必须为100的倍数哦~', 0);
            }
            if ($dhnums > $minems['recharge_num']) {
                ajaxReturn('您账户暂时没有这么多兑换卡余额', 0);
            }
            switch ($type) {
                case '1':
                    $bili = $rechar_balance;
                    break;
                
                case '2':
                    $bili = $rechar_integral;
                    break;
                default:
                    ajaxReturn('系统出错，请尝试刷新一下',0);
                    break;
            }
            
            //验证交易密码
            $minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
            $user_object = D('Home/User');
            $user_info = $user_object->Trans($minepwd['account'], $pwd);
            $dataget = M('store')->where(array('uid' => $uid))->find();
            M()->startTrans();
            switch ($type) {
                case '1':
                    // 充值兑换余额
                    $canget = $dhnums * $bili;
                    // $canget = number_format($canget,2);
                    $dataget['recharge_num'] = $dataget['recharge_num'] - $dhnums;
                    $dataget['cangku2_num'] = $dataget['cangku2_num'] + $canget;
                    $res_get = M('store')->where(array('uid' => $uid))->save($dataget);//金积分转入银积分
                    break;
                
                case '2':
                    // 充值兑换积分
                    $canget = $dhnums * $bili;
                    // $canget = number_format($canget,2);
                    $dataget['recharge_num'] = array('exp', "recharge_num - $dhnums");
                    $dataget['fengmi_num'] = array('exp', 'fengmi_num + ' . $canget);
                    $res_get = M('store')->where(array('uid' => $uid))->save($dataget);
                    break;
            }

            //查找当前账户余额
            $is_yue = M('store')->where(array('uid' => $uid))->getField('recharge_num');
           //执行兑换
            if ($res_get && $type == 1) {
                $datac['pay_id'] = $uid;
                $datac['get_id'] = $uid;
                $datac['now_nums'] = $is_yue;
                $datac['now_nums_get'] = $is_yue;
                $datac['is_release'] = 1;                
                $datac['get_nums'] = -$dhnums;
                $datac['createtime'] = time();
                $datac['get_type'] = 30;

                // 查找当前账户的余额
                $pay_n = M('store')->where(array('uid' => $uid))->getfield('cangku2_num');
                $data['pay_id'] = $uid;
                $data['get_id'] = $uid;
                $data['now_nums'] = $pay_n;
                $data['now_nums_get'] = $pay_n;
                $data['is_release'] = 1;                
                $data['get_nums'] = $canget;
                $data['createtime'] = time();
                $data['get_type'] = 31;
            }elseif($res_get && $type == 2){
                $datac['pay_id'] = $uid;
                $datac['get_id'] = $uid;
                $datac['now_nums'] = $is_yue;
                $datac['now_nums_get'] = $is_yue;
                $datac['is_release'] = 1;                
                $datac['get_nums'] = -$dhnums ;
                $datac['createtime'] = time();
                $datac['get_type'] = 30;

                // 查找当前账户的积分
                $pay_n = M('store')->where(array('uid' => $uid))->getfield('fengmi_num');
                $data['pay_id'] = $uid;
                $data['get_id'] = $uid;
                $data['now_nums'] = $pay_n;
                $data['now_nums_get'] = $pay_n;
                $data['is_release'] = 1;                
                $data['get_nums'] = $canget;
                $data['createtime'] = time();
                $data['get_type'] = 32;
            }

            $add_Detsc = M('tranmoney')->add($datac);

            $add_Dets = M('tranmoney')->add($data);
            if($res_get && $add_Detsc && $add_Dets){
                M()->commit();
                
                //判断用户等级
                $uChanlev = D('Home/index');
                $uChanlev->Checklevel($uid);
                ajaxReturn('兑换积分成功', 1, '/Index/exehange');
            }else{
                M()->rollback();
                ajaxReturn('兑换失败，请重新尝试',0);
            }
        }
        
        $instructions = M('exchange_instructions')->select();
        $this->assign('info',$instructions);

        $this->assign('minems', $minems);
        
        
        $this->assign('rechar_balance',$rechar_balance);
        $this->assign('rechar_integral',$rechar_integral);
        
        $this->display();
    }
  
    //银积分记录
    public function Exchangerecords()
    {
        $uid = session('userid');
        
        // $type=I('get.type');
        // if(isset($type)){
        //     $str='1,';
        // }
        // if($type==1){
        // 	$str="1,25";
        // }
        // if($type==2){
        // 	$str="24,26";
        // }
        // if($type==3){
        // 	$str="77,27";
        // }
        // if($type==4){
        // 	$str="23,28";
        // }
        
        $where['get_id|pay_id'] = $uid;
        $where['get_type'] = array('in', '30,31,32');
        // $where['get_type'] = 1;
        $traInfo = M('tranmoney');
        //分页
        $p = getpage($traInfo, $where, 50);
        $page = $p->show();
        $Chan_info = $traInfo->where($where)->order('id desc')->select();
        foreach ($Chan_info as $k => $v) {
            $Chan_info[$k]['get_nums'] = $v['get_nums'];
            $Chan_info[$k]['get_timeymd'] = date('Y-m-d', $v['get_time']);
            $Chan_info[$k]['get_timedate'] = date('H:i:s', $v['get_time']);
            if ($uid == $v['pay_id']) {
                $Chan_info[$k]['trtype'] = 1;
            } else {
                $Chan_info[$k]['trtype'] = 2;
            }
        }
        if (IS_AJAX) {
            if (count($Chan_info) >= 1) {
                ajaxReturn($Chan_info, 1);
            } else {
                ajaxReturn('暂无记录', 0);
            }
        }
        $this->assign('uid', $uid);
        $this->assign('Chan_info', $Chan_info);
        $this->assign('page', $page);
        $this->display();
    }

    // 登陆升值
    private function appreciation(){
        $uid = session('userid');
        $starttime = strtotime(date('Y-m-d'));
        $coins=M('coins')->where("name='MXC'")->find();
    
        if($coins['todaytime'] <= $starttime){
            $add = $coins['bili'] * $coins['todayadd']/100;
            $data['add'] = number_format($add,4);
            $data['bili'] = $coins['bili'] + $add;
            $data['bili'] = number_format($data['bili'],4);
            $data['todaytime'] = time();
            M('coins')->where("name='MXC'")->save($data);
        }


    }


    //获取仓库数据
    public function StoreData()
    {
        if (!IS_AJAX) {
            return false;
        }
        $store = D('Store');
        $userid = get_userid();
        $where['uid'] = $userid;
        $s_info = $store->field('cangku_num,fengmi_num,plant_num,huafei_total')->where($where)->find();

        $data['cangku'] = $s_info['cangku_num'] + 0;
        // $data['fengmi']=$s_info['fengmi_num']+0;
        $data['plant'] = $s_info['plant_num'] + 0;
        // $data['huafei_total']=$s_info['huafei_total']+0;
        // $data['total']=$s_info['cangku_num']+$s_info['plant_num'];
        $this->ajaxReturn($data);
    }

    //果树数据
    public function landdata()
    {
        if (!IS_AJAX) {
            return false;
        }
        $table = M('nzusfarm');
        $uid = session('userid');
        $where['uid'] = $uid;
        $where['status'] = array('gt', 0);
        $info = $table->field('id,seeds+fruits as num,farm_type type,status')->where($where)->order('id')->select();
        if ($info) {
            $this->ajaxReturn($info);
        }

    }


    public function tooldata()
    {
        if (!IS_AJAX) {
            return false;
        }

        $tree = M('config')->where(array('id' => array('in', array(8, 10, 12, 36))))->order('id asc')->field('value as price,id')->select();
        $tool = M('tool')->field('t_num as price,id')->order('id asc')->select();
        $data = array_merge($tree, $tool);
        if (empty($data)) {
            ajaxReturn('数据加载失败');
        } else {
            ajaxReturn('数据加载成功', 1, '', $data);
        }
    }

    //一键采蜜和狗粮
    public function onefooddata()
    {
        if (!IS_AJAX) {
            return false;
        }

        $where['uid'] = session('userid');
        $data = M('user_tool_month')->field('oneclick one,end_oneclick_time endo,dogfood food,end_dogfood_time endf')->where($where)->find();

        if (empty($data)) {
            ajaxReturn(null);
        } else {
            $time = time();
            if ($data['one'] > 0) {
                if ($time > $data['endo'])
                    $data['one_status'] = '已过期';
                else
                    $data['one_status'] = '使用中';

                $data['endo1'] = date('Y-m-d', $data['endo']);
            }
            if ($data['food'] > 0) {
                if ($time > $data['endf'])
                    $data['food_status'] = '已过期';
                else
                    $data['food_status'] = '使用中';

                $data['endf1'] = date('Y-m-d', $data['endf']);
            }
            ajaxReturn('数据加载成功', 1, '', $data);
        }
    }

    /**
     * 站内信
     */
    public function znx()
    {
        if (IS_AJAX) {
            $db_letter = M('nzletter');
            $userid = session('userid');

            $userInfo = session('user_login');

            $data['recipient_id'] = 0;
            $data['send_id'] = $userid;
            $data['title'] = trim(I('post.title'));
            $data['content'] = trim(I('post.content'));
            $data['username'] = $userInfo['username'];
            $data['account'] = $userInfo['account'];

            if (empty($data['title']) || empty($data['content'])) {
                ajaxReturn('标题或内容不能为空');
                return;
            }

            $data['time'] = time();
            $res = $db_letter->data($data)->add();
            if ($res) {
                ajaxReturn('我们已收到，会尽快处理您的问题', 1);
            } else {
                ajaxReturn('提交失败');
            }
        }

    }


    //购买
    public function buytool()
    {
        if (!IS_AJAX) {
            return false;
        }

        $id = I('post.id', 0, 'intval');
        $num = I('post.num', 1, 'intval');
        $typetree = I('post.type');
        if (empty($id)) {
            ajaxReturn('参数错误');
        }

        $uid = session('userid');
        if ($typetree == 'tree') {

            if ($id == 8 || $id == 36) {
                $type = 1;
            } elseif ($id == 10) {
                $type = 2;
            } elseif ($id == 12) {
                $type = 3;
            } else {
                ajaxReturn("操作失败");
            }
            //最低数
            $config = D('config');
            $min_guozi = $config->where(array('id' => $id))->getField('value');

            $des_num = $min_guozi;
            $is_land = no_land();
            if ($is_land && $id != 36) {
                $des_num = $des_num + 30;
            }

            $t_info['t_num'] = $des_num;
            $t_info['t_name'] = '树';
            $t_info['t_img'] = '';
            $num = 1;
            $order_type = 1; //树
        } else {

            $t_info = M('tool')->find($id);
            if (empty($t_info)) {
                ajaxReturn('参数错误');
            }

            //判断是否已拥有，如果已拥有，不在购买
            $type = $t_info['t_type'];
            if ($type == 2) {
                $field = $t_info['t_fieldname'];
                $isbuytool = M('user_level')->where(array('uid' => $uid))->getField($field);
                if ($isbuytool > 0) {
                    ajaxReturn('您已经拥有了哦！');
                }
            }
            $order_type = 0; //道具
        }


        $data['tool_id'] = $id;
        $data['tool_name'] = $t_info['t_name'];
        $data['tool_price'] = $t_info['t_num'];
        $data['tool_img'] = $t_info['t_img'];
        $data['order_status'] = 0;
        $data['order_no'] = date('YmdHis');
        $data['tool_num'] = $num;
        $data['total_price'] = $num * $t_info['t_num'];
        $data['uid'] = $uid;
        $data['order_type'] = $order_type;


        $order = M('order');
        $order->startTrans();
        $res = $order->add($data);
        if ($res) {
            $url = U('Index/orderdetail', array('order_no' => $data['order_no']));
            ajaxReturn('购买成功', 1, $url);
        } else {
            ajaxReturn('购买失败');
            $order->startTrans();
        }
    }


    //选择支付
    public function orderdetail()
    {
        $order_no = I('order_no');
        $order_no = safe_replace($order_no);
        if (empty($order_no)) {
            return false;
        }
        $where['order_no'] = $order_no;
        $where['order_status'] = 0;
        $order = M('order');
        $o_info = $order->where($where)->find();
        if (empty($o_info)) {
            return false;
        }
        $uid = session('userid');
        $cangku_num = M('store')->where(array('uid' => $uid))->getField('cangku_num');
        $this->assign('o_info', $o_info)->assign('cangku_num', $cangku_num)->display();

    }

    public function gopay()
    {
        if (!IS_POST) {
            return false;
        }

        $order_paytype = I('post.paytype');
        $type_arr = array(1, 2, 3);
        if (!in_array($order_paytype, $type_arr)) {
            ajaxReturn('请选择支付方式');
        }
        $order_no = I('post.order_no');
        $order_no = safe_replace($order_no);
        if (empty($order_no)) {
            ajaxReturn('订单不存在');
        }
        $where['order_no'] = $order_no;
        $where['order_status'] = 0;
        $order = M('order');
        $count = $order->where($where)->count(1);
        if ($count == 0) {
            ajaxReturn('该订单已失效，请重新下单');
        }

        $arr = array(1 => '微信支付', 2 => '支付宝支付', 3 => '支付');
        $res = $order->where($where)->setField('order_paytype', $arr[$order_paytype]);
        $wxurl = 'http://yxgsgy.com/wxPay/example/jsapi.php?order_no=' . $order_no;
        $arr_url = array(1 => $wxurl, 2 => '', 3 => U('Ajaxdz/kaiken'));
        if ($res === false) {
            ajaxReturn('下单失败');
        } else {
            ajaxReturn('', 1, $arr_url[$order_paytype]);
        }
    }

   
}