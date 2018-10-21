<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/10/1
 * Time: 17:05
 */

namespace app\index\model;

use think\Model;


class Payment extends Model
{
    public $purchaseStatus = false; // 订单支付状态
    // 目前创建订单已经成功，回调地址是 CallBack/youZanPay ，回调时调用本类下的verifyYZ方法（还未写完），verifyYZ 验证成功后设置全局变量为true，并返回给CallBack。
    // 前台JS循环使用get获取index/index/checkOrder?orderid=xxxx 方法的数据，通过调用本类的checkOrder方法获取全局变量的数据，为真则支付成功！

    // 创建有赞支付订单，返回支付二维码信息
    public function createYouZanOrder($uid, $chargecount)
    {
        $qrcodeApi = "https://open.youzan.com/api/oauthentry/youzan.pay.qrcode/3.0.0/create";
        $accessToken = $this->getYZToken();
        $postData = array(
            "access_token" => $accessToken,
            "qr_type" => "QR_TYPE_DYNAMIC",
            "qr_price" => $chargecount * 100,
            "qr_name" => 'UID:' . $uid . '-账户充值',
        );
        $qrcodeInfo = self::http_request($qrcodeApi, $postData);
        $qrcodeInfo = json_decode($qrcodeInfo, true)["response"];
//        var_dump($qrcodeInfo);
        if (!array_key_exists("qr_id", $qrcodeInfo)) {
//            var_dump($qrcodeInfo);
            return array("status" => false, "message" => "API请求失败");
        } else {
            // 获取支付信息成功，写入数据库
            if (self::createOrder($qrcodeInfo["qr_id"], $uid, '扫码支付', 'cash', $chargecount, 0)) {
                return array('status' => true, 'qrcode' => $qrcodeInfo["qr_code"], 'qrurl' => $qrcodeInfo["qr_url"], 'qrid' => $qrcodeInfo["qr_id"]);
            } else {
                return array('status' => false, 'message' => '创建订单失败，请联系管理员！');
            }
            $record = (new Payment())->table('ca_chargerecord')->insert($sqldata);
        }
    }

    static function createOrder($orderid = 0, $uid, $method, $type, $count, $status)
    {
        if ($orderid === 0) {
            $orderid = self::createOrderId();
        }
        $sqldata = array(
            'orderid' => $orderid,
            'uid' => $uid,
            'method' => $method,
            'type' => $type, // cash代表充值 未来会增加更多。
            'count' => $count,
            'status' => $status,
            'date' => date('Y-m-d H:i:s')
        );
        $record = (new Payment())->table('ca_chargerecord')->insert($sqldata);
        if ($record) {
            return true;
        } else {
            return false;
        }
    }

    static function createOrderId()
    {
        $osn = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return $osn;
    }

    public function getYZToken()
    {
        $youzan = ChargeHandle::getMethodById(1);
        $apiUrl = "https://open.youzan.com/oauth/token";
        $postData = array(
            "client_id" => $youzan['pObj']->secureid,
            "client_secret" => $youzan['pObj']->securekey,
            "grant_type" => "silent",
            "kdt_id" => $youzan['pObj']->thirdkey,
        );
        $result = self::http_request($apiUrl, $postData);
        $access_token = json_decode($result, true)["access_token"];
        return $access_token;
    }

    public function checkOrder() //这个orderid是用来入库时候的
    {
        return array('status' => $this->purchaseStatus);
    }

    public function verifyYZ($jsonData)
    {
        $yzRt = ChargeHandle::getMethodById(1);
        if ($jsonData["status"] != "TRADE_SUCCESS" || $jsonData["sign"] != md5($yzRt['pObj']->secureid . $jsonData["msg"] . $yzRt['pObj']->securekey)) {
            return array('status' => false, 'message' => '身份权限认证失败！');
        }
        $qrid = $this->getQRidByOrderId($jsonData['id']);
        $success = $this->setSuccess($qrid);
        $this->purchaseStatus = true;
        return array('status' => true);  //返回到CallBack里的youZanPay方法内
    }

    public function getQRidByOrderId($orderid)
    {
        $apiUrl = 'https://open.youzan.com/api/oauthentry/youzan.trade/4.0.0/get';
        $accessToken = $this->getYZToken();
        $postData = array(
            'access_token' => $accessToken,
            'tid' => $orderid
        );
        $result = self::http_request($apiUrl, $postData);
        $result = json_decode($result, true);
//        var_dump($result);
        $qrid = $result['response']['qr_info']['qr_id'];
        return $qrid;
    }

    public function setSuccess($orderid)
    {
        $record = new Payment();
        $r = $record->table('ca_chargerecord')->where('orderid', '=', $orderid)->select();
        if ($r[0]->data['status'] === 0) {
            switch ($r[0]->data['type']) {
                case 'cash':
                    $charge = UserHandle::giveCash($r[0]->data['uid'], $r[0]->data['count'], '扫码支付');
                    $r = $record->table('ca_chargerecord')->where('orderid', '=', $orderid)->update([
                        'status' => 1
                    ]);
                    break;
                default:
                    // code..
                    break;
            }
        }
        return true;
    }


    static function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}