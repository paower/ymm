<?php
namespace Home\ Controller;
use Think\Controller;
class TurntableController extends CommonController {
	/**
	 * 直推獎勵 
	 */
//   public function test(){

	


//  $allid = M('deals')->Field("sell_id,id")->select();
// //dump($allid);die;
//   foreach ($allid as $k => $v) {


//         $uname = M('user')->where(array('userid'=>$v['sell_id']))->getField('username');       
//         $datapay["buy_uname"]=$uname;
//         $res_pay = M('deals')->where(array("id"=>$v['id']))->save($datapay);

	   
//      }

//  }

	public function index(){
		
		$uid = session('userid');
		

	
		//當前我的資產
		$res=M('store')->where(array('uid'=>$uid))->find();
		
		$minecoins=$res['fengmi_num']+$res['xiaofei_num']+$res['fenhong_num']+$res['tuiguang_num'];
		
		
		
		$waadd = M('user')->where(array('userid'=>$uid))->getField('wallet_add');
		if(empty($waadd) || $waadd == ''){
			$waadd = build_wallet_add();

			M('user')->where(array('userid'=>$uid))->setField('wallet_add',$waadd);
		}
//      $this->assign('coindets',$coindets);
		$this->assign('minecoins',$minecoins);
		$this->assign('waadd',$waadd);
		$this->assign('uid',$uid);

		$this->display();
	}

	
	//轉賬的對象
	public function Checkuser(){
		$paynums = I('paynums','float',0);
		$getu = trim(I('moneyadd'));
		$uid = session('userid');

		$store=M('store')->where(array('uid'=>$uid))->find();
		$mwenums = $store['fengmi_num']+$store['xiaofei_num']+$store['fenhong_num']+$store['tuiguang_num'];
		
		if($paynums > $mwenums){
			ajaxReturn('您當前暫無這麽多MXC幣哦~',0);
		}

		$where['userid|mobile|wallet_add'] = $getu;
		$uinfo = M('user')->where($where)->Field('userid,username')->find();
		if($uinfo['userid'] == $uid){
			ajaxReturn('您不能給自己轉賬哦~',0);
		}

		if(empty($uinfo) || $uinfo == ''){
			ajaxReturn('您輸入的轉出地址有誤哦~',0);
		}
		$getmsg = array('uname'=>$uinfo['username'],'getuid'=>$uinfo['userid']);
		ajaxReturn($getmsg,1);
	}

	// 協議
	public function Agreement(){
		$news=M('news')->where(array('id'=>105))->find();
		$this->assign('news',$news);
		$this->display();
	}

 //NYT
	public function Wbaobei(){
		$uid = session('userid');
		// $assets1 = M('assets')->where(array('id'=>1))->field('id,time,lixi')->find();
	 //    $assets2 = M('assets')->where(array('id'=>2))->field('id,time,lixi')->find();
	 //    $assets3 = M('assets')->where(array('id'=>3))->field('id,time,lixi')->find();
	 //    $assets4 = M('assets')->where(array('id'=>4))->field('id,time,lixi')->find();
	 //    $assets5 = M('assets')->where(array('id'=>5))->field('id,time,lixi')->find();
		$step = I('step');
		if($step < 1){
			$step = 1;
		}


		$times=strtotime(date("Y-m-d"));
		$timee=$times+86400;
		$lastsy = M('wbao_detail')->where(array('uid'=>$uid,'is_dj'=>0))->sum('num');
		$qishu = M('wbao_detail')->where(array('uid'=>$uid,'is_dj'=>0))->select();
		$leiji = M('lixi')->where(array('uid'=>$uid))->sum('shouyi');
		$lixi = M('lixi')->where(array('uid'=>$uid))->select();
		$num = M('wbao_detail')->where(array('uid'=>$uid))->select();/*dump($num);exit;*/
		$times = time();
		// dump(date('Y-m-d',$times));
		// $times = strtotime('+1 day');
		
		$zuori = 0;
		foreach ($qishu as $k => $v) {
			$diff = round(($times-$v['create_time'])/3600/24);
			$timess = M('assets')->where(array('id'=>$v['qishu']))->find();
			// dump($v['qishu']);

			if ($v['qishu']==1 && $diff>=1 && $diff<=$timess['time']) {
				for($i=1;$i<=$diff;$i++){

					if($i == $diff){
						$time = $v['create_time']+$diff*86400;
						$res = M('lixi')->where(array('time'=>$time,'uid'=>$uid))->count(1);
						if(!$res){
							$shouyi = $v['lixi']/$timess['time'];
							$data = array('uid'=>$uid,'time'=>$time,'shouyi'=>$shouyi);
							$lxjl = M('lixi')->add($data);
						}
					}
				}
				$zuori = $zuori+$v['lixi']/$timess['time'];
				if ($diff>=$timess['time']) {
					$store = M('store')->where(array('uid'=>$uid))->select();//dump($store['cangku_num']+$num+$lixi);exit;
					$cangku_num = $store['cangku_num']+$num['num']['num']+$num['lixi'];
					$cangku_num_add = M('store')->where(array('uid'=>$uid))->save($cangku_num);
					$is_dj = M('wbao_detail')->where(array('id'=>$v['id']))->setField('is_dj',1);
				}

			}elseif($v['qishu']==2 && $diff >=1 && $diff<=(30*$timess['time'])){
				for($i=1;$i<=$diff;$i++){

					if($i == $diff){
						$time = $v['create_time']+$diff*86400;
						$res = M('lixi')->where(array('time'=>$time,'uid'=>$uid))->count(1);
						if(!$res){
							$shouyi = $v['lixi']/30/$timess['time'];
							$data = array('uid'=>$uid,'time'=>$time,'shouyi'=>$shouyi);
							$lxjl = M('lixi')->add($data);
						}
					}
				}
				$zuori = $zuori+$v['lixi']/30/$timess['time'];
				if ($diff>=30*$timess['time']) {
					$store = M('store')->where(array('uid'=>$uid))->select();
					$cangku_num = $store['cangku_num']+$num['num']+$num['lixi'];
					$cangku_num_add = M('store')->where(array('uid'=>$uid))->save($cangku_num);
					$is_dj = M('wbao_detail')->where(array('id'=>$v['id']))->setField('is_dj',1);
				}

			}elseif($v['qishu']==3 && $diff >=1 && $diff<=30*$timess['time']){
				for($i=1;$i<=$diff;$i++){

					if($i == $diff){
						$time = $v['create_time']+$diff*86400;
						$res = M('lixi')->where(array('time'=>$time,'uid'=>$uid))->count(1);
						if(!$res){
							$shouyi = $v['lixi']/(30*$timess['time']);
							$data = array('uid'=>$uid,'time'=>$time,'shouyi'=>$shouyi);
							$lxjl = M('lixi')->add($data);
						}
					}
				}
				$zuori = $zuori+$v['lixi']/30/$timess['time'];
				if ($diff==3*$timess['time']) {
					$store = M('store')->where(array('uid'=>$uid))->select();
					$cangku_num = $store['cangku_num']+$num['num']+$num['lixi'];
					$cangku_num_add = M('store')->where(array('uid'=>$uid))->save($cangku_num);
					$is_dj = M('wbao_detail')->where(array('id'=>$v['id']))->setField('is_dj',1);
				}

			}elseif($v['qishu']==4 && $diff >=1 && $diff<=30*$timess['time']){
				for($i=1;$i<=$diff;$i++){

					if($i == $diff){
						$time = $v['create_time']+$diff*86400;
						$res = M('lixi')->where(array('time'=>$time,'uid'=>$uid))->count(1);
						if(!$res){
							$shouyi = $v['lixi']/30/$timess['time'];
							$data = array('uid'=>$uid,'time'=>$time,'shouyi'=>$shouyi);
							$lxjl = M('lixi')->add($data);
						}
					}
				}
				$zuori = $zuori+$v['lixi']/30/$timess['time'];
				if ($diff==30*$timess['time']) {
					$store = M('store')->where(array('uid'=>$uid))->select();
					$cangku_num = $store['cangku_num']+$num['num']+$num['lixi'];
					$cangku_num_add = M('store')->where(array('uid'=>$uid))->save($cangku_num);
					$is_dj = M('wbao_detail')->where(array('id'=>$v['id']))->setField('is_dj',1);
				}

			}elseif($v['qishu']==5 && $diff >=1 && $diff<=30*$timess['time']){
				for($i=1;$i<=$diff;$i++){

					if($i == $diff){
						$time = $v['create_time']+$diff*86400;
						$res = M('lixi')->where(array('time'=>$time,'uid'=>$uid))->count(1);
						if(!$res){
							$shouyi = $v['lixi']/30/$timess['time'];
							$data = array('uid'=>$uid,'time'=>$time,'shouyi'=>$shouyi);
							$lxjl = M('lixi')->add($data);
						}
					}
				}
				$zuori = $zuori+$v['lixi']/30/$timess['time'];
				if ($diff==30*$timess['time']) {
					$store = M('store')->where(array('uid'=>$uid))->select();
					$cangku_num = $store['cangku_num']+$num['num']+$num['lixi'];
					$cangku_num_add = M('store')->where(array('uid'=>$uid))->save($cangku_num);
					$is_dj = M('wbao_detail')->where(array('id'=>$v['id']))->setField('is_dj',1);
				}

			}
		}/*dump($zuori);exit;*/

		
		//$leiji = M('wbao_detail')->where(array('uid'=>$uid,'is_dj'=>0))->sum('num');
		if($step==1){
			 $list=M('zenzhilog')->where("uid=".$uid)->order("time desc")->select(); //轉入記錄

		}elseif($step==2){
			 $list=M('wbao_detail')->where("type=1 and uid=".$uid)->order("create_time desc")->select(); //轉入記錄

		}elseif($step==3){
			 $list=M('wbao_detail')->where("type=2 and uid=".$uid)->order("create_time desc")->select(); //轉入記錄

		}

		
		$wbd = M('store')->where(array('uid'=>$uid))->getField('fengmi_num');
		$wbc = M('store')->where(array('uid'=>$uid))->getField('xiaofei_num');
		$wbb = M('store')->where(array('uid'=>$uid))->getField('fenhong_num');
		$wba = M('store')->where(array('uid'=>$uid))->getField('tuiguang_num');
		$wbtotal=number_format($wbd+$wbc+$wbb+$wba,4,".", "");
		$grade= M('store')->where(array('uid'=>$uid))->getField('vip_grade');
		$num = M('store')->where(array('uid'=>$uid))->getField('plant_num');
		
		$this->assign('num',$num);
		$this->assign('step',$step);
		$this->assign('lastsy',$lastsy);
		$this->assign('zuori',$zuori);
		$this->assign('leiji',$leiji);
		$this->assign('wbc',$wbc);
		$this->assign('grade',$grade);
		$this->assign('wbtotal',$wbtotal);
		$this->assign('list',$list);
		$this->assign('lixi',$lixi);
		$this->display();
	}



