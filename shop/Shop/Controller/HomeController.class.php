<?php
namespace Shop\Controller;
//use Home\Controller\CommonController;
use Admin\Model\GoodsModel;
class HomeController extends CommonController
{
    public function editUpload(){
        $result = array();
        $imgal = file_uploading();
        if ($imgal['stats'] == 'error') {
            $result['errno'] = $imgal['res'];
            $result['data'] = array();
            echo json_encode($result);
            exit();
        }
        $result['errno'] = 0;
        $data = array();
        foreach ($imgal['res'] as $key => $value) {
            $data[] = $value;

        }
        $result['data'] = $data;
        echo json_encode($result);

    }



//余额买卖
    public function balance()
    {
        $this->display();
    }
//充值余额-填写信息
    public function recharge()
    {
        $this->display();
    }
    public function rechargess()
    {
        $M=M('chongzhi');
        $data= I("post.");
        $res = img_uploading();
		$photo = $res['res'];
		$data['img'] = "/Uploads/image/touxiang/".$photo;
/* var_dump($photo);die;*/
      if(empty($data['username'])){
            echo '<script>alert("请输入充值MXC账号"); window.history.back(-1); </script>';
      }elseif(empty($data['name'])){
            echo '<script>alert("请输入账号昵称"); window.history.back(-1); </script>';
      }elseif(empty($data['money'])){
           echo '<script>alert("请输入充值金额"); window.history.back(-1); </script>';
      }elseif(empty($data['phone']) ){
           echo '<script>alert("请输入联系手机"); window.history.back(-1); </script>';
      }else{
      	$data['addtime']=time();
      	$chongzhi=$M->add($data);
      	if($chongzhi){
      		echo "<script>alert('提交成功，等待审核')</script>";
        	$this->sendcode();		
        }else{
            echo '<script>alert("提交失败"); window.history.back(-1); </script>';
        }
      }
    }


    function sendcode(){
                $mes=newMsg();
                if($mes=='0'){
                    echo '<script>location.href="'.U('shop/Home/balance').'"</script>';
                }else{
                    echo '<script>alert("短信发送失败"); window.history.back(-1); </script>';
                }
    }


     //充值余额-成功
    public function recharge3()
    {
        $this->display();
    }
 //出售余额
    public function balance_sell()
    {
        $this->display();
    }
    public function balance_sellss()
    {
      $M=M('chushou');
      $data= I("post.");
        $res = img_uploading();
		$photo = $res['res'];
		$data['img'] = "/Uploads/image/touxiang/".$photo;
if(empty($data['name']) || $data['name']=="联系人"){

             echo '<script>alert("请输入联系人"); window.history.back(-1); </script>';
      }elseif(empty($data['some']) || $data['some']=="出售数量"){

           echo '<script>alert("请输入出售数量"); window.history.back(-1); </script>';
      }elseif(empty($data['phone']) || $data['phone']=="联系手机"){
           echo '<script>alert("请输入联系手机"); window.history.back(-1); </script>';
      }elseif(empty($data['jiawei'])){
           echo '<script>alert("请输入出售价位"); window.history.back(-1); </script>';
      }else{
      	$data['addtime']=time();
      	$chushou=$M->add($data);
      	      if($chushou){
      	 echo "<script>alert('提交成功，等待审核')</script>";
         $this->sendcode();
      }else{
           echo '<script>alert("提交失败"); window.history.back(-1); </script>';
      }
      }

    }
//出售余额-成功
    public function balance_sellok()
    {
        $this->display();
    }
 //余额详情
    public function balance_detail()
    {
        $this->display();
    }
 //联系客服
    public function balance_help()
    {
    	$id['tid']=I('ids');

         $kefu=M('wenzhang')->where($id)->find();

        $this->assign('kefu',$kefu);

        $this->display();
    }

	public function alife()
	{
		$productList = M("product_detail")->where(array("type_id" => 16))->order("praise_num desc")->select();

		$topProduct = M("product_detail")->where(array("type_id" => 16, "stock" => array("gt", "0")))->order("stock asc")->limit(3)->select();

		$this->assign("topProduct", $topProduct);
		$this->assign("productList", $productList);
		$this->display();
	}

	public function caor()
	{

		$this->display();
	}

