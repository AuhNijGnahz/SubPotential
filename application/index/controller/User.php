<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/8/29
 * Time: 13:20
 */

namespace app\index\controller;

use app\index\model\UserHandle;
use app\index\controller\BasicControl;
use app\index\model\ActiveHandle;
use app\index\model\IndexHandle;
use app\index\model\Mail;
use think\Controller;
use IpLocation\IpLocation;
use think\Request;
use think\Session;
use think\Cookie;

class User extends Controller
{
    /**
     * @param $ip
     * @return mixed
     */
    public function getLocation($ip)
    {
        $ipp = new IpLocation('qqwry.dat');
        $location = $ipp->getlocation($ip);
        $info = iconv('gbk', 'utf-8', $location['country'] . $location['area']);
        return $info;


//        $urlTaobao = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip;
//        $urlSina = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=' . $ip;
//        $json = file_get_contents($urlTaobao);
//        $jsonDecode = json_decode($json);
//        if ($jsonDecode->code == 0) {//如果取不到就去取新浪的
//            $data['country'] = $jsonDecode->data->country;
//            $data['province'] = $jsonDecode->data->region;
//            $data['city'] = $jsonDecode->data->city;
//            $data['isp'] = $jsonDecode->data->isp;
//            return $data;
//        } else {
//            $json = file_get_contents($urlSina);
//            $jsonDecode = json_decode($json);
//            $data['country'] = $jsonDecode->country;
//            $data['province'] = $jsonDecode->province;
//            $data['city'] = $jsonDecode->city;
//            $data['isp'] = $jsonDecode->isp;
//            $data['district'] = $jsonDecode->district;
//            return $data;
//        }
    }

    /**
     * @param Request $request
     * @return string
     */
    public function reg(Request $request)
    { //注册账户
        $term = $request->post('term');
        $ip = $request->ip();
        $challenge = $request->post('geetest_challenge');
        $validate = $request->post('geetest_validate');
        $seccode = $request->post('geetest_seccode');
        if (empty($challenge) || empty($validate) || empty($seccode)) {
            return json_encode(array('status' => false, 'message' => '人机验证失败！'));
        }
        $data = array(
            'geetest_challenge' => $challenge,
            'geetest_validate' => $validate,
            'geetest_seccode' => $seccode,
            'ip' => $ip
        );
        if ($term != 'true') {
            return json_encode(array('status' => false, 'message' => '您必须同意用户协定才能注册！'));
        }
        $captcha = new Index();
        $captcha->checkCaptcha($data);
        if (!$captcha) {
            return json_encode(array('status' => false, 'message' => '人机验证失败！'));
        }
        $username = $request->post('username');
        if (!preg_match('/^[a-zA-Z0-9_-]{4,16}$/', $username)) {
            return json_encode(array('status' => false, 'message' => '用户名格式不正确，只能包含字母和数字！'));
        }
        $password = $request->post('password');
        if (!preg_match('/^(?![A-Za-z]+$)(?![A-Z\\d]+$)(?![A-Z\\W]+$)(?![a-z\\d]+$)(?![a-z\\W]+$)(?![\\d\\W]+$)\\S{8,20}$/', $password)) {
            return json_encode(array('status' => false, 'message' => '密码格式不正确，必须包含大写字母，小写字母，数字，特殊字符其中3种！'));
        }
        $password = md5($password . config('sault'));
        $email = $request->post('email');
        if (!preg_match('/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z0-9]{2,6}$/', $email)) {
            return json_encode(array('status' => false, 'message' => '邮箱格式不正确'));
        }
        if (!empty($username) && !empty($password) && !empty($email)) {
            $regreturn = UserHandle::reg($username, $password, $email);
            if ($regreturn['status']) {
                return json_encode(array('status' => true, 'message' => '注册成功，页面将自动跳转！'));
            } else {
                return json_encode(array('status' => false, 'message' => $regreturn['message']));
            }
        } else {
            return json_encode(array('status' => false, 'message' => '信息填写不完整，注册失败！'));
        }
    }

    public function login(Request $request)
    {
        $email = $request->post('email');
        $password = md5($request->post('password') . config('sault'));
        $ip = $request->ip();
        $remember = $request->post('remember');
        $location = $this->getLocation($ip);
        $challenge = $request->post('geetest_challenge');
        $validate = $request->post('geetest_validate');
        $seccode = $request->post('geetest_seccode');
        if (empty($challenge) || empty($validate) || empty($seccode)) {
            return json_encode(array('status' => false, 'message' => '人机验证失败！'));
        }
        $data = array(
            'geetest_challenge' => $challenge,
            'geetest_validate' => $validate,
            'geetest_seccode' => $seccode,
            'ip' => $ip
        );
        $captcha = new Index();
        $captcha->checkCaptcha($data);
        if (!$captcha) {
            return json_encode(array('status' => false, 'message' => '人机验证失败！'));
        }
//        $location = $location['country'] . " " . $location['province'] . " " . $location['city'];
        if (!empty($password) && !empty($email)) {
            $loginreturn = UserHandle::login($email, $password, $ip, $location);
            if ($loginreturn['status']) {
                $user = $loginreturn['userObj'];
                Session::set('username', $user->username);
                Session::set('uid', $user->uid);
                Session::set('email', $email);
                if ($remember === 'true') {
                    $week = 7 * 24 * 60 * 60;
                    $expiretime = time() + $week;
                    $logintoken = $email . '|' . $password . '|' . $ip . '|' . $location . '|' . $expiretime;
                    $logintoken = encryption($logintoken, 'E');
                    Cookie::set('ltoken', $logintoken, ['prefix' => 'cqp_', 'expire' => $week]);
                }
                //$this->success('登录成功！', '/index');
                return json_encode(array('status' => true, 'message' => $user->username . '，欢迎您回来，页面将自动跳转！'));
            } else {
                return json_encode(array('status' => false, 'message' => '账号或密码错误'));
            }
        }
    }

