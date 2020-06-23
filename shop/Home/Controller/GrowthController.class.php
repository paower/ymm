<?php
namespace Home\Controller;
use Think\Controller;
class GrowthController extends CommonController {


	 //===========记录===============
	public function StealDeatail(){
		if(!IS_AJAX){
			return false;
		}
		$userid=session('userid');
		$m=M('steal_detail');
		$where['uid']=$userid;

		$p = I('p','0','intval');
		$page=$p*10;
		$arr=$m->field("num s_num,username uname,type_name,FROM_UNIXTIME(create_time,'%Y-%m-%d %H:%i') as tt ")->where($where)->order('id desc')->limit(
			$page,10)->select();
	   if(empty($arr)){
			   $arr=null; 
		}
		$this->ajaxReturn($arr);
	}

	//充值
	public function Option(){
		$id = session('userid');
		$recharge = M('user')->where("userid=$id")->getField('recharge');
		//判断充值是否关闭
         // $close=is_close_recharge();
         // if($close['value']==0){
         if($recharge==0){
             success_alert('充值暂时关闭',U('index/index'));
         }
		$this->display();
	}

//    转入
	public function Intro(){
		$id = session("userid");
		$to_jm = M('user')->where("userid=$id")->getField('to_jm');
		//判断转入JM是否关闭
         // $close=is_close_to_jm();
         // if($close['value']==0){
         if($to_jm==0){
             success_alert('转入JM暂时关闭',U('index/index'));
         }
		$time = time();
		$userid = session('userid');
		$u_ID = $userid;
		$drpath = './Uploads/Rcode';
		$imgma = 'codes' . $userid . '.png';
		$urel = '/Uploads/Rcode/' . $imgma;
		if (!file_exists($drpath . '/' . $imgma)) {
			sp_dir_create($drpath);
			vendor("phpqrcode.phpqrcode");
			$phpqrcode = new \QRcode();
			$hurl = "http://{$_SERVER['HTTP_HOST']}" . U('Index/Changeout/sid/' . $u_ID);
		   
			$size = "7";
			//$size = "10.10";
			$errorLevel = "L";
			$phpqrcode->png($hurl, $drpath . '/' . $imgma, $errorLevel, $size);
		}
		$this->urel = $urel;
		$this->display();
	}
	public function test(){
		//获取要下载的文件名
		$filename = $_GET['filename'];
		//设置头信息
		ob_end_clean();
//        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//        header('Content-Description: File Transfer');
//        header('Content-Type: application/octet-stream');

//        header('Content-Disposition:attachment;filename=' . basename($filename));
//        header('Content-Length:' . filesize($filename));

		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Length: ' . filesize($filename));
		header('Content-Disposition: attachment; filename=' . basename($filename));

		//读取文件并写入到输出缓冲
		readfile($filename);
		echo "<script>alert('下载成功')</script>";
	}



	//转入明细
	public function Introrecords(){
		$uid = session('userid');
		$where['get_id'] = $uid;
		$where['get_type'] = 0;
		$Chan_info = M('tranmoney')->where($where)->order('id desc')->select();
		$this->assign('Chan_info',$Chan_info);
		$this->assign('uid',$uid);
		$this->display();
	}


	//取消订单
 public function quxiao_order(){

	$id = (int)I('id','intval',0);
	$uid = session('userid');
	$mydeal = M('trans')->where(array("id"=>$id,"payin_id|payout_id"=>$uid,"pay_state"=>array("lt",2)))->find();

	 if(!$mydeal)ajaxReturn('订单不存在~',0);

	$type=$mydeal["trans_type"];
	M('trans_quxiao')->add($mydeal);//把记录复制到另一个表

	
	if($type==0){//卖出单，自己是购买方，只清空payin_id和改变pay_state为0

			$payout['payin_id'] =0;
			$payout['pay_state'] =4;
			$res1 = M('trans')->where(array('id'=>$id))->save($payout); 


	}elseif($type==1){//为购买单，删除订单

		$res1 = M('trans')->delete($id); 


	}

	if($res1){       
	ajaxReturn('取消成功',1);
	}else{
	ajaxReturn('操作失败',1);
	}
}