	public function details()
	{
		$proid = I("proid");
		$uid = session('user_auth.uid');
		$product = M("product_detail")->where(array('id' => $proid))->find();
		$colorList = explode(",", $product['color_cate']);
		$sizeList = explode(",", $product['csize']);
		$jifentype = explode(",", $product['jifen_type']);

		$parameter = M('product_parameter')->where("gid=$proid")->select();
		foreach ($parameter as $k => $v) {
			$kucun+=$v['kucun'];
		}
		$this->assign('kucun',$kucun);
		$this->assign('parameter',$parameter);

		//销售数量
		$sale_num = M('order_detail')->where("com_id=$proid")->count();
		$this->assign('sale_num',$sale_num);
		//是否收藏
		$isCollect = M("collect")->where(array("uid" => $uid, "proid" => $product['id']))->count();
		$this->assign("isCollect", $isCollect);
		$this->assign("sizeList", $sizeList);
		$this->assign("colorList", $colorList);
		$this->assign("product", $product);
		$this->assign("jifentype", $jifentype);
		$this->display();
	}


	public function addCar()
	{
		$uid = session('userid');
		$pid = I("p");
		$num = I("n");
		$size = I("s");
		$color = I("c");
		$jifentype = I("jtype");
		if (!$uid) message("请登录后进行操作。",2);
		if (!$pid) message("参数不正确。");
		if (!$num) message("数量不正确。");
//          if(!$size) message("尺寸不为空。");
//          if(!$color) message("颜色不为空。");
//          if(!$jifentype) message("积分类型不能为空。");


		//查询商品名以及价格
		$pdetail = M("product_detail")->where(array("id" => $pid))->find();
		if (!$pdetail) message("该商品不存在");
		if($pdetail['shangjia'] == $uid){
			$this->error('无需购买自己店铺商品');
		}
                


		$name = $pdetail['name'];
		$price = I('price');
		if($price!=0){
			$price = I('price');
		}else{
			$price = $pdetail['price'];
		}
		$pic = $pdetail['pic'];
		$type = I('type');
		$list = array();
		$car = session("car");
		//购物车是否有商品
		if ($car) {
			$product = $car[$pid];

			//是否存在该商品
			if ($product) {
				$tag = "no";
				foreach ($product as $key => $prod) {
					if ($prod['pid'] == $pid && $prod['size'] == $size && $prod['color'] == $color && $prod['type'] == $type) {
						$product[$key]['num'] = $product[$key]['num'] + $num;
						$tag = "ok";
					}
				}

				$car[$pid] = $product;
				if ($tag == "no") {
					$pp['pid'] = $pid;
					$pp['size'] = $size;
					$pp['color'] = $color;
					$pp['num'] = $num;
					$pp['name'] = $name;
					$pp['price'] = $price;
					$pp['pic'] = $pic;
					$pp['jtype'] = $jifentype;
					$pp['type'] = $type;
					$car[$pid][] = $pp;
				}
				session("car", $car);
			} else {
				$product['pid'] = $pid;
				$product['size'] = $size;
				$product['color'] = $color;
				$product['num'] = $num;
				$product['name'] = $name;
				$product['price'] = $price;
				$product['pic'] = $pic;
				$product['jtype'] = $jifentype;
				$product['type'] = $type;
				$car[$pid] = array($product);
				session("car", $car);
			}
		} else {
			$product['pid'] = $pid;
			$product['size'] = $size;
			$product['color'] = $color;
			$product['num'] = $num;
			$product['name'] = $name;
			$product['price'] = $price;
			$product['pic'] = $pic;
			$product['jtype'] = $jifentype;
			$product['type'] = $type;
			$prod[$pid] = array($product);
			session("car", $prod);
		}
		message("加入购物车成功", "1");
	}


public function cation()
	{
		if(IS_GET){
			$id = I('get.id');
			$where['id'] = array('neq',1);
			
			if(isset($id)&&$id!=0){
				$where['shop_cate'] = $id;
				$this->assign('id',$id);
			}
		}
		$oneCateList = M("product_cate")->where(array("pid" => 0, 'is_duobao' => 1))->field('id,pid,pic,name,type')->order('sort asc')->select();
		//$oneCateid = M("product_cate")->where(array("pid" => 0, 'is_duobao' => 1))->getField('id');

		// $twoCateList = array();
		// foreach ($oneCateList as $k => $v) {
		// 	$cateList = M("gerenshangpu")->where(array("shop_cate" => $v['id'],'id'=>array('neq',1)))->select();
		// 	foreach ($cateList as $kv => $vv) {
		// 		array_push($twoCateList, $vv);
		// 	}
		// }


		$shopping =M("gerenshangpu")->where($where)->where(array('shop_zhuangtai'=>1))->select();
		// $data['one'] = $oneCateList;
		// $data['two'] = $twoCateList;
		// $jsonData = json_encode($data);
		$this->assign('oneCateList',$oneCateList);
		$this->assign('shopping',$shopping);
		// $this->assign('jsonData', $jsonData);
		$this->display();
	}

