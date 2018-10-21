<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/8/30
 * Time: 18:15
 */

namespace app\admin\controller;

use app\admin\model\SubModel;
use app\admin\model\UserModel;
use app\index\model\SubHandle;
use think\Request;

class UserControl extends BasicControl
{
    public function userList()
    { //首页需要先获取用户列表
        $getuser = UserModel::getAlluser();
        //var_dump($getuser);
        if ($getuser['status']) {
            $userlist = $getuser['userObj'];
            $this->assign([
                'userlist' => $userlist,
                'usercount' => count($userlist)
            ]);
        }
        return $this->fetch('userlist');
    }

    public function dSingleUser(Request $request)
    { //删除单个用户
        $uid = $request->post('uid');
        if ($uid === "") {
            return json_encode(array('status' => false));
        } else if (UserModel::deleteSingleUser($uid)['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => false));
        }
    }

    public function addUser(Request $request)
    {
        $username = $request->post('username');
        $email = $request->post('email');
        $password = md5($request->post('password') . config('sault'));
        $status = $request->post('status');
        $cash = $request->post('cash');
        $groupid = $request->post('groupid');
        $expireday = $request->post('expiretime');
        if (!empty($expireday)) {
            $expiretime = date('Y-m-d H:i:s', time() + $expireday * 3600 * 24);
        } else {
            $expiretime = '3099-12-31 23:59:59';
        }
        $credit = $request->post('credit');
        $addreturn = UserModel::addUser($email, $username, $password, $groupid, $expiretime, $status, $cash, $credit);
        if ($addreturn['status']) {
            $this->success('新增用户成功！');
        } else {
            $this->error($addreturn['message']);
        }
    }

    public function addUserIndex()
    {
        $group = UserModel::getAllUserGroup();
        $this->assign([
            'group' => $group['groupObj']
        ]);
        return $this->fetch('adduser');
    }

    public function userGroupIndex()
    {
        $g = UserModel::getAllUserGroup();
        $this->assign([
            'group' => $g['groupObj']
        ]);
        return $this->fetch('ugroup');
    }

    public function controlUserGroup(Request $request)
    {
        $name = $request->post('name');
        $color = $request->post('color');
        $discount = $request->post('discount');
        $price = $request->post('price');
        $gRt = UserModel::addUserGroup($name, $color, $discount, $price);
        $this->success('操作成功完成');
    }

    public function deleteUserGroup(Request $request)
    {
        $id = $request->post('id');
        $dRt = UserModel::deletGroup($id);
        if ($dRt['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => false, 'message' => $dRt['message']));
        }
    }

    public function editUserIndex(Request $request)
    {
        $uid = $request->get('uid');
        $user = UserModel::getSingleUser($uid);
        $group = UserModel::getAllUserGroup();
        if ($user['status']) {
            $this->assign([
                'user' => $user['userObj'],
                'group' => $group['groupObj']
            ]);
            return $this->fetch('edituser');
        } else {
            $this->error('要编辑的用户已经不存在了！', '/admin/UserControl/editUserIndex');
        }
    }

    public function editUser(Request $request)
    {
        $username = $request->post('username');
        $email = $request->post('email');
        $password = $request->post('password');
        if (!empty($password)) {
            $password = md5($password . config('sault'));
        } else {
            $password = "";
        }
        $groupid = $request->post('groupid');
        $expireday = $request->post('expiretime');
        if (!empty($expireday)) {
            $expiretime = date('Y-m-d H:i:s', time() + $expireday * 3600 * 24);
        } else {
            $expiretime = '3099-12-31 23:59:59';
        }
        $status = $request->post('status');
        $eavatar = $request->post('eavatar');
        $cash = $request->post('cash');
        $credit = $request->post('credit');
        $editReturn = UserModel::editUser($email, $username, $password, $groupid, $expiretime, $status, $cash, $credit, $eavatar);
        if ($editReturn['status']) {
            $this->success($editReturn['message']);
        } else {
            $this->error($editReturn['message']);
        }
    }

    public function userSubList()
    {
        $s = UserModel::userSubList();
        $this->assign([
            'sublist' => $s['sublist']
        ]);
        return $this->fetch('usersub');
    }

    public function changeUserSubStatus(Request $request)
    {
        $id = $request->post('id');
        $changeRt = UserModel::changeUserSubStatusById($id);
        if ($changeRt['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => false, 'message' => $changeRt['message']));
        }
    }
}