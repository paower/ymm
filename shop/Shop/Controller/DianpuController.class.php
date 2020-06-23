<?php

namespace Shop\Controller;

use Think\Controller;

class DianpuController extends CommonController
{

    protected function _initialize()
    {
        parent::_initialize();
        // 获取当前用户ID
        //define('UID', is_login());
        //  if (!UID) {// 还没登录 跳转到登录页面
        //   $this->redirect('Personal/login');
        // }
    }

// 入驻商铺列表
    public function businesslist(){
        $dede=I('s_cate');
        /* $dede['shop_dengji']=array('neq',0);*/
        $id=array('gt',1);
        if($dede ){

            $dianpu=M('gerenshangpu')->where(array('shop_zhuangtai'=>1,'shop_cate'=>$dede,'id'=>$id))->order('shop_stort asc')->select();

        }else{
            $dianpu=M('gerenshangpu')->where(array('shop_zhuangtai'=>1,'id'=>$id))->order('shop_stort asc')->select();

        }

        $product_cate = M('product_cate')->where(array('pid'=>0))->order('sort asc')->field('name,id,pic')->select();

        $this->assign('product_cate',$product_cate);
        $this->assign('dianpu',$dianpu);

        $this->display();
    }
    //店铺首页
    public function index(){
        $did = I("did");
        if(!$did&&empty($did)){
            $did = session('userid');
        }

        $dailishang = M("gerenshangpu")->where(array("userid"=>$did))->find();
        if(!$dailishang){
            error_alert("你还未申请店铺，请申请！");
        }


        //查询当前店铺商品
        $productList = M("product_detail")->where(array("shangjia"=>$did,'gr_tuijian'=>1,"is_shangjia"=>1))->order("old_price desc")->select();
        $newshop = M("product_detail")->where(array("shangjia"=>$did,'gr_new'=>1,"is_shangjia"=>1))->order("ctime desc")->select();
        $hotshop = M("product_detail")->where(array("shangjia"=>$did,'gr_hot'=>1,"is_shangjia"=>1))->order("ctime desc")->select();
        $shop = M("product_detail")->where(array("shangjia"=>$did,"is_shangjia"=>1))->order("ctime desc")->select();
		foreach ($productList as $k => $v) {
            $where['gid'] = $v['id'];
            $parameter = M("product_parameter")->where($where)->count();
            if($parameter!=0){
                $price = M("product_parameter")->where($where)->find();
                $productList[$k]['price'] = $price['price'];
            }
        }

        foreach ($newshop as $k => $v) {
            $where['gid'] = $v['id'];
            $parameter = M("product_parameter")->where($where)->count();
            if($parameter!=0){
                $price = M("product_parameter")->where($where)->find();
                $newshop[$k]['price'] = $price['price'];
            }
        }

        foreach ($hotshop as $k => $v) {
            $where['gid'] = $v['id'];
            $parameter = M("product_parameter")->where($where)->count();
            if($parameter!=0){
                $price = M("product_parameter")->where($where)->find();
                $hotshop[$k]['price'] = $price['price'];
            }
        }

        foreach ($shop as $k => $v) {
            $where['gid'] = $v['id'];
            $parameter = M("product_parameter")->where($where)->count();
            if($parameter!=0){
                $price = M("product_parameter")->where($where)->find();
                $shop[$k]['price'] = $price['price'];
            }
        }
        $this->assign("hotshop",$hotshop);
        $this->assign("shop",$shop);
        $this->assign("newshop",$newshop);
        $this->assign("productList",$productList);
        $this->assign("dailishang",$dailishang);
        $this->display();
    }