    public function recovery(Request $request)
    {
        // 目前问题:可以重复使用链接！！！！！
        $email = $request->post('email');
        $code = $request->get('code');
        if (!empty($code)) {
            // 收到验证代码，进行验证找回密码操作
            $url = $request->root();
            $code = $request->get('code');
            $uid = $request->get('uid');
            $message = '<div style="margin: -15px; padding: 8vh 0 2vh;color: #a6aeb3; background-color: #f7f9fa; text-align: center; font-family:NotoSansHans-Regular,\'Microsoft YaHei\',Arial,sans-serif; -webkit-font-smoothing: antialiased;"><div style="width: 750px; max-width: 85%; margin: 0 auto; background-color: #fff; -webkit-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);-moz-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);"><div style="text-align: center; padding: 50px 10%; font-size: 16px; line-height: 16px;"><br></div><h1 style="margin:32px auto; max-width: 95%; color: #0e2026;">{status}！</h1><p style="width: 750px; max-width: 90%; margin: 32px auto;padding: 0;">{message}</p><p style="width: 750px; max-width: 90%; margin: 32px auto;padding: 0;"><br></p><p style="width: 750px; max-width: 90%; margin: 32px auto;padding: 0;"><br></p><p style="width: 750px; max-width: 90%; margin: 32px auto;padding: 0;"><br></p></div></div>';
            if (UserHandle::createRecoverCode($uid, true) === $code) {
                $a = UserHandle::recovery($uid, $code);
                if ($a['status']) {
                    $message = str_replace('{status}', '<h1 style="margin:32px auto; max-width: 95%; color: #0e2026;"><img src="' . $url . '/static/assets/images/ok.png" style="width: 15%;"></h1>重置密码成功', $message);
                    $message = str_replace('{message}', '重设密码成功，您的新密码为：' . '<br/><h3>' . $a['newpwd'] . '</h3><br/>请您尽快修改密码！', $message);
                    echo $message;
                    exit();
                } else {
                    $message = str_replace('{status}', '<h1 style="margin:32px auto; max-width: 95%; color: #0e2026;"><img src="' . $url . '/static/assets/images/wrong.png" style="width: 15%;"></h1>重置密码失败', $message);
                    $message = str_replace('{message}', $a['message'], $message);
                    echo $message;
                    exit();
                }
            } else {
                $message = str_replace('{status}', '<h1 style="margin:32px auto; max-width: 95%; color: #0e2026;"><img src="' . $url . '/static/assets/images/wrong.png" style="width: 15%;"></h1>重置密码失败', $message);
                $message = str_replace('{message}', '重置密码链接篡改，请重新获取！', $message);
                echo $message;
                exit();
            }
        }
        if (!preg_match('/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z0-9]{2,6}$/', $email)) {
            return json_encode(array('status' => false, 'message' => '邮箱格式不正确'));
        }
        $user = UserHandle::getuserinfo(0, $email);
        if (!$user['status']) {
            return json_encode(array('status' => false, 'message' => '用户不存在！'));
        }
        $uid = $user['userObj']->uid;
        $status = UserHandle::activeStatus($uid);
        if (!$status['email']) {
            return json_encode(array('status' => false, 'message' => '邮箱尚未激活，无法用于找回密码！'));
        }
        $web = IndexHandle::getWebSettings();
        $url = $web['webObj']->url;
        $webtitle = $web['webObj']->title;
        $code = UserHandle::createRecoverCode($uid);
        if (!$code) {
            return json_encode(array('status' => false, 'message' => '验证邮件发送太频繁，请等待5分钟后重试！'));
        }
        $activelink = $url . '/index/User/recovery?&uid=' . $uid . '&code=' . $code;
        $templet = ActiveHandle::getTemplet('recovery');
        $t = str_replace('{webtitle}', $webtitle, $templet);
        $t = str_replace('{url}', $url, $t);
        $t = str_replace('{activelink}', $activelink, $t);
//        var_dump($t);
        $send = Mail::sendEmail($email, $user['userObj']->username, '找回密码 - ' . $webtitle, $t);
        if ($send['status']) {
            return json_encode(array('status' => true, 'message' => '发送邮件成功！'));
        } else {
            return json_encode(array('status' => false, 'message' => $send['message']));
        }
    }

