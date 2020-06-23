<?php
// +----------------------------------------------------------------------
// | #
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Think\Page;

/**
 * 系统配置控制器
 * 
 */
class ConfigController extends AdminController
{
  //客服配置
  public function kefu()
  {
    $info = I('photo');
    $weixin = I('name');
    $img = $this->upload($info);
    $where['name']='kf_weixin';
    $where2['name']='kf_ewm';
    M('config')->where($where2)->setField('value',$img);
    M('config')->where($where)->setField('value',$weixin);
    $this->success('保存成功');
  }

  public function upload(){
    $upload = new \Think\Upload();// 实例化上传类
    $upload->maxSize   =     3145728 ;// 设置附件上传大小
    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
    $upload->savePath  =     ''; // 设置附件上传（子）目录
    // 上传文件 
    $info   =   $upload->upload();
    if(!$info) {// 上传错误提示错误信息
        $this->error($upload->getError());
    }else{// 上传成功
        return '/Uploads/'.$info['photo']['savepath'].$info['photo']['savename'];
    }
  }
  //兑换开关
  public function change_exchange()
  {
    $info = I();
    $data['value']=$info['close_reg'];
    $data['tip']=$info['tip'];
    $where['name']="exchange";
    M('Config')->where($where)->save($data);
    $this->success('保存成功');
  }

  //提现开关
  public function change_tixian()
  {
    $info = I();
    $data['value']=$info['close_reg'];
    $data['tip']=$info['tip'];
    $where['name']="tixian";
    M('Config')->where($where)->save($data);
    $this->success('保存成功');
  }

  //充值开关
  public function change_recharge()
  {
    $info = I();
    $data['value']=$info['close_reg'];
    $data['tip']=$info['tip'];
    $where['name']="recharge";
    M('Config')->where($where)->save($data);
    $this->success('保存成功');
  }

  //转出JM开关
  public function change_out_jm()
  {
    $info = I();
    $data['value']=$info['close_reg'];
    $data['tip']=$info['tip'];
    $where['name']="out_jm";
    M('Config')->where($where)->save($data);
    $this->success('保存成功');
  }

  //转入JM开关
  public function change_to_jm()
  {
    $info = I();
    $data['value']=$info['close_reg'];
    $data['tip']=$info['tip'];
    $where['name']="to_jm";
    M('Config')->where($where)->save($data);
    $this->success('保存成功');
  }

  //修改会员等级名称
  public function change_user_level_name()
  {
    $info = I();
    // dump($info);die;
    $is_level1 = M('vip_level')->where("level=1")->count();
    if($is_level1==0){
      $data1['level']=1;
      $data1['name']=$info['l1name'];
      $data1['pay_money']=$info['l1pay_money'];
      M('vip_level')->where("level=1")->delete();
      M('vip_level')->add($data1);
    }else{
      $data1['name']=$info['l1name'];
      $data1['pay_money']=$info['l1pay_money'];
      M('vip_level')->where("level=1")->save($data1);
    }
    $is_level2 = M('vip_level')->where("level=2")->count();
    if($is_level2==0){
      $data2['level']=2;
      $data2['name']=$info['l2name'];
      $data2['pay_money']=$info['l2pay_money'];
      M('vip_level')->where("level=2")->delete();
      M('vip_level')->add($data2);
    }else{
      $data2['name']=$info['l2name'];
      $data2['pay_money']=$info['l2pay_money'];
      M('vip_level')->where("level=2")->save($data2);
    }
    $is_level3 = M('vip_level')->where("level=3")->count();
    if($is_level3==0){
      $data3['level']=3;
      $data3['name']=$info['l3name'];
      $data3['pay_money']=$info['l3pay_money'];
      M('vip_level')->where("level=3")->delete();
      M('vip_level')->add($data3);
    }else{
      $data3['name']=$info['l3name'];
      $data3['pay_money']=$info['l3pay_money'];
      M('vip_level')->where("level=3")->save($data3);
    }
    $is_leveld4 = M('vip_level')->where("level=4")->count();
    if($is_level4==0){
      $data4['level']=4;
      $data4['name']=$info['l4name'];
      $data4['pay_money']=$info['l4pay_money'];
      M('vip_level')->where("level=4")->delete();
      M('vip_level')->add($data4);
    }else{
      $data4['name']=$info['l4name'];
      $data4['pay_money']=$info['l4pay_money'];
      M('vip_level')->where("level=4")->save($data4);
    }
    $is_level5 = M('vip_level')->where("level=5")->count();
    if($is_level5==0){
      $data5['level']=5;
      $data5['name']=$info['l5name'];
      $data5['pay_money']=$info['l5pay_money'];
      M('vip_level')->where("level=5")->delete();
      M('vip_level')->add($data5);
    }else{
      $data5['name']=$info['l5name'];
      $data5['pay_money']=$info['l5pay_money'];
      M('vip_level')->where("level=5")->save($data5);
    }
	$is_level6 = M('vip_level')->where("level=6")->count();
    if($is_level6==0){
      $data6['level']=6;
      $data6['name']=$info['l6name'];
      $data6['pay_money']=$info['l6pay_money'];
      M('vip_level')->where("level=6")->delete();
      M('vip_level')->add($data6);
    }else{
      $data6['name']=$info['l6name'];
      $data6['pay_money']=$info['l6pay_money'];
      M('vip_level')->where("level=6")->save($data6);
    }
    $is_level7 = M('vip_level')->where("level=7")->count();
    if($is_level7==0){
      $data7['level']=7;
      $data7['name']=$info['l7name'];
      $data7['pay_money']=$info['l7pay_money'];
      M('vip_level')->where("level=7")->delete();
      M('vip_level')->add($data7);
    }else{
      $data7['name']=$info['l7name'];
      $data7['pay_money']=$info['l7pay_money'];
      M('vip_level')->where("level=7")->save($data7);
    }
    $is_level8 = M('vip_level')->where("level=8")->count();
    if($is_level8==0){
      $data8['level']=8;
      $data8['name']=$info['l8name'];
      $data8['pay_money']=$info['l8pay_money'];
      M('vip_level')->where("level=8")->delete();
      M('vip_level')->add($data8);
    }else{
      $data8['name']=$info['l8name'];
      $data8['pay_money']=$info['l8pay_money'];
      M('vip_level')->where("level=8")->save($data8);
    }
    $is_level9 = M('vip_level')->where("level=9")->count();
    if($is_level9==0){
      $data9['level']=9;
      $data9['name']=$info['l9name'];
      $data9['pay_money']=$info['l9pay_money'];
      M('vip_level')->where("level=9")->delete();
      M('vip_level')->add($data9);
    }else{
      $data9['name']=$info['l9name'];
      $data9['pay_money']=$info['l9pay_money'];
      M('vip_level')->where("level=9")->save($data9);
}
    echo 1;die;
  }