	//二级分类详情
	public function Twodets(){
		$id = I('id');
		$TwodetList = M("product_cate")->where(array("pid" =>$id))->getfield('id',true);
		$goods = M('product_detail')->where(array('type_id'=>array('IN',$TwodetList)))->select();
		$this->assign("goodsdet", $goods);
		$this->display('mend');


	}


	public function cationList()
	{
		$id = I("id");
		$nextCateList = M("product_cate")->where(array("pid" => $id))->select();
		$cid = I("cid");
		$cateList = M("product_cate")->where(array("pid" => $cid))->select();
		$this->assign("id", $id);
		$this->assign("cid", $cid);
		$this->assign("nextCateList", $nextCateList);
		$this->assign("cateList", $cateList);
		$this->display("cation");
	}

	public function evaluation()
	{

		$this->display();
	}


	public function fbym()
	{
		$id = I('id');
		$this->assign('id',$id);
		$this->display();
	}

	public function ferred()
	{
		$youxuan = M("product_cate")->where(array("id" => 17))->find();

		$productList = M("product_detail")->where(array("type_id" => 17))->select();

		$this->assign("productList", $productList);
		$this->assign("youxuan", $youxuan);
		$this->display();
	}


	public function indexs(){

		$this->display("/Index/index");
	}






	public function index()
	{
		$sshop_banner = M('sshop_banner')->select();
		$this->assign("sshop_banner", $sshop_banner);
	
		//查出首页的7个菜单 第8个菜单是分类
		$product_cate_table = M("product_cate");

		$menuList = $product_cate_table->where(array("pid" =>0))->order("sort desc")->limit(10)->select();
		$this->assign("menuList", $menuList);

		$bannerList = M("shop_banner")->select();
		$this->assign("bannerList", $bannerList);

        $today_product_list = M("product_detail")->where(array("status"=>1))->order("ctime desc")->limit(5)->select();
        $this->assign("today_product_list",$today_product_list);

        //总后台商品还是个人店铺商品，0为总后台
        $shang['shangjia']=array('eq',0);
	    //火爆产品
	    $hot_product_list = M("product_detail")->where(array("is_hot"=>1,"is_shangjia"=>1))->where($shang)->limit(6)->order("is_sort,ctime desc")->select();
		foreach ($hot_product_list as $k => $v) {
	    	$where['gid'] = $v['id'];
	    	$num = M('product_parameter')->where($where)->count();
			if($num!=0 || $v['price']==0){
				$where['gid'] = $v['id'];
				$parameter = M('product_parameter')->where($where)->select();
				$hot_product_list[$k]['price'] = $parameter[0]['price'];
			}
		}
	    $this->assign("hot_product_list",$hot_product_list);
		
        //最新产品
        $new_product_list = M("product_detail")->where(array("is_new"=>1,"is_shangjia"=>1))->where($shang)->order("is_sort,ctime desc")->limit(7)->select();
		foreach ($new_product_list as $k => $v) {
	    	$where['gid'] = $v['id'];
	    	$num = M('product_parameter')->where($where)->count();
			if($num!=0 || $v['price']==0){
				$where['gid'] = $v['id'];
				$parameter = M('product_parameter')->where($where)->select();
				$new_product_list[$k]['price'] = $parameter[0]['price'];
			}
		}
        $this->assign("new_product_list",$new_product_list);

        //母婴用品
        $mu_product_list = M("product_detail")->where(array("status"=>1,"is_shangjia"=>1))->order("ctime asc")->limit(8)->select();
        $this->assign("mu_product_list",$mu_product_list);

        //为您推荐
        $like_product_list = M("product_detail")->where(array("is_tuijian"=>1,"is_shangjia"=>1))->where($shang)->order("is_sort,ctime desc")->limit(8)->select();
		foreach ($like_product_list as $k => $v) {
	    	$where['gid'] = $v['id'];
	    	$num = M('product_parameter')->where($where)->count();
			if($num!=0 || $v['price']==0){
				$where['gid'] = $v['id'];
				$parameter = M('product_parameter')->where($where)->select();
				$like_product_list[$k]['price'] = $parameter[0]['price'];
			}
		}
        $this->assign("like_product_list",$like_product_list);

        //新闻公告
        $notice = M("notice")->order("notice_addtime asc")->select();
        $this->assign('notice',$notice);

        //入驻商家
        $id=array("neq",1);
        $ruzhu=M('gerenshangpu')->where(array('shop_zhuangtai'=>1,'id'=>$id))->order('shop_stort asc')->limit(9)->select();
        $this->assign('ruzhu',$ruzhu);


		$this->display();
	}

/*public  function chaxun(){
	
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

		$data=$model->show_shangpin($search);
var_dump($filter);die;
		$this->category=M('product_cate')->select();

		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('goodsdet',$data['goodsdet']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出

		$this->display();
}*/
	public function mend()
	{
		
		$cid = I("id");
		$cates = M('product_cate');


		/*找到二级分类*/
/*		$goodsdetList = array();
		$twocat = $cates->where(array('pid' => $cid))->order('ctime desc')->field('id,pid,ctime')->select();
		foreach ($twocat as $ktwo => $vtwo){
			$threecats = $cates->where(array('pid' => $vtwo['id']))->order('ctime desc')->field('id,pid,ctime')->select();
			array_push($goodsdetList, $vtwo['id']);
			foreach ($threecats as $kthree => $vthree){
				array_push($goodsdetList, $vthree['id']);
			}
		}*/
		$status=I('sta');//首页区分点击时查看的产品/最新、推荐、火热、全部
		$cate = I('cate');
		$shang['shangjia']=array('eq',0);//0为总后台商品
		if(!empty($cate)){
			$shang['pd.type_id'] = $cate;
			$cate_name = M('product_cate')->where(array('id'=>$cate))->getField('name');
			$this->assign('cate_name',$cate_name);
			$this->assign('cate',$cate);
		}else{
			$this->assign('cate',0);
		}

		$area = I('area');
		if(!empty($area)){
			$area_name = M('area')->where(array('id'=>$area))->getField('area_name');
			$shang['g.shop_address'] = array('like','%'.$area_name.'%');
			$this->assign('area_name',$area_name);
			$this->assign('area_id',$area);
		}else{
			$this->assign('area_id',0);
		}

		$sale_num=I('sale_num');


	if($_GET['sousuo']=='sousuo'){

			$model=D('Shop/Goods');

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

			$data=$model->show_shangpin($search);

			foreach ($data['goodsdet'] as $k => $v) {
				# code...
				if($v['price']=='0.00'){
					$id = $v['id'];
					$price = M('product_parameter')->where("gid = $id")->select();
					$data['goodsdet'][$k]['price'] = $price[0]['price'];
				}
			}
			
			$this->category=M('product_cate')->select();

			$this->assign('empty',$data['empty']);// 赋值数据集
			$this->assign('goodsdet',$data['goodsdet']);// 赋值数据集
			$this->assign('page',$data['page']);// 赋值分页输出
	}else{
		if($status==4){//全部产品
			if(!empty($sale_num)){
				if($sale_num==1){
					$goodsdet = M('product_detail pd')->where($shang)->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.buy_num desc')->field('pd.id,pd.name,pd.price,pd.pic,pd.buy_num')->select();
					$this->assign('sale_num',$sale_num);
				}
				if($sale_num==0){
					$goodsdet = M('product_detail pd')->where($shang)->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.buy_num asc')->field('pd.id,pd.name,pd.price,pd.pic,pd.buy_num')->select();
					$this->assign('sale_num',$sale_num);
				}
			}else{
				$goodsdet = M('product_detail pd')->where($shang)->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.ctime desc')->field('pd.id,pd.name,pd.price,pd.pic,pd.buy_num')->select();
			}

        }
        elseif($status==1){//火热产品
        	if(!empty($sale_num)){
        		if($sale_num==1){
					$goodsdet = M('product_detail pd')->where(array('is_hot'=>1))->where($shang)->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.buy_num desc')->field('pd.id,pd.name,pd.price,pd.pic')->select();
					$this->assign('sale_num',$sale_num);
				}
				if($sale_num==0){
					$goodsdet = M('product_detail pd')->where(array('is_hot'=>1))->where($shang)->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.buy_num asc')->field('pd.id,pd.name,pd.price,pd.pic')->select();
					$this->assign('sale_num',$sale_num);
				}
        	}else{
        		$goodsdet = M('product_detail pd')->where(array('is_hot'=>1))->where($shang)->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.ctime desc')->field('pd.id,pd.name,pd.price,pd.pic')->select();
        	}
        }
        elseif($status==2){//最新产品
        	if(!empty($sale_num)){
        		if($sale_num==1){
					$goodsdet = M('product_detail pd')->where(array('is_new'=>1))->where($shang)->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.buy_num desc')->field('pd.id,pd.name,pd.price,pd.pic')->select();
					$this->assign('sale_num',$sale_num);
				}
				if($sale_num==0){
					$goodsdet = M('product_detail pd')->where(array('is_new'=>1))->where($shang)->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.buy_num asc')->field('pd.id,pd.name,pd.price,pd.pic')->select();
					$this->assign('sale_num',$sale_num);
				}
        	}else{
				$goodsdet = M('product_detail pd')->where(array('is_new'=>1))->where($shang)->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.ctime desc')->field('pd.id,pd.name,pd.price,pd.pic')->select();
        	}
        }
        elseif($status==3){//推荐产品
        	if(!empty($sale_num)){
        		if($sale_num==1){
					$goodsdet = M('product_detail pd')->where(array('is_tuijian'=>1))->where($shang)->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.buy_num desc')->field('pd.id,pd.name,pd.price,pd.pic')->select();
					$this->assign('sale_num',$sale_num);
				}
				if($sale_num==0){
					$goodsdet = M('product_detail pd')->where(array('is_tuijian'=>1))->where($shang)->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.buy_num asc')->field('pd.id,pd.name,pd.price,pd.pic')->select();
					$this->assign('sale_num',$sale_num);
				}
        	}else{
				$goodsdet = M('product_detail pd')->where(array('is_tuijian'=>1))->where($shang)->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.ctime desc')->field('pd.id,pd.name,pd.price,pd.pic')->select();
        	}
        }  
        else{//产品
        	if(!empty($sale_num)){
        		if($sale_num==1){
					$goodsdet = M('product_detail pd')->where(array('type_id'=>$cid))->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.buy_num desc')->field('pd.id,pd.name,pd.price,pd.pic')->select();
					$this->assign('sale_num',$sale_num);
				}
				if($sale_num==0){
					$goodsdet = M('product_detail pd')->where(array('type_id'=>$cid))->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.buy_num asc')->field('pd.id,pd.name,pd.price,pd.pic')->select();
					$this->assign('sale_num',$sale_num);
				}
        	}else{
				$goodsdet = M('product_detail pd')->where(array('type_id'=>$cid))->join('ysk_gerenshangpu g ON pd.shangjia=g.userid')->order('pd.ctime desc')->field('pd.id,pd.name,pd.price,pd.pic')->select();
        	}
        }
     	
     	$product_cate = M('product_cate')->where('pid=0')->order('sort desc')->field('id,name')->select();

     	$area = M('area')->where(array('area_parent_id'=>0))->field("id,area_name")->select();

     	$this->assign('area',$area);
		$this->assign('product_cate',$product_cate);
		$this->assign('status',$status);
		foreach ($goodsdet as $k => $v) {
			if($v['price']==0){
				$where['gid'] = $v['id'];
				$parameter = M('product_parameter')->where($where)->select();
				$goodsdet[$k]['price'] = $parameter[0]['price'];

			}
		}
		$this->assign("goodsdet", $goodsdet);
		// dump($goodsdet);die;  
}

	 
		$this->display();
}


