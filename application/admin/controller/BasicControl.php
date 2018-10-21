<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/8/31
 * Time: 15:50
 */

namespace app\admin\controller;

use think\Controller;
use think\Session;


class BasicControl extends Controller
{
    public function _initialize()
    {
        $adminUser = Session::get('adminUser');
        $actionName = strtolower(ACTION_NAME);
        $controllerName = strtolower(CONTROLLER_NAME);
        if ($adminUser === null || $adminUser === "") {
            switch ($controllerName) {
                case 'index':
                    if (!in_array($actionName, array("login", 'adminlogin', 'logout'))) {
                        $this->error('您尚未登录，请先登录！', '/admin/index/adminLogin');
                        exit;
                    }
                    break;
                default:
                    $this->error('您尚未登录，请先登录！', '/admin/index/adminLogin');
                    exit;
            }
        }
        $this->assign([
            'adminUser' => $adminUser,
        ]);
    }

}