<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller
{
    /**
     * 后台登陆
     */
	 //微信购买结果查询
     public function wxbuyresult(){
        $testxml  = file_get_contents("php://input");
        // $testxml = $GLOBALS[‘HTTP_RAW_POST_DATA’];
        $jsonxml = json_encode(simplexml_load_string($testxml, 'SimpleXMLElement', LIBXML_NOCDATA));
        $result = json_decode($jsonxml, true);//转成数组，

        if($result){
            //如果成功返回了
            $out_trade_no = $result['out_trade_no'];
                    if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
      //执行业务逻辑
                            $out_trade_no = explode('_',$out_trade_no);
                            $where['order_no'] = $out_trade_no[0];
                            $order_id = M('order')->where($where)->getField('order_id');
                            $com_id = $out_trade_no[1];
                            $map['order_id'] = $order_id;
                            $map['com_id'] = $com_id;
                            M('order_detail')->where($map)->setField('goods_status',2);
                            M('order')->where("order_id = $order_id")->setField('status',1);
                        }
                    }
        
        echo "<xml>
                  <return_code><![CDATA[SUCCESS]]></return_code>
                  <return_msg><![CDATA[OK]]></return_msg>
                </xml>";
        $this->redirect('Shop/home/index');
    }
	
	 //微信充值结果查询
     public function wxpayresult(){
        $testxml  = file_get_contents("php://input");
        // $testxml = $GLOBALS[‘HTTP_RAW_POST_DATA’];
        $jsonxml = json_encode(simplexml_load_string($testxml, 'SimpleXMLElement', LIBXML_NOCDATA));
        $result = json_decode($jsonxml, true);//转成数组，

        if($result){
            //如果成功返回了
            $out_trade_no = $result['out_trade_no'];
                    if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
      //执行业务逻辑
                        $result = M('wxrecharge')->where("out_trade_no = $out_trade_no")->getField('result_code');
                        if($result!=1){
                            $id = session('userid');
                            $data['transaction_id'] = $result['transaction_id'];
                            $data['time_end'] = $result['time_end'];
                            $data['cash_fee'] = $result['cash_fee'];
                            $data['bank_type'] = $result['bank_type'];
                            $data['result_code'] = 1;
                            M('store')->where('uid = $')->setInc('recharge_num',$result['cash_fee']/100);
                            $where['out_trade_no'] = $out_trade_no;
                            M('wxrecharge')->where($where)->save($data);
                        }
                    }
        }
        echo '<xml>
                  <return_code><![CDATA[SUCCESS]]></return_code>
                  <return_msg><![CDATA[OK]]></return_msg>
                </xml>';
        $this->redirect('index/index');
}
	 
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
    public function login()
    {
        //判断网站是否关闭
        $close=is_close_site();
        if($close['value']==0){
            $this->assign('message',$close['tip'])->display('closesite');
        }else{
            $starttime = strtotime(date('Y-m-d'));
            $coins=M('coins')->where("name='MXC'")->find();
            if($coins['todaytime'] <= $starttime){
                $add = $coins['bili'] * $coins['todayadd']/100;
                $data['add'] = number_format($add,4);
                $data['bili'] = $coins['bili'] + $add;
                $data['todaytime'] =  time();
                $data['bili'] = number_format($data['bili'],4);
                M('coins')->where("name='MXC'")->save($data);

            }
            $this->display();
        }
    }

    public function msglogin()
    {
        //判断网站是否关闭
        $close=is_close_site();
        if($close['value']==0){
            $this->assign('message',$close['tip'])->display('closesite');
        }else{
            $account = session('account');
            $this->assign('account',$account);
            $this->display();
        }
    }

    public function wshm(){
        $userid = I("uid");
        session("userid",$userid);

    }



    //注册好友
    public function register(){
        if(IS_AJAX){
            //接收数据
            $user=D('User');
            $data        = $user->create();
            if(!$data){
                ajaxReturn($user->getError(),0);
                return ;
            }

            //dump(11);
            //验证码
            $code = I('code');
            $mobile = I('mobile');
            $code2=session('code');
            $mobile2=session('mobile');
            if($code!=$code2 || $mobile!=$mobile2){
                ajaxReturn("验证码错误或已过期");
            }
            //判断仓库
            // $store=D('Store');
            $data['account']=$data['mobile'];
            //密码加密
            $salt= substr(md5(time()),0,3);
            $data['login_pwd']=$user->pwdMd5($data['login_pwd'],$salt);
            $data['login_salt']=$salt;


            $data['safety_pwd']=$user->pwdMd5($data['safety_pwd'],$salt);
            $data['safety_salt']=$salt;

            //推荐人
            $pid=$data['pid'];
            $last['userid|mobile'] = $pid;
            $p_info=$user->where(array('userid'=>$pid))->field('userid,pid,gid,username,account,mobile,path,deep')->find();
//            $p_info=$user->field('pid,gid,username,account,mobile,path,deep')->find($pid);
            $gid=$p_info['pid'];//上上级ID
            $ggid=$p_info['gid'];//上上上级ID

            if($gid){
                $data['gid']=$gid;
            }
            if($ggid){
                $data['ggid']=$ggid;
            }
            

            //拼接路径
            $path=$p_info['path'];
            $deep=$p_info['deep'];
            if(empty($path)){
                $data['path']='-'.$pid.'-';
            }else{
                $data['path']=$path.$pid.'-';
            }
            $data['deep']=$deep+1;

            $user->startTrans();//开启事务
            $uid=$user->add($data);

            if(!$uid){
                M()->rollback();
                ajaxReturn('注册失败');
            }
            
           
            $store = array();
            $store['uid'] = $uid;
            $store['cangku_num'] = 0;
            $store['fengmi_num'] = 0;
            $store['xiaofei_num'] = 0;
            $store['fenhong_num'] = 0;
            $store['tuiguang_num'] = 0;
            $res = M("store")->add($store);
		
        




            
            if($uid&&$res){
                M()->commit();

                ajaxReturn('注册成功',1,U('Login/login'));
            }
            else{
                M()->rollback();
                ajaxReturn('注册失败',0);
            }
        }


        $mobile = trim(I('mobile'));
        $parent_account = session("parent_account");
        if(empty($mobile)){
            if($parent_account){
                $mobile = $parent_account;
            }
        }
        $this->assign('mobile',$mobile);
        $this->display();
    }

    //快速登录
    public function quickLogin(){
        if (IS_AJAX) {
            $account = I('account');
            $code = I('code');

            // 验证验证码是否正确
            $user_object = D('Home/User');
            if(!check_sms($code,$account)){
                ajaxReturn('验证码错误或已过期');
            }
            $user_info   = $user_object->Quicklogin($account);
            if (!$user_info) {
                ajaxReturn($user_object->getError(),0);
            }
            // 设置登录状态
            $uid = $user_object->auto_login($user_info);
            // 跳转
            if (0 < $uid && $user_info['userid'] === $uid) {
                session('in_time',time());
                ajaxReturn('登录成功',1,U('Index/signin'));
            }else{
                ajaxReturn('签名错误',0);
            }
        }
    }

    public function checkLogin(){
        if (IS_AJAX) {
            $account = I('account');
            $password = I('password');
            // 验证用户名密码是否正确
            $user_object = D('Home/User');
            $user_info   = $user_object->login($account, $password);
            
            $start = strtotime(date('Y-m-d'));
            if($user_info['sign_time'] < $start){
                $res = M('user')->where(array('userid'=>$userid))->setField('is_sign',0);
            }
            if (!$user_info) {
                ajaxReturn($user_object->getError(),0);
            }
            session('account',$account);
            
            


            $user_info   = $user_object->Quicklogin($account);
            if (!$user_info) {
                ajaxReturn($user_object->getError(),0);
            }
            // 设置登录状态
            $uid = $user_object->auto_login($user_info);
            // 跳转
            if (0 < $uid && $user_info['userid'] === $uid) {
                session('in_time',time());
                ajaxReturn('登录成功',1,U('Index/signin'));
            }


            //ajaxReturn('请输入短信验证码',1,U('Index/index'));


//            // 设置登录状态
//            $uid = $user_object->auto_login($user_info);
//            // 跳转
//            if (0 < $uid && $user_info['userid'] === $uid) {
//                session('in_time',time());
//               ajaxReturn('登录成功',1,U('Index/index'));
//            }else{
//                ajaxReturn('签名错误',0);
//            }
        }
    }

    /**
     * 注销
     * 
     */
    public function logout()
    {   
        cookie('msg',null);
        session(null);
        $this->redirect('Login/login');
    }

    /**
     * 图片验证码生成，用于登录和注册
     * 
     */
    public function verify()
    {
        set_verify();
    }


    //找回密码
    public function getpsw(){
        
        $this->display();
    }

    public function setpsw(){
        if(!IS_AJAX)
            return ;

        $mobile=I('post.mobile');
        $code=I('post.code');
        $password=I('post.password');
        $reppassword=I('post.passwordmin');
        if(empty($mobile)){
            ajaxReturn('手机号码不能为空');
        }
        if(empty($code)){
            ajaxReturn('验证码不能为空');
        }
        if(empty($password)){
            ajaxReturn('密码不能为空');
        }
        if($password  != $reppassword){
            ajaxReturn('两次输入的密码不一致');
        }

        if(!check_sms($code,$mobile)){
            ajaxReturn('验证码错误或已过期'); 
        }

        $user=D('User');
        $mwhere['mobile']=$mobile;
        $userid=$user->where($mwhere)->getField('userid');
        if(empty($userid)){
            ajaxReturn('手机号码错误或不在系统中');
        }

        $where['userid']=$userid;
        //密码加密
        $salt=user_salt();
        $data['login_pwd']=$user->pwdMd5($password,$salt);
        $data['login_salt']=$salt;
        $res=$user->field('login_pwd,login_salt')->where($where)->save($data);
        if($res){
            session('sms_code',null);
            ajaxReturn('修改成功',1,U('Login/logout'));
        }
        else{
            ajaxReturn('修改失败');
        }

    }
    /*找回支付密码*/
    //找回密码
    public function getpswpay(){

        $this->display();
    }

    public function setpswpay(){
        if(!IS_AJAX)
            return ;
        $mobile=I('post.mobile');
        $code=I('post.code');
        $password=I('post.password');
        $reppassword=I('post.passwordmin');
        if(empty($mobile)){
            ajaxReturn('手机号码不能为空');
        }
        if(empty($code)){
            ajaxReturn('验证码不能为空');
        }
        if(empty($password)){
            ajaxReturn('密码不能为空');
        }
        if($password  != $reppassword){
            ajaxReturn('两次输入的密码不一致');
        }

        if(!check_sms($code,$mobile)){
            ajaxReturn('验证码错误或已过期');
        }

        $user=D('User');
        $mwhere['mobile']=$mobile;
        $userid=$user->where($mwhere)->getField('userid');
        if(empty($userid)){
            ajaxReturn('手机号码错误或不在系统中');
        }

        $where['userid']=$userid;
        //密码加密

        $salt=user_salt();
        $data['safety_pwd']=$user->pwdMd5($password,$salt);
        $data['safety_salt']=$salt;
        $res=$user->field('safety_pwd,safety_salt')->where($where)->save($data);
        if($res){
            session('sms_code',null);
            ajaxReturn('支付密码修改成功',1,U('Index/index'));
        }
        else{
            ajaxReturn('支付密码修改失败');
        }
    }

    /** 
 *  
 * 验证码生成 
 */  
