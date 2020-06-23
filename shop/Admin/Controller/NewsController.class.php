<?php

namespace Admin\Controller;

use Think\Page;

/**
 * 用户控制器
 *
 */
class NewsController extends AdminController
{


    /**
     * 用户列表
     *
     */
    public function index()
    {
        // 获取所有用户
        $map['status'] = array('egt', '0'); // 禁用和正常状态
        $user_object   = M('news');
        //分页
        $p=getpage($user_object,$map,10);
        $page=$p->show();  

        $data_list     = $user_object
            ->where($map)->where('id <> 1 and id <> 105')
            ->order('id desc')
            ->select();
       

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

            $user_object = D('news');
            $data        = I('post.');
            if(empty($data['title'])){
              $this->error('标题不能为空');  
            }
            $data['uid_str']        = '0,';
            $data['create_time']        = time();
            $data['status']         =1;
            if ($data) {
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

    /**
     * 编辑用户
     *
     */
    public function edit($id)
    {
        if (IS_POST) {
            // 提交数据
            $user_object = D('news');
            $data        = I('post.');
            $data['create_time'] = time();
            if(empty($data['title'])){
              $this->error('标题不能为空');  
            }
          //  var_dump($data);exit;
            if ($data) {
                $result = $user_object
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
            $info = D('news')->find($id);
            $this->assign('info',$info);
            $this->display();
        }
    }

    /**
     * 分享页背景
     *
     */
    public function sharecodebj()
    {
        if(IS_POST){
            if($_FILES['bj']['tmp_name'])
            {
                $inf=$this->upload();
                
                $data=$inf['bj']['savepath'].$inf['bj']['savename'];
            }
            M('sharecodebj')->where(array('id'=>1))->setField('image',$data);
        }
        

        // 获取背景
        $data_list = M('sharecodebj')->where(array('id'=>'1'))->find();
        $this->assign('list',$data_list);
        $this->display();
    }

    /**
     * 设置一条或者多条数据的状态
     *
     */
    public function setStatus($model = CONTROLLER_NAME)
    {
        $ids = I('request.ids');
        parent::setStatus($model);
    }


    // 分享页内容
    public function sharecode()
    {
        // 获取所有用户
        $user_object   = M('news');

        $map = 'id = 1';
        $data_list     = $user_object
            ->where($map)
            ->order('id desc')
            ->select();
       

        $this->assign('list',$data_list);
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
    // 挖金协议
    public function agreement()
    {
        // 获取所有用户
        $user_object   = M('news');

        $map = 'id = 105';
        $data_list     = $user_object
            ->where($map)
            ->order('id desc')
            ->select();
       

        $this->assign('list',$data_list);
        $this->display();
    }

    // 资产转入
    public function assets()
    {
        $assets1 = M('assets')->where(array('id'=>1))->field('id,time,lixi')->find();
        $assets2 = M('assets')->where(array('id'=>2))->field('id,time,lixi')->find();
        $assets3 = M('assets')->where(array('id'=>3))->field('id,time,lixi')->find();
        $assets4 = M('assets')->where(array('id'=>4))->field('id,time,lixi')->find();
        $assets5 = M('assets')->where(array('id'=>5))->field('id,time,lixi')->find();
        
        $this->assign('assets1',$assets1);
        $this->assign('assets2',$assets2);
        $this->assign('assets3',$assets3);
        $this->assign('assets4',$assets4);
        $this->assign('assets5',$assets5);
        $this->display();
    }
    public function xiugai(){

        $data1['id'] = I('id1');
        $data1['time'] = I('time1');
        $data1['lixi'] = I('lixi1');
        $assets1 = M('assets')->where('id='.$data1['id'])->save($data1);

        $data2['id'] = I('id2');
        $data2['time'] = I('time2');
        $data2['lixi'] = I('lixi2');
        $assets2 = M('assets')->where('id='.$data2['id'])->save($data2);

        $data3['id'] = I('id3');
        $data3['time'] = I('time3');
        $data3['lixi'] = I('lixi3');
        $assets3 = M('assets')->where('id='.$data3['id'])->save($data3);

        $data4['id'] = I('id4');
        $data4['time'] = I('time4');
        $data4['lixi'] = I('lixi4');
        $assets4 = M('assets')->where('id='.$data4['id'])->save($data4);

        $data5['id'] = I('id5');
        $data5['time'] = I('time5');
        $data5['lixi'] = I('lixi5');
        $assets5 = M('assets')->where('id='.$data5['id'])->save($data5);

        header('Location:'.U('News/assets'));
    }
}
