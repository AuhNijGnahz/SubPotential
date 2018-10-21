<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/9/11
 * Time: 12:24
 */

namespace app\index\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Model;

class OrderHandle extends Model
{
    static function getPrice($priceid)
    {
        try {
            $price = new OrderHandle();
            $price = $price->table('ca_price')->where('id', '=', $priceid)->select();
        } catch (DataNotFoundException $e) {
        } catch (ModelNotFoundException $e) {
        } catch (DbException $e) {
        }
        if ($price) {
            return array('status' => true, 'price' => $price);
        } else {
            return array('status' => false, 'message' => '系统错误，获取价格失败!');
        }
    }

    static function checkCoupon($coupon, $sid)
    {
        $cp = new OrderHandle();
        try {
            // 先查询优惠码是不是全部产品都可用
            $cpp = $cp->table('ca_coupon')->where('coupon', '=', $coupon)->where('sid', '=', 'all')->select();
            if ($cpp != false && $cpp != null) {
//                var_dump($cpp);
                return array('status' => true, 'cpObj' => $cpp);
            } else {
                $cpp = $cp->table('ca_coupon')->where('coupon', '=', $coupon)->where('sid', '=', $sid)->select();
                if ($cpp != false && $cpp != null) {
                    return array('status' => true, 'cpObj' => $cpp);
                } else {
                    return array('status' => false, 'message' => '该优惠代码不存在或不适用于本商品！');
                }
            }
        } catch (DataNotFoundException $e) {
        } catch (ModelNotFoundException $e) {
        } catch (DbException $e) {
        }
    }

    static function getUserCash($uid)
    {
        $u = new OrderHandle();
        try {
            $user = $u->table('ca_user')->where('uid', '=', $uid)->field('cash,credit,status')->select();
        } catch (DataNotFoundException $e) {
        } catch (ModelNotFoundException $e) {
        } catch (DbException $e) {
        }
        if ($user) {
            return array('status' => true, 'userObj' => $user);
        } else {
            return array('status' => false, 'message' => '系统错误，读取用户信息失败');
        }
    }

    static function purchase($isrenew, $renewid, $uid, $sid, $paymethod, $price, $time, $newcash, $pricename, $email) //能传到这一步说明已经通过验证了直接入库就可以
    {
        //扣款
        $user = new OrderHandle();
        $record = new OrderHandle();
        $mysub = new OrderHandle();
        if ($user->table('ca_user')->where('uid', '=', $uid)->update([$paymethod => $newcash])) { //支付成功！
            $sub = SubHandle::getSubInfo($sid);
            if ($sub['status']) {
                $sname = $sub['subObj'][0]->sname; // 获取商品名 写入购买记录
            }
            $purtime = time();
            if ($isrenew) {
                try {
                    $exexpiretime = (new OrderHandle)->table('ca_mysubscribe')->where('id', '=', $renewid)->field('expiretime')->select();
                } catch (DataNotFoundException $e) {
                } catch (ModelNotFoundException $e) {
                } catch (DbException $e) {
                }//原到期时间
                $expiretime = strtotime($exexpiretime[0]->expiretime) + $time * 24 * 3600;
                $record->table('ca_buyrecord')->insert([
                    'uid' => $uid,
                    'subname' => $sname . ' - ' . $pricename,
                    'method' => $paymethod,
                    'type' => 1, //1代表续费
                    'price' => $price,
                    'purchasetime' => date('Y-m-d H:i:s', $purtime)
                ]); // 写入购买记录
                $mysub->table('ca_mysubscribe')->where('id', '=', $renewid)->update([
                    'uid' => $uid,
                    'sid' => $sid,
                    'sname' => $sname . ' - ' . $pricename,
                    'status' => '正常',
                    'expiretime' => date('Y-m-d H:i:s', $expiretime)
                ]); // 更新我的订阅
                //发送邮件
                $web = IndexHandle::getWebSettings();
                $webtitle = $web['webObj']->title;
                $url = $web['webObj']->url;
                $templet = ActiveHandle::getTemplet('subscribe');
                $t = str_replace('{webtitle}', $webtitle, $templet);
                $t = str_replace('{subname}', $sname, $t);
                $t = str_replace('{pricename}', $pricename, $t);
                $t = str_replace('{url}', $url, $t);
                $send = Mail::sendEmail($email, $uid, '感谢您的购买 - ' . $webtitle, $t);
                return array('status' => true); // 未来这里还可以发邮件
            } else {
                $expiretime = $purtime + $time * 24 * 3600;
                $record->table('ca_buyrecord')->insert([
                    'uid' => $uid,
                    'subname' => $sname . ' - ' . $pricename,
                    'method' => $paymethod,
                    'price' => $price,
                    'purchasetime' => date('Y-m-d H:i:s', $purtime)
                ]); // 写入购买记录
                $mysub->table('ca_mysubscribe')->insert([
                    'uid' => $uid,
                    'sid' => $sid,
                    'sname' => $sname . ' - ' . $pricename,
                    'status' => '正常',
                    'starttime' => date('Y-m-d H:i:s', $purtime),
                    'expiretime' => date('Y-m-d H:i:s', $expiretime)
                ]); // 写入我的订阅
                //发送邮件
                $web = IndexHandle::getWebSettings();
                $webtitle = $web['webObj']->title;
                $url = $web['webObj']->url;
                $templet = ActiveHandle::getTemplet('subscribe');
                $t = str_replace('{webtitle}', $webtitle, $templet);
                $t = str_replace('{subname}', $sname, $t);
                $t = str_replace('{pricename}', $pricename, $t);
                $t = str_replace('{url}', $url, $t);
                $send = Mail::sendEmail($email, $uid, '感谢您的购买 - ' . $webtitle, $t);
                return array('status' => true); // 未来这里还可以发邮件
            }
        }
    }

    static function getBuyRecordById($uid)
    {
        $record = new OrderHandle();
        $r = $record->table('ca_buyrecord')->where('uid', '=', $uid)->order('purchasetime', 'desc')->paginate(10);
        return array('status' => true, 'recordObj' => $r);
    }
}