	//NYT轉入頁面
	public function WbaoIn(){

	$uid = session('userid');
	$store=M('store')->where(array('uid'=>$uid))->find();
	$mwenums = $store['cangku_num'];
	$assets1 = M('assets')->where(array('id'=>1))->field('id,time,lixi,type')->find();
	$assets2 = M('assets')->where(array('id'=>2))->field('id,time,lixi,type')->find();
	$assets3 = M('assets')->where(array('id'=>3))->field('id,time,lixi,type')->find();
	$assets4 = M('assets')->where(array('id'=>4))->field('id,time,lixi,type')->find();
	$assets5 = M('assets')->where(array('id'=>5))->field('id,time,lixi,type')->find();
	$this->assign('assets1',$assets1);
	$this->assign('assets2',$assets2);
	$this->assign('assets3',$assets3);
	$this->assign('assets4',$assets4);
	$this->assign('assets5',$assets5);
	$this->assign('mwenums',$mwenums);
	$this->display();
	}



	//NYT凍結資產頁面
	public function WBDongjie(){

	$uid = session('userid');
	$store=M('store')->where(array('uid'=>$uid))->find();
	$mwenums = $store['cangku_num'];
	$this->assign('mwenums',$mwenums);
	$this->display();
	}





	//NYT轉入核對
	public function WBCheckuser(){
		$paynums = I('paynums','float',0);
		$paynums = (float)$paynums;



		$uid = session('userid');
		$store=M('store')->where(array('uid'=>$uid))->find();
		$mwenums = $store['cangku_num'];
		if($paynums > $mwenums){
			ajaxReturn('您當前暫無這麽多資產余額哦~',0);
		}
		if ($paynums < 100) {
			ajaxReturn('轉入數量至少為100哦~',0);
		}
		if ($paynums % 100 != 0) {
			ajaxReturn('轉入數量必須為100的倍數哦~',0);
		}
   
		$getmsg = array('uname'=>$uinfo['username'],'getuid'=>$uinfo['userid']);
		ajaxReturn($getmsg,1);
	}


//NYT轉出核對
	public function WBCheckuser1(){
		$paynums = I('paynums','float',0);
		$paynums = (float)$paynums;

		$uid = session('userid');
		$store=M('store')->where(array('uid'=>$uid))->find();
		$mwenums = $store['fengmi_num']+$store['xiaofei_num']+$store['fenhong_num']+$store['tuiguang_num'];
		
		if($paynums > $mwenums){
			ajaxReturn('您當前暫無這麽多可用資產哦~',0);
		}

		$getmsg = array('uname'=>$uinfo['username'],'getuid'=>$uinfo['userid']);
		ajaxReturn($getmsg,1);
	}