	public function getAreaChild(){
		$area_parent_id = (int)I('pid');

		$childArea = M('area')->where(array('area_parent_id'=>$area_parent_id))->field('area_name,id')->select();
		$childArea = json_encode($childArea);

		exit(json_encode(array('msg'=>$childArea)));

	}


	public function getCateChild(){
		$pid = (int)I('pid');

		$childCate = M('product_cate')->where(array('pid'=>$pid))->order('sort desc')->field('id,name')->select();
		$childCate = json_encode($childCate);

		exit(json_encode(array('msg'=>$childCate)));
	}

	public function sale_num(){
		$pid = (int)I('pid');

		$childCate = M('product_cate')->where(array('pid'=>$pid))->order('sort desc')->field('id,name')->select();
		$childCate = json_encode($childCate);

		exit(json_encode(array('msg'=>$childCate)));
	}

	public function order()
	{
		$addid = I('addid');
		/*默认收货地址*/
		$uid = session('userid');
		if ($addid == '') {
			$addinfp = M('address')->where(array('member_id' => $uid, 'zt_' => 1))->find();
		} else {
			$addinfp = M('address')->where(array('address_id' => $addid))->find();
		}
		if ($addinfp == '') {
			$addinfp = M('address')->where(array('member_id' => $uid))->order('address_id desc')->find();
		}
		$data = I("shop_sel");
		//查询出要购买的商品
		$shopCar = session("car");
		$list = array();
		foreach ($data as $key => $product) {
			$proArr = explode("|", $product);
			$pid = $proArr[0];
			$gid = $proArr[1];
			$list[$pid][$gid] = $shopCar[$pid][$gid];
		}

		if ($list) {
			session("selCar", $list);
		} else {
			$list = session("selCar");
		}
		foreach ($list as $k => $v) {
			$freight = M('product_detail')->where("id = $k")->getField('freight');
			if($freight=="到付" || $freight=="包邮"){
				$freight=0;
			}
			foreach ($v as $key => $value) {
				# code...
				$list[$k][$key]['freight'] = $freight;
			}
		}
		$this->assign("selProductList", $list);
		$this->assign("addinfp", $addinfp);
		$this->display();
	}

