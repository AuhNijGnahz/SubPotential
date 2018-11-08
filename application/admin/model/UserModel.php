<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/8/30
 * Time: 16:14
 */

namespace app\admin\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Model;

class UserModel extends Model
{
    protected $table = 'ca_user';

    static function getAlluser()
    {
        try {
            $userlist = UserModel::all();
        } catch (DbException $e) {
        }
        return array('status' => true, 'userObj' => $userlist);
    }

    static function getSingleUser($uid)
    {
        try {
            $user = UserModel::get($uid);
        } catch (DbException $e) {
        }
        if ($user) {
            return array('status' => true, 'userObj' => $user);
        } else {
            return array('status' => false);
        }
    }

    static function deleteSingleUser($uid)
    {
        try {
            $user = UserModel::get($uid);
        } catch (DbException $e) {
        }
        if ($user) {
            $user->delete();
            return array('status' => true);
        } else {
            return array('status' => false);
        }
    }

    static function addUser($email, $username, $password, $groupid, $expiretime, $status, $cash, $credit)
    {
        try {
            $user = UserModel::get(['email' => $email]);
        } catch (DbException $e) {
        }
        if ($user) {
            return array('status' => false, 'message' => '用户已存在');
        } else {
            $user = new UserModel();
            $user->email = $email;
            $user->username = $username;
            $user->password = $password;
            $user->groupid = $groupid;
            $user->avatar = '/static/uploads/avatar/default/default.png';
            $user->expiretime = $expiretime;
            $user->status = $status;
            $user->cash = $cash;
            $user->credit = $credit;
            $user->regdate = date('Y-m-d H:i:s');
            if ($user->save()) {
                return array('status' => true, 'message' => '新增用户成功');
            } else {
                return array('status' => false, 'message' => '系统错误，新增失败');
            }
        }
    }

    static function editUser($email, $username, $password, $groupid, $expiretime, $status, $cash, $credit, $eavatar)
    {
        try {
            $user = UserModel::get(['email' => $email]);
        } catch (DbException $e) {
        }
        if (!$user) {
            //假
            return array('status' => false, 'message' => '要编辑的用户已经不存在了！');
        }
        if (!empty($password)) {
            $user->password = $password;
        }
        $user->username = $username;
        $user->email = $email;
        $user->groupid = $groupid;
        $user->expiretime = $expiretime;
        $user->cash = $cash;
        $user->credit = $credit;
        $user->status = $status;
        if ($eavatar === 'true') {
            $user->avatar = '/static/uploads/avatar/default/default.png';
        }
        if ($user->isUpdate(true)->save()) {
            return array('status' => true, 'message' => '成功更新用户资料！');
        } else {
            return array('status' => false, 'message' => '系统错误，更新用户资料失败！');
        }
    }

    static function userSubList()
    {
        $sub = new UserModel();
        try {
            $s = $sub->table('ca_mysubscribe')->select();
        } catch (DataNotFoundException $e) {
        } catch (ModelNotFoundException $e) {
        } catch (DbException $e) {
        }
        return array('status' => true, 'sublist' => $s);
    }

    static function changeUserSubStatusById($id)
    {
        $status = new UserModel();
        try {
            $s = $status->table('ca_mysubscribe')->where('id', '=', $id)->select();
        } catch (DataNotFoundException $e) {
        } catch (ModelNotFoundException $e) {
        } catch (DbException $e) {
        }
        if ($s[0]->status === '正常') {
            $newstatus = '停用';
        } else {
            $newstatus = '正常';
        }
        if ($status->table('ca_mysubscribe')->where('id', '=', $id)->update(['status' => $newstatus])) {
            return array('status' => true);
        } else {
            return array('status' => false, 'message' => '系统错误，修改失败！');
        }
    }

    static function getAllUserGroup()
    {
        $group = new UserModel();
        $g = $group->table('ca_ugroup')->select();
        return array('status' => true, 'groupObj' => $g);
    }

    static function addUserGroup($name, $color, $discount, $price)
    {
        $group = new UserModel();
        $g = $group->table('ca_ugroup')->where('groupname', '=', $name)->select();
        if ($g) {//存在了，更新
            $g = $group->table('ca_ugroup')->where('groupname', '=', $name)->update([
                'groupname' => $name,
                'groupcolor' => $color,
                'groupdiscount' => $discount,
                'changeusernameprice' => $price
            ]);
        } else {// 不存在 新增
            $g = $group->table('ca_ugroup')->insert([
                'groupname' => $name,
                'groupcolor' => $color,
                'groupdiscount' => $discount,
                'changeusernameprice' => $price
            ]);
        }
        return array('status' => true);
    }

    static function deletGroup($id)
    {
        $group = new UserModel();
        $user = $group->table('ca_user')->where('groupid', '=', $id)->select();
        if ($user) {
            return array('status' => false, 'message' => '该用户下仍有用户，无法删除！');
        } else {
            $g = $group->table('ca_ugroup')->where('id', '=', $id)->delete();
            return array('status' => true);
        }
    }
}

