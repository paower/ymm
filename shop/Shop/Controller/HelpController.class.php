<?php

namespace Shop\Controller;

use Think\Controller;

/**
 * 公告控制器
 */
class HelpController extends CommonController
{
	public function index()
	{
		$newinfo = M('shop_help')->select();
		$this->assign('newinfo',$newinfo);
		$this->display();
	}
	public function detail()
    {
        $nid = I('nid', 'intval', 0);
        $newdets = M('shop_help')->where(array('id' => $nid))->find();
        $this->assign('newdets', $newdets);
        $this->display();
    }
}