  //修改兑换说明
  public function exchange_instructions()
  {
    $info = I();
    foreach ($info as $k => $v) {
      $check = M('exchange_instructions')->where("name='%s'",array($k))->count();
      if($check==0){
        $data['name']=$k;
        $data['content']=$v;
        M('exchange_instructions')->add($data);
      }else{
        $data['content']=$v;
        M('exchange_instructions')->where("name='%s'",array($k))->save($data);
      }
    }
    echo 1;die;
  }



    public function msgs()
	{	
		if(IS_POST){
			
			$content=I('MSG');
			$account=I('MSG_account');
			$password=I('MSG_password');
			$re1=M('config','nc')->where(array('name'=>'MSG_password'))->setField('value',$password);
			$re2=M('config','nc')->where(array('name'=>'MSG_account'))->setField('value',$account);
			$re3=M('config','nc')->where(array('name'=>'MSG'))->setField('value',$content);
			
				return $this->success('修改成功', U('Config/msgs'));
			
		}
		$this->breadcrumb2 = '短信设置';
	
		$content=M('config','nc')->where(array('name'=>'MSG'))->getField('value');
   	 	$account=M('config','nc')->where(array('name'=>'MSG_account'))->getField('value');
		$password=M('config','nc')->where(array('name'=>'MSG_password'))->getField('value');
		$this->assign('account', $account);
		$this->assign('password', $password);
		$this->assign('content', $content);
		$this->display();
	}



    /**
     * 获取某个分组的配置参数
     */
    public function group($group = 4)
    {
        //根据分组获取配置
        $map['group']  = array('eq', $group);
        $field         = 'name,value,tip,type';
        $data_list     = D('Config')->lists($map,$field);
        $display=array(1=>'base',2=>'system',3=>'siteclose',4=>'fee',5=>'price',6=>'zhongchou');
        // dump($data_list);die;
        $this->assign('info',$data_list)->display($display[$group]);
    }

 public function group1($group = 4)
    {
        //根据分组获取配置
     $config_object = D('Config');
     $growem=$config_object->where("name='growem'")->getField('value');
      

        $data_list=array();

        for($i=1;$i<=5;$i++){
           $data_list[]= D('coindets')->where("cid=".$i)->order('coin_addtime desc')->find();

        }

        $this->assign('info',$data_list)->assign('growem',$growem)->display("price");
    }