	//NYT轉出頁面
	public function WbaoOut(){

	$uid = session('userid');
	$mwenums = M('store')->where(array('uid'=>$uid))->getField('plant_num');
	$this->assign('mwenums',$mwenums);
	$this->display();
	}


// NYT凍結資產
	public function WBDong(){
		$paynums = I('paynums','float',0);
		$pwd = trim(I('pwd'));
		$uid = session('userid');
		$store=M('store')->where(array('uid'=>$uid))->find();
		$mwenums = $store['fengmi_num']+$store['xiaofei_num']+$store['fenhong_num']+$store['tuiguang_num'];
		if($paynums > $mwenums){
			ajaxReturn('您當前暫無這麽多可用資產哦~',0);
		}
		//驗證交易密碼
		$minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
		$user_object = D('Home/User');
		$user_info = $user_object->Trans($minepwd['account'], $pwd);
		
		
		//凍結減NYT可用資產 加鎖定資產  

		$num= $store['fengmi_num']-$paynums;
		if($num<0){
			$datapay['fengmi_num'] = 0;
			$num=abs($num);
			$num1=$store['xiaofei_num']-$num;
			if($num1<0){
				$datapay['xiaofei_num']=0;
				$num1=abs($num1);
				$num2=$store['fenhong_num']-$num1;
				if($num2<0){
					$datapay['fenhong_num']=0;
					$num2=abs($num2);
					$num3=$store['tuiguang_num']-$num2;
					if($num3<0){
						ajaxReturn('您當前暫無這麽多可用資產哦~',0);
					}else{
						$datapay['tuiguang_num'] = array('exp', 'tuiguang_num - ' . $num2);
					}
				}else{
					$datapay['fenhong_num'] = array('exp', 'fenhong_num - ' . $num1);
				}
			}else{
				$datapay['xiaofei_num'] = array('exp', 'xiaofei_num - ' . $num);
			}
			
		}else{
			$datapay['fengmi_num'] = array('exp', 'fengmi_num - ' . $paynums);
		}
		//dump($datapay);die;
		
	   
		$datapay['huafei_total'] = array('exp', 'huafei_total + ' . $paynums);
		$res_pay = M('store')->where(array('uid'=>$uid))->save($datapay);

		if($res_pay){

		//添加NYT交易記錄
		$wbaoss["crowds_id"]=0;
		$wbaoss["create_time"]=time();
		$wbaoss["num"]=$paynums;
		$wbaoss["uid"]=$uid;
		$wbaoss["dprice"]=0;
		$wbaoss["tprice"]=0;
		$wbaoss["type"]=5;//鎖定資產
		$wbao_ss = M('wbao_detail')->add($wbaoss);

			ajaxReturn('可用資產鎖定成功',1,"Wbaobei");
		}else{
			ajaxReturn('可用資產鎖定失敗',0);
		}
	}