	public function jieshao()
	{
		$id['tid']=I('ids');
		$jieshao=M('wenzhang')->where($id)->find();

		$this->assign('jieshao',$jieshao);
		$this->display();
	}


	public function share()
	{

		$this->display();
	}

	public function size()
	{

		$this->display();
	}

	public function spcs()
	{

		$this->display();
	}

	public function tjms()
	{
		$productList = M("product_detail")->where(array("type_id" => 19))->select();

		$this->assign("productList", $productList);
		$this->display();
	}


	public function like()
	{
		$uid = session('user_auth.uid');
		$pid = I("pid");
		$product = M("product_detail")->where(array("id" => $pid))->find();
		if(!$product){
            $mes = array('status'=>3,'info'=>'收藏的商品不存在');
            $this->ajaxReturn($mes);
        }
		//是否存在 存在就取消 不存在就添加
		$collect = M("collect")->where(array("uid" => $uid, "proid" => $product['id']))->find();
		if ($collect) {
			M("collect")->where(array("uid" => $uid, "proid" => $product['id']))->delete();
			$mes = array('status'=>2,'info'=>'已取消收藏');
			$this->ajaxReturn($mes);
		} else {
			$data['uid'] = $uid;
			$data['proid'] = $product['id'];
			$data['time'] = time();
			M("collect")->add($data);
			$mes = array('status'=>1,'info'=>'收藏成功');
			$this->ajaxReturn($mes);
		}


	}


