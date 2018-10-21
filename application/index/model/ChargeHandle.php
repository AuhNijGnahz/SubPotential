<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/9/30
 * Time: 15:57
 */

namespace app\index\model;

use think\Model;
use Damon\YouzanPay\YouzanPay;
use Damon\YouzanPay\QrCode\QrCode;

class ChargeHandle extends Model
{
    static function getAllMethod()
    {
        $purchase = new ChargeHandle();
        $p = $purchase->table('ca_purchasemethod')->select();
        return array('status' => true, 'pObj' => $p);
    }

    static function getMethodById($id)
    {
        $purchase = new ChargeHandle();
        $p = $purchase->table('ca_purchasemethod')->where('id', '=', $id)->select();
        return array('status' => true, 'pObj' => $p[0]);
    }

    static function getRecordByUid($uid)
    {
        $record = new ChargeHandle();
        $r = $record->table('ca_chargerecord')->where('uid', '=', $uid)->order('date', 'desc')->paginate(10);
        return array('status' => true, 'rObj' => $r);
    }
}