<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/9/8
 * Time: 23:31
 */

namespace app\index\model;

use think\db\exception\BindParamException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\Model;

class SubHandle extends Model
{
    static function getSubList()
    {
        $sub = new SubHandle();
        $sql = "SELECT *,MAX(price) as maxprice, MIN(price) as minprice from ca_sublist INNER join ca_price on ca_sublist.sid = ca_price.sid where ca_sublist.status = '启用' group by ca_sublist.sid";

        $sub = $sub->query($sql);
        return array('status' => true, 'subObj' => $sub);
    }

    static function getSubPrice($sid)
    {


        $price = (new SubHandle)->table('ca_price')->where('sid', '=', $sid)->order('time', 'asc')->select();
        return array('status' => true, 'priceObj' => $price, 'renewprice');
    }

    static function getSubInfo($sid)
    {

        $sub = (new SubHandle)->table('ca_sublist')->where('sid', '=', $sid)->select();

//        var_dump($sub);
        if ($sub) {
            return array('status' => true, 'subObj' => $sub);
        } else {
            return array('status' => false);
        }
    }

    static function getOrderInfo($sid)
    {
        $sub = self::getSubInfo($sid);
        $subprice = self::getSubPrice($sid);
        if ($sub['status'] && $subprice['status']) {
            return array('status' => true, 'subObj' => $sub['subObj'], 'priceObj' => $subprice['priceObj']);
        } else {
            return array('status' => false);
        }
    }

    static function getMySubList($uid)
    {
        $mysub = new SubHandle();

        $ms = $mysub->table('ca_mysubscribe')->where('uid', '=', $uid)->order('expiretime', 'desc')->paginate(10);
        return array('status' => true, 'mysub' => $ms);
    }

    static function mySubExist($id, $sid)
    { // 查看我的某个订阅是否存在并且没到期
        $mysub = new SubHandle();

        $ms = $mysub->table('ca_mysubscribe')->where('id', '=', $id)->select();
        $sub = self::getSubInfo($sid);

        if ($ms && $ms[0]->sid == $sid) { //这里不能用 === 全等因为数据库里SID是整数而传过来的值是字符串
            return array('status' => true, 'sub' => $sub, 'mysub' => $ms);
        } else {
            return array('status' => false);
        }
    }

    static function bindGroup($id, $groupnum)
    {
        $mysub = new SubHandle();
        $s = $mysub->table('ca_mysubscribe')->where('id', '=', $id)->select();
        $v = $mysub->table('ca_mysubscribe')->where('groupnum', '=', $groupnum)->select();
        if ($v) {
            return array('status' => false, 'message' => '该群号码已经被绑定！');
        }
//        var_dump($s);
        if ($s[0]->groupnum === 0) {
            $s = $mysub->table('ca_mysubscribe')->where('id', '=', $id)->update([
                'groupnum' => $groupnum
            ]);
//            var_dump($groupnum);
            if ($s) {
                return array('status' => true);
            } else {
                return array('status' => false, 'message' => '绑定群号码失败！');
            }
        } else {
            return array('status' => false, 'message' => '群号码已绑定过！');
        }
    }
}