	public function unlike()
	{
		$uid = session('user_auth.uid');
		if (!$uid) message("请登录后进行操作");
		$pid = I("pid");
		//是否存在 存在就取消
		$collect = M("collect")->where(array("uid" => $uid, "proid" => $pid))->find();
		if (!$collect) message("收藏的商品不存在或已取消收藏");
		M("collect")->where(array("uid" => $uid, "proid" => $pid))->delete();
		message("已取消", "1");
	}

	//提交订单未添加地址回跳
	public function rebound()
	{
		$uname = I('uname');
		$nowurl = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		var_dump($nowurl);
		if ($uname == '') {
			$mes = array('status' => 1, 'info' => '请先填写收货地址', 'wenhref' => 'member/addresslist');
			$this->ajaxReturn($mes);
		}
	}

	//一元夺宝页面
	public function yuan()
	{
		$tables = M('product_detail');
		$goodslist = $tables->where(array('is_duobao' => 2))->order('id desc')->select();
		$this->assign('goodslist', $goodslist);
		$this->display();
	}

	public function yuandets()
	{
		$id = I('id');
		$tables = M('product_detail');
		$product = $tables->where(array('id' => $id))->find();
		$this->assign('product', $product);
		$this->display();
	}

	//商家入驻
	public function Business(){
		//修改商品
		$id = I('id');
		$prodsinfo = M('product_detail');
		$produce_dets = $prodsinfo->where(array('id'=>$id))->find();
		if($id){
			$product_parameter = M('product_parameter')->where("gid=$id")->select();
			$produce_dets['parameter'] = $product_parameter;
		}
		$this->assign('produce_dets',$produce_dets);
		$this->assign('id',$id);

		//三级分类详情
		$typeinfo = M('product_cate')->where(array('pid'=>0))->order('id asc')->field('id,pid,name')->select();
		$this->assign('typeinfo',$typeinfo);
		$this->display();

		
	}
	
	//点击一级分类出现对应二级分类
	public function Change_city(){
		$pid = I('pid');
		if($pid != ''){
			$two_cates = M('product_cate') ->where(array('pid'=>$pid))->field('id,pid,name')->select();
			$mes = array('status'=>1,'info'=>$two_cates);
			$this->ajaxReturn($mes);
		}
	}

	//点击二级分类出现对应三级分类
	public function Change_town(){
		$pid = I('pid');
		if($pid != ''){
			$two_cates = M('product_cate') ->where(array('pid'=>$pid))->field('id,pid,name')->select();
			if($two_cates != ''){
				$mes = array('status'=>1,'info'=>$two_cates);
				$this->ajaxReturn($mes);
			}else{
				$mes = array('status'=>2,'info'=>$two_cates);
				$this->ajaxReturn($mes);
			}
		}
	}
	
