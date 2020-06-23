<?php
namespace Admin\Controller;
/**
 * 广告管理
 */
class GuangGaoController extends AdminController
{
	//图片上传
	public function upload(){
	    $upload = new \Think\Upload();// 实例化上传类
	    $upload->maxSize   =     3145728 ;// 设置附件上传大小
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	    $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
	    $upload->savePath  =     'guanggao/'; // 设置附件上传（子）目录
	    // 上传文件 
	    $info   =   $upload->upload();
	    if(!$info) {// 上传错误提示错误信息
	        $this->error($upload->getError());
	    }else{// 上传成功
	        return $info;
	    }
	}
	//首页
	public function index()
	{
		$list = D('guanggao')->where("status=1")->select();
		$this->assign('list',$list);
		$this->display();
	}
	//新增广告图片
	public function add()
	{
		if (IS_POST) {
            $user_object = M('guanggao');
            $data        = $_FILES;
            $data['status']         =1;
         
            if ($data) {
                if($_FILES['picture']['tmp_name'])
                {
                   $inf=$this->upload();
                   $data['src']=$inf['picture']['savepath'].$inf['picture']['savename'];
                }else{
                   $data['picture']='';
   
                }
                
                $id = $user_object->add($data);
                if ($id) {
                    $this->success('新增成功', U('index'));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($user_object->getError());
            }
        } else {
                $this->display('edit');
        }
	}
	public function del()
	{
		$info = I();
		M('guanggao')->delete($info['id']);
		$this->success('删除成功','index');
	}
}
