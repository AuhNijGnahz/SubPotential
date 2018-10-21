<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/10/1
 * Time: 16:59
 */

namespace app\index\controller;

use app\index\model\Payment;

class CallBack extends BasicControl
{
    public function youZanPay()
    {
        $tradeMsg = json_decode(file_get_contents("php://input"), true);
        $paymentObj = new Payment();
        $paymentVerify = $paymentObj->verifyYZ($tradeMsg);
        if ($paymentVerify['status']) {
            return json_encode(array('code' => 0, 'msg' => 'success'));
        } else {
            return "error";
        }
    }
}