	public function up(){
        $path_url = 'images/';
        $upload = new \Think\Upload();//UploadFile();// 实例化上传类
        
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     $path_url; // 设置附件上传（子）目录
        
        $info   =   $upload->upload();
        if(!$info) {
            // 上传错误提示错误信息
            $this->error($upload->getError());
        }else{
            $savename = $info[0]['savename'];
            $up_url = $upload->rootPath.$info[0]['savepath'].$info[0]['savename'];
            $data = array();
            $data['status'] = 1;
            $data['info'] = '';
            $data['pic_url'] = $up_url;
            $this->ajaxReturn($data);
        }
    }

	//上级入驻产品上传
    public function Productsup()
    {
    	$info = I();
        //$pic = $_FILES['pic']['name'];
        //$imgal = file_uploading();
		//dump($imgal);die;
        $twocate = I('city');
        $id = I('id');
        if ($id == '') {
            if ($imgal['stats'] == 'error') {
                $this->error($imgal['res']);
            }
            //是否选择类型
 /*           if ($twocate) {
                $data['type_id'] = $twocate;
            } else {

                $this->error('请选择商品类型');
            }*/
        }
		
        //商品颜色
        $colors = trim(I('color'), ',');
        $data['color_cate'] = $colors;
        //商品尺寸
        $sizes = trim(I('size'), ',');
        $data['csize'] = $sizes;
        //$jifentypes = I('jifen_type');
        //$jifentypes = implode(',', $jifentypes);
        $products = M('product_detail');
// if($id == ''){
// 	    $data['pic'] = $imgal['res'][0];
//         $data['pic1'] = $imgal['res'][1];
//         $data['pic2'] = $imgal['res'][2];
//         $data['pic3'] = $imgal['res'][3];
//         $data['pic4'] = $imgal['res'][4];
//         $data['pic5'] = $imgal['res'][5];
// }else{
	$newmun = count($imgal['name']);
	if($newmun>0){
       foreach ($imgal['name'] as $k => $v) {
         // array_search($v,$imgal['name']);
         $data[$v] = $imgal['res'][$k];

       }
	}
//$imgal = array('res'=array(0=>'112133',1=>'3333'),'name'=>array(0=>'pic1',1=>'pic3'))

// }
        $data['name'] = I('name');
        $data['content'] = I('content');
        $data['old_price'] = I('old_price');
        // $data['price'] = I('price');
        //$data['jifen_nums'] = I('jifen_nums');
        $data['gr_new'] = I('gr_new');
        $data['gr_hot'] = I('gr_hot');
        $data['gr_tuijian'] = I('gr_tuijian');
        // $data['xiaofei_bili'] = I('xiaofei_bili');
        // $data['sanji_use'] = I('sanji_use');
        $data['is_duobao'] = 1;//普通商品
        // $data['jifen_nums'] = I('jifen_nums');//赠送积分数量
        $data['shangjia'] = user_login();//上传用户id
        $uid=user_login();
        $zong_s=M('gerenshangpu')->where(array('userid'=>$uid))->find();
		$type_a['id']=$zong_s['shop_type'];
		$zong_a=M('product_cate')->where($type_a)->find();
		$data['type_id'] = $zong_a['id'];
		
		$data['pic'] = I('pic');
		$data['pic1'] = I('pic1');
		$data['pic2'] = I('pic2');
		$data['pic3'] = I('pic3');
		$data['pic4'] = I('pic4');

		if(!$data['pic'] && !$data['pic1'] && !$data['pic2'] && !$data['pic3'] && !$data['pic4']){
			$this->error('请上传商品');
		}
		
		// 查询返还积分的百分比
		$fengmi_ratio = M('gerenshangpu gr')->where(array('gr.userid'=>$uid))->join('ysk_product_cate pc ON gr.shop_type=pc.type')->getField('fengmi_ratio');
		$data['jifen_nums'] = $data['price'] * $fengmi_ratio;


        $data['status'] = 0;//是否启用
   /*     $sendadd = I('sendpro');*/
   /*     if ($sendadd == '') {
            $data['address'] = '深圳';//发货地址
        } else {
            $data['address'] = $sendadd;//发货地址
        }*/

        // $kucun = I('kucun');
        // if ($kucun == '') {
        //     $data['stock'] = '888'; //库存量
        // } else {
        //     $data['stock'] = $kucun; //库存量
        // }

        if(!$data['name']){
            $this->error("商品名称不为空");
        }

        if(!$data['content']){
            $this->error("商品详情不为空");
        }
/*
        if(!$data['old_price']){
            $this->error("售价不为空");
        }
*/
     /*   $data['price'] = $data['old_price'];
*/
        // if(!$data['price']){
        //     $this->error("售价不为空");
        // }

        $data['freight'] = I('freight');
        $data['ctime'] = time();//上传时间点
	
		$user_id=user_login();
		$geren=M('gerenshangpu')->where(array('userid'=>$user_id))->find();

        if($id){
	        $goods = M('product_detail');
	        $res = $goods->where(array('id'=> $id))->save($data);
	        M('product_parameter')->where("gid=$id")->delete();
			$paramater['gid'] = $id;
			$paramater['cate'] = $info['cate'];
			$paramater['price'] = $info['price'];
			$paramater['kucun'] = $info['kucun'];
			for ($i=0; $i <count($info['cate']) ; $i++) {
				$data2[$i]['gid'] = $id; 
				$data2[$i]['type'] = $paramater['cate'][$i];
				$data2[$i]['price'] = $paramater['price'][$i];
				$data2[$i]['kucun'] = $paramater['kucun'][$i];
				$data2[$i]['jifen_nums'] = $paramater['price'][$i]* $fengmi_ratio;
			}
			M('product_parameter')->addAll($data2);
        }elseif(empty($geren)){
			$this->error('您还没有入驻店铺');
        }else{
			$goods = M('product_detail');
			$res = $goods->add($data);
			M('product_parameter')->where("gid=$res")->delete();
			$paramater['gid'] = $res;
			$paramater['cate'] = $info['cate'];
			$paramater['price'] = $info['price'];
			$paramater['kucun'] = $info['kucun'];
			for ($i=0; $i <count($info['cate']) ; $i++) {
				$data2[$i]['gid'] = $res; 
				$data2[$i]['type'] = $paramater['cate'][$i];
				$data2[$i]['price'] = $paramater['price'][$i];
				$data2[$i]['kucun'] = $paramater['kucun'][$i];
				$data2[$i]['jifen_nums'] = $paramater['price'][$i]* $fengmi_ratio;
			}
			M('product_parameter')->addAll($data2);	
		}

      /*  $upgoods['uid'] = session('user_auth.uid');*/
        $upgoods['uid'] =$geren['id'];
        $upgoods['goods_id'] = $res;
        $upgoods['uptime'] = time();
         //var_dump($imgal);die;
        $res_upgoods = M('upgoods')->add($upgoods);

        if ($res) {
            $this->redirect('shop/home/Minepros');
        } else {
            $this->error('商品上传出错');
        }
        	   /* var_dump($user_id);   */
    }
	public function lianxi(){
		$id = I('id');
		 
		$geren =  M('gerenshangpu')->where(array('userid' => $id))->find();
		$this->assign('geren', $geren);
		$this->display();
	}