    public function group2($group = 1)
    {
        //根据分组获取配置
        // $jifen= D('config')->where("name='jifen'")->getField("value");
        // $regjifen= D('config')->where("name='regjifen'")->getField("value");
        //   $jifens= D('config')->where("name='jifens'")->getField("value");
        // $rens= D('config')->where("name='rens'")->getField("value");
        
        // $coins=M('coins')->where("name='MXC'")->find();
        
        // $reward=M('reward')->where('id=1')->find();
    
        // $coins['bili'] = number_format($coins['bili'],4);
        // $this->assign('time',time())->assign('jifen',$jifen)->assign('regjifen',$regjifen)->assign('jifens',$jifens)->assign('rens',$rens)->assign('coins',$coins)->assign('reward',$reward)->display("base");
      $info = M('exchange_instructions')->select();
      $info2 = M('vip_level')->order('level asc')->select();
      // dump($info2);die;
      $this->assign('info2',$info2);
      $this->assign('info',$info);
      $this->display('basic');
    }




//众筹设置
    public function group3()
    {
        //根据分组获取配置
    $time_n=time();
    $open_time=date("Y-m-d");

    $is_has=M('crowds')->where("open_time<=".$time_n." and status<>2")->order("create_time desc")->find();

    if($is_has){
      $jindu=$is_has['jindu'];
      $open_time=date("Y-m-d",$is_has['open_time']);
      $num=(int)$is_has['num'];
      $id=(int)$is_has['id'];
    }
         

        $this->assign('open_time',$open_time)->assign('is_has',$is_has)->assign('jindu',$jindu)->assign('id',$id)->assign('num',$num)->display("zhongchou");
    }


//奖励设置
    public function group4()
    {
    //一级
		$rechar_firstReward =  M('config')->where(array('name'=>'rechar_firstReward'))->getField('value');
		$rechar_secondReward =  M('config')->where(array('name'=>'rechar_secondReward'))->getField('value');
		$reward_to_rechar =  M('config')->where(array('name'=>'reward_to_rechar'))->getField('value');
		$reward_to_shopEta =  M('config')->where(array('name'=>'reward_to_shopEta'))->getField('value');

    $rechar2_firstReward =  M('config')->where(array('name'=>'rechar2_firstReward'))->getField('value');
    $rechar2_secondReward =  M('config')->where(array('name'=>'rechar2_secondReward'))->getField('value');
    $reward2_to_rechar =  M('config')->where(array('name'=>'reward2_to_rechar'))->getField('value');
    $reward2_to_shopEta =  M('config')->where(array('name'=>'reward2_to_shopEta'))->getField('value');

    //二级
    $level2_rechar_firstReward =  M('config')->where(array('name'=>'level2_rechar_firstReward'))->getField('value');
    $level2_rechar_secondReward =  M('config')->where(array('name'=>'level2_rechar_secondReward'))->getField('value');
    $level2_reward_to_rechar =  M('config')->where(array('name'=>'level2_reward_to_rechar'))->getField('value');
    $level2_reward_to_shopEta =  M('config')->where(array('name'=>'level2_reward_to_shopEta'))->getField('value');

    $level2_rechar2_firstReward =  M('config')->where(array('name'=>'level2_rechar2_firstReward'))->getField('value');
    $level2_rechar2_secondReward =  M('config')->where(array('name'=>'level2_rechar2_secondReward'))->getField('value');
    $level2_reward2_to_rechar =  M('config')->where(array('name'=>'level2_reward2_to_rechar'))->getField('value');
    $level2_reward2_to_shopEta =  M('config')->where(array('name'=>'level2_reward2_to_shopEta'))->getField('value');
    
    //三级
    $level3_rechar_firstReward =  M('config')->where(array('name'=>'level3_rechar_firstReward'))->getField('value');
    $level3_rechar_secondReward =  M('config')->where(array('name'=>'level3_rechar_secondReward'))->getField('value');
    $level3_reward_to_rechar =  M('config')->where(array('name'=>'level3_reward_to_rechar'))->getField('value');
    $level3_reward_to_shopEta =  M('config')->where(array('name'=>'level3_reward_to_shopEta'))->getField('value');

    $level3_rechar2_firstReward =  M('config')->where(array('name'=>'level3_rechar2_firstReward'))->getField('value');
    $level3_rechar2_secondReward =  M('config')->where(array('name'=>'level3_rechar2_secondReward'))->getField('value');
    $level3_reward2_to_rechar =  M('config')->where(array('name'=>'level3_reward2_to_rechar'))->getField('value');
    $level3_reward2_to_shopEta =  M('config')->where(array('name'=>'level3_reward2_to_shopEta'))->getField('value');

    //四级
    $level4_rechar_firstReward =  M('config')->where(array('name'=>'level4_rechar_firstReward'))->getField('value');
    $level4_rechar_secondReward =  M('config')->where(array('name'=>'level4_rechar_secondReward'))->getField('value');
    $level4_reward_to_rechar =  M('config')->where(array('name'=>'level4_reward_to_rechar'))->getField('value');
    $level4_reward_to_shopEta =  M('config')->where(array('name'=>'level4_reward_to_shopEta'))->getField('value');

    $level4_rechar2_firstReward =  M('config')->where(array('name'=>'level4_rechar2_firstReward'))->getField('value');
    $level4_rechar2_secondReward =  M('config')->where(array('name'=>'level4_rechar2_secondReward'))->getField('value');
    $level4_reward2_to_rechar =  M('config')->where(array('name'=>'level4_reward2_to_rechar'))->getField('value');
    $level4_reward2_to_shopEta =  M('config')->where(array('name'=>'level4_reward2_to_shopEta'))->getField('value');

    //五级
    $level5_rechar_firstReward =  M('config')->where(array('name'=>'level5_rechar_firstReward'))->getField('value');
    $level5_rechar_secondReward =  M('config')->where(array('name'=>'level5_rechar_secondReward'))->getField('value');
    $level5_reward_to_rechar =  M('config')->where(array('name'=>'level5_reward_to_rechar'))->getField('value');
    $level5_reward_to_shopEta =  M('config')->where(array('name'=>'level5_reward_to_shopEta'))->getField('value');

    $level5_rechar2_firstReward =  M('config')->where(array('name'=>'level5_rechar2_firstReward'))->getField('value');
    $level5_rechar2_secondReward =  M('config')->where(array('name'=>'level5_rechar2_secondReward'))->getField('value');
    $level5_reward2_to_rechar =  M('config')->where(array('name'=>'level5_reward2_to_rechar'))->getField('value');
    $level5_reward2_to_shopEta =  M('config')->where(array('name'=>'level5_reward2_to_shopEta'))->getField('value');

        $rechar_balance = M('config')->where(array('name'=>'rechar_balance'))->getField('value');
        $rechar_integral = M('config')->where(array('name'=>'rechar_integral'))->getField('value');
        $reward =  M('config')->where(array('name'=>'reward'))->getField('value');

        //比例
        $exchange_ratio = M('config')->where(array('name'=>'exchange_ratio'))->getField('value');
        $cash_ratio = M('config')->where(array('name'=>'cash_ratio'))->getField('value');
        $transfer_ratio = M('config')->where(array('name'=>'transfer_ratio'))->getField('value');

		$this->assign('reward',$reward);
        $this->assign('rechar_balance',$rechar_balance);
        $this->assign('rechar_integral',$rechar_integral);
        $this->assign('exchange_ratio',$exchange_ratio);
        $this->assign('cash_ratio',$cash_ratio);
        $this->assign('transfer_ratio',$transfer_ratio);
		//一级
		$this->assign('rechar_firstReward',$rechar_firstReward);
		$this->assign('rechar_secondReward',$rechar_secondReward);
		$this->assign('reward_to_rechar',$reward_to_rechar);
		$this->assign('reward_to_shopEta',$reward_to_shopEta);

    $this->assign('rechar2_firstReward',$rechar2_firstReward);
    $this->assign('rechar2_secondReward',$rechar2_secondReward);
    $this->assign('reward2_to_rechar',$reward2_to_rechar);
    $this->assign('reward2_to_shopEta',$reward2_to_shopEta);

    //二级
    $this->assign('level2_rechar_firstReward',$level2_rechar_firstReward);
    $this->assign('level2_rechar_secondReward',$level2_rechar_secondReward);
    $this->assign('level2_reward_to_rechar',$level2_reward_to_rechar);
    $this->assign('level2_reward_to_shopEta',$level2_reward_to_shopEta);

    $this->assign('level2_rechar2_firstReward',$level2_rechar2_firstReward);
    $this->assign('level2_rechar2_secondReward',$level2_rechar2_secondReward);
    $this->assign('level2_reward2_to_rechar',$level2_reward2_to_rechar);
    $this->assign('level2_reward2_to_shopEta',$level2_reward2_to_shopEta);

    //三级
    $this->assign('level3_rechar_firstReward',$level3_rechar_firstReward);
    $this->assign('level3_rechar_secondReward',$level3_rechar_secondReward);
    $this->assign('level3_reward_to_rechar',$level3_reward_to_rechar);
    $this->assign('level3_reward_to_shopEta',$level3_reward_to_shopEta);

    $this->assign('level3_rechar2_firstReward',$level3_rechar2_firstReward);
    $this->assign('level3_rechar2_secondReward',$level3_rechar2_secondReward);
    $this->assign('level3_reward2_to_rechar',$level3_reward2_to_rechar);
    $this->assign('level3_reward2_to_shopEta',$level3_reward2_to_shopEta);

    //四级
    $this->assign('level4_rechar_firstReward',$level4_rechar_firstReward);
    $this->assign('level4_rechar_secondReward',$level4_rechar_secondReward);
    $this->assign('level4_reward_to_rechar',$level4_reward_to_rechar);
    $this->assign('level4_reward_to_shopEta',$level4_reward_to_shopEta);

    $this->assign('level4_rechar2_firstReward',$level4_rechar2_firstReward);
    $this->assign('level4_rechar2_secondReward',$level4_rechar2_secondReward);
    $this->assign('level4_reward2_to_rechar',$level4_reward2_to_rechar);
    $this->assign('level4_reward2_to_shopEta',$level4_reward2_to_shopEta);

    //五级
    $this->assign('level5_rechar_firstReward',$level5_rechar_firstReward);
    $this->assign('level5_rechar_secondReward',$level5_rechar_secondReward);
    $this->assign('level5_reward_to_rechar',$level5_reward_to_rechar);
    $this->assign('level5_reward_to_shopEta',$level5_reward_to_shopEta);

    $this->assign('level5_rechar2_firstReward',$level5_rechar2_firstReward);
    $this->assign('level5_rechar2_secondReward',$level5_rechar2_secondReward);
    $this->assign('level5_reward2_to_rechar',$level5_reward2_to_rechar);
    $this->assign('level5_reward2_to_shopEta',$level5_reward2_to_shopEta);
        $this->display("fee");

    }
	