 // NYT轉出
	public function WBgetout(){
		$paynums = I('paynums','float',0);
		$pwd = trim(I('pwd'));
		$uid = session('userid');
		$mwenums = M('store')->where(array('uid'=>$uid))->getField('plant_num');
		if($paynums > $mwenums){
			ajaxReturn('您當前暫無這麽多NYT可用資產哦~',0);
		}
		  if($paynums<=0){
			ajaxReturn('非法操作~',0);
		}

		//驗證交易密碼
		$minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
		$user_object = D('Home/User');
		$user_info = $user_object->Trans($minepwd['account'], $pwd);


		//壹旦用戶轉出小於某數量的幣，則等級會降
		$grade1=3;
		if($paynums<1000){
			 $grade1=0;
		}elseif($paynums<5000){
			 $grade1=1;
		}elseif($paynums<10000){
			 $grade1=2;
		}

		//轉出減NYT可用資產 加MXC幣  

		$locknumd = M('store')->where(array('uid'=>$uid))->getField('huafei_total');
		$oldgrade = M('store')->where(array('uid'=>$uid))->getField('vip_grade');
		$locknumz =$mwenums+$locknumd;//總資產低於相應幣數就會降級
		$locknum=$locknumz-$paynums;//當前總資產數
		$grade=0;
		if($locknum>=10000){
			 $grade=3;
		}elseif($locknum>=5000){
			 $grade=2;
		}elseif($locknum>=1000){
			 $grade=1;
		}
		$graden=$grade1>$grade?$grade:$grade1;//取最小的
		if($oldgrade>$graden) $datapay['vip_grade'] = $graden;//只降級不升級

		$datapay['plant_num'] = array('exp', 'plant_num - ' . $paynums);
		$res_pay = M('store')->where(array('uid'=>$uid))->save($datapay);//轉出-NYT
	 
		$payout['c_nums'] = array('exp', 'c_nums + ' . $paynums);
		$res_pay = M('ucoins')->where(array('c_uid'=>$uid,'cid'=>1))->save($payout);//轉出+MXC




		if($res_pay){

		//添加NYT交易記錄
		$wbaoss["crowds_id"]=0;
		$wbaoss["create_time"]=time();
		$wbaoss["num"]=$paynums;
		$wbaoss["uid"]=$uid;
		$wbaoss["dprice"]=0;
		$wbaoss["tprice"]=0;
		$wbaoss["type"]=1;//轉出
		$wbao_ss = M('wbao_detail')->add($wbaoss);

			ajaxReturn('NYT可用資產轉出成功',1,"Wbaobei");
		}else{
			ajaxReturn('NYT可用資產轉出失敗',0);
		}
	}


// NYT轉入
	public function WBgetin(){
		$paynums = I('paynums','float',0);

		$pwd = trim(I('pwd'));
		$uid = session('userid');
		$mairjie = I('post.mairjie');
		$type = I('type');
		if($type == 'd'){
			$d = '天';
		}elseif($type == 'm'){
			$m = '個月';
		}
		switch($mairjie){
			case('time'.$d):$mairjie=1;break;
			case('time'.$m):$mairjie=2;break;
			case('time'.$m):$mairjie=3;break;
			case('time'.$m):$mairjie=4;break;
			case('time'.$m):$mairjie=5;break;
			// case('6期'):$mairjie=6;break;
			// case('7期'):$mairjie=7;break;
			// case('8期'):$mairjie=8;break;
			// case('9期'):$mairjie=9;break;
			// case('10期'):$mairjie=10;break;
			// case('11期'):$mairjie=11;break;
			// case('12期'):$mairjie=12;break;
		}
		// echo "<meta charset='utf-8'/>";
		// dump($mairjie);exit;
		$store=M('store')->where(array('uid'=>$uid))->find();
		$mwenums = $store['cangku_num'];
		if($paynums > $mwenums){
			ajaxReturn('您當前暫無這麽多平臺幣哦~',0);
		}
		if ($paynums < 100) {
			ajaxReturn('轉入數量至少為100哦~',0);
		}
		if ($paynums % 100 != 0) {
			ajaxReturn('轉入數量必須為100的倍數哦~',0);
		}

		 if($paynums<=0){
			ajaxReturn('非法操作~',0);
		}


		//驗證交易密碼
		$minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
		$user_object = D('Home/User');
		$user_info = $user_object->Trans($minepwd['account'], $pwd);

		//轉入加NYT可用資產 減MXC幣  
		$num= $store['cangku_num']-$paynums;
		if($num<0){
			ajaxReturn('您當前暫無這麽多可用資產余額哦~',0);
		}else{
			$datapay['cangku_num'] = array('exp', 'cangku_num - ' . $paynums);
		}
		$res_pay = M('store')->where(array('uid'=>$uid))->save($datapay);//轉出+MXC
		//轉出的扣錢
		$assets = M('assets')->where(array('id'=>$mairjie))->Field('time,lixi')->find();
		if($res_pay){
		//添加NYT交易記錄
		$wbaoss["crowds_id"]=0;
		$wbaoss["create_time"]=time();
		$wbaoss["num"]=$paynums;
		$wbaoss["uid"]=$uid;
		$wbaoss["dprice"]=0;
		$wbaoss["tprice"]=0;
		$wbaoss["type"]=2;//轉入
		$wbaoss['qishu']=$mairjie;
		$wbaoss['is_dj']=0;
		if ($mairjie==1) {
			$wbaoss['lixi'] = $paynums*$assets['lixi']/100;
		}elseif($mairjie==2){
			$wbaoss['lixi'] = $paynums*$assets['lixi']/100;
		}elseif($mairjie==3){
			$wbaoss['lixi'] = $paynums*$assets['lixi']/100;
		}elseif($mairjie==4){
			$wbaoss['lixi'] = $paynums*$assets['lixi']/100;
		}elseif($mairjie==5){
			$wbaoss['lixi'] = $paynums*$assets['lixi']/100;
		}
		$wbao_ss = M('wbao_detail')->add($wbaoss);

			ajaxReturn('余額轉入成功',1,"Wbaobei");
		}else{
			ajaxReturn('余額轉入失敗',0);
		}
	}




	   


//    MXC轉入
	public function Wegetin(){
		$paynums = $addnums = I('paynums','float',0);
		$getu = trim(I('moneyadd'));
		$pwd = trim(I('pwd'));
		$uid = session('userid');
		$nums = M('store')->field('fengmi_num,xiaofei_num,fenhong_num,tuiguang_num')->where(array('uid'=>$uid))->find();
		$mwenums = $nums['fengmi_num'] + $nums['xiaofei_num'] + $nums['fenhong_num'] + $nums['tuiguang_num'];
		// dump($mwenums);
		if($paynums > $mwenums){
			ajaxReturn('您當前暫無這麽多MXC幣哦~',0);
		}

		$where['userid|mobile|wallet_add'] = $getu;
		$uinfo = M('user')->where($where)->Field('userid,username')->find();
		
		if($uinfo['userid'] == $uid){
			ajaxReturn('您不能給自己轉賬哦~',0);
		}

		if(empty($uinfo) || $uinfo == ''){
			ajaxReturn('您輸入的轉出地址有誤哦~',0);
		}

		//驗證交易密碼
		$minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
		$user_object = D('Home/User');
		$user_info = $user_object->Trans($minepwd['account'], $pwd);

		// 判斷MXC是否充足,不充足則使用其它積分
		if($nums['fengmi_num'] >= $paynums){
			$diff = $nums['fengmi_num'] - $paynums;
			$paynums = 0;
			$res_tran = M('store')->where(array('uid'=>$uid))->setField('fengmi_num',$diff);
		}else{
			$paynums = $paynums - $nums['fengmi_num'];
			M('store')->where(array('uid'=>$uid))->setField('fengmi_num',0);
		}
		if($nums['xiaofei_num'] >= $paynums && $paynums != 0){
			$diff = $nums['xiaofei_num'] - $paynums;
			$res_tran = M('store')->where(array('uid'=>$uid))->setField('xiaofei_num',$diff);
			$paynums = 0;
		}elseif($nums['xiaofei_num'] < $paynums){
			$paynums = $paynums - $nums['xiaofei_num'];
			M('store')->where(array('uid'=>$uid))->setField('xiaofei_num',0);
		}
		if($nums['fenhong_num'] >= $paynums && $paynums !=0){
			$diff = $nums['fenhong_num'] - $paynums;
			$res_tran = M('store')->where(array('uid'=>$uid))->setField('fenhong_num',$diff);
			$paynums = 0;
		}elseif($nums['fenhong_num'] < $paynums){
			$paynums = $paynums - $nums['fenhong_num'];
			M('store')->where(array('uid'=>$uid))->setField('fenhong_num',0);
		}
		if($nums['tuiguang_num'] >= $paynums && $paynums !=0){

			$diff = $nums['tuiguang_num'] - $paynums;
			$res_tran = M('store')->where(array('uid'=>$uid))->setField('tuiguang_num',$diff);
			$paynums = 0;
		}elseif($nums['tuiguang_num'] < $paynums){

			$paynums = $paynums - $nums['tuiguang_num'];
			M('store')->where(array('uid'=>$uid))->setField('tuiguang_num',0);
		}
		// 轉入的加MXC

		$res_s1 = M('store')->where(array('uid'=>$uinfo['userid']))->setInc('fengmi_num',$addnums);


		//記錄
		$jifen_dochange['pay_id'] = $uid;
		$jifen_dochange['get_id'] = $uinfo['userid'];
		$jifen_dochange['get_nums'] = $addnums;
		$jifen_dochange['get_time'] = time();
		$jifen_dochange['get_type'] = 1;
		$res_tran = M('wetrans')->add($jifen_dochange);

		$pay_fengmi = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
		$get_fengmi = M('store')->where(array('uid'=>$getu))->getField('fengmi_num');
		$arr = array(
			'pay_id' => $uid,
			'get_id' => $uinfo['userid'],
			'get_nums' => $addnums,
			'get_time'  => time(),
			'get_type' => 1,
			'now_nums' => $pay_fengmi,
			'now_nums_get' => $get_fengmi,
			'is_release' => 1,
		);
		$res = M('tranmoney')->add($arr);

		if($res_tran&&$res_s1&&$res){
			ajaxReturn('MXC幣轉出成功',1,"index");
		}else{
			ajaxReturn('MXC幣轉出失敗',0);
		}
	}

	public function Trans(){
		$type = I('type','intval',0);
		$traInfo = M('wetrans');
		$uid = session('userid');
		if($type == 1){
			$where['pay_id'] = $uid;
		}else{
			$where['get_id'] = $uid;
		}

		$where['get_type'] = 1;
		//分頁
		$p = getpage($traInfo, $where, 15);
		$page = $p->show();
		$Chan_info = $traInfo->where($where)->order('id desc')->select();
		foreach ($Chan_info as $k => $v) {
			$Chan_info[$k]['get_timeymd'] = date('Y-m-d', $v['get_time']);
			$Chan_info[$k]['get_timedate'] = date('H:i:s', $v['get_time']);
			$Chan_info[$k]['outinfo'] = M('user')->where(array('userid'=>$v['get_id']))->getField('username');
			$Chan_info[$k]['ininfo'] = M('user')->where(array('userid'=>$v['pay_id']))->getField('username');

			//轉入轉出
			if ($type == 1) {
				$Chan_info[$k]['trtype'] = 1;
			} else {
				$Chan_info[$k]['trtype'] = 2;
			}
		}
		if (IS_AJAX) {
			if (count($Chan_info) >= 1) {
				ajaxReturn($Chan_info, 1);
			} else {
				ajaxReturn('暫無記錄', 0);
			}
		}
		$this->assign('page', $page);
		$this->assign('Chan_info', $Chan_info);
		$this->assign('type',$type);
		$this->display();
	}


	public function Turnout(){

		$this->display();
	}


