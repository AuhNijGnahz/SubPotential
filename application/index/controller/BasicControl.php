<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/8/31
 * Time: 16:56
 */

namespace app\index\controller;

use app\admin\model\UserModel;
use app\admin\model\WebModel;
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
        // 如果获取不到UID则表示没有登录
        if (empty($uid)) {
            // 是否有Cookies 如果有则用Cookies登录
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
                // 通过Cookies登录
                $loginRt = UserHandle::login($email, $password, $ip, $location);
                if ($loginRt['status']) {
                    $user = $loginRt['userObj'];
                    if ($user->status === 1) {
                        //封禁
                        Cookie::clear('cqp_');
                        $this->redirect('/index/index/login');
                        exit();
                    }
                    $group = UserHandle::getGroupByid($user->groupid);
                    Session::set('username', $user->username);
                    Session::set('uid', $user->uid);
                    Session::set('email', $email);
                    $activeStatus = UserHandle::activeStatus($user->uid);
                    $user->emailactive = $activeStatus['email'];
                    $user->phoneactive = $activeStatus['phone'];
                    $this->checkGroupExpire($user, $user->expiretime);
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
            //获取到UID，表示已登录
        } else {
            $userObj = new User();
            $user = $userObj->getuserinfo($uid)['userObj'];
            if ($user->status === 1) {
                //封禁
                (new User())->logout();
            }
            $group = $userObj->getGroupInfo($user->groupid);
            $activeStatus = UserHandle::activeStatus($uid);
            $user->emailactive = $activeStatus['email'];
            $user->phoneactive = $activeStatus['phone'];
            $this->checkGroupExpire($user, $user->expiretime);
            $this->assign([
                'me' => $user,
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

    public function checkGroupExpire($user, $expiretime)
    {
        $expire = checkExpire($expiretime);
        $default = WebModel::getRegSettings();
        $default = $default['webObj'][0]->groupid;
        if ($expire) {
            //已经过期
            $result = UserModel::editUser($user->email, $user->username, $user->password, $default, '3099-12-31 23:59:59', $user->status, $user->cash, $user->credit, $user->avatar);
            return;
        } else {
            //没过期
            return;
        }
    }

}