  //一级奖励设置
	public function recharge_jlcssz(){
	    $rechar_firstReward = I('rechar_firstReward');
	    $rechar_secondReward = I('rechar_secondReward');
		$reward_to_rechar = I('reward_to_rechar');
		$reward_to_shopEta = I('reward_to_shopEta');
	    if($rechar_firstReward == ''){
	        echo 2;die;
	    }
	    if($rechar_secondReward == ''){
	        echo 2;die;
	    }
		if($reward_to_rechar == ''){
		    echo 2;die;
		}
		if($reward_to_shopEta == ''){
		    echo 2;die;
		}
	
	    $rechar_firstReward = $rechar_firstReward/100;
	    $rechar_secondReward = $rechar_secondReward/100;
		$reward_to_rechar = $reward_to_rechar/100;
		$reward_to_shopEta = $reward_to_shopEta/100;
	    $res = M('config')->where(array('name'=>'rechar_firstReward'))->setField('value',$rechar_firstReward);
	    $res2 = M('config')->where(array('name'=>'rechar_secondReward'))->setField('value',$rechar_secondReward);
		$res3 = M('config')->where(array('name'=>'reward_to_rechar'))->setField('value',$reward_to_rechar);
		$res4 = M('config')->where(array('name'=>'reward_to_shopEta'))->setField('value',$reward_to_shopEta);
	    if($res !== false||$res2 !== false||$res3 !== false||$res4 !== false){
	        echo 1;die;
	    }else{
	        echo 2;die;
	    }
	}

  public function recharge_jlcssz2(){
      $rechar_firstReward = I('rechar_firstReward');
      $rechar_secondReward = I('rechar_secondReward');
    $reward_to_rechar = I('reward_to_rechar');
    $reward_to_shopEta = I('reward_to_shopEta');
      if($rechar_firstReward == ''){
          echo 2;die;
      }
      if($rechar_secondReward == ''){
          echo 2;die;
      }
    if($reward_to_rechar == ''){
        echo 2;die;
    }
    if($reward_to_shopEta == ''){
        echo 2;die;
    }
  
      $rechar_firstReward = $rechar_firstReward/100;
      $rechar_secondReward = $rechar_secondReward/100;
    $reward_to_rechar = $reward_to_rechar/100;
    $reward_to_shopEta = $reward_to_shopEta/100;
      $res = M('config')->where(array('name'=>'rechar2_firstReward'))->setField('value',$rechar_firstReward);
      $res2 = M('config')->where(array('name'=>'rechar2_secondReward'))->setField('value',$rechar_secondReward);
    $res3 = M('config')->where(array('name'=>'reward2_to_rechar'))->setField('value',$reward_to_rechar);
    $res4 = M('config')->where(array('name'=>'reward2_to_shopEta'))->setField('value',$reward_to_shopEta);
      if($res !== false||$res2 !== false||$res3 !== false||$res4 !== false){
          echo 1;die;
      }else{
          echo 2;die;
      }
  }