	// 直接充值
	public function Recharge(){
		$uid = session('userid');
		$cid = trim(I('cid'));

		if(empty($cid)){
			$mapcas['user_id&is_default'] =array($uid,1,'_multi'=>true);
			$carinfo = M('ubanks')->where($mapcas)->count(1);

			if($carinfo < 1){
				$morecars = M('ubanks as u')->join('RIGHT JOIN ysk_bank_name as banks ON u.card_id = banks.pid' )->where(array('u.user_id'=>$uid))->limit(1)->field('u.hold_name,u.id,u.card_number,u.user_id,banks.banq_genre')->find();
				$morecars['type'] = 'bank';
				unset($mapcas);
				$mapcas['userid&is_default'] = array($uid,1,'_multi'=>true);
				$carinfo = M('uwx')->where($mapcas)->count(1);
				if($carinfo == 1){
					$morecars = M('uwx')->where(array('userid'=>$uid,'is_default'=>1))->find();
					$morecars['type'] = 'wx';
				}
				// dump($morecars);
				$mapcas['userid&is_default'] = array($uid,1,'_multi'=>true);
				$carinfo = M('ualipay')->where($mapcas)->count(1);
				if($carinfo == 1){
					$morecars = M('ualipay')->where(array('userid'=>$uid,'is_default'=>1))->find();
					$morecars['type'] = 'alipay';
				}
			}else{
				$morecars = M('ubanks as u')->join('RIGHT JOIN ysk_bank_name as banks ON u.card_id = banks.pid' )->where(array('u.user_id'=>$uid,'is_default'=>1))->limit(1)->field('u.hold_name,u.id,u.card_number,u.user_id,banks.banq_genre')->find();
				$morecars['type'] = 'bank';
			}
			
		}else{
			$morecars = M('ubanks as u')->join('RIGHT JOIN ysk_bank_name as banks ON u.card_id = banks.pid' )->where(array('u.id'=>$cid))->limit(1)->field('u.hold_name,u.id,u.card_number,u.user_id,banks.banq_genre')->find();
			$morecars['type'] = 'bank';
		}


		$pingtai = M('ubanks')->where(array('id'=>1))->find();
		// 创建直接充值订单
		if(IS_AJAX){
			$pwd = trim(I('pwd'));
			$cardid = trim(I('cardid'));//微信，支付宝，银行卡id
			$sellnums = trim(I('sellnums'));//出售数量
			$sellAll = array(200,1800,3600,10000,36000);
			$bank_type = $type = trim(I('type'));//辨别微信，支付宝，银行卡
			$picname = $_FILES['uploadfile']['name'];
			$picsize = $_FILES['uploadfile']['size'];
			$money2 = I('money2');

			$agren = (int)I('agren');
			if($agren != 1){
				ajaxReturn('请勾选《JM商城用户协议》',0);
			}


			// dump($sellnums);dump($sellAll);exit;
			// if (!in_array($sellnums, $sellAll)) {
			// 	ajaxReturn('您选择买入的金额不正确',0);
			// }

			//验证银行卡是否是自己
			if($type == 'bank'){
				$id_Uid = M('ubanks')->where(array('id'=>$cardid))->getField('user_id');
			}elseif($type == 'wx'){
				$id_Uid = M('uwx')->where(array('id'=>$cardid))->getField('userid');
			}elseif($type == 'alipay'){
				$id_Uid = M('ualipay')->where(array('id'=>$cardid))->getField('userid');
			}
			if($id_Uid != $uid){
				if($type == 'bank'){
					ajaxReturn('对不起,该张银行卡不是您的哦~',0);
				}elseif($type == 'wx'){
					ajaxReturn('对不起,该微信不是您的哦~',0);
				}elseif($type == 'alipay'){
					ajaxReturn('对不起,该支付宝不是您的哦~',0);
				}
				
			}
			//验证交易密码
			$minepwd = M('user')->where(array('userid'=>$uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
			$user_object = D('Home/User');
			$user_info = $user_object->Trans($minepwd['account'], $pwd);
			//生成订单
			if($sellnums==1800){
				$type=3;
			}else{
				$type=0;
			}
			
			
			if ($picname != "") {
				if ($picsize > 2014000) { //限制上传大小
					ajaxReturn('图片大小不能超过2M',0);
				}
				$type = strstr($picname, '.'); //限制上传格式
				if ($type != ".gif" && $type != ".jpg" && $type != ".png"  && $type != ".jpeg") {
					ajaxReturn('图片格式不对',0);
				}
				$rand = rand(100, 999);
				$pics = uniqid() . $type; //命名图片名称
				//上传路径
				$pic_path = "./Uploads/Payvos/". $pics;
				move_uploaded_file($_FILES['uploadfile']['tmp_name'], $pic_path);
			}
			$size = round($picsize/1024,2); //转换成kb
			$pic_path = trim($pic_path,'.');
			if($size){
				$data['uid'] = $uid;
				if(!empty($money2)){
					$data['money'] = $money2;
				}else{
					$data['money'] = $sellnums;
				}
				$data['image'] = $pic_path;
				$data['createtime'] = time();
				$data['type'] = $bank_type;
				$data['bank_id'] = $cardid;
				$res_Add = M('recharge')->add($data);
				ajaxReturn('充值订单创建成功，等待后台审核',1);
			}else{
			ajaxReturn('请上传打款截图',0);
			}
		}

		$this->assign('morecars',$morecars);
		$this->assign('pingtai',$pingtai);
		$this->display();

	}

	// 充值记录
	public function Record(){
		$uid = session('userid');
		$data = M('recharge')->where(array('uid'=>$uid))->order('id desc')->field('money,examine,examinetime,createtime,image')->select();
		$this->assign('data',$data);
		$this->display();
	}

	//快速充值
	public function Fastpay(){
		$this->display();
	}

	//微信支付接口
	public function wxpay(){
		$money2 = I('money2')*100;
		if(!empty($money2)){
			$money = $money2;
		}
		// $money= $money;                     //充值金额 微信支付单位为分
		$userip = get_client_ip(); //获得用户设备IP
		$appid  = "wx57cca767b5d66219";                  //应用APPID
		$mch_id = "1520694901";                  //微信支付商户号
		$key    = "adflksjflasjgiugnertjlkfdg35468s";                 //微信商户API密钥
		$out_trade_no = date('YmdHis').rand(1000,9999);//平台内部订单号
		$nonce_str = createNoncestr();//随机字符串
		$body = "购卡";//内容
		$total_fee = $money; //金额
		$spbill_create_ip = $userip; //IP
		$notify_url = "http://ymm.com/Login/wxpayresult"; //回调地址
		$trade_type = 'MWEB';//交易类型 具体看API 里面有详细介绍
		$scene_info ='{"h5_info":{"type":"Wap","wap_url":"http://ymm.com","wap_name":"支付"}}';//场景信息 必要参数
		$signA ="appid=$appid&attach=$out_trade_no&body=$body&mch_id=$mch_id&nonce_str=$nonce_str&notify_url=$notify_url&out_trade_no=$out_trade_no&scene_info=$scene_info&spbill_create_ip=$spbill_create_ip&total_fee=$total_fee&trade_type=$trade_type";
		$strSignTmp = $signA."&key=$key"; //拼接字符串  注意顺序微信有个测试网址 顺序按照他的来 直接点下面的校正测试 包括下面XML  是否正确
		$sign = strtoupper(MD5($strSignTmp)); // MD5 后转换成大写
		$post_data = "<xml>
		                    <appid>$appid</appid>
		                    <mch_id>$mch_id</mch_id>
		                    <body>$body</body>
		                    <out_trade_no>$out_trade_no</out_trade_no>
		                    <total_fee>$total_fee</total_fee>
		                    <spbill_create_ip>$spbill_create_ip</spbill_create_ip>
		                    <notify_url>$notify_url</notify_url>
		                    <trade_type>$trade_type</trade_type>
		                    <scene_info>$scene_info</scene_info>
		                    <attach>$out_trade_no</attach>
		                    <nonce_str>$nonce_str</nonce_str>
		                    <sign>$sign</sign>
		            </xml>";//拼接成XML 格式
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";//微信传参地址
		$dataxml = postXmlCurl($post_data,$url); //后台POST微信传参地址  同时取得微信返回的参数

		$data['uid'] = session('userid');
		$data['total_fee'] =  $money/100;
		$data['out_trade_no'] = $out_trade_no;
		M('wxrecharge')->add($data);

		$objectxml = (array)simplexml_load_string($dataxml, 'SimpleXMLElement', LIBXML_NOCDATA); //将微信返回的XML 转换成数组
		$objectxml['mweb_url'] = $objectxml['mweb_url'] . '&redirect_url=' . $notify_url;
		// dump($objectxml);die;
		if($objectxml['result_code']=='SUCCESS'){
			$this->assign('money',$money);
			$this->assign('objectxml',$objectxml);
			$this->display();
		}else{
			$this->error('充值错误');
		}

	}
	
	//买入
	public function Purchase(){

		$uid = session('userid');
		$cid = trim(I('cid'));
		if(empty($cid)){
			$mapcas['user_id&is_default'] =array($uid,1,'_multi'=>true);
			$carinfo = M('ubanks')->where($mapcas)->count(1);
			// var_dump($carinfo);
			// exit;
			if($carinfo < 1){
				$morecars = M('ubanks as u')->join('RIGHT JOIN ysk_bank_name as banks ON u.card_id = banks.pid' )->where(array('u.user_id'=>$uid))->limit(1)->field('u.hold_name,u.id,u.card_number,u.user_id,banks.banq_genre')->find();
				$morecars['type'] = 'bank';
				unset($mapcas);
				$mapcas['userid&is_default'] = array($uid,1,'_multi'=>true);
				$carinfo = M('uwx')->where($mapcas)->count(1);
				if($carinfo == 1){
					$morecars = M('uwx')->where(array('userid'=>$uid,'is_default'=>1))->find();
					$morecars['type'] = 'wx';
				}
				// dump($morecars);
				$mapcas['userid&is_default'] = array($uid,1,'_multi'=>true);
				$carinfo = M('ualipay')->where($mapcas)->count(1);
				if($carinfo == 1){
					$morecars = M('ualipay')->where(array('userid'=>$uid,'is_default'=>1))->find();
					$morecars['type'] = 'alipay';
				}
			}else{
				$morecars = M('ubanks as u')->join('RIGHT JOIN ysk_bank_name as banks ON u.card_id = banks.pid' )->where(array('u.user_id'=>$uid,'is_default'=>1))->limit(1)->field('u.hold_name,u.id,u.card_number,u.user_id,banks.banq_genre')->find();
				$morecars['type'] = 'bank';
			}
			
		}else{
			$morecars = M('ubanks as u')->join('RIGHT JOIN ysk_bank_name as banks ON u.card_id = banks.pid' )->where(array('u.id'=>$cid))->limit(1)->field('u.hold_name,u.id,u.card_number,u.user_id,banks.banq_genre')->find();
			$morecars['type'] = 'bank';
		}
		//生成买入订单
		if(IS_AJAX){
			$pwd = trim(I('pwd'));
			$sellnums = trim(I('sellnums'));//出售数量
			$cardid = trim(I('cardid'));//微信，支付宝，银行卡id
			$messge = trim(I('messge'));//留言
			$sellAll = array(200,500,1000,5000,10000);
			$type = trim(I('type'));//辨别微信，支付宝，银行卡
			if (!in_array($sellnums, $sellAll)) {
				ajaxReturn('您选择买入的金额不正确',0);
			}
//            //自己是否有足够余额
//            $is_enough = M('store')->where(array('uid'=>$uid))->getField('cangku_num');
//            if($sellnums > $is_enough){
//                ajaxReturn('您当前账户暂无这么多余额~',0);
//            }
			//验证银行卡是否是自己
			if($type == 'bank'){
				$id_Uid = M('ubanks')->where(array('id'=>$cardid))->getField('user_id');
			}elseif($type == 'wx'){
				$id_Uid = M('uwx')->where(array('id'=>$cardid))->getField('userid');
			}elseif($type == 'alipay'){
				$id_Uid = M('ualipay')->where(array('id'=>$cardid))->getField('userid');
			}
			if($id_Uid != $uid){
				if($type == 'bank'){
					ajaxReturn('对不起,该张银行卡不是您的哦~',0);
				}elseif($type == 'wx'){
					ajaxReturn('对不起,该微信不是您的哦~',0);
				}elseif($type == 'alipay'){
					ajaxReturn('对不起,该支付宝不是您的哦~',0);
				}
				
			}
			//扣除比例

			// $transfer_ratio = M('config')->where(array('name'=>'transfer_ratio'))->getField('value');
			// $transfer_sell = $sellnums * $transfer_ratio;

			//验证交易密码
			$minepwd = M('user')->where(array('userid'=>$uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
			$user_object = D('Home/User');
			$user_info = $user_object->Trans($minepwd['account'], $pwd);
			//生成订单
			if($sellnums==1800){
				$type=3;
			}else{
				$type=0;
			}
			
			$data['pay_no'] = build_order_no();
			$data['payin_id'] = $uid;
			$data['out_card'] = $cardid;
			$data['pay_nums'] = $sellnums;
			$data['trade_notes'] = $messge;
			$data['pay_time'] = time();
			$data['trans_type'] = $type;
			$res_Add = M('trans')->add($data);
			//给自己减少这么多余额
			if($res_Add){
//                $doDec = M('store')->where(array('uid'=>$uid))->setDec('cangku_num',$sellnums);
				ajaxReturn('买入订单创建成功',1);
			}
		}
		$this->assign('morecars',$morecars);
		$this->display();

	}

	//添加银行卡
	public function test1(){
		$sellnums = array(500,1000,3000,5000,10000,30000);
		$sellnums = 5000;//出售数量
		$sellAll = array(500,1000,3000,'5000',10000,30000);
		if (!in_array(500, $sellAll)) {
			echo "Got Irix";
		}
	}


	public function user_agreem(){
		$detail = M('news')->where(array('id'=>106))->find();
		$this->assign('detail',$detail);
		$this->display();
	}



	/**
	 * 增加银行卡
	 */
	public function Addbank(){
		$bakinfo = M('bank_name')->order('q_id asc')->select();
		$this->assign('bakinfo',$bakinfo);
		if(IS_AJAX){
			$uid = session('userid');
			$crkxm = I('crkxm');
			$khy = I('khy');
			$yhk = I('yhk');
			$khzy = I('khzy');
			$is_default = I('post.is_default');

			if(empty($crkxm)){
				ajaxReturn('请输入真实姓名',0);
			}
			if(empty($khy)){
			   ajaxReturn('请选择开户行',0);
			}
			if(empty($yhk)){
				ajaxReturn('请输入银行卡号',0);
			}
			if(empty($khzy)){
				ajaxReturn('请输入开户支行',0);
			}

			$data['hold_name'] = $crkxm;
			$data['card_id'] = $khy;
			$data['card_number'] = $yhk;
			$data['open_card'] = $khzy;
			$data['add_time'] = time();
			$data['user_id'] = $uid;
			$data['is_default'] = $is_default;

			$res_addcard = M('ubanks')->add($data);
			if($res_addcard){
				//设置用户银行卡姓名
				$bank_uname = M('user')->where(array('userid'=>$uid))->getField('bank_uname');
				if(empty($bank_uname)){
					M('user')->where(array('userid'=>$uid))->setField('bank_uname',$crkxm);
				}
					ajaxReturn('银行卡添加成功',1,'/Growth/Cardinfos');
			}
		}
		$this->display();
	}

	


	public function Addweixin(){
		if(IS_POST){
			$data['wx_name']=I('post.wx_name');
			$data['wx_num']=I('post.wx_num');
			if($_FILES['wx_qrcode']['tmp_name'])
			{
				$inf=$this->upload();
				$data['wx_qrcode']=$inf['wx_qrcode']['savepath'].$inf['wx_qrcode']['savename'];
			}else{
				
			}
			$data['add_time'] = time();
			$data['userid'] = session('userid');
			$res=M('uwx')->add($data);
			if($res){
				ajaxReturn('添加成功',1,'/Growth/Cardinfos');
			}else{
				ajaxReturn('添加失败',2,'/Growth/Cardinfos');
			}
			
		}
		$this->display();
	}
	public function AddAlipay(){
	   if(IS_POST){
			$data['alipay_name']=I('post.zfb_name');
			$data['alipay_num']=I('post.zfb_num');
			if($_FILES['zfb_qrcode']['tmp_name'])
			{
				$inf=$this->upload();
				$data['alipay_qrcode']=$inf['zfb_qrcode']['savepath'].$inf['zfb_qrcode']['savename'];
			}else{
				
			}
			$data['add_time'] = time();
			$data['userid'] = session('userid');
			$res=M('ualipay')->add($data);
			if($res){
				ajaxReturn('添加成功',1,'/Growth/Cardinfos');
			}else{
				ajaxReturn('添加失败',2,'/Growth/Cardinfos');
			}
			
		}
		$this->display();
	}
	
	//订单中心
	public function Nofinsh(){
		$state = trim(I('state'));
		$uid = session('userid');
		$traInfo = M('trans');
		if($state > 0){
			$where['pay_state'] =  array('between','1,2');
		}else{
			$where['pay_state'] = 0;
		}
		$where['payin_id'] = $uid;

		//分页
		$p=getpage($traInfo,$where,20);
		$page=$p->show();
		$orders = $traInfo->where($where)->order('id desc')->select();
		
		
		foreach($orders as $k =>$v){
			if($v['payin_id'] != ''){
				//银行卡号.开户支行.开户银行
				$uinfomsg = M('user')->where(array('userid'=>$v['payout_id']))->Field('username,mobile')->find();
				if($v['card_type'] == 'bank'){
					$banks = M('ubanks');
					$bankinfos = $banks ->where(array('id'=>$v['card_id']))->field('hold_name,card_number,card_id,open_card,bank_name')->find();
					if($v['payout_id'] == 1){
						$orders[$k]['cardnum'] = $bankinfos['card_number'];
						$orders[$k]['bname'] = $bankinfos['bank_name'];
						$orders[$k]['openrds'] = $bankinfos['open_card'];
						$orders[$k]['uname'] = $bankinfos['hold_name'];
						$orders[$k]['umobile'] = M('admin')->where(array('id'=>5))->getField('mobile');
						$orders[$k]['type'] = $v['card_type'];
					}else{
						$orders[$k]['cardnum'] = $bankinfos['card_number'];
						$orders[$k]['bname'] = $bankinfos['bank_name'];
						$orders[$k]['openrds'] = $bankinfos['open_card'];
						$orders[$k]['uname'] = $uinfomsg['hold_name'];
						$orders[$k]['umobile'] = $uinfomsg['mobile'];
						$orders[$k]['type'] = $v['card_type'];
					}
				}elseif($v['card_type'] == 'wx'){
					if($v['payout_id'] == 1){
						$wxinfos = M('ubanks')->where(array('id'=>$v['card_id']))->field('wx_num,wx_name,hold_name')->find();
						$orders[$k]['uname'] = $wxinfos['hold_name'];
						$orders[$k]['wxnum'] = $wxinfos['wx_num'];
						$orders[$k]['wxname'] = $wxinfos['wx_name'];
						
						$orders[$k]['umobile'] = M('admin')->where(array('id'=>5))->getField('mobile');
						$orders[$k]['type'] = $v['card_type'];
					}else{
						$wxinfos = M('uwx')->where(array('id'=>$v['card_id']))->find();
						$orders[$k]['uname'] = $uinfomsg['username'];
						$orders[$k]['wxnum'] = $wxinfos['wx_num'];
						$orders[$k]['wxname'] = $wxinfos['wx_name'];
						$orders[$k]['umobile'] = $uinfomsg['mobile'];
						$orders[$k]['type'] = $v['card_type'];
					}
				}elseif($v['card_type'] == 'alipay'){
					if($v['payout_id'] == 1){
						$alipay = M('ubanks')->where(array('id'=>$v['card_id']))->field('hold_name,zfb_name,zfb_num')->find();
						$orders[$k]['uname'] = $alipay['hold_name'];
						$orders[$k]['alipaynum'] = $alipay['zfb_num'];
						$orders[$k]['alipayname'] = $alipay['zfb_name'];

						$orders[$k]['umobile'] = M('admin')->where(array('id'=>5))->getField('mobile');
						$orders[$k]['type'] = $v['card_type'];
					}else{
						$alipay = M('ualipay')->where(array('id'=>$v['card_id']))->find();
						$orders[$k]['uname'] = $uinfomsg['username'];
						$orders[$k]['alipaynum'] = $alipay['alipay_num'];
						$orders[$k]['alipayname'] = $alipay['alipay_name'];
						$orders[$k]['umobile'] = $uinfomsg['mobile'];
						$orders[$k]['type'] = $v['card_type'];
					}
					
				}

			}
		}
		
		$this->assign('state',$state);
		$this->assign('orders',$orders);
		$this->assign('page',$page);
		$this->display();
	}
	//确认打款
	public function Conpay(){
		//查询我买入的
		$uid = session('userid');
		$traInfo = M('trans');
		$banks = M('ubanks');
		$where['payin_id'] = $uid;
		$where['pay_state'] = 1;
		//分页
		$p=getpage($traInfo,$where,20);
		$page=$p->show();
		$orders = $traInfo->where($where)->order('id desc')->select();
		
		//收款人
		foreach($orders as $k =>$v){
			//银行卡号.开户支行.开户银行
			$uinfomsg = M('user')->where(array('userid'=>$v['payout_id']))->Field('username,mobile')->find();
			if($v['payout_id'] == 1){

				$bankinfos = $banks ->where(array('id'=>$v['card_id']))->field('bank_name,hold_name,card_number,card_id,open_card,wx_name,wx_num,zfb_name,zfb_num,wx_qrcode,zfb_qrcode')->find();
				$orders[$k]['cardnum'] = $bankinfos['card_number'];
				$orders[$k]['bname'] = $bankinfos['bank_name'];
				$orders[$k]['openrds'] = $bankinfos['open_card'];
				$orders[$k]['uname'] = $uinfomsg['username'];
				$orders[$k]['umobile'] = M('admin')->where(array('id'=>5))->getField('mobile');
				$orders[$k]['wx_name'] = $bankinfos['wx_name'];
				$orders[$k]['wx_num'] = $bankinfos['wx_num'];
				$orders[$k]['wx_qrcode'] = $bankinfos['wx_qrcode'];
				$orders[$k]['zfb_name'] = $bankinfos['zfb_name'];
				$orders[$k]['zfb_num'] = $bankinfos['zfb_num'];
				$orders[$k]['zfb_qrcode'] = $bankinfos['zfb_qrcode'];
			}else{
				$bankinfos = $banks ->where(array('id'=>$v['card_id']))->field('bank_name,hold_name,card_number,card_id,open_card')->find();
				$wxinfos = M('uwx')->where(array('userid'=>$v['payout_id']))->order('id desc')->find();
				$alipay = M('ualipay')->where(array('userid'=>$v['payout_id']))->order('id desc')->find();
				
				$orders[$k]['cardnum'] = $bankinfos['card_number'];
				$orders[$k]['bname'] = M('bank_name')->where(array('q_id'=>$bankinfos['card_id']))->getField('banq_genre');
				$orders[$k]['openrds'] = $bankinfos['open_card'];
				$orders[$k]['uname'] = $uinfomsg['username'];
				$orders[$k]['umobile'] = $uinfomsg['mobile'];
				if($wxinfos){
					$orders[$k]['wx_name'] = $wxinfos['wx_name'];
					$orders[$k]['wx_num'] = $wxinfos['wx_num'];
					$orders[$k]['wx_qrcode'] = $wxinfos['wx_qrcode'];
				}else{
					$orders[$k]['wx_name'] = '暂无记录';
					$orders[$k]['wx_num'] = '暂无记录';
					$orders[$k]['wx_qrcode'] = '暂无记录';
				}
				if($alipay){
					$orders[$k]['zfb_name'] = $alipay['alipay_name'];
					$orders[$k]['zfb_num'] = $alipay['alipay_num'];
					$orders[$k]['zfb_qrcode'] = $alipay['alipay_qrcode'];
				}else{
					$orders[$k]['zfb_name'] = '暂无记录';
					$orders[$k]['zfb_num'] = '暂无记录';
					$orders[$k]['zfb_qrcode'] = '暂无记录';
				}
				
			}
			
			
			
		}
		
		/* var_dump($orders);
		exit;  */
		
		if(IS_AJAX){
			$uid = session('userid');
			$picname = $_FILES['uploadfile']['name'];
			$picsize = $_FILES['uploadfile']['size'];
			$trid = trim(I('trid'));

			if($trid <= 0){
				ajaxReturn('提交失败,请重新提交',0);
			}
			if ($picname != "") {
				if ($picsize > 2014000) { //限制上传大小
					ajaxReturn('图片大小不能超过2M',0);
				}
				$type = strstr($picname, '.'); //限制上传格式
				if ($type != ".gif" && $type != ".jpg" && $type != ".png"  && $type != ".jpeg") {
					ajaxReturn('图片格式不对',0);
				}
				$rand = rand(100, 999);
				$pics = uniqid() . $type; //命名图片名称
				//上传路径
				$pic_path = "./Uploads/Payvos/". $pics;
				move_uploaded_file($_FILES['uploadfile']['tmp_name'], $pic_path);
			}
			$size = round($picsize/1024,2); //转换成kb
			$pic_path = trim($pic_path,'.');
			if($size){
				$res = M('trans')->where(array('id'=>$trid))->setField(array('trans_img'=>$pic_path,'pay_state'=>2));
				if($res){
					ajaxReturn('打款提交成功',1,'/Growth/Conpay');
				}else{
					ajaxReturn('打款提交失败',0);
				}
			}
		}
		$this->assign('page',$page);
		$this->assign('orders',$orders);
		$this->display();
	}

	// 设置默认银行
	public function isDefault(){
		$is_default = I('post.is_default');
		$cid = I('post.cid');
		$type = I('post.type');
		$userid = session('userid');
		$lei = I('post.lei'); 
		if($is_default == 0){
			M('ubanks')->where(array('user_id'=>$userid,'is_default'=>1))->setField('is_default',0);
			M('uwx')->where(array('userid'=>$userid,'is_default'=>1))->setField('is_default',0);
			M('ualipay')->where(array('userid'=>$userid,'is_default'=>1))->setField('is_default',0);
			
			if($lei == 'bank'){
				$res2 = M('ubanks')->where(array('id'=>$cid,'user_id'=>$userid))->setField('is_default',1);
			}elseif($lei == 'wx'){
				$res2 = M('uwx')->where(array('id'=>$cid,'userid'=>$userid))->setField('is_default',1);
			}elseif($lei == 'alipay'){
				$res2 = M('ualipay')->where(array('id'=>$cid,'userid'=>$userid))->setField('is_default',1);
			}
			
			if($res2 != false){
				if($type == 'SellCentr'){
					ajaxReturn('修改成功',0,"/Trading/{$type}");
				}elseif($type == 'Purchase'){
					ajaxReturn('修改成功',0,"/Growth/{$type}");
				}elseif($type == 'Recharge'){
					ajaxReturn('修改成功',0,"/Growth/{$type}");
				}else{
					ajaxReturn('修改成功',1);
				}
			}else{
				ajaxReturn('修改失败,请重新尝试',1);
			}
		}else{
			if($type == 'SellCentr'){
				ajaxReturn('修改成功',0,"/Trading/{$type}");
			}elseif($type == 'Purchase'){
				ajaxReturn('修改成功',0,"/Growth/{$type}");
			}elseif($type == 'Recharge'){
				ajaxReturn('修改成功',0,"/Growth/{$type}");
			}else{
				ajaxReturn('修改成功',1);
			}
		}
	}

	public function Paidimg(){
		$id = I('id');
		$imginfo = M('trans')->where(array('id'=>$id))->getField('trans_img');
		$this->assign('imginfo',$imginfo);

		$this->display();
	}

	//已完成订单
	public function Dofinsh(){
		//查询我买入的
		$uid = session('userid');
		$traInfo = M('trans');
		$banks = M('ubanks');
		$where['payin_id'] = $uid;
		$where['pay_state'] = 3;
		//分页
		$p=getpage($traInfo,$where,20);
		$page=$p->show();
		$orders = $traInfo->where($where)->order('id desc')->select();
		//收款人
		foreach($orders as $k =>$v){
			//银行卡号.开户支行.开户银行
			$bankinfos = $banks ->where(array('id'=>$v['card_id']))->field('hold_name,card_number,card_id,open_card')->find();
			$uinfomsg = M('user')->where(array('userid'=>$v['payout_id']))->Field('username,mobile')->find();
			$orders[$k]['cardnum'] = $bankinfos['card_number'];
			$orders[$k]['bname'] = M('bank_name')->where(array('q_id'=>$bankinfos['card_id']))->getfield('banq_genre');
			$orders[$k]['openrds'] = $bankinfos['open_card'];
			$orders[$k]['uname'] = $uinfomsg['username'];
			$orders[$k]['umobile'] = $uinfomsg['mobile'];
		}
		$this->assign('page',$page);
		$this->assign('orders',$orders);
		$this->display();
	}

	//买入记录
	public function Buyrecords(){
		$traInfo = M('trans');
		$uid = session('userid');
		$where['payin_id'] = $uid;
		//分页
		$p=getpage($traInfo,$where,20);
		$page=$p->show();
		$Chan_info = $traInfo->where($where)->order('id desc')->select();
		foreach ($Chan_info as $k =>$v){
			$Chan_info[$k]['username'] = M('user')->where(array('userid'=>$v['payout_id']))->getField('username');
			$Chan_info[$k]['get_timeymd'] = date('Y-m-d',$v['pay_time']);
			$Chan_info[$k]['get_timedate'] = date('H:i:s',$v['pay_time']);
		}
		if(IS_AJAX){
			if(count($Chan_info) >= 1) {
				ajaxReturn($Chan_info,1);
			}else{
				ajaxReturn('暂无记录',0);
			}
		}
		$this->assign('page',$page);
		$this->assign('Chan_info',$Chan_info);
		$this->assign('uid',$uid);
		$this->display();
	}


//卖入中心
	public function Buycenter(){
		if(IS_AJAX){
			$pricenum = I('mvalue');
			if($pricenum == ''){
				ajaxReturn('请选择正确的订单价格',0);
			}
			if($pricenum == 'NaN'){ 	
				$where = array(
					'tr.pay_state'=>0,
					'tr.trans_type'=>1,
				);
			}else{
				$where = array(
					'tr.pay_state'=>0,
					'tr.trans_type'=>1,
					'tr.pay_nums'=>$pricenum
				);
			}
			$order_info = M('trans as tr')->join('LEFT JOIN  ysk_user as us on tr.payout_id = us.userid')->where($where)->order('id desc')->select();

			foreach($order_info as $k => $v){
				$order_info[$k]['cardinfo'] = M('bank_name')->where(array('q_id'=>$v['card_id']))->getfield('banq_genre');
				$order_info[$k]['spay'] = $v['pay_nums'];
			}
			if(count($order_info) <= 0){
				ajaxReturn('没找到相关记录',0);
			}else{
				ajaxReturn($order_info,1);
			}
		}
		$this->display();
	}

	public function Dopurs(){
		if(IS_AJAX){
			$uid = session('userid');
			$trid = I('trid',1,'intval');
			$pwd = trim(I('pwd'));
			$sellnums = M('trans')->where(array('id'=>$trid))->field('pay_nums,payout_id,pay_state')->find();

			$sellAll = array(200,1800,3600,10000,36000);
			if (!in_array($sellnums['pay_nums'], $sellAll)) {
				ajaxReturn('您选择购买的金额不正确',0);
			}
			if($sellnums['payout_id'] == $uid){
				ajaxReturn('您不能买入自己上架的哦~',0);
			}
			if($sellnums['pay_state'] != 0){
				ajaxReturn('该订单存在异常,暂时无法购买哦~',0);
			}
			//验证交易密码
			$minepwd = M('user')->where(array('userid'=>$uid))->Field('account,mobile,safety_pwd,safety_salt')->find();
			$user_object = D('Home/User');
			$user_info = $user_object->Trans($minepwd['account'], $pwd);
			//记录买入会员
			$res_Buy = M('trans')->where(array('id'=>$trid))->setField(array('payin_id'=>$uid,'pay_state'=>1));
			if($res_Buy){

				ajaxReturn('买入成功',1);
			}
		}
		$this->display();
	}
	//银行卡信息
	public function Cardinfos(){
		$uid = session('userid');
		$type = I('type');
		$morecars = M('ubanks as u')->join('RIGHT JOIN ysk_bank_name as banks ON u.card_id = banks.pid' )->where(array('u.user_id'=>$uid))->order('u.id desc')->field('u.hold_name,u.id,u.card_number,u.user_id,banks.banq_genre,banks.banq_img,u.wx_name,u.wx_num,u.zfb_name,u.zfb_num,u.is_default')->select();
		$wechat = M('uwx')->where(array('userid'=>$uid))->order('id desc')->select();
		$alipay = M('ualipay')->where(array('userid'=>$uid))->order('id desc')->select();
		if(IS_AJAX){
			$cardid = I('bangid');
			$type = I('post.type');
			if($type == 1){
				//是否是自己绑定的银行卡
				$isuid = M('ubanks')->where(array('id'=>$cardid))->getField('user_id');
				if($isuid != $uid){
					ajaxReturn('该张银行卡暂不属于您~',0);
				}
				$res = M('ubanks')->where(array('id'=>$cardid))->delete();
				if($res){
					ajaxReturn('该银行卡删除成功',1,'/Growth/Cardinfos');
				}
			}elseif($type == 2){
				// 是否是自己绑定的微信
				$isuid = M('uwx')->where(array('id'=>$cardid))->getField('userid');
				if($isuid != $uid){
					ajaxReturn('该微信不属于您~',0);
				}
				$res = M('uwx')->where(array('id'=>$cardid))->delete();
				if($res){
					ajaxReturn('该微信删除成功',1,'/Growth/Cardinfos');
				}
			}elseif($type == 3){
				// 是否是自己绑定的支付宝
				$isuid = M('ualipay')->where(array('id'=>$cardid))->getField('userid');
				if($isuid != $uid){
					ajaxReturn('该微信不属于您~',0);
				}
				$res = M('ualipay')->where(array('id'=>$cardid))->delete();
				if($res){
					ajaxReturn('该支付宝删除成功',1,'/Growth/Cardinfos');
				}
			}
		}
		$this->assign('type',$type);
		$this->assign('morecars',$morecars)->assign('wechat',$wechat)->assign('alipay',$alipay);
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
}