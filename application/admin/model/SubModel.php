<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/9/4
 * Time: 15:52
 */

namespace app\admin\model;

use app\index\model\SubHandle;
use think\exception\DbException;
use think\Model;

class SubModel extends Model
{
    protected $table = 'ca_sublist';

    static function subList()
    {
        try {
            $sub = SubModel::all();
        } catch (DbException $e) {
        }
        return array('status' => true, 'subObj' => $sub);
    }

    static function changeStatus($sid)
    {
        try {
            $sub = SubModel::get($sid);
        } catch (DbException $e) {
        }
        if (!$sub) {
            return array('status' => false, 'message' => '订阅产品不存在');
        } else if ($sub->status === '启用') {
            $sub->status = '停用';
        } else {
            $sub->status = '启用';
        }
        if ($sub->save()) {
            return array('status' => true);
        } else {
            return array('status' => false, 'message' => '系统错误，修改失败');
        }
    }

    static function dSingleSub($sid)
    {
        try {
            $sub = SubModel::get($sid);
        } catch (DbException $e) {
        }
        if (!$sub) {
            return array('status' => false, 'message' => '订阅产品不存在');
        } else if ($sub->delete()) {
            return array('status' => true);
        } else {
            return array('status' => false, 'message' => '系统错误，删除失败');
        }
    }

    static function addSub($sid, $name, $des, $skey, $status, $bindgroup, $discount)
    {
        try {
            $sub = SubModel::get($sid);
        } catch (DbException $e) {
        }
        if ($sub) {
            return array('status' => false, 'message' => '订阅产品ID已存在');
        } else {
            $sub = new SubModel();
            $sub->sid = $sid;
            $sub->sname = $name;
            $sub->des = $des;
            $sub->skey = $skey;
            $sub->status = $status;
//            $sub->mode = $mode;
            $sub->bindgroup = $bindgroup;
            $sub->renewdiscount = $discount / 100;
            $sub->adddate = date('Y-m-d H:i:s');
            if ($sub->save()) {
                return array('status' => true);
            } else {
                return array('status' => false, 'message' => '系统错误，添加失败！');
            }
        }
    }

    static function getSidList()
    {
        try {
            $sub = SubModel::all((new SubModel)->field('sid'));
        } catch (DbException $e) {
        }
        return array('status' => true, 'subObj' => $sub);

    }

    static function getSingleSub($sid)
    {
        try {
            $sub = SubModel::get($sid);
        } catch (DbException $e) {
        }
        if ($sub) {
            return array('status' => true, 'subObj' => $sub);
        } else {
            return array('status' => false, 'message' => '系统错误，获取失败！');
        }
    }

    static function editSub($skey, $sname, $status, $bind, $discount, $des)
    {
        try {
            $sub = SubModel::get(['skey' => $skey]);
        } catch (DbException $e) {
        }
        if (!$sub) {
            return array('status' => false, 'message' => '该产品已经不存在了');
        } else {
            $sub->sname = $sname;
            $sub->skey = $skey;
            $sub->status = $status;
            $sub->bindgroup = $bind;
            $sub->renewdiscount = $discount / 100;
            $sub->des = $des;
            if ($sub->isUpdate(true)->save()) {
                return array('status' => true);
            } else {
                return array('status' => false, 'message' => '系统错误，修改失败！');
            }
        }
    }
}