  //二级奖励设置
  public function level2_recharge_jlcssz(){
      $rechar_firstReward = I('rechar_firstReward');
      $rechar_secondReward = I('rechar_secondReward');
    $reward_to_rechar = I('reward_to_rechar');
    $reward_to_shopEta = I('reward_to_shopEta');
      if($rechar_firstReward == ''){
          echo 2;die;
      }
      if($rechar_secondReward == ''){
          echo 2;die;
      }
    if($reward_to_rechar == ''){
        echo 2;die;
    }
    if($reward_to_shopEta == ''){
        echo 2;die;
    }
  
      $rechar_firstReward = $rechar_firstReward/100;
      $rechar_secondReward = $rechar_secondReward/100;
    $reward_to_rechar = $reward_to_rechar/100;
    $reward_to_shopEta = $reward_to_shopEta/100;
      $res = M('config')->where(array('name'=>'level2_rechar_firstReward'))->setField('value',$rechar_firstReward);
      $res2 = M('config')->where(array('name'=>'level2_rechar_secondReward'))->setField('value',$rechar_secondReward);
    $res3 = M('config')->where(array('name'=>'level2_reward_to_rechar'))->setField('value',$reward_to_rechar);
    $res4 = M('config')->where(array('name'=>'level2_reward_to_shopEta'))->setField('value',$reward_to_shopEta);
      if($res !== false||$res2 !== false||$res3 !== false||$res4 !== false){
          echo 1;die;
      }else{
          echo 2;die;
      }
  }

  public function level2_recharge_jlcssz2(){
      $rechar_firstReward = I('rechar_firstReward');
      $rechar_secondReward = I('rechar_secondReward');
    $reward_to_rechar = I('reward_to_rechar');
    $reward_to_shopEta = I('reward_to_shopEta');
      if($rechar_firstReward == ''){
          echo 2;die;
      }
      if($rechar_secondReward == ''){
          echo 2;die;
      }
    if($reward_to_rechar == ''){
        echo 2;die;
    }
    if($reward_to_shopEta == ''){
        echo 2;die;
    }
  
      $rechar_firstReward = $rechar_firstReward/100;
      $rechar_secondReward = $rechar_secondReward/100;
    $reward_to_rechar = $reward_to_rechar/100;
    $reward_to_shopEta = $reward_to_shopEta/100;
      $res = M('config')->where(array('name'=>'level2_rechar2_firstReward'))->setField('value',$rechar_firstReward);
      $res2 = M('config')->where(array('name'=>'level2_rechar2_secondReward'))->setField('value',$rechar_secondReward);
    $res3 = M('config')->where(array('name'=>'level2_reward2_to_rechar'))->setField('value',$reward_to_rechar);
    $res4 = M('config')->where(array('name'=>'level2_reward2_to_shopEta'))->setField('value',$reward_to_shopEta);
      if($res !== false||$res2 !== false||$res3 !== false||$res4 !== false){
          echo 1;die;
      }else{
          echo 2;die;
      }
  }

  //三级奖励设置
  public function level3_recharge_jlcssz(){
      $rechar_firstReward = I('rechar_firstReward');
      $rechar_secondReward = I('rechar_secondReward');
    $reward_to_rechar = I('reward_to_rechar');
    $reward_to_shopEta = I('reward_to_shopEta');
      if($rechar_firstReward == ''){
          echo 2;die;
      }
      if($rechar_secondReward == ''){
          echo 2;die;
      }
    if($reward_to_rechar == ''){
        echo 2;die;
    }
    if($reward_to_shopEta == ''){
        echo 2;die;
    }
  
      $rechar_firstReward = $rechar_firstReward/100;
      $rechar_secondReward = $rechar_secondReward/100;
    $reward_to_rechar = $reward_to_rechar/100;
    $reward_to_shopEta = $reward_to_shopEta/100;
      $res = M('config')->where(array('name'=>'level3_rechar_firstReward'))->setField('value',$rechar_firstReward);
      $res2 = M('config')->where(array('name'=>'level3_rechar_secondReward'))->setField('value',$rechar_secondReward);
    $res3 = M('config')->where(array('name'=>'level3_reward_to_rechar'))->setField('value',$reward_to_rechar);
    $res4 = M('config')->where(array('name'=>'level3_reward_to_shopEta'))->setField('value',$reward_to_shopEta);
      if($res !== false||$res2 !== false||$res3 !== false||$res4 !== false){
          echo 1;die;
      }else{
          echo 2;die;
      }
  }

  public function level3_recharge_jlcssz2(){
      $rechar_firstReward = I('rechar_firstReward');
      $rechar_secondReward = I('rechar_secondReward');
    $reward_to_rechar = I('reward_to_rechar');
    $reward_to_shopEta = I('reward_to_shopEta');
      if($rechar_firstReward == ''){
          echo 2;die;
      }
      if($rechar_secondReward == ''){
          echo 2;die;
      }
    if($reward_to_rechar == ''){
        echo 2;die;
    }
    if($reward_to_shopEta == ''){
        echo 2;die;
    }
  
      $rechar_firstReward = $rechar_firstReward/100;
      $rechar_secondReward = $rechar_secondReward/100;
    $reward_to_rechar = $reward_to_rechar/100;
    $reward_to_shopEta = $reward_to_shopEta/100;
      $res = M('config')->where(array('name'=>'level3_rechar2_firstReward'))->setField('value',$rechar_firstReward);
      $res2 = M('config')->where(array('name'=>'level3_rechar2_secondReward'))->setField('value',$rechar_secondReward);
    $res3 = M('config')->where(array('name'=>'level3_reward2_to_rechar'))->setField('value',$reward_to_rechar);
    $res4 = M('config')->where(array('name'=>'level3_reward2_to_shopEta'))->setField('value',$reward_to_shopEta);
      if($res !== false||$res2 !== false||$res3 !== false||$res4 !== false){
          echo 1;die;
      }else{
          echo 2;die;
      }
  }

