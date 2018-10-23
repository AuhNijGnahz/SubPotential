<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/8/31
 * Time: 16:56
 */

namespace app\index\controller;

use app\index\model\UserHandle;
use IpLocation\IpLocation;
use think\Controller;
use think\Cookie;
use think\Session;
use app\index\model\IndexHandle;
use Identicon\Identicon;

class BasicControl extends Controller
{
    public function _initialize()
    {
        $uid = Session::get('uid');
        $actionName = strtolower(ACTION_NAME);
        $controllerName = strtolower(CONTROLLER_NAME);
//        var_dump(Cookie::has('ltoken', 'cqp_'));
        if (empty($uid)) {
//            var_dump(Cookie::has('email','cqp_'));
            if (Cookie::has('ltoken', 'cqp_')) {
                $logintoken = encryption(Cookie::get('ltoken', 'cqp_'), 'D');
                $logintoken = explode('|', $logintoken);
                $email = $logintoken[0];
                $password = $logintoken[1];
                $ip = $logintoken[2];
                $myip = getIp();
                $location = $logintoken[3];
                $expiretime = $logintoken[4];

//                var_dump($email);
//                return;
                if ($expiretime - time() <= 0) {
                    Cookie::clear('cqp_');
                    $this->redirect('/index/index/login');
                    exit();
                }
                if ($ip != $myip) {
                    Cookie::clear('cqp_');
                    $this->redirect('/index/index/login');
                    exit();
                }
                $loginRt = UserHandle::login($email, $password, $ip, $location);
                if ($loginRt['status']) {
                    $user = $loginRt['userObj'];
                    $group = UserHandle::getGroupByid($user->groupid);
                    Session::set('username', $user->username);
                    Session::set('uid', $user->uid);
                    Session::set('email', $email);
                    $activeStatus = UserHandle::activeStatus($user->uid);
                    $user->emailactive = $activeStatus['email'];
                    $user->phoneactive = $activeStatus['phone'];
                    $this->assign([
                        'me' => $user,
                        'group' => $group['groupObj'][0],
                        'domain' => $_SERVER['HTTP_HOST'],
                        'avatar' => $this->getAvatar($user->uid),
//                    'username' => $username,
//                    'cash' => number_format($userinfo['userObj']->cash, 2),
//                    'credit' => $userinfo['userObj']->credit,
//                    'uid' => $userinfo['userObj']->uid,
//                    'email'=>$userinfo['userObj']->email
                    ]);
                } else {
                    Cookie::clear('cqp_');
                    $this->redirect('/index/index/login');
                    exit();
                }
            } else {
//                var_dump($actionName);
                switch ($controllerName) {
                    case 'index':
                        if (!in_array($actionName, array("register", 'login', 'getcaptcha', 'checkcaptcha'))) {
                            $this->redirect('/index/index/login');
                            exit();
                        }
                        break;
                    case 'user':
                        if (!in_array($actionName, array("reg", 'login'))) {
                            $this->redirect('/index/index/login');
                            exit();
                        }
                        break;
                    case 'callback':
                        if (!in_array($actionName, array("youzanpay"))) {
                            $this->redirect('/index/index/login');
                            exit();
                        }
                        break;
                    default:
                        $this->redirect('/index/index/login');
                        exit();
                }
            }
        } else {
            $user = new User();
            $userinfo = $user->getuserinfo($uid);
            $group = $user->getGroupInfo($userinfo['userObj']->groupid);
            $activeStatus = UserHandle::activeStatus($uid);
            $userinfo['userObj']->emailactive = $activeStatus['email'];
            $userinfo['userObj']->phoneactive = $activeStatus['phone'];
            if ($userinfo['status']) {
                $this->assign([
                    'me' => $userinfo['userObj'],
                    'group' => $group['groupObj'],
                    'domain' => $_SERVER['HTTP_HOST'],
                    'avatar' => $this->getAvatar($uid),
//                    'username' => $username,
//                    'cash' => number_format($userinfo['userObj']->cash, 2),
//                    'credit' => $userinfo['userObj']->credit,
//                    'uid' => $userinfo['userObj']->uid,
//                    'email'=>$userinfo['userObj']->email
                ]);
            }
        }
        $web = new IndexHandle();
        $webObj = $web::getWebSettings();
        $anObj = $web::getAnnounce();
        if ($webObj['status']) {
            $this->assign([
                'title' => $webObj['webObj']->title,
                'subtitle' => $webObj['webObj']->subtitle,
                'seo' => $webObj['webObj']->seo,
                'describe' => $webObj['webObj']->sitedesc,
                'qq' => $webObj['webObj']->qq,
                'anlist' => $anObj['anObj'],
            ]);

        }
    }

    public function getAvatar($uid)
    { //返回头像链接
        $identicon = new Identicon();
        $url = $identicon->getImageDataUri($uid, 100);
        return $url;
    }

}