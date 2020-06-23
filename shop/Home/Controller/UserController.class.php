<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends CommonController
{
   public function Personal()
    {
        $uid = session('userid');
        $uinfo = M('user')->where(array('userid' => $uid))->field('username,is_dailishang,userid,img_head,use_grade,user_credit,vip_grade,is_sign,sign_time,pay_money')->find();

        //今日可領取收益
        $time= date('Y-m-d',time());
        $time = strtotime($time);

        // 檢測今天是否簽到
        $start = strtotime(date('Y-m-d'));

        if($uinfo['sign_time'] < $start){
            $res = M('user')->where(array('userid'=>$userid))->setField('is_sign',0);
		}
        if($uinfo['is_sign'] == 0){
            $is_release = 0;
            $can_get = M('reward')->where(array('id'=>1))->getField('qd_price');
        }else{
            $is_release = 1;
        }

        $vip_info = M('vip_level')->order('level asc')->select();
        foreach ($vip_info as $k => $v) {
            if($uinfo['pay_money']>=$v['pay_money']){
                $uinfo['vip_grade'] = $v['name'];
            }
        }
        if($uinfo['pay_money']==null){
            $uinfo['vip_grade']=$vip_info[0]['name'];
        }


        //判斷當前語言
        $lang = LANG_SET;
        if (preg_match("/zh-C/i", $lang))
            $lantype = 1;//簡體中文
        if (preg_match("/en/i", $lang))
            $lantype = 2;//English

        $this->assign('is_release',$is_release);
        $this->assign('uid', $uid);
        $this->assign('uinfo', $uinfo);
        $this->assign('lantype', $lantype);
        $this->display();
    }

    public function Imgup()
    {
        $uid = session('userid');
        $picname = $_FILES['uploadfile']['name'];
        $picsize = $_FILES['uploadfile']['size'];
        if ($uid != "") {
            if ($picsize > 2014000) { //限制上傳大小
                ajaxReturn('圖片大小不能超過2M', 0);
            }
            $type = strstr($picname, '.'); //限制上傳格式
            if ($type != ".gif" && $type != ".jpg" && $type != ".png" && $type != ".jpeg") {
                ajaxReturn('圖片格式不對', 0);
            }
            $rand = rand(100, 999);
            $pics = uniqid() . $type; //命名圖片名稱
            //上傳路徑
            $pic_path = "./Public/home/wap/heads/" . $pics;
            move_uploaded_file($_FILES['uploadfile']['tmp_name'], $pic_path);
        }
        $size = round($picsize / 1024, 2); //轉換成kb
        $pic_path = trim($pic_path, '.');
        if ($size) {
            $res = M('user')->where(array('userid' => $uid))->setField('img_head', $pics);
            ajaxReturn($pic_path, 1);
        }
    }

    public function test()
    {
        $this->display();
    }

    public function imgUps()
    {
        if (IS_AJAX) {
            $uid = session('userid');
            $dataflow = trim(I('dataflow'));
            $base64 = str_replace('data:image/jpeg;base64,', '', $dataflow);
            $img = base64_decode($base64);
            //保存地址
            $imgDir = './Public/home/wap/heads/';
            //要生成的圖片名字
            $filename = md5(time() . mt_rand(10, 99)) . ".png"; //新圖片名稱
            $newFilePath = $imgDir . $filename;
            $res = file_put_contents($newFilePath, $img);//返回的是字節數
            if ($res > 1000) {
                //修改頭像
                $res_change = M('user')->where(array('userid' => $uid))->setField('img_head', $filename);
                if ($res_change) {
                    ajaxReturn('頭像修改成功', 1);
                } else {
                    ajaxReturn('頭像修改失敗', 0);
                }
            } else {
                ajaxReturn('頭像修改失敗', 0);
            }
        }
    }


    public function Setuname()
    {
        $uid = session('userid');
        $uname = M('user')->where(array('userid' => $uid))->getField('username');
        if (IS_AJAX) {
            $uname = trim(I('uname'));
            if ($uname == '') {
                ajaxReturn('請填寫姓名', 0);
            } else {
                $res_Save = M('user')->where(array('userid' => $uid))->setField('username', $uname);
                if ($res_Save) {
                    ajaxReturn('昵稱修改成功', 1, '/User/Personal');
                } else {
                    ajaxReturn('昵稱修改失敗', 0, '/User/Personal');
                }
            }
        }
        $this->assign('uname', $uname);
        $this->display();
    }

   public function Mobile()
    {
        $uid = session('userid');
        $uname = M('user')->where(array('userid' => $uid))->getField('mobile');
        // if (IS_AJAX) {
        //     $uname = trim(I('uname'));
        //     if ($uname == '') {
        //         ajaxReturn('請填寫姓名', 0);
        //     } else {
        //         $res_Save = M('user')->where(array('userid' => $uid))->setField('username', $uname);
        //         if ($res_Save) {
        //             ajaxReturn('昵稱修改成功', 1, '/User/Personal');
        //         } else {
        //             ajaxReturn('昵稱修改失敗', 0, '/User/Personal');
        //         }
        //     }
        // }
        $this->assign('uname', $uname);
        $this->display();
    }
       

    public function Setpwd()
    {
        $type = trim(I('type'));

        if ($type == 1) {
            $title = '登錄密碼修改';
        } else {
            $title = '支付密碼修改';
        }
        if (IS_AJAX) {
            $user = D('Home/User');
            $user_object = D('Home/User');
            $uid = session('userid');
            $pwd = trim(I('pwd'));
            $pwdrpt = trim(I('pwdrpt'));
            $type = trim(I('pwdtype'));
            if ($pwdrpt == '') {
                ajaxReturn('新密碼不能為空哦', 0);
            }
            $account = M('user')->where(array('userid' => $uid))->Field('account,mobile,login_pwd')->find();
            //驗證初始密碼
            $user_info = $user_object->Savepwd($account['mobile'], $pwd, $type);
            $salt = substr(md5(time()), 0, 3);
            if ($type == 1) {
                //密碼加密
                $data['login_pwd'] = $user->pwdMd5($pwdrpt, $salt);
                $data['login_salt'] = $salt;
            } else {
                $data['safety_pwd'] = $user->pwdMd5($pwdrpt, $salt);
                $data['safety_salt'] = $salt;
            }
            $res_Sapwd = M('user')->where(array('userid' => $uid))->save($data);
            if ($res_Sapwd) {
                ajaxReturn('密碼修改成功', 1, '/User/Personal');
            } else {
                ajaxReturn('密碼修改失敗', 0);
            }
        }
        $this->assign('title', $title);
        $this->assign('type', $type);
        $this->display();
    }

    public function News()
    {
        $newinfo = M('news')->where('id <> 1')->order('id desc')->limit(8)->select();
        $this->assign('newinfo', $newinfo);
        $this->display();
    }

    public function Newsdetail()
    {
        $nid = I('nid', 'intval', 0);
        $newdets = M('news')->where(array('id' => $nid))->find();
        $this->assign('newdets', $newdets);
        $this->display();
    }

    //個人二維碼
    public function Sharecode()
    {
        $time = time();
        $userid = session('userid');

//        $u_ID = M('user')->where(array('userid'=>$userid))->getField('mobile');
        $u_ID = $userid;
        $drpath = './Uploads/Scode';
        $imgma = 'codes' . $userid . '.png';
        $urel = './Uploads/Scode/' . $imgma;
       if (!file_exists($drpath . '/' . $imgma)) {
            sp_dir_create($drpath);
            vendor("phpqrcode.phpqrcode");
            $phpqrcode = new \QRcode();
            $hurl ="http://".$_SERVER['SERVER_NAME']. U('Login/register/mobile/' . $u_ID);
            $size = "7";
            //$size = "10.10";
            $errorLevel = "L";
            $phpqrcode->png($hurl, $drpath . '/' . $imgma, $errorLevel, $size);

            
            $phpqrcode->scerweima1($hurl,$urel,$hurl);

         
       }
        $aurl = "http://".$_SERVER['SERVER_NAME']. U('Login/register/mobile/' . $u_ID);

        $sharecodebj = M('sharecodebj')->where(array('id'=>1))->getField('image');
        $this->assign('sharecodebj',$sharecodebj);
        $this->urel = ltrim($urel,".");
        $this->aurl = $aurl;
        $this->display();
    }

    public function Teamdets()
    {
        //查詢我的會員
        $uid = session('userid');
        if (IS_POST) {
            $uinfo = trim(I('uinfo'));
            if (!empty($uinfo) && $uinfo != '') {
                $where['userid|mobile'] = array('like', '%' . $uinfo . '%');
                $this->assign('uinfo',$uinfo);
            }
        }
        $where['pid'] = $uid;
        $muinfo = M('user')->where($where)->order('userid desc')->select();
        $vip_info = M('vip_level')->order('level asc')->select();
        foreach ($muinfo as $k => $v) {
            foreach ($vip_info as $key => $value) {
                if($v['pay_money']>=$value['pay_money']){
                    $muinfo[$k]['vip_grade'] = $value['name'];
                }
            }
            if($v['pay_money']==null){
                $muinfo[$k]['vip_grade']=$vip_info[0]['name'];
            }
        }
        // dump($muinfo);
        // die;
        $this->assign('levelinfo',$vip_info);
        $this->assign('muinfo', $muinfo);
        $this->display();
    }

    /**
     * [Friends 我的好友]
     */
    public function FriendsData()
    {
        $userid = session('userid');
        $where['pid'] = $userid;
        $where['gid'] = $userid;
        $where['ggid'] = $userid;
        $where['_logic'] = 'or';
        if (IS_AJAX) {
            $p = I('p', '0', 'intval');
            $page = $p * 10;
            $u_info = M('user a')->join('ysk_user_huafei b ON a.userid=b.uid')->field('username as u_name,account as u_zh,pid as u_fuji,gid as u_yeji,ggid as u_yyj,pid_caimi,gid_caimi,ggid_caimi,datestr,uid')->where($where)->limit($page, 10)->order('userid')->select();


            if (empty($u_info)) {
                $u_info = null;
            }

            $this->ajaxReturn($u_info);
        }
    }

    //判斷是否自己好友
    private function isfriend($uid, $fid)
    {
        $user = M('user');
        $c_info = $user->where(array('userid' => $fid))->field('pid,gid,ggid')->find();
        $pid = $c_info['pid'];
        $gid = $c_info['gid'];
        $ggid = $c_info['ggid'];

        if ($pid == $uid) { //壹級
            $lv = M('config')->where(array('id' => 24))->getField('value');
            $lv = $lv / 100;
            $data['lever'] = 1;
            $data['lv'] = $lv;
            return $data;
        } elseif ($pid == $gid) { //二級
            $lv = M('config')->where(array('id' => 25))->getField('value');
            $lv = $lv / 100;
            $data['lever'] = 2;
            $data['lv'] = $lv;
            return $data;
        } elseif ($pid == $gid) { //三級
            $lv = M('config')->where(array('id' => 35))->getField('value');
            $lv = $lv / 100;
            $data['lever'] = 3;
            $data['lv'] = $lv;
            return $data;
        } else {
            return false;
        }
    }

    /**
     * 修改密碼
     */
    public function updatepassword()
    {
        if (!IS_AJAX)
            return;

        $password_old = I('post.old_pwdt');
        $password = I('post.new_pwd');
        $passwordr = I('post.rep_pwd');
        $two_password = I('post.new_pwdt');
        $two_passwordr = I('post.rep_pwdt');
        if (empty($password_old)) {
            ajaxReturn('請輸入登錄密碼');
            return;
        }
        if ($password != $passwordr) {
            ajaxReturn('兩次輸入登錄密碼不壹致');
            return;
        }

        if ($two_password != $two_passwordr) {
            ajaxReturn('兩次輸入交易密碼不壹致');
        }

        $user = D('User');
        $user->startTrans();
        //驗證舊密碼
        if (!$user->check_pwd_one($password_old)) {
            ajaxReturn('舊登錄密碼錯誤');
        }

        //=============登錄密碼加密==============
        if ($password) {
            $salt = substr(md5(time()), 0, 3);
            $data['login_salt'] = $salt;
            $data['login_pwd'] = md5(md5(trim($password)) . $salt);
        }

        //=============安全密碼加密==============
        if ($two_password) {
            $two_salt = substr(md5(time()), 0, 3);
            $data['safety_salt'] = $two_salt;
            $data['safety_pwd'] = $two_password = md5(md5(trim($two_passwordr)) . $two_salt);
        }
        if (empty($data)) {
            ajaxReturn("請輸入要修改的密碼");
        }
        $userid = session('userid');
        $where['userid'] = $userid;
        $res = $user->where($where)->save($data);

        if ($res) {
            $user->commit();
            ajaxReturn("修改成功", 1);
        } else {
            $user->rollback();
            ajaxReturn("修改失敗");
        }

    }
    //投訴建議
    public function Complaint()
    {
        $uid = session('userid');
        if (IS_POST) {
            $content = I('post.content');
            $data['content'] = $content;
            $data['user_id'] = $uid;
            $data['create_time'] = time();
            $Complaint = M('complaint');
            $result = $Complaint->add($data);
            if($result){
                ajaxReturn("提交成功", 1,'/User/Personal');
            }else{
                ajaxReturn("提交失敗");
            }
            exit;
        }


        $list = M('complaint')->where(array('user_id'=>$uid))->select();
        $this->assign('list',$list);
        $this->display();
    }

    //關於我們
    public function Aboutus()
    {
        $this->display();
    }

    //退出登錄
    public function Loginout()
    {
        session_destroy();
        $this->redirect('Login/login');
    }
}