    //收藏店铺
    public function dianpu(){


        $this->display();
    }
    public function dp_info(){

        $M=M('gerenshangpu');
        $uid = session('userid');

        $dianpu=$M->where(array("userid"=>$uid))->find();
        $d_type = M('user')->where(array('userid'=>$uid))->getField('d_type');
        $cate_id = M('user')->where(array('userid'=>$uid))->getField('cate_id');
        
        $d_name = M('product_cate')->where(array('id'=>$cate_id))->getField('name');
        // $d_type=M('product_cate')->select();
        $this->assign("cate_id",$cate_id);
        $this->assign("d_type",$d_type);
        $this->assign('d_name',$d_name);
        $this->assign("dianpu",$dianpu);
        $this->display();
    }


//入驻店铺资料
    public function adddp_info(){

        $M=M('gerenshangpu');

        $uid= user_login();
        $user=$M->where(array("userid"=>$uid))->find();
        $dengji=M('member')->where(array('member_id'=>$uid))->find();

        $data=I('post.');
        $data['userid']=$uid;

        $pic = $_FILES['pic']['name'];
        $imgal = uploadimg();

        /*     if($imgal['stats']=='error'){
                 exit('<script>alert("'+$imgal['res']+'"); window.history.back(-1); </script>');
             }

     */
        $data['shop_logo'] = $imgal['shop_logo'];

        $data['shop_beijing'] = $imgal['shop_beijing'];
        $data['shop_vpay'] = $imgal['shop_vpay'];
        $data['shop_zhifubao'] = $imgal['shop_zhifubao'];
        $data['shop_weixin'] = $imgal['shop_weixin'];
        $data['shop_guanggao'] = $imgal['shop_guanggao'];
        /*
        */
  /*      if($dengji['member_grade'] ==0){
            error_alert("请先升级会员");
        }else{*/

if(empty($imgal['shop_logo'])){
    $data['shop_logo']=$user['shop_logo'];

}
if(empty($imgal['shop_beijing'])){
    $data['shop_beijing']=$user['shop_beijing'];

}
if(empty($imgal['shop_vpay'])){
    $data['shop_vpay']=$user['shop_vpay'];

}
if(empty($imgal['shop_zhifubao'])){
    $data['shop_zhifubao']=$user['shop_zhifubao'];

}
if(empty($imgal['shop_weixin'])){
    $data['shop_weixin']=$user['shop_weixin'];

}
if(empty($imgal['shop_guanggao'])){
    $data['shop_guanggao']=$user['shop_guanggao'];

}


           /* p($data);die;*/
            if($user){
                $M->where(array("id"=>$user['id']))->save($data);
               /* success_alert('修改成功',document.referrer);*/        
           /* echo '<script>alert("修改成功"); javascript:window.location.href=document.referrer; </script>';*/
                success_alert('修改成功',U('Dianpu/dp_info'));
            }else{

                if(empty($data['shop_name'])){
                    error_alert("请填写店铺名称");
                }elseif(empty($data['shop_address'])){
                    error_alert("请填写店铺地址");
                }
				// elseif(empty($data['shop_type'])){
                    // error_alert("请选择店铺分类");
                // }
				elseif(empty($data['kaihuhang'])){
                    error_alert("请填写开户行");
                }elseif(empty($data['name'])){
                    error_alert("请填写开户姓名");
                }elseif(empty($data['bank'])){
                    error_alert("请填写银行卡号");
                }elseif(empty($data['shop_phone'])){
                    error_alert("请填写手机号码");
                }
				elseif(empty($data['shop_logo'])){
                    error_alert("请上传店铺Logo");
                }
				// elseif(empty($data['shop_beijing'])){
                    // error_alert("请上传店铺背景");
                // }
				// elseif(empty($data['shop_guanggao'])){
                    // error_alert("请上传店铺广告背景");
                    // // elseif(empty($data['shop_vpay']) && empty($data['shop_zhifubao']) && empty($data['shop_weixin'])){
                    // //     error_alert("请上传一种二维码");
                    // // }
                // }
				else{
                    $M->add($data);
                    success_alert("入驻成功",U('Dianpu/dp_info'));
                }
            }

   /*     }*/


    }



}

?>