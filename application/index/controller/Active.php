<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/10/10
 * Time: 17:32
 */

namespace app\index\controller;

use app\index\model\IndexHandle;
use app\index\model\UserHandle;
use think\Request;
use app\index\model\Mail;
use app\index\model\ActiveHandle;
use think\Session;

class Active extends BasicControl
{
    public function verifyEmail()
    {
        $uid = Session::get('uid');
        $status = UserHandle::activeStatus($uid);
        if ($status['email']) {
            return json_encode(array('status' => false, 'message' => '邮箱已经激活过！'));
        }
        $username = Session::get('username');
        $email = Session::get('email');
        $web = IndexHandle::getWebSettings();
        $url = $web['webObj']->url;
        $webtitle = $web['webObj']->title;
        $code = ActiveHandle::createActiveCode($uid, $email);
        if (!$code) {
            return json_encode(array('status' => false, 'message' => '验证邮件发送太频繁，请等待5分钟后重试！'));
        }
        $activelink = $url . '/index/Active/activeEmail?code=' . $code;
        $templet = ActiveHandle::getTemplet('verifyemail');
        $t = str_replace('{webtitle}', $webtitle, $templet);
        $t = str_replace('{url}', $url, $t);
        $t = str_replace('{activelink}', $activelink, $t);
//        var_dump($t);
        $send = Mail::sendEmail($email, $username, '邮箱验证 - ' . $webtitle, $t);
        if ($send['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => false, 'message' => $send['message']));
        }
    }

    public function activeEmail(Request $request)
    {
        $url = $request->root();
        $uid = Session::get('uid');
        $code = $request->get('code');
        $email = Session::get('email');
        $message = '<div style="margin: -15px; padding: 8vh 0 2vh;color: #a6aeb3; background-color: #f7f9fa; text-align: center; font-family:NotoSansHans-Regular,\'Microsoft YaHei\',Arial,sans-serif; -webkit-font-smoothing: antialiased;"><div style="width: 750px; max-width: 85%; margin: 0 auto; background-color: #fff; -webkit-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);-moz-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);"><div style="text-align: center; padding: 50px 10%; font-size: 16px; line-height: 16px;"><br></div><h1 style="margin:32px auto; max-width: 95%; color: #0e2026;">{status}！</h1><p style="width: 750px; max-width: 90%; margin: 32px auto;padding: 0;">{message}</p><p style="width: 750px; max-width: 90%; margin: 32px auto;padding: 0;"><br></p><p style="width: 750px; max-width: 90%; margin: 32px auto;padding: 0;"><br></p><p style="width: 750px; max-width: 90%; margin: 32px auto;padding: 0;"><br></p></div></div>';
        if (ActiveHandle::createActiveCode($uid, $email, true) === $code) {
            $a = ActiveHandle::activeEmail($uid);
            if ($a['status']) {
                $message = str_replace('{status}', '<h1 style="margin:32px auto; max-width: 95%; color: #0e2026;"><img src="' . $url . '/static/assets/images/ok.png" style="width: 15%;"></h1>激活成功', $message);
                $message = str_replace('{message}', '您的邮箱已经成功激活，您现在可以继续您的操作了！', $message);
                echo $message;
                exit();
            } else {
                $message = str_replace('{status}', '<h1 style="margin:32px auto; max-width: 95%; color: #0e2026;"><img src="' . $url . '/static/assets/images/wrong.png" style="width: 15%;"></h1>激活失败', $message);
                $message = str_replace('{message}', $a['message'], $message);
                echo $message;
                exit();
            }
        } else {
            $message = str_replace('{status}', '<h1 style="margin:32px auto; max-width: 95%; color: #0e2026;"><img src="' . $url . '/static/assets/images/wrong.png" style="width: 15%;"></h1>激活失败', $message);
            $message = str_replace('{message}', '激活链接已被篡改，请重新获取激活链接！', $message);
            echo $message;
            exit();
        }
    }

}