public function verify_c(){  
    $Verify = new \Think\Verify();  
    $Verify->fontSize = 18;  
    $Verify->length   = 4;  
    $Verify->useNoise = false;  
    $Verify->codeSet = '0123456789';  
    $Verify->imageW = 130;  
    $Verify->imageH = 50;  
    //$Verify->expire = 600;  
    $Verify->entry();  
} 
/* 验证码校验 */
public function check_verify($code, $id = '')
{
    $verify = new \Think\Verify();
    $res = $verify->check($code, $id);
    $this->ajaxReturn($res, 'json');
}
    public function sendCodelogin(){
        $mobile=I('post.mobile');
        if(empty($mobile)){
            $mes['status']=0;
            $mes['message']='手机号码不能为空';
            $this->ajaxReturn($mes);
        }
        $where['mobile|userid'] = $mobile;
        $isset = M('user')->where($where)->count(1);
        if($isset < 1){
            $mes['status']=0;
            $mes['message']='手机号码不在系统中';
            $this->ajaxReturn($mes);
        }
        $this->ajaxReturn(Loginmsg($mobile));
    }

    public function sendCode(){
        $mobile=I('post.mobile');
        if(empty($mobile)){
            $mes['status']=0;
            $mes['message']='手机号码不能为空';
            $this->ajaxReturn($mes);
        } 
        if(!empty($_SERVER["HTTP_CLIENT_IP"]))
        {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        }
        else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
        {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        else if(!empty($_SERVER["REMOTE_ADDR"]))
        {
            $cip = $_SERVER["REMOTE_ADDR"];
        }
        // var_dump($cip);
        $datas=array();
        $datas['ip']=$cip;
        $datas['time']=time();
        $res=M('preventip')->where(array('ip'=>$cip))->find();
        if(empty($res)){
            $user=D('User');

            $code = rand(0,9).rand(0,9).rand(0,9).rand(0,9);   
            $content = "你的验证码是：".$code."【JAMMA】";

            session('mobile',$mobile);
            session('code',$code);

            $result=sendMsg($mobile,$content);
            // var_dump($result);
            if($result['status']==1){
               M('preventip')->add($datas);
            }
            $this->ajaxReturn("1");
        }elseif(!empty($res)){
            if(time()-$res['time'] <= 60){
                $mes=array();
                $mes['status'] = 2;
                $mes['message'] = '1分钟内禁止注册';
                $this->ajaxReturn($mes);
            }else{
                $user=D('User');
                $result=sendMsg($mobile);
                // var_dump($result);
                if($result['status']==1){
                    
                    $datas['id']=$res['id'];
                    $ress=M('preventip')->save($datas);
                 }
                $this->ajaxReturn($result);
            }
        }
       
//        if($count==1){
//            $mes['status']=0;
//            $mes['message']='手机号码已在系统中';
//            $this->ajaxReturn($mes);
//        }
        
    }

    
    public function adduser(){
        //判断是否开启交易功能
        $return=IsTrading(32);
        if($return['value']==0){
          $this->assign('content',$return['tip'])->display('Close/index');  
          exit();
        }

        $mobile=I('get.mobile');
        $this->assign('mobile',$mobile);
        $this->display();
    }

    public function saveuser(){
        if(IS_AJAX){
            //接收数据
            $user=D('User');
            $data        = $user->create();

            if(!$data){
                ajaxReturn($user->getError(),0);
                return ;
            }

            //判断仓库
            $store=D('Store');
            $data['account']=$data['mobile'];
            //密码加密
            $salt= substr(md5(time()),0,3);
            $data['login_pwd']=$user->pwdMd5($data['login_pwd'],$salt);
            $data['login_salt']=$salt;
            $data['safety_pwd']=$user->pwdMd5($data['safety_pwd'],$salt);
            $data['safety_salt']=$salt;

            //推荐人
            $pid=$data['pid'];
            $p_info=$user->field('pid,gid,username,account,mobile,path,deep')->find($pid);
            $gid=$p_info['pid'];//上上级ID
            $ggid=$p_info['gid'];//上上上级ID
            if($gid){
                $data['gid']=$gid;
            }
            if($ggid){
                $data['ggid']=$ggid;
            }

             //拼接路径
            $path=$p_info['path'];
            $deep=$p_info['deep'];
            if(empty($path)){
              $data['path']='-'.$pid.'-';
            }else{
              $data['path']=$path.$pid.'-';
            }
            $data['deep']=$deep+1;


            $user->startTrans();//开启事务
            $uid=$user->add($data);
            if(!$uid){
                $user->rollback();
                ajaxReturn('注册失败');
            }
            //为新会员创建仓库和土地
            if(!$store->CreateCangku(0,$uid)){
                $user->rollback();
                ajaxReturn('仓库创建失败，请联系管理员',0);
            }
              
            //给上级添加值推人数
            M('user_level')->where(array('uid'=>$pid))->setInc('children_num',1);
            //给用户添加等级
            AddUserLevel($pid);

            if($uid){
                $user->commit();
                ajaxReturn('注册成功',1,U('Login/login'));
            }
            else{
                $user->rollback();
                ajaxReturn('注册失败',0);
            }
        }
    }



 //TD每分钟增长任务
    public function Growem()
    {
     
  
             
    }


public function get_between($input, $start, $end) {
    $substr = substr($input, strlen($start)+strpos($input, $start),(strlen($input) - strpos($input, $end))*(-1));
    return $substr;

}

    //积分释放
    public function Relase()
    {
		
		//分红计划
		$fh_status=M('reward')->where(array('id'=>1))->getField('fh_status');
		if($fh_status==1){
			$time = date('Y-m-d',time());
	        $todaystime = strtotime($time);
			$user=M('user')->where(array('status'=>1,'vip_grade'=>array('gt',0)))->select();
			foreach($user as $key => $val){
				$time2=M('tranmoney')->where(array('get_id'=>$val['id'],'get_type'=>77,'is_release'=>1))->getField('get_time');
				if($todaystime>$time2 || empty($time2)){
					$xiaofei_num=M('store')->where(array('uid'=>$val['id'],'xiaofei_num'=>array('gt',0)))->getField('xiaofei_num');
					$xf_price=M('reward')->where(array('id'=>1))->getField('xf_price');
					
					$xf_jl=$xiaofei_num*$xf_price;
					
					//生成奖励记录
					$data['pay_id']=$val['id'];
					$data['get_id']=$val['id'];
					$data['get_nums']=$xf_jl;
					$data['get_time']=$todaystime;
					$data['get_type']=77;
					$data['now_nums']=$xiaofei_num;
					$data['now_nums_get']=$xiaofei_num;
					$data['is_release']=1;
					$res=M('tranmoney')->add($data);
					if($res){
						M('store')->where(array('uid'=>$val['id'],'xiaofei_num'=>array('gt',0)))->setInc('$xiaofei_num',$xf_jl);
						
						
						M('coins')->where(array('name'=>'MXC'))->setInc('yhcy_num',$xf_jl);
						M('coins')->where(array('name'=>'MXC'))->setDec('yxf_num',$xf_jl);
					}
				}	 
					
				
			}
		}
		
		$time = date('Y-m-d',time());
	    $todaystime = strtotime($time);
		
		//每日签到
		$user=M('user')->where(array('status'=>1))->select();
		foreach($user as $key => $val){
			$newlog=M('tranmoney')->where(array('get_id'=>$val['id'],'get_type'=>14))->order('get_time desc')->limit(1)->getField('get_time');
			if($newlog<$todaystime || empty($newlog)){
				$qd_price=M('reward')->where(array('id'=>1))->getField('qd_price');
				$now_nums=M('store')->where(array('uid'=>$val['id']))->getField('fengmi_num');
				//生成奖励记录
				$data['pay_id']=$val['id'];
				$data['get_id']=$val['id'];
				$data['get_nums']=$qd_price;
				$data['get_time']=$todaystime;
				$data['get_type']=14;
				$data['now_nums']=$now_nums;
				$data['now_nums_get']=$now_nums;
				$data['is_release']=0;
				$res=M('tranmoney')->add($data);
			}
		}
		
		
		
		//增值计划
		$q_status=M('reward')->where(array('id=1'))->getField('q_status');
		
		if($q_status==1){
			$wbao_detail=M('wbao_detail')->select();
			
			foreach($wbao_detail as $key =>$val){
				$Date_1 = date("Y-m-d",$val['create_time']);
				$Date_2 = date('Y-m-d',time());
				$d1 = strtotime($Date_1);
				$d2 = strtotime($Date_2);
				$Days = round(($d2-$d1)/3600/24);
				if($Days>0){
					$qiday=date("Y-m-d h:i:s",strtotime("+".$val['qishu']."months",strtotime($val['create_time'])));
					$qiday=strtotime($qiday);
					if($qiday>=time()){
						$index=M('zenzhilog')->where(array('uid'=>$val['uid'],'day'=>$Days,'wid'=>$val['id']))->find();
						if(empty($index)){
							$name='q'.$val['qishu'];
							$q_jl=M('reward')->where(array('id'=>1))->getField($name);
							//每日增值
							$jiangli=$val['num']*$q_jl;
							
							M('store')->where(array('uid'=>$val['uid']))->setInc('plant_num',$jiangli);
							M('coins')->where(array('name'=>'MXC'))->setInc('yhcy_num',$jiangli);
							M('coins')->where(array('name'=>'MXC'))->setDec('sqyy_num',$jiangli);
							
							
							$zenzhilog['id']=$val['id'];
							$zenzhilog['uid']=$val['uid'];
							$zenzhilog['day']=$Days;
							$zenzhilog['time']=time();
							$zenzhilog['price']=$jiangli;
							M('zenzhilog')->add($zenzhilog);
						}
					}else if($qiday<time()){
						//自动转入MXC余额
						$z_jl=M('zenzhilog')->where(array('id'=>$val['id'],'uid'=>$val['uid']))->sum('price');
						//本金加利息
						$z_num=$z_jl+$val['num'];
						
						$now_nums=M('store')->where(array('uid'=>$val['uid']))->getField('fengmi_num');
						
						$data['pay_id']=$val['id'];
						$data['get_id']=$val['id'];
						$data['get_nums']=$z_num;
						$data['get_time']=time();
						$data['get_type']=52;
						$data['now_nums']=$now_nums;
						$data['now_nums_get']=$now_nums;
						$data['is_release']=1;
						$res=M('tranmoney')->add($data);
						
						if($res){
							M('store')->where(array('uid'=>$val['uid']))->setDec('plant_num',$z_num);
							M('store')->where(array('uid'=>$val['uid']))->setInc('fengmi_num',$z_num);
							
							//删除该条增值记录
							M('wbao_detail')->where(array('id'=>$val['id']))->delete();
						}
							
						
					}
				}
				
			}
		}
		

		
    }

    public function Appload(){
        $this->display();
    }
    public function Anzhorload(){
        //判断是否在微信端

            $url='http://www...../TD.apk';
            //是否为安卓
            if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
                ajaxReturn('IOS请下载苹果版',0);
            }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
                if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
                    ajaxReturn('安卓端请在浏览器打开下载',0);
                }else{
                    ajaxReturn($url,1);
                }
            }else{
                ajaxReturn($url,1);
            }
//
    }
}