    public function getuserinfo($uid)
    {
        if ($uid != "") {
            $userreturn = UserHandle::getuserinfo($uid);
            if ($userreturn['status']) {
                return array('status' => true, 'userObj' => $userreturn['userObj']);
            } else {
                self::logout();
            }
        }
    }

    public function getGroupInfo($id)
    {
        $g = UserHandle::getGroupByid($id);
        return array('status' => true, 'groupObj' => $g['groupObj'][0]);
    }

    public function logout()
    {
//        Session::clear();
//        Session::destroy();
        Session::delete('uid');
        Cookie::clear('cqp_');
        $this->redirect('/index');
    }

    public function changePwd(Request $request)
    {
        $uid = Session::get('uid');
        $oldpwd = $request->post('oldpwd');
        $newpwd = $request->post('newpwd');
        $confirm = $request->post('confirm');
        if (!empty($oldpwd) && !empty($newpwd) && !empty($confirm)) {
            if ($newpwd != $confirm) {
                return json_encode(array('status' => false, 'message' => '两次输入的密码不一致！'));
            }
            if (!preg_match('/^(?![A-Za-z]+$)(?![A-Z\\d]+$)(?![A-Z\\W]+$)(?![a-z\\d]+$)(?![a-z\\W]+$)(?![\\d\\W]+$)\\S{8,20}$/', $newpwd)) {
                return json_encode(array('status' => false, 'message' => '新密码格式不正确，必须包含大写字母，小写字母，数字，特殊字符其中3种！'));
            }
            $oldpwd = md5($oldpwd . config('sault'));
            $newpwd = md5($newpwd . config('sault'));
            $cRt = UserHandle::changePwd($uid, $oldpwd, $newpwd);
            if ($cRt['status']) {
                Session::delete('uid');
                return json_encode(array('status' => true));
            } else {
                return json_encode(array('status' => false, 'message' => $cRt['message']));
            }
        } else {
            return json_encode(array('status' => false, 'message' => '信息填写不完整！'));
        }
    }

    public function changeUserName(Request $request)
    {
        $newusername = $request->post('username');
        $uid = Session::get('uid');
        $user = UserHandle::getuserinfo($uid);
        $group = UserHandle::getGroupByid($user['userObj']->groupid);
        $price = $group['groupObj'][0]->changeusernameprice;
        if (!preg_match('/^[a-zA-Z0-9_-]{4,16}$/', $newusername)) {
            return json_encode(array('status' => false, 'message' => '用户名格式不正确！'));
        } elseif ($user['userObj']->cash < $price) {
            return json_encode(array('status' => false, 'message' => '您的余额不足！'));
        } elseif ($newusername == $user['userObj']->username) {
            return json_encode(array('status' => false, 'message' => '用户名未变动！'));
        } else {
            $cRt = UserHandle::changeUserName($uid, $newusername, $price);
            if ($cRt['status']) {
                return json_encode(array('status' => true));
            } else {
                return json_encode(array('status' => false, 'message' => $cRt['message']));
            }
        }
    }

    public function changeAvatar(Request $request)
    {
        $uid = Session::get('uid');
        $file = $request->file('avatar');
        $filePath = 'avatar/uavatar';
        $width = 150;
        $height = 150;
        if ($file) {
            $filePaths = ROOT_PATH . 'public' . DS . 'static' . DS . 'uploads' . DS . $filePath;
            if (!file_exists($filePaths)) {
                mkdir($filePaths, 0777, true);
            }
            $info = $file->validate(['size' => 1048576, 'ext' => 'jpg,png,gif'])->rule('sha1')->move($filePaths);
            if ($info) {
                $imgpath = $filePaths . '/' . $info->getSaveName();
                $image = \think\Image::open($imgpath);
                $image->thumb($width, $height)->save($imgpath);
                $imgpath = '/static/uploads/' . $filePath . '/' . $info->getSaveName();
                $avatarRt = UserHandle::changeAvatar($uid, $imgpath);
                if ($avatarRt['status']) {
                    return json_encode(array('status' => true));
                } else {
                    return json_encode(array('status' => false, 'message' => $avatarRt['message']));
                }
            } else {
                // 上传失败获取错误信息
                return json_encode(array('status' => false, 'message' => '上传失败！' . $file->getError()));
            }
        }
    }

    public function deleteMyAcc(Request $request)
    {
        $uid = Session::get('uid');
        $password = md5($request->post('password') . config('sault'));
        $user = UserHandle::getuserinfo($uid);
        if ($user['userObj']->password === $password) {
            //密码正确 可以删除
            $dRt = UserHandle::deleteMyAcc($uid);
            if ($dRt['status']) {
                return json_encode(array('status' => true));
            } else {
                return json_encode(array('status' => false));
            }
        } else {
            return json_encode(array('status' => false));
        }
    }
}