  //四级奖励设置

  public function level4_recharge_jlcssz(){
      $rechar_firstReward = I('rechar_firstReward');
      $rechar_secondReward = I('rechar_secondReward');
    $reward_to_rechar = I('reward_to_rechar');
    $reward_to_shopEta = I('reward_to_shopEta');
      if($rechar_firstReward == ''){
          echo 2;die;
      }
      if($rechar_secondReward == ''){
          echo 2;die;
      }
    if($reward_to_rechar == ''){
        echo 2;die;
    }
    if($reward_to_shopEta == ''){
        echo 2;die;
    }
  
      $rechar_firstReward = $rechar_firstReward/100;
      $rechar_secondReward = $rechar_secondReward/100;
    $reward_to_rechar = $reward_to_rechar/100;
    $reward_to_shopEta = $reward_to_shopEta/100;
      $res = M('config')->where(array('name'=>'level4_rechar_firstReward'))->setField('value',$rechar_firstReward);
      $res2 = M('config')->where(array('name'=>'level4_rechar_secondReward'))->setField('value',$rechar_secondReward);
    $res3 = M('config')->where(array('name'=>'level4_reward_to_rechar'))->setField('value',$reward_to_rechar);
    $res4 = M('config')->where(array('name'=>'level4_reward_to_shopEta'))->setField('value',$reward_to_shopEta);
      if($res !== false||$res2 !== false||$res3 !== false||$res4 !== false){
          echo 1;die;
      }else{
          echo 2;die;
      }
  }

  public function level4_recharge_jlcssz2(){
      $rechar_firstReward = I('rechar_firstReward');
      $rechar_secondReward = I('rechar_secondReward');
    $reward_to_rechar = I('reward_to_rechar');
    $reward_to_shopEta = I('reward_to_shopEta');
      if($rechar_firstReward == ''){
          echo 2;die;
      }
      if($rechar_secondReward == ''){
          echo 2;die;
      }
    if($reward_to_rechar == ''){
        echo 2;die;
    }
    if($reward_to_shopEta == ''){
        echo 2;die;
    }
  
      $rechar_firstReward = $rechar_firstReward/100;
      $rechar_secondReward = $rechar_secondReward/100;
    $reward_to_rechar = $reward_to_rechar/100;
    $reward_to_shopEta = $reward_to_shopEta/100;
      $res = M('config')->where(array('name'=>'level4_rechar2_firstReward'))->setField('value',$rechar_firstReward);
      $res2 = M('config')->where(array('name'=>'level4_rechar2_secondReward'))->setField('value',$rechar_secondReward);
    $res3 = M('config')->where(array('name'=>'level4_reward2_to_rechar'))->setField('value',$reward_to_rechar);
    $res4 = M('config')->where(array('name'=>'level4_reward2_to_shopEta'))->setField('value',$reward_to_shopEta);
      if($res !== false||$res2 !== false||$res3 !== false||$res4 !== false){
          echo 1;die;
      }else{
          echo 2;die;
      }
  }

  //五级奖励设置
  public function level5_recharge_jlcssz(){
      $rechar_firstReward = I('rechar_firstReward');
      $rechar_secondReward = I('rechar_secondReward');
    $reward_to_rechar = I('reward_to_rechar');
    $reward_to_shopEta = I('reward_to_shopEta');
      if($rechar_firstReward == ''){
          echo 2;die;
      }
      if($rechar_secondReward == ''){
          echo 2;die;
      }
    if($reward_to_rechar == ''){
        echo 2;die;
    }
    if($reward_to_shopEta == ''){
        echo 2;die;
    }
  
      $rechar_firstReward = $rechar_firstReward/100;
      $rechar_secondReward = $rechar_secondReward/100;
    $reward_to_rechar = $reward_to_rechar/100;
    $reward_to_shopEta = $reward_to_shopEta/100;
      $res = M('config')->where(array('name'=>'level5_rechar_firstReward'))->setField('value',$rechar_firstReward);
      $res2 = M('config')->where(array('name'=>'level5_rechar_secondReward'))->setField('value',$rechar_secondReward);
    $res3 = M('config')->where(array('name'=>'level5_reward_to_rechar'))->setField('value',$reward_to_rechar);
    $res4 = M('config')->where(array('name'=>'level5_reward_to_shopEta'))->setField('value',$reward_to_shopEta);
      if($res !== false||$res2 !== false||$res3 !== false||$res4 !== false){
          echo 1;die;
      }else{
          echo 2;die;
      }
  }

  public function level5_recharge_jlcssz2(){
      $rechar_firstReward = I('rechar_firstReward');
      $rechar_secondReward = I('rechar_secondReward');
    $reward_to_rechar = I('reward_to_rechar');
    $reward_to_shopEta = I('reward_to_shopEta');
      if($rechar_firstReward == ''){
          echo 2;die;
      }
      if($rechar_secondReward == ''){
          echo 2;die;
      }
    if($reward_to_rechar == ''){
        echo 2;die;
    }
    if($reward_to_shopEta == ''){
        echo 2;die;
    }
  
      $rechar_firstReward = $rechar_firstReward/100;
      $rechar_secondReward = $rechar_secondReward/100;
    $reward_to_rechar = $reward_to_rechar/100;
    $reward_to_shopEta = $reward_to_shopEta/100;
      $res = M('config')->where(array('name'=>'level5_rechar2_firstReward'))->setField('value',$rechar_firstReward);
      $res2 = M('config')->where(array('name'=>'level5_rechar2_secondReward'))->setField('value',$rechar_secondReward);
    $res3 = M('config')->where(array('name'=>'level5_reward2_to_rechar'))->setField('value',$reward_to_rechar);
    $res4 = M('config')->where(array('name'=>'level5_reward2_to_shopEta'))->setField('value',$reward_to_shopEta);
      if($res !== false||$res2 !== false||$res3 !== false||$res4 !== false){
          echo 1;die;
      }else{
          echo 2;die;
      }
  }

