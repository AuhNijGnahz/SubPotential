<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/8/31
 * Time: 15:47
 */

namespace app\admin\controller;

use app\admin\model\SubModel;
use app\admin\model\TicketModel;
use app\admin\model\UserModel;
use think\Controller;
use app\admin\model\AdminModel;
use think\Session;
use think\Request;
use app\admin\model\Mail;

class AdminControl extends Controller
{
    public function login(Request $request)
    {
        $username = $request->post('username');
        $pwd = $request->post('password');
        $loginRt = AdminModel::adminLogin($username, $pwd);
        if ($loginRt['status']) {
            Session::set('adminUser', $username);
            $this->success('尊敬的管理员，欢迎您回来！', '/admin/index');
        } else {
            $this->error('账号或密码错误！');
        }
    }

    public function logOut()
    {
        Session::clear();
        Session::destroy();
        $this->success('成功退出登录！', '/admin/index');
    }
}