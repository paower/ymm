<?php
/**
 * #
 *
 * ==========================================================================
 *
#
 #
 * ==========================================================================
 *
##
 *
 */
namespace SAdmin\Controller;
use SAdmin\Model\GoodsModel;
class GoodsController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='商城';
		$this->breadcrumb2='商品管理';
	}
	public function check_sales()
	{
		$star = strtotime(I('star'));
		$end = strtotime(I('end'));
		$province = I('province');
		$city = I('city');
		$county = I('county');
		$address = $province.$city.$county;

		if($address=='省份地级市市、县级市'){
			if(!empty($star) && !empty($end)){
				$where['time'] = array('between',"$star,$end");
				$sales = M('order')->where($where)->count();
				ajaxReturn($sales);
			}else{
				$sales = M('order')->count();
				ajaxReturn($sales);
			}
		}else{
			$leng = strlen($province)-3;
			$province =substr($province,0,$leng);
			$address = $province.$city.$county.'%';
			$where['buy_address'] = array('like',$address);
			if(!empty($star) && !empty($end)){
				$where2['time'] = array('between',"$star,$end");
				$sales = M('order')->where($where)->where($where2)->count();
				ajaxReturn($sales);
			}
			$sales = M('order')->where($where)->count();
			ajaxReturn($sales);
		}
	}
	public function shop_info()
	{
		$id = I('id');
		$status = I('shop_info');
		M('gerenshangpu')->where("id = $id")->setField('shop_info',$status);
		$this->success("修改成功",U("Goods/ggshop"));
	}
	public function index(){
		$model=new GoodsModel();

		$filter=I('get.');

		$search=array();

		if(isset($filter['name'])){
			$search['name']=$filter['name'];
		}
		if(isset($filter['category'])){
			$search['category']=$filter['category'];
			$this->get_category=$search['category'];
		}
		if(isset($filter['status'])){
			$search['status']=$filter['status'];
			$this->get_status=$search['status'];
		}

		$data=$model->show_goods_page($search);
		
		$this->category=M('product_cate')->select();

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出


		$this->display();
	}

 public function dianpu(){

		if(IS_POST){
			$model=new GoodsModel();
			$data=I('post.');
			$return=$model->add_dianpu($data);
			 $this->osc_alert($return);
		}
		$category = array();
		$cateList = M('product_cate')->select();

		//查出最后一级
		foreach ($cateList as $key => $value) {
			$count = M("product_cate")->where(array("pid"=>$value["id"]))->count();
			if($count==0){
				$category[] = $value;
			}
		}
$proid = 1;
		$this->goods = M("gerenshangpu")->where(array("id"=>$proid))->find();
$this->id=$proid;
		$this->category=$category;
		$this->action=U('Goods/dianpu');
		$this->breadcrumb2='总后台店铺';
		$this->crumbs='修改店铺';

		$this->display('dianpu');

    }






	function add(){

		if(IS_POST){
			$model=new GoodsModel();
			$data=I('post.');
			$return=$model->add_goods($data);
			$this->osc_alert($return);
		}
		$category = array();
		$cateList = M('product_cate')->select();

		//查出最后一级
		foreach ($cateList as $key => $value) {
			$count = M("product_cate")->where(array("pid"=>$value["id"]))->count();
			if($count==0){
				$category[] = $value;
			}
		}


		$this->category=$category;
		$this->action=U('Goods/add');
		$this->crumbs='新增';
		$this->display('add');
	}





	public function cate(){

		$protype = M('product_cate');
		$oneCate = array();
		$twoCate = array();
		$threeCate = array();

		//查询当前产品分类
		$oneList = $protype ->where(array("pid"=>0))->order("sort asc")->select();
		foreach($oneList as $keyy => $one){
			//添加一级
			array_push($oneCate,$one);
			$twoList = $protype -> where(array('pid'=>$one['id']))->order("sort asc")-> select();
			foreach ($twoList as $key => $two) {
				//添加二级
				array_push($twoCate,$two);
				$threeList = $protype -> where(array('pid'=>$two['id']))->order("sort asc")-> select();
				foreach ($threeList as $k => $three) {
					//添加三级
					array_push($threeCate,$three);
				}
			}

		}


		$this->assign("oneCate",$oneCate);
		$this->assign("twoCate",$twoCate);
		$this->assign("threeCate",$threeCate);

		$this->breadcrumb2='分类管理';
		$this->display();
	}


	public function catesort(){
		$id = (int)I('post.id');
		$sort = (int)I('post.sort');

		$is_exis = M('product_cate')->where(array('id'=>$id))->count();
		if(!$is_exis){
			exit(json_encode(array('code'=>0,'msg'=>"该参数不存在，请刷新重试")));
		}

		$res = M('product_cate')->where(array('id'=>$id))->setField('sort',$sort);
		if($res !== false){
			exit(json_encode(array('code'=>1,'msg'=>"排序成功")));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>"排序失败")));
		}
	}



	public function delCate(){
		$id = I("id");
		//该ID是否存在下级分类
		$count = M("product_cate")->where(array("pid"=>$id))->count();
		$goods = M("product_detail")->where(array("type_id"=>$id))->count();
		$v = M("verify_list")->where(array("cate_id"=>$id))->count();
		$u = M("gerenshangpu")->where(array("shop_cate"=>$id))->count();
		if($count>0||$goods>0||$v>0||$u>0){
			$this->error("存在子分类或商品或商家，无法删除。");
		}else{
			M("product_cate")->where(array("id"=>$id))->delete();
			$this->success("删除成功");
		}
	}

	public function addcate(){
		$protype = M('product_cate');
		$oneCate = array();
		$twoCate = array();
		$threeCate = array();

		//查询当前产品分类
		$oneList = $protype ->where(array("pid"=>0))->order("sort desc")->select();
		foreach($oneList as $keyy => $one){
			//添加一级
			array_push($oneCate,$one);
			$twoList = $protype -> where(array('pid'=>$one['id'])) -> select();
			foreach ($twoList as $key => $two) {
				//添加二级
				array_push($twoCate,$two);
				$threeList = $protype -> where(array('pid'=>$two['id'])) -> select();
				foreach ($threeList as $k => $three) {
					//添加三级
					array_push($threeCate,$three);
				}
			}

		}


		$this->assign("oneCate",$oneCate);
		$this->assign("twoCate",$twoCate);
		$this->assign("threeCate",$threeCate);
		$this->display("adcate");
	}


	public function editCate(){
		$id = I("id");

		$protype = M('product_cate');

		$cate = $protype->where(array("id"=>$id))->find();

		$this->assign("cate",$cate);


		$this->breadcrumb2='分类管理';


		$this->display("edcate");
	}

	public function cateUpdate(){
		$pic = I("pic");
		$name = I("name");
		$id = I("id");
		$cangku_ratio = I('cangku_ratio');
		$fengmi_ratio = I('fengmi_ratio');
		$goods_end = (Float)I('goods_end');

		$data['id'] = $id;
		$data['name'] = $name;
		$data['cangku_ratio'] = $cangku_ratio/100;
		$data['fengmi_ratio'] = $fengmi_ratio/100;
		if($goods_end>0){
			$data['goods_end'] = $goods_end * 3600;
		}else{
			$data['goods_end'] = 3600*24*7;
		}
		

		$res = img_uploading();
		$photo = $res['res'];
		if(strstr($photo,'/')){
			$data['pic'] = $photo;
		}

		$isSave = M("product_cate")->save($data);

		if($isSave !== false){
			$this->success("修改成功",U("Goods/cate"));
		}else{
			$this->error("修改失败");
		}

	}

	public function cateAdd(){
		$pic = I("pic");
		$name = I("name");
		$pid = I("pid");
		$cangku_ratio = I('cangku_ratio');
		$fengmi_ratio = I('fengmi_ratio');
		$goods_end = (float)I('goods_end');
		$category = (int)I('category');
		if(!$name){
			$this->error("参数不能为空");
		}

		if($cangku_ratio==''){
			$this->error('返还商家余额折扣不能为空');
		}
		if($fengmi_ratio==''){
			$this->error('返还商家积分折扣不能为空');
		}


		//当前分类下是否存在相同名字
		$count = M("product_cate")->where(array("name"=>$name,"pid"=>$pid))->count(1);
		if($count>0){
			$this->error("已经存在该分类标题");
		}

		if(!$pid)$pid=0;

		$res = img_uploading();
		$photo = $res['res'];
		$data['pic'] = $photo;
		$data['name'] = $name;
		$data['pid'] = $pid;
		$data['ctime'] = time();
		$data['type_id'] = $category;
		$data['cangku_ratio'] = $cangku_ratio/100;
		$data['fengmi_ratio'] = $fengmi_ratio/100;
		if($goods_end >0){
			$data['goods_end'] = $goods_end * 3600;
		}else{
			$data['goods_end'] = 3600 * 24 * 7;
		}

		$isAdd = M("product_cate")->add($data);
		if($isAdd){
			$this->success("添加商品分类成功",U('Goods/cate'));
		}else{
			$this->error("商品分类添加失败");
		}


	}



	function edit(){
		$model=new GoodsModel();

		if(IS_POST){
			$data=I('post.');
			//dump($data);die;
			$return=$model->edit_goods($data);
			$this->osc_alert($return);
		}
		$this->crumbs='编辑';
		$this->action=U('Goods/edit');

		$category = array();
		$cateList = M('product_cate')->select();

		//查出最后一级
		foreach ($cateList as $key => $value) {
			$count = M("product_cate")->where(array("pid"=>$value["id"]))->count();
			if($count==0){
				$category[] = $value;
			}
		}
		$this->category=$category;
		$proid = I("id");
		$goods = M("product_detail")->where(array("id"=>$proid))->find();
		$goods['parameter'] = M("product_parameter")->where(array('gid'=>$proid))->select();
		$this->assign('n',$i);
		$goods['zhifu_pay'] = explode(',',$goods['zhifu_pay']);
		$this->goods =$goods;
		$this->id=$proid;
		$this->display('edit');
	}

	function test(){
		$a = ',1,2,3';
		$b = 1;
		$a = explode(',',$a);
		if(in_array($b, $a)){
			echo 2;
		}else{
			echo 3;
		}
	}

	function copy_goods(){
		$id =I('id');
		$model=new GoodsModel();
		if($id){
			foreach ($id as $k => $v) {
				$model->copy_goods($v);
			}
//			$data['redirect']=U('Goods/index');
			$this->ajaxReturn($data);
			die;
		}
	}

	function del(){
		$model=new GoodsModel(); 
		$return=$model->del_goods(I('get.id'));
		$this->osc_alert($return);
	}

  	public function ggshop(){
		$filter=I('get.');
		$search=array();

		if(isset($filter['name'])){
			$search['shop_name']=array('like','%'.$filter['name'].'%');
		}
		if(isset($filter['shop_cate'])){
			$search['shop_cate']=$filter['shop_cate'];
			$this->get_category=$search['shop_cate'];
		}
		
		$id['id']=array('neq',1);
		$ggshop=M('gerenshangpu')->where($id)->where($search)->select();
		foreach($ggshop as $k => $v){
			$type_name = M('product_cate')->where('id='.$v['shop_cate'])->getField('name');
			$ggshop[$k]['type_name'] = $type_name;
		}
		
		$this->breadcrumb1='商家入驻';
		$this->breadcrumb2='入驻店铺';
		$category = M('product_cate')->order('id asc')->field('id,name')->select();
		$this->assign('category',$category);
		$this->assign('ggshop',$ggshop);
		$this->display();
  	}


  public function verify(){
	  
	$data = M('verify_list')->order("status asc,id desc,cate_id")->select();
	foreach ($data as $k => $v) {
  		$userd_type = M('user')->where('userid='.$v['uid'])->getField('cate_id');
  		$data[$k]['userd_type'] = M('product_cate')->where('id='.$userd_type)->getField('name');
  	}
	$this->data = $data;
	$this->breadcrumb1='商家入驻';
	$this->breadcrumb2='认证列表';
	$this->display();
  }

  public function saveVerify(){
		  $id = I("id");
  		$status = I("status");

  		if(empty($id)||!isset($id)){
  			$this->error("参数错误");
  		}

  		if(empty($status)||!isset($status)){
  			$this->error("参数错误");
  		}

  		$verifyInfo = M("verify_list")->where(array("status"=>0,"id"=>$id))->find();
  		$type = $verifyInfo['type'];
  		$typeField = $type==2?"is_e_verify":"is_p_verify";
  		$statusField = $status==1?1:0;
  		$isVerify = M("verify_list")->where(array("status"=>0,"id"=>$id))->setField("status",$status);

  		// M("member")->where(array("member_id"=>$verifyInfo['uid']))->setField($typeField,$statusField);
  		// if($type==2){
  		// 	M("member")->where(array("member_id"=>$verifyInfo['uid']))->setField("is_dailishang",2);
  		// }

  		if($isVerify){
			M('user')->where(array('userid'=>$verifyInfo['uid']))->setField("is_dailishang",2);
  			$this->success("操作成功");
  		}else{
  			$this->error("操作失败");
  		}

  }


    public function level(){
	  	$data = M('level_list')->order("status asc")->select();
	  	foreach ($data as $key => $value) {
	  		$data[$key]['account'] = M("member")->where(array("member_id"=>$value['uid']))->getField("phone");
	  		$data[$key]['username'] = M("member")->where(array("member_id"=>$value['uid']))->getField("uname");
	  	}

	  	$this->data = $data;
	  	$this->breadcrumb1='会员等级';
	  	$this->breadcrumb2='升级列表';
	  	$this->display();
  	}


  	public function levelup(){
  		$id = I("id");
  		$status = I("status");

  		if(empty($id)||!isset($id)){
  			$this->error("参数错误");
  		}

  		if(empty($status)||!isset($status)){
  			$this->error("参数错误");
  		}


  		$verifyInfo = M("level_list")->where(array("status"=>0,"id"=>$id))->find();
  		$isVerify = M("level_list")->where(array("status"=>0,"id"=>$id))->setField("status",$status);

  		if($status==1){
  			$isVerify = M("member")->where(array("member_id"=>$verifyInfo['uid']))->setField("member_grade",$verifyInfo['level']);
  		}

  		if($isVerify){
  			$this->success("操作成功");
  		}else{
  			$this->error("操作失败");
  		}


  	}
  	    public function wen(){
  	    	$this->breadcrumb1='商家入驻';
	  	$this->breadcrumb2 = '入驻店铺';


		$id = I('id');
		$configsAll = M('gerenshangpu');
		$msgll = $configsAll->where(array('id' => $id))->find();
		$this->assign('id', $id);
		$this->assign('msgll', $msgll);
	  	$this->display();
  	}
  	public function Savewen()
	{
		$id = I('id');
		$configsAll = M('gerenshangpu');
		$data['shop_stort'] = I('shop_stort');
		if(empty($id)){
            echo '<script>alert("没有找到店铺"); window.history.back(-1); </script>';
            return;
        }else{
        	$configsAll->where(array('id'=>$id))->save($data);
        	$this->success("修改成功",U("Goods/ggshop"));
        }

    }
      	public function zhuangtai()
	{
		$id = I('id');
		$configsAll = M('gerenshangpu');
		$data['shop_zhuangtai'] = I('shop_zhuangtai');
		if(empty($id)){
            echo '<script>alert("没有找到店铺"); window.history.back(-1); </script>';
            return;
        }else{
        	$configsAll->where(array('id'=>$id))->save($data);
        	$this->success("修改成功",U("Goods/ggshop"));
        }

    }
          	public function dltgeren()
	{
		$id = I('id');
		$configsAll = M('gerenshangpu');

		if(empty($id)){
            echo '<script>alert("没有找到店铺"); window.history.back(-1); </script>';
            return;
        }else{
        	$configsAll->where(array('id'=>$id))->delete();
        	$this->success("删除成功",U("Goods/ggshop"));
        }

    }





}
?>