    public function ratio(){
        $exchange_ratio = I('exchange_ratio');
        $cash_ratio = I('cash_ratio');
        $transfer_ratio = I('transfer_ratio');
        if($exchange_ratio == ''){
            echo 2;die;
        }

        if($cash_ratio == ''){
            echo 2;die;
        }
        if($transfer_ratio == ''){
            echo 2;die;
        }

        $exchange_ratio = $exchange_ratio/100;
        $cash_ratio = $cash_ratio/100;  
        $transfer_ratio = $transfer_ratio/100;
        $res = M('config')->where(array('name'=>'exchange_ratio'))->setField('value',$exchange_ratio);
        $res2 = M('config')->where(array('name'=>'cash_ratio'))->setField('value',$cash_ratio);
        $res3 = M('config')->where(array('name'=>'transfer_ratio'))->setField('value',$transfer_ratio);
        if($res !== false||$res2 !== false||$res3 !== false){
            echo 1;die;
        }else{
            echo 2;die;
        }
    }

	//奖励配置
	public function reward_config(){
        $reward = I('reward');
        $reward = $reward/100;
        $res =  M('config')->where(array('name'=>'reward'))->setField('value',$reward);
		if($res !== false){
			echo 1;die;
		}else{
			echo 2;die;
		}
	}
    
    
    public function recharge_swap(){
        $rechar_balance = I('rechar_balance');
        $rechar_integral = I('rechar_integral');
        if($rechar_balance == ''){
            echo 2;die;
        }

        if($rechar_integral == ''){
            echo 2;die;
        }

        $rechar_balance = $rechar_balance/100;
        $rechar_integral = $rechar_integral/100;
        $res = M('config')->where(array('name'=>'rechar_balance'))->setField('value',$rechar_balance);
        $res2 = M('config')->where(array('name'=>'rechar_integral'))->setField('value',$rechar_integral);
        if($res !== false||$res2 !== false){
            echo 1;die;
        }else{
            echo 2;die;
        }
    }
	




/**
     * 管理奖保存配置
     * 
     */

 public function manage_Save()
    {
        $config=I('post.');
        if ($config && is_array($config)) {
            $config_object = D('Config');
            for($i=1;$i<=3;$i++) {
                $map = array('name' => "guanli".$i);
                // 如果值是数组则转换成字符串，适用于复选框等类型

                $config_object->where($map)->setField('value',$config["managej_".($i-1)]);
                $config_object->where($map)->setField('tip',$config["manage_".($i-1)]);
            }
        }

        $this->success('保存成功！');
    }




/**
     * 区块奖保存配置
     * 
     */

 public function qukuai_Save()
    {
        $config=I('post.');
        if ($config && is_array($config)) {
            $config_object = D('Config');
            for($i=1;$i<=5;$i++) {
                $map = array('name' => "qukuai".$i);
                // 如果值是数组则转换成字符串，适用于复选框等类型

                $config_object->where($map)->setField('value',$config["qukuaij_".($i-1)]);
                $config_object->where($map)->setField('tip',$config["qukuai_".($i-1)]);
            }
        }

        $this->success('保存成功！');
    }



/**
     * 区块奖保存配置
     * 
     */

 public function vip_Save()
    {
        $config=I('post.');
        if ($config && is_array($config)) {
            $config_object = D('Config');
            for($i=1;$i<=2;$i++) {
                $map = array('name' => "vip".$i);
                // 如果值是数组则转换成字符串，适用于复选框等类型

                $config_object->where($map)->setField('value',$config["vipj_".($i-1)]);
                $config_object->where($map)->setField('tip',$config["vip_".($i-1)]);
            }
        }

        $this->success('保存成功！');
    }


    /**
     * 区块奖保存配置
     * 
     */

 public function fenx_Save()
    {
        $config=I('post.');
        if ($config && is_array($config)) {
            $config_object = D('Config');
            for($i=1;$i<=4;$i++) {
                $map = array('name' => "zhitui".$i);
                // 如果值是数组则转换成字符串，适用于复选框等类型
                $config_object->where($map)->setField('value',$config["fenxj_".($i-1)]);
                $config_object->where($map)->setField('tip',$config["fenx_".($i-1)]);


                $map1 = array('name' => "zhuand".$i);
                $config_object->where($map1)->setField('value',$config["zhuandj_".($i-1)]);
                $config_object->where($map1)->setField('tip',$config["fenx_".($i-1)]);
            }
        }

        $this->success('保存成功！');
    }


    /**
     * 批量保存配置
     * 
     */
    public function groupSave()
    {
        $config=I('post.');
        unset($config['file']);
        if ($config && is_array($config)) {
            $config_object = D('Config');
            foreach ($config as $name => $value) {
                $map = array('name' => $name);
                // 如果值是数组则转换成字符串，适用于复选框等类型
                if (is_array($value)) {
                    $value = implode(',', $value);
                }

                $config_object->where($map)->setField('value',$value);
            }
        }

        $this->success('保存成功！');
    }



