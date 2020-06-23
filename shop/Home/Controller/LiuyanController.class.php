<?php

namespace Home\Controller;

use Think\Controller;

/**
 * 留言控制器
 */
class LiuyanController extends Controller
{
	public function weixin(){
		$where['name'] = 'kf_weixin';
		$where2['name'] = 'kf_ewm';
		$kf_weixin = M('Config')->where($where)->getField('value');
		$kf_ewm = M('Config')->where($where2)->getField('value');
		$this->assign('kf_weixin',$kf_weixin);
		$this->assign('kf_ewm',$kf_ewm);
		$this->display();
	}
}