	//金積分交易
	public function transaction(){

		$cid = (int)I('cid','intval',0);
	   
	   if($cid=='intval')$cid=1;
		$uid = session('userid');

		//查詢當前幣對應價格名稱信息
		// $coindets = M('coindets')->order('coin_addtime desc,cid asc')->where(array("cid"=>$cid))->find();
		$coins = M('coins')->where(array('name'=>'MXC'))->find();
		 //當前我的資產
		$minecoins = M('ucoins')->where(array('c_uid'=>$uid,'cid'=>$cid))->order('id asc')->find();
		$my_yue = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
		$fengmi_num = M('store')->where(array('uid'=>$uid))->getField('fengmi_num');
		 //交易列表

		$deals = M('deal a')->join('ysk_user b ON a.sell_id=b.userid')->field('b.username as u_name,a.id as d_id,b.account as u_account,b.img_head as u_img_head,a.num as d_num,a.dprice as d_dprice,a.id as d_id')->where(array("a.type"=>1,"a.status"=>0,"a.cid"=>$cid))->limit($page, 5000)->order('dprice asc')->select();
	  
		$this->assign('coindets',$coins);
		$this->assign('deals',$deals);
		$this->assign('minecoins',$minecoins);
		$this->assign('my_yue',$my_yue);
		$this->assign('fengmi_num',$fengmi_num);
		$this->assign('cid',$cid);

		$this->display();

	}


//金積分購買
	public function yue_goumai(){

//防重復提交
	if(session("gou_last_time")){
		if((int)time()-(int)session("gou_last_time") <10 ){           
			ajaxReturn('對不起，10秒內不能頻繁提交~',0);
		}
	}

	
	$t = (int)time();
	session("gou_last_time", $t);
	

		$num = (float)I('num');
		$cid = (int)I('cid','intval',0);
		$dealid = I('dealid','intval',0);
		$dprice = trim(I('dprice'));
		$tprice = trim(I('tprice'));
		$pwd = trim(I('pwd'));
		$uid = session('userid');
		

		$ss1 = M('deal')->where(array('id'=>$dealid,'type'=>1))->getField('num');
		$restn= $num-$ss1;

		if($num<0||$tprice<0||$dprice<0)ajaxReturn('非法輸入~',0);
		if(!$num|!$tprice)ajaxReturn('交易幣的數量不能為空~',0);
		if($restn>0)ajaxReturn('交易幣的數量超過最大限制~',0);



		//驗證交易密碼
		$minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
		$user_object = D('Home/User');
		$user_info = $user_object->Trans($minepwd['account'], $pwd);

		//自己是否有足夠金積分        
			$my_yue = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
			if($tprice> $my_yue){
				ajaxReturn('您當前賬戶暫無這麽多余額~',0);
			}


		//掛賣單人的ID
		$sell_id = M('deal')->where(array('id'=>$dealid,'type'=>1))->getField('sell_id');

		if($uid==$sell_id){
			ajaxReturn('您不能和自己交易~',0);
		}
		//檢查 store表和 coindets 表是否有記錄

		$ishas_store_u = M('store')->where(array('uid'=>$uid))->count(1);
		if(!$ishas_store_u)M('store')->add(array('uid'=>$uid,'cangku_num'=>0.0000));   
		$ishas_store_s = M('store')->where(array('uid'=>$sell_id))->count(1);
		if(!$ishas_store_s)M('store')->add(array('uid'=>$sell_id,'cangku_num'=>0.0000));   


		$issetgetu = M('ucoins')->where(array('c_uid'=>$uid,'cid'=>$cid))->count(1);
		if($issetgetu <= 0){
			$coinone['cid'] = $cid;
			$coinone['c_nums'] = 0.0000;
			$coinone['c_uid'] = $uid;
			M('ucoins')->add($coinone);
		}


		$issetgets = M('ucoins')->where(array('c_uid'=>$sell_id,'cid'=>$cid))->count(1);
		if($issetgets <= 0){
			$coinone1['cid'] = $cid;
			$coinone1['c_nums'] = 0.0000;
			$coinone1['c_uid'] = $sell_id;
			M('ucoins')->add($coinone1);
		}

		//購買的加幣的數量、減金積分    



		// $datapay['c_nums'] = array('exp', 'c_nums + ' . $num);
		// $res0 = M('ucoins')->where(array('c_uid'=>$uid,'cid'=>$cid))->save($datapay);
		
		$datapay1['fengmi_num'] = array('exo','fengmi_num - '.$num);
		$datapay1['cangku_num'] = array('exp', 'cangku_num - ' . $tprice);
		$res1 = M('store')->where(array('uid'=>$uid))->save($datapay1);

		//出售的扣幣的數量、加金積分
		
		// $payout['djie_nums'] = array('exp', 'djie_nums - ' . $num);
		// $res2 = M('ucoins')->where(array('c_uid'=>$sell_id,'cid'=>$cid))->save($payout);

		$payout1['blockage'] = array('exp','blockage - '.$num);
		$payout1['cangku_num'] = array('exp', 'cangku_num + ' . $tprice);
		$res3 = M('store')->where(array('uid'=>$sell_id))->save($payout1);


		$pay_n = M('store')->where(array('uid' => $uid))->getField('cangku_num');
		$get_n = M('store')->where(array('uid' => $sell_id))->getField('cangku_num');


		$changenums['now_nums'] = $pay_n;
		$changenums['now_nums_get'] = $get_n;
		$changenums['is_release'] = 1;                 
		$changenums['pay_id'] = $uid;
		$changenums['get_id'] = $sell_id;
		$changenums['get_nums'] = $tprice;
		$changenums['get_time'] = time();
		$changenums['get_type'] = 4;
		M('tranmoney')->add($changenums);


		//剩余數量，更新訂單狀態1，為匹配交易
		if($restn>=0)$deals["status"]=1;
		$deals["num"]=array('exp', 'num - ' . $num);
		$deal_s = M('deal')->where(array('id'=>$dealid,'type'=>1))->save($deals);

		//添加交易記錄
		$buy_name = M('user')->where(array('userid'=>$uid))->getField('username');    

		$dealss["d_id"]=$dealid;
		$dealss["sell_id"]=$sell_id;
		$dealss["buy_id"]=$uid;
		$dealss["create_time"]=time();
		$dealss["buy_uname"]=$buy_name;
		$dealss["cid"]=$cid;
		$dealss["type"]=1;
		$dealss["num"]=$num;
		$dealss["dprice"]=$dprice;
		$dealss["tprice"]=$tprice;
		$deal_ss = M('deals')->add($dealss);

		if($res3&&$deal_ss){
			ajaxReturn('購買成功',1,"/Turntable/Transaction");
		}else{
			ajaxReturn('購買失敗',0);
		}

	}



//出售幣
	public function yue_chushou(){

	if(session("chu_last_time")){
				 if((int)time()-(int)session("chu_last_time") <10 ){           
				  ajaxReturn('對不起，10秒內不能頻繁提交~',0);
				 }
	}
	$t = (int)time();
	session("chu_last_time", $t);


		
		$num = (float)I('num');
		$cid = (int)I('cid','intval',0);
		$dealid = I('dealid','intval',0);
		$dprice = trim(I('dprice'));
		$tprice = trim(I('tprice'));
		$pwd = trim(I('pwd'));
		$uid = session('userid');
		

		 $ss1 = M('deal')->where(array('id'=>$dealid,'type'=>2))->getField('num');
		 $restn= $num-$ss1;


		if($num<0||$tprice<0||$dprice<0)ajaxReturn('非法輸入~',0);
		if(!$num|!$tprice)ajaxReturn('交易幣的數量不能為空~',0);
		if($restn>0)ajaxReturn('交易幣的數量超過最大限制~',0);
		//驗證交易密碼
		$minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
		$user_object = D('Home/User');
		$user_info = $user_object->Trans($minepwd['account'], $pwd);



		//自己是否有足夠幣出售        
		$my_bi = M('store')->where(array('uid'=>$uid))->getField('fengmi_num');            
			if($num> $my_bi){
				ajaxReturn('您當前賬戶暫無這麽多幣出售~',0);
			}


		//掛買單人的ID
		$sell_id = M('deal')->where(array('id'=>$dealid,'type'=>2))->getField('sell_id');
		if($uid==$sell_id){
				ajaxReturn('您不能和自己交易~',0);
			}


		//檢查 store表和 coindets 表是否有記錄

		 $ishas_store_u = M('store')->where(array('uid'=>$uid))->count(1);
		if(!$ishas_store_u)M('store')->add(array('uid'=>$uid,'cangku_num'=>0.0000));   
		$ishas_store_s = M('store')->where(array('uid'=>$sell_id))->count(1);
		if(!$ishas_store_s)M('store')->add(array('uid'=>$sell_id,'cangku_num'=>0.0000));      



		$issetgetu = M('ucoins')->where(array('c_uid'=>$uid,'cid'=>$cid))->count(1);
		if($issetgetu <= 0){
			$coinone['cid'] = $cid;
			$coinone['c_nums'] = 0.0000;
			$coinone['c_uid'] = $uid;
			M('ucoins')->add($coinone);
		}


		$issetgets = M('ucoins')->where(array('c_uid'=>$sell_id,'cid'=>$cid))->count(1);
		if($issetgets <= 0){
			$coinone1['cid'] = $cid;
			$coinone1['c_nums'] = 0.0000;
			$coinone1['c_uid'] = $sell_id;
			M('ucoins')->add($coinone1);
		}

   
	   

		//出售的減對應的幣數、加對應的金積分
		// $datapay['c_nums'] = array('exp', 'c_nums - ' . $num);
		// $res_pay0 = M('ucoins')->where(array('c_uid'=>$uid,'cid'=>$cid))->save($datapay);

		$datapay1['fengmi_num'] = array('exp','fengmi_num - '.$num);
		$datapay1['cangku_num'] = array('exp', 'cangku_num + ' . $tprice);
		$res_pay1 = M('store')->where(array('uid'=>$uid))->save($datapay1);


		//購買的扣金積分、加幣
		 $payout['c_nums'] = array('exp', 'c_nums + ' . $num);
		 $res_pay2 = M('ucoins')->where(array('c_uid'=>$sell_id,'cid'=>$cid))->save($payout);
		 
		 $payout1['fengmi_num'] = array('exp','fengmi_num + '.$num);
		 $payout1['cangku_num'] = array('exp', 'cangku_num - ' . $tprice);
		 
		 $res_pay3= M('store')->where(array('uid'=>$sell_id))->save($payout1);
		//  var_dump($res_pay3);
		// var_dump(M('store')->getLastSql());

		//更新訂單狀態1，為匹配交易
		if($restn>=0) $deals["status"]=1;
		$deals["num"]=array('exp', 'num - ' . $num);
		$deal_s = M('deal')->where(array('id'=>$dealid,'type'=>2))->save($deals);
//dump($res_pay3);die;

		//添加交易記錄
		$buy_name = M('user')->where(array('userid'=>$sell_id))->getField('username');    

		$dealss["d_id"]=$dealid;
		$dealss["sell_id"]=$sell_id;
		$dealss["buy_id"]=$uid;
		$dealss["create_time"]=time();
		$dealss["buy_uname"]=$buy_name;
		$dealss["cid"]=$cid;
		$dealss["type"]=2;
		$dealss["num"]=$num;
		$dealss["dprice"]=$dprice;
		$dealss["tprice"]=$tprice;
		
		$deal_ss = M('deals')->add($dealss);



		$pay_n = M('store')->where(array('uid' => $sell_id))->getField('cangku_num');
		$get_n = M('store')->where(array('uid' => $uid))->getField('cangku_num');


		$changenums['now_nums'] = $pay_n;
		$changenums['now_nums_get'] = $get_n;
		$changenums['is_release'] = 1;  
		$changenums['pay_id'] = $sell_id;
		$changenums['get_id'] = $uid;
		$changenums['get_nums'] = $tprice;
		$changenums['get_time'] = time();
		$changenums['get_type'] = 5;
		M('tranmoney')->add($changenums);


		
		// var_dump($res_pay3);
	   
		if($res_pay3&&$deal_ss){
			ajaxReturn('售出成功',1,"/Turntable/Transaction");

		}else{
			ajaxReturn('售出失敗',0);
		}

	}