   /**
     * 保存实时价格
     * 
     */
    public function groupSave1()
    {
        $config=I('post.');
     
     $config_object = D('Config');
     $growem=$config["growem"];
     $config_object->where("name='growem'")->setField('value',$growem);

     $arr=array(1=>"MXC",2=>"比特币",3=>"莱特币",4=>"以太坊",5=>"狗狗币");
                $timen=time();
                for($i=1;$i<=5;$i++){
                $coinone['cid'] = $i;
                $coinone['coin_price'] =$config["s".$i];

                $coinone['coin_name'] =$arr[$i];

              //  dump($arr[$i]);
                $coinone['max'] =$config["g".$i];
                $coinone['min'] =$config["d".$i];
                $coinone['coin_addtime'] = $timen;
                M('coindets')->add($coinone);
                }


        $this->success('保存成功！');
    }


 /**
     * 基本设置
     * 
     */
    public function groupSave2()
    {
        $jifen=I('post.jifen');
        $regjifen=I('post.regjifen');
     
   		D('Config')->where("name='jifen'")->setField('value',$jifen);
   		D('Config')->where("name='regjifen'")->setField('value',$regjifen);
   		
        $this->success('保存成功！');
    }

 /**
     * 基本设置
     * 
     */
    public function tuijian()
    {
        $jifens=I('post.jifens');
        $rens=I('post.rens');
     
  		D('Config')->where("name='jifens'")->setField('value',$jifens);
   		D('Config')->where("name='rens'")->setField('value',$rens);


        $this->success('保存成功！');
    }

	//代币设置
	public function coins(){
		$name=I('post.name');
        $bili=I('post.bili');
        $todayadd = I('post.todayadd');
//		$znum=I('post.znum');
//		$yxf_num=I('post.yxf_num');
//		$sjkd_num=I('post.sjkd_num');
//		$yytd_num=I('post.yytd_num');
//		$ytz_num=I('post.ytz_num');
//		$sqyy_num=I('post.sqyy_num');
//		$tgjl_num=I('post.tgjl_num');
		$data=array(
//			'znum'=>(int)$znum,
//			'yxf_num'=>(int)$yxf_num,
//			'sjkd_num'=>(int)$sjkd_num,
//			'yytd_num'=>(int)$yytd_num,
//			'ytz_num'=>(int)$ytz_num,
//			'sqyy_num'=>(int)$sqyy_num,
//			'tgjl_num'=>(int)$tgjl_num,
            'bili'=>$bili,
            'todayadd'=>$todayadd,
		);
		
		$res=M('coins')->where("name='".$name."'")->save($data);
		if($res){
			echo 1;
			die;
		}else{
			echo 2;
			die;
		}
	}


/**
     * 发布众筹
     * 
     */
    public function groupSave3()
    {

        $num=I('post.num', 'intval', 0);
        $dprice=(float)I('post.dprice');
        $date=I('post.open_time');
        $jindu=I('post.jindu', 'intval', 0);
        $open_time=strtotime($date);


          //把其它众筹项目状态改为2，表示已完成
          M('crowds')->where("status<>2")->save(array("status"=>2));

          $datas["num"]=$num;
          $datas["dprice"]=$dprice;
          $datas["open_time"]=$open_time;
          $datas["create_time"]=time();
          $datas["status"]=0;
          $datas["jindu"]=$jindu;
          M('crowds')->add($datas);  

        $this->success('发布成功！');
    }



/**
     * 修改众筹
     * 
     */
    public function groupSave4()
    {

        $id=I('post.tid', 'intval', 0);
        $jindu=(float)I('post.jindu'); 
 
          $datas["jindu"]=$jindu;
          M('crowds')->where("id=".$id)->save($datas);  

        $this->success('修改成功！');
    }


	public function basge(){
		$shabi=M('base')->where(array('userid'=>session('userid')))->find();
		foreach($shabi as $key =>$val){
			$shabi[$key]["$val"]=$this->basge($val['id']);
		}
		$this->assign('shabi',$shabi);
		$thsi->display();
		
	}



    public function BaseSave(){

      $ids=I('post.ids');
      $limit_num=I('post.limit_num');
      $test=M('limit');
      foreach ($ids as $k => $v) {
        $where['id']=$v;
        $data['limit_num']=$limit_num[$k];
        $test->where($where)->save($data);
      }
      $this->success('保存成功！');
      
   }


    public function sitecloseSave()
    {
        $config=I('post.');
        $key=(array_keys($config));
        
        if ($config && is_array($config)) {
            $map['name']=$key[0];
            $config_object = D('Config');
            $data['value']=$config[$key[0]];
            $data['tip']=$config['tip'];

            $config_object->where($map)->save($data);
        }

        $this->success('保存成功！');
    }

    //商家信息开关
    public function merchant_information_swith()
    {
        $config=I('post.');
        $key=(array_keys($config));
        
        if ($config && is_array($config)) {
            $map['name']=$key[0];
            $config_object = D('Config');
            $data['value']=$config[$key[0]];

            $config_object->where($map)->save($data);
        }

        $this->success('保存成功！');
    }

    public function turntable(){
        $info=M('turntable_lv')->order('id')->find();
        $this->assign('info',$info);
        $this->display();
    }

    //保存转盘数据
    public function savezhuanpan(){
        $data = I('post.');
        $info=M('turntable_lv')->where('id=1')->save($data);
        $this->success('保存成功！');
    }

    public function tool(){
        $info=M('tool')->order('id')->select();
        $this->assign('info',$info);
        $this->display();
    }

    //保存转盘数据
    public function savetool(){
        $ids = I('post.id');
        $nums = I('post.num');
        $tool=M('tool');
        foreach ($ids as $k => $val) {
            $tool->where(array('id'=>$val))->save(array('t_num'=>$nums[$k]));
        }
        $this->success('保存成功！');
    }
    
    
    public function shangjia(){
    	$data['shangjia_num']=I('post.shangjia_num');
    	$res=M('reward')->where('id=1')->save($data);
    	if($res){
    		echo 1;die;
    	}else{
    		echo 2;die;
    	}
    }
}