	public function kaijiang(){
		$id = I('id');
		$tables = M('product_detail');
		$product = $tables->where(array('id' => $id))->find();
		$this->assign('product', $product);
		$this->display();
	}

	//我的产品
	public function Minepros(){
		$uid = user_login();
		$nctx['shangjia'] = $uid;
		$member = M("product_detail");
		$count = $member->where($nctx)->count(1);// 查询满足要求的总记录数
		$Page = new \Think\Page($count, 30);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$list = $member->where($nctx)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		//商品型号数量大于2时,并且价格不一样时,价格显示区间
		foreach ($list as $k => $v) {
			# code...
			$sql = 'SELECT * FROM ' . C('DB_PREFIX') . 'product_parameter WHERE gid = ' . $v['id'];
			$res = M()->query($sql);
			if(!empty($res)){
				$is_parameter = $res;
				$min_price = $is_parameter[0]['price'];
				$max_price = $is_parameter[0]['price'];
				foreach ($is_parameter as $key => $value) {
					# code...
					if($value['price']<$min_price){
						$min_price = $value['price'];
					}
					if($value['price']>$max_price){
						$max_price = $value['price'];
					}
					if($min_price!=$max_price){
						$list[$k]['price'] = $min_price . '-' . $max_price;
					}else{
						$list[$k]['price'] = $min_price;
					}
				}
			}
		}
		$this->assign('list', $list);// 赋值数据集
		$this->assign('page', $show);// 赋值分页输出
		$this->display();
	}

	//删除自己上传产品
	public function Delminepros(){
		$uid = session('user_auth.uid');
		$id = I('id');

		if($id != ''){
			$res_del = M('product_detail')->where(array('id'=>$id))->delete();
			if($res_del){
				$mes = array('status'=>1,'info'=>'删除成功');
				$this->ajaxReturn($mes);
			}else{
				$mes = array('status'=>2,'info'=>'删除失败');
				$this->ajaxReturn($mes);
			}
		}
	}


}

?>