	//交易中心
	public function Transactionsell(){
		
	   $cid = (int)I('cid','intval',0);
	//    dump($cid);
	   if($cid=='intval')$cid=1;
		$uid = session('userid');

	   //查詢當前幣對應價格名稱信息
		// $coindets = M('coindets')->order('coin_addtime desc,cid asc')->where(array("cid"=>$cid))->find();
		$coins = M('coins')->where(array('name'=>'MXC'))->find();

		 //當前我的資產
		$minecoins = M('ucoins')->where(array('c_uid'=>$uid,'cid'=>$cid))->order('id asc')->find();
		$my_yue = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
		$fengmi_num = M('store')->where(array('uid'=>$uid))->getField('fengmi_num');
		 //交易列表
			  

		$deals = M('deal a')->join('ysk_user b ON a.sell_id=b.userid')->field('b.username as u_name,a.id as d_id,b.account as u_account,b.img_head as u_img_head,a.num as d_num,a.dprice as d_dprice,a.id as d_id')->where(array("a.type"=>2,"a.status"=>0,"a.cid"=>$cid))->limit($page, 5000)->order('d_dprice desc')->select();


	  
		$this->assign('coindets',$coins);
		$this->assign('cname',$cname);
		$this->assign('deals',$deals);
		$this->assign('minecoins',$minecoins);
		$this->assign('my_yue',$my_yue);
		$this->assign('fengmi_num',$fengmi_num);
		$this->assign('cid',$cid);

		$this->display();


	}






//取消訂單
public function quxiao_order(){

	$id = (int)I('id','intval',0);
	$uid = session('userid');
	$mydeal = M('deal')->where(array("id"=>$id,"sell_id"=>$uid))->find();

	if(!$mydeal)ajaxReturn('訂單不存在~',0);

	$type=$mydeal["type"];
	$num=$mydeal["num"];
	$cid=$mydeal["cid"];
	$dprice=$mydeal["dprice"];
	if($type==1){//為出售單，則返還剩余相應的幣

			$payout['blockage'] = array('exp','blockage - '.$num);
			$payout['fengmi_num'] = array('exp', 'fengmi_num + ' . $num);
			$res1 = M('store')->where(array('uid'=>$uid))->save($payout); 


	}elseif($type==2){//為購買單，則返還剩余相應的金積分

			$tprice=$num*$dprice;
			$payout1['can_blockage'] = array('exp','can_blockage - '.$tprice);
			$payout1['cangku_num'] = array('exp', 'cangku_num + ' . $tprice);
			$res2 = M('store')->where(array('uid'=>$uid))->save($payout1);


			//生成金積分記錄
			$pay_n = M('store')->where(array('uid' => $uid))->getField('cangku_num');

			$changenums['now_nums'] = $pay_n;
			$changenums['now_nums_get'] = $pay_n;
			$changenums['is_release'] = 1;
			$changenums['pay_id'] = 0;
			$changenums['get_id'] = $uid;
			$changenums['get_nums'] = $tprice;
			$changenums['get_time'] = time();
			$changenums['get_type'] = 6;
			M('tranmoney')->add($changenums);


	}
	//把此訂單狀態設置為2，即為取消

	$payout2['status'] =2;
	$res3 =M('deal')->where(array("id"=>$id,"sell_id"=>$uid))->save($payout2);

	if($res3){       
		ajaxReturn('取消成功',0);
	}else{
		ajaxReturn('操作失敗',0);
	}
}



