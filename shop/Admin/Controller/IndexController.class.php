<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends AdminController {

    public function index(){
        //会员统计
        $this->getUserCount();
        //交易量
        $this->TraingCount();
        
        // 升值
        $this->appreciation();
        $this->assign('meta_title', "首页");
        $this->display();
    }
    
    // 登陆升值
    public function appreciation(){
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


    public function getUserCount(){
        $user=D('User');
        $user_total=$user->count(1);
        $start=strtotime(date('Y-m-d'));
        $end=$start+86400;
        $where="reg_date BETWEEN {$start} AND {$end}";
        $user_count=$user->where($where)->count(1);
        $this->assign('user_total', $user_total);
        $this->assign('user_count', $user_count);
    }

    public function TraingCount(){
        $traing=M('trading');
        $trading_free=M('trading_free');

        $start=strtotime(date('Y-m-d'));
        $end=$start+86400;
        $where="create_time BETWEEN {$start} AND {$end}";

        $traing_count=$traing->where($where)->count(1);
        $traing_total=$traing->count(1);

        $traing_count+=$trading_free->where($where)->count(1);
        $traing_total+=$trading_free->count(1);

        $this->assign('traing_count', $traing_count);
        $this->assign('traing_total', $traing_total);
    }

    /**
     * 删除缓存
     * @author jry <598821125@qq.com>
     */
    public function removeRuntime()
    {
        $file   = new \Util\File();
        $result = $file->del_dir(RUNTIME_PATH);
        if ($result) {
            $this->success("缓存清理成功1");
        } else {
            $this->error("缓存清理失败1");
        }
    }
    
    
    
    public function receivables(){
    	
    	if(IS_POST){
    		$data['bank_name']=I('post.bank_name');
    		$data['hold_name']=I('post.hold_name');
    		$data['card_number']=I('post.card_number');
    		$data['open_card']=I('post.open_card');
    		$data['zfb_name']=I('post.zfb_name');
    		$data['zfb_num']=I('post.zfb_num');
    		$data['wx_name']=I('post.wx_name');
    		$data['wx_num']=I('post.wx_num');
            
    		if($_FILES['wx_qrcode']['tmp_name'])
            {
                $inf=$this->upload();
                
                $data['wx_qrcode']=$inf['wx_qrcode']['savepath'].$inf['wx_qrcode']['savename'];
            }
            if($_FILES['zfb_qrcode']['tmp_name'])
            {
                $inf=$this->upload();
                
                $data['zfb_qrcode']=$inf['zfb_qrcode']['savepath'].$inf['zfb_qrcode']['savename'];
            }
            
            M('ubanks')->where('id=1')->save($data);
			
            echo "<script>alert('配置成功');location.href='';</script>";
    	}
    	
    	
    	$bank=M('ubanks')->where('id=1')->find();
    	$this->assign('bank',$bank);
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