	//提交發布出售訂單
	public function T_Salesell(){

		$num = (float)I('num');
		$cid = (int)I('cid','intval',0);
		$dprice = trim(I('dprice'));
		$tprice = trim(I('tprice'));
		$pwd = trim(I('pwd'));
		$uid = session('userid');



		$nowprice=M('coindets')->where(array('cid'=>$cid))->order('coin_addtime desc')->getField('coin_price');

		  if($num<0||$tprice<0||$dprice<0)ajaxReturn('非法輸入~',0);
	
		if(!$num|!$tprice)ajaxReturn('交易幣的數量不能為空~',0);
	 

		if($dprice>1.1*$nowprice)ajaxReturn('交易幣的單價不能高過當前價格10%~',0);

		//驗證交易密碼
		$minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
		$user_object = D('Home/User');
		$user_info = $user_object->Trans($minepwd['account'], $pwd);

		//當前我的資產
		// $minecoins = M('ucoins')->where(array('c_uid'=>$uid,'cid'=>$cid))->order('id asc')->getField('c_nums');
		// MXC
		$fengmi_num = M('store')->where(array('uid'=>$uid))->getField('fengmi_num');
	  //  $my_yue = M('store')->where(array('uid'=>$uid))->getField('cangku_num');   
 
		if($fengmi_num<$num){
			 ajaxReturn('交易幣的數量不足',0);
		 }

		//凍結我的資產
		$payout['blockage'] = array('exp', 'blockage + ' . $num);
		$payout['fengmi_num'] = array('exp', 'fengmi_num - ' . $num);
		$res2 = M('store')->where(array('uid'=>$uid))->save($payout);


		//生成交易記錄
		$deal['sell_id'] = $uid;  //掛售出單人ID
		$deal['num'] = $num;
		$deal['ynum'] = $num;
		$deal['create_time'] = time();
		$deal['tprice'] = $tprice;       
		$deal['dprice'] = $dprice;
	   
		$deal['cid'] = $cid;
		$deal['type'] = 1;//1為出售訂單
		$res_tran = M('deal')->add($deal);
 
		ajaxReturn('發布成功',1,"/Turntable/Transaction/cid/".$cid);         
 

	}




	//發布出售訂單的頁面
	public function Salesell(){

		$uid = session('userid');
		$cid = (int)I('cid','intval',0);

		//查詢當前幣對應價格及名稱
		// $coindets = M('coindets')->order('coin_addtime desc,cid asc')->where(array("cid"=>$cid))->find();
		$coins = M('coins')->where(array('name'=>'MXC'))->find();


		//當前我的資產
		$minecoins = M('ucoins')->where(array('c_uid'=>$uid,'cid'=>$cid))->order('id asc')->find();
		$my_yue = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
		$fengmi_num = M('store')->where(array('uid'=>$uid))->getField('fengmi_num');

		$this->assign('minecoins',$minecoins);
		$this->assign('my_yue',$my_yue);
		$this->assign('coindets',$coins);
		$this->assign('fengmi_num',$fengmi_num);
		$this->assign('cid',$cid);

		$this->display();

	}




	//發布購買訂單的頁面
	public function Salebuys(){
		
		$uid = session('userid');
		$cid = (int)I('cid','intval',0);


	   //查詢當前幣對應價格及名稱
		// $coindets = M('coindets')->order('coin_addtime desc,cid asc')->where(array("cid"=>$cid))->find();
		$coindets = M('coins')->where(array('name'=>'MXC'))->find();

		//當前我的資產
		$minecoins = M('ucoins')->where(array('c_uid'=>$uid,'cid'=>$cid))->order('id asc')->find();
		$my_yue = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
		$fengmi_num = M('store')->where(array('uid'=>$uid))->getField('fengmi_num');

		$this->assign('minecoins',$minecoins);
		$this->assign('my_yue',$my_yue);
		$this->assign('fengmi_num',$fengmi_num);
		$this->assign('coindets',$coindets);
		$this->assign('cid',$cid);

		$this->display();


	}


	//提交發布購買訂單
	public function T_Salebuys(){

		$num = (float)I('num');
		$cid = (int)I('cid','intval',0);
		$dprice = trim(I('dprice'));
		$tprice = $num*$dprice;
		$pwd = trim(I('pwd'));
		$uid = session('userid');



		$nowprice=M('coindets')->where(array('cid'=>$cid))->order('coin_addtime desc')->getField('coin_price');
	
	  if($num<0||$tprice<0||$dprice<0)ajaxReturn('非法輸入~',0);
		if(!$num|!$tprice)ajaxReturn('交易幣的數量不能為空~',0);

		if($dprice>1.1*$nowprice)ajaxReturn('交易幣的單價不能高過當前價格10%~',0);

		//驗證交易密碼
		$minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
		$user_object = D('Home/User');
		$user_info = $user_object->Trans($minepwd['account'], $pwd);

		//自己是否有足夠金積分        
			$my_yue = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
			if($tprice> $my_yue){
				ajaxReturn('您當前賬戶暫無這麽多余額~',0);
			}

		 //凍結我的金積分
		 $payout1['can_blockage'] = array('exp', 'djie_num + ' . $tprice);
		 $payout1['cangku_num'] = array('exp', 'cangku_num - ' . $tprice);
		 $res_pay3 = M('store')->where(array('uid'=>$uid))->save($payout1);


		//生成交易記錄
		$deal['sell_id'] = $uid;  //掛售出單人ID
		$deal['num'] = $num;
		$deal['ynum'] = $num;
		$deal['create_time'] = time();
		$deal['tprice'] = $tprice;       
		$deal['dprice'] = $dprice;       
		$deal['cid'] = $cid;
		$deal['type'] = 2;//2為購買訂單
		$res_tran = M('deal')->add($deal);

		$pay_n = M('store')->where(array('uid' => $uid))->getfield('cangku_num');

		//生成金積分記錄
		$changenums['pay_id'] = $uid;
		$changenums['get_id'] = 0;
		$changenums['now_nums'] = $pay_n;
		$changenums['now_nums_get'] = $pay_n;
		$changenums['is_release'] = 1;
		$changenums['get_nums'] = $tprice;
		$changenums['get_time'] = time();
		$changenums['get_type'] = 3;
		M('tranmoney')->add($changenums);

 
		ajaxReturn('發布成功',1,"/Turntable/Transaction");
		 
 

	}


	//訂單
	public function Orderinfos(){

		$cid = (int)I('cid','intval',0);
   
		$step =I('step');//
		if(!$step) $step =1;
		$uid = session('userid');
		$where["sell_id"]=$uid;
		$where["status"]=0;   
		$where["cid"]=$cid;            
		if($step ==2) $where["status"]=1;
		$list = M('deal')->order('id desc')->where($where)->limit(1000)->select();
		// dump($list);
		$this->assign('list',$list);        
		$this->assign('step',$step);
		$this->assign('cid',$cid);
		$this->display();
	}

	//交易記錄
	public function Transreocrds(){
	
		$cid = (int)I('cid','intval',0);
		$uid = session('userid');
		$where["buy_id"]=$uid;
		$where["cid"]=$cid;
		$list = M('deals')->order('id desc')->where($where)->limit(1000)->select();
		$this->assign('list',$list);  
		 $this->assign('cid',$cid);
		   
		$this->display();


	}




	//眾籌
	public function Crowds(){
		$step = I('step');
		$html = 'Crowds'.$step;
		$time_n=time();

		if($step >= 1){

			if($step==1){
			 $list=M('crowds')->where("open_time<=".$time_n." and status<>2")->order("create_time desc")->find();
			}else{

			   $list=M('crowds')->where("open_time<=".$time_n." and status=2")->order("create_time desc")->find(); 
			}
		  
			$this->assign('list',$list);
			$this->display('Turntable/'.$html);
		}else{

			$list = M('crowds')->where("status=0 and open_time>".$time_n)->order('id desc')->find();
			$this->assign('list',$list);
			$this->display();
		}
	}



	//金積分購買
  public function Crowds_goumai(){

		//防重復提交
		 if(session("gou_last_time1")){
				 if((int)time()-(int)session("gou_last_time1") <10 ){           
				  ajaxReturn('對不起，10秒內不能頻繁提交~',0);
				 }
		 }

	
		$t = (int)time();
		session("gou_last_time1", $t);
	

		$num = (float)I('num');
		$cid = (int)I('cid','intval',0);
		$dealid = I('dealid','intval',0);
		$dprice = trim(I('dprice'));
		$tprice = trim(I('tprice'));
		$pwd = trim(I('pwd'));
		$uid = session('userid');
		

		$ss1 = 1000;
		$restn= $num-$ss1;

		if($num<0||$tprice<0||$dprice<0)ajaxReturn('非法輸入~',0);
		if(!$num|!$tprice)ajaxReturn('交易幣的數量不能為空~',0);
		if($restn>0)ajaxReturn('交易幣的數量超過最大限制~',0);



		//驗證交易密碼
		$minepwd = M('user')->where(array('userid' => $uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
		$user_object = D('Home/User');
		$user_info = $user_object->Trans($minepwd['account'], $pwd);

		//自己是否有足夠金積分        
			$my_yue = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
			if($tprice> $my_yue){
				ajaxReturn('您當前賬戶暫無這麽多余額~',0);
			}


		//查詢該會員本期已經買了多少MXC
		$bnums=0;    
		$benqi = M('crowds_detail')->where(array('crowds_id'=>$dealid,'uid'=>$uid))->Field('sum(num) as nums')->find();
		if($benqi) $bnums=$benqi["nums"];

		if($bnums>=$ss1){
				ajaxReturn('本期眾籌您已經購買了'.$ss1.'枚，無法繼續購買~',0);
			}

		//檢查 store表和 coindets 表是否有記錄

		 $ishas_store_u = M('store')->where(array('uid'=>$uid))->count(1);
		if(!$ishas_store_u)M('store')->add(array('uid'=>$uid,'cangku_num'=>0.0000));   
  

		$issetgetu = M('ucoins')->where(array('c_uid'=>$uid,'cid'=>$cid))->count(1);
		if($issetgetu <= 0){
			$coinone['cid'] = $cid;
			$coinone['c_nums'] =0.0000;
			$coinone['c_uid'] = $uid;
			M('ucoins')->add($coinone);
		}
		// else{
		//     M('ucoins')->where(array('c_uid'=>$uid,'cid'=>$cid))->setinc('c_nums',$num);
		// }


		//購買的在NYT的凍結字段裏加幣的數量、並減該會員金積分    huafei_total字段為凍結數
		$datapay['fengmi_num'] = array('exp','fengmi_num + '.$num);
		$datapay['huafei_total'] = array('exp', 'huafei_total + ' . $num);
		$datapay['cangku_num'] = array('exp', 'cangku_num - ' . $tprice);
		$res1 = M('store')->where(array('uid'=>$uid))->save($datapay);

		//添加金積分記錄


		$pay_n = M('store')->where(array('uid' => $uid))->getfield('cangku_num');
		$changenums['now_nums'] = $pay_n;
		$changenums['now_nums_get'] = $pay_n;
		$changenums['is_release'] = 1;
		$changenums['pay_id'] = $uid;
		$changenums['get_id'] = 0;
		$changenums['get_nums'] = $tprice;
		$changenums['get_time'] = time();
		$changenums['get_type'] = 7;
		M('tranmoney')->add($changenums);


		//添加眾籌交易記錄
		$dealss["crowds_id"]=$dealid;
		$dealss["uid"]=$uid;
		$dealss["create_time"]=time();
		$dealss["num"]=$num;
		$dealss["dprice"]=$dprice;
		$dealss["tprice"]=$tprice;
		$deal_ss = M('crowds_detail')->add($dealss);

		//添加NYT交易記錄
		$wbaoss["crowds_id"]=$dealid;
		$wbaoss["create_time"]=time();
		$wbaoss["num"]=$num;
		$wbaoss["uid"]=$uid;
		$wbaoss["dprice"]=$dprice;
		$wbaoss["tprice"]=$tprice;
		$wbaoss["type"]=2;//轉入
		$wbao_ss = M('wbao_detail')->add($wbaoss);


		if($res1&&$deal_ss&&$wbao_ss){
			ajaxReturn('購買成功',1,"/Turntable/Crowds/step/1");
		}else{
			ajaxReturn('購買失敗',0);
		}

	}




	//眾籌記錄
	public function Crowdrecords(){

	   $step = I('step');
	   $uid = session('userid');
	   if($step==1){
		$list=M('wbao_detail')->where("type=3 and uid=".$uid)->order("create_time desc")->select(); //釋放記錄
	   }else{
   
		$list=M('crowds_detail')->where("uid=".$uid)->order("create_time desc")->select();
		}

		$this->assign('list',$list);
		$this->assign('step',$step);
		$this->display();
	}

   
}