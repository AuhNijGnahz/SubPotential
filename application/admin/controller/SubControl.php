<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/9/4
 * Time: 15:51
 */

namespace app\admin\controller;

use app\admin\controller\BasicControl;
use app\admin\model\CardModel;
use think\Request;
use app\admin\model\SubModel;
use app\admin\model\PriceModel;
use app\admin\model\CouponModel;

class SubControl extends BasicControl
{
    // 订阅产品列表页面
    public function subListIndex()
    {
        $sub = SubModel::subList();
        if ($sub['status']) {
            $this->assign([
                'sublist' => $sub['subObj'],
                'subcount' => count($sub['subObj'])
            ]);
        }
        return $this->fetch('sublist');
    }

    // 添加订阅产品页面
    public function addSubIndex()
    {
        return $this->fetch('addsub');
    }

    // 价格方案管理页面
    public function subPrice()
    {
        $subRt = SubModel::getSidList();
        $priceRt = PriceModel::getPriceList();
        if ($subRt['status']) {
            $this->assign([
                'sidlist' => $subRt['subObj'],
                'pricelist' => $priceRt['priceObj']
            ]);
        }
        return $this->fetch('subPrice');
    }

    // 编辑订阅产品页面
    public function editSubIndex(Request $request)
    {
        $sid = $request->get('sid');
        $subRt = SubModel::getSingleSub($sid);
        if ($subRt['status']) {
            $subObj = $subRt['subObj'];
            $this->assign([
                'sid' => $subObj->sid,
                'skey' => $subObj->skey,
                'name' => $subObj->sname,
                'status' => $subObj->status,
                'bind' => $subObj->bindgroup,
                'desc' => $subObj->des,
                'discount' => $subObj->renewdiscount
            ]);
            return $this->fetch('editsub');
        } else {
            $this->error($subRt['message']);
        }
    }

    // 优惠券管理页面
    public function coupon(Request $request)
    {
        $coupon = $request->get('coupon');
        if ($coupon === "" || $coupon === null) {
            $subRt = SubModel::getSidList();
            $couponlist = CouponModel::getCouponList();
            if ($subRt['status']) {
                $this->assign([
                    'sidlist' => $subRt['subObj'],
                    'couponlist' => $couponlist['cpObj'],
                    'pricelist' => null
                ]);
            }
            return $this->fetch('coupon');
        } else {
            $cpRt = CouponModel::getCouponSub($coupon);
            $this->assign([
                'coupon' => $coupon,
                'forsub' => $cpRt['cpObj']
            ]);
            return $this->fetch('forsub');
        }
    }

    // 充值卡管理页面
    public function card()
    {
        $card = $this->getAllCards();
        $this->assign([
            'card' => $card,
        ]);
        return $this->fetch('card');
    }

    // 修改订阅产品信息
    public function editSub(Request $request)
    {
        $skey = $request->post('skey');
        $sname = $request->post('name');
        $status = $request->post('status');
        $discount = $request->post('discount');
        $bind = $request->post('bind');
        if ($status === "true") {
            $status = '启用';
        } else {
            $status = '停用';
        }
        if ($bind === "true") {
            $bind = '是';
        } else {
            $bind = '否';
        }
        $des = $request->post('describe');
        $subRt = SubModel::editSub($skey, $sname, $status, $bind, $discount, $des);
        if ($subRt['status']) {
            $this->success("编辑产品信息成功");
        } else {
            $this->error($subRt['message']);
        }
    }

    // 修改订阅产品状态
    public function changeStatus(Request $request)
    {
        $sid = $request->post('sid');
        $subRt = SubModel::changeStatus($sid);
        if ($subRt['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => true, 'message' => $subRt['message']));
        }
    }

    // 删除单个订阅
    public function dSingleSub(Request $request)
    {
        $sid = $request->post('sid');
        $subRt = SubModel::dSingleSub($sid);
        if ($subRt['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => true, 'message' => $subRt['message']));
        }
    }

    // 添加订阅
    public function addSub(Request $request)
    {
        $sid = $request->post('sid');
        $skey = $request->post('skey');
        $name = $request->post('name');
        $status = $request->post('status');
        $discount = $request->post('discount');
        $bind = $request->post('bind');
        if ($status === 'true') {
            $status = '启用';
        } else {
            $status = '停用';
        }
        if ($bind === "true") {
            $bind = '是';
        } else {
            $bind = '否';
        }
//        $mode = $request->post('mode');
//        if($mode === 'free'){
//            $mode = '免费模式';
//        }
//        else{
//            $mode = '付费模式';
//        }
        $bind = $request->post('bind');
        // var_dump($bind);
        if ($bind === 'true') {
            $bind = '是';
        } else {
            $bind = '否';
        }
        $describe = $request->post('describe');
        $subRt = SubModel::addSub($sid, $name, $describe, $skey, $status, $bind, $discount);
        if ($subRt['status']) {
            $this->success('添加订阅产品成功');
        } else {
            $this->error($subRt['message']);
        }
    }

    // 更改价格方案状态
    public function changePriceStatus(Request $request)
    {
        $pid = $request->post('pid');
        $priceRt = PriceModel::changePriceStatus($pid);
        if ($priceRt['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => true, 'message' => $priceRt['message']));
        }
    }

    // 删除单个价格方案
    public function dSinglePrice(Request $request)
    {
        $sid = $request->post('pid');
        $subRt = PriceModel::dSinglePrice($sid);
        if ($subRt['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => true, 'message' => $subRt['message']));
        }
    }

    // 添加价格方案
    public function addSubPrice(Request $request)
    {
        $sid = $request->post('sid');
        $name = $request->post('name');
        $method = $request->post('method');
        if ($method === 'credit') {
            $method = '积分';
        } else {
            $method = '余额';
        }
        $status = $request->post('status');
        if ($status === 'true') {
            $status = '启用';
        } else {
            $status = '停用';
        }
        $price = $request->post('price');
        $time = $request->post('time');
        $priceRt = PriceModel::addPrice($sid, $name, $method, $price, $time, $status);
        if ($priceRt['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => false, 'message' => '添加价格方案失败！'));
        }
    }

    // 添加优惠代码
    public function addCoupon(Request $request)
    {
        $sid = $request->post('sid/a');
        $coupon = $request->post('coupon');
        $discount = $request->post('discount');
        $begintime = $request->post('begintime');
        if ($begintime === "") {
            $begintime = date('Y-m-d H:i:s');
        }
        $expireday = $request->post('expireday');
        foreach ($sid as $value) {
            $CouRt = CouponModel::addCoupon($value, $coupon, $discount, $begintime, $expireday);
        }
        return json_encode(array('status' => true, 'message' => '添加完毕，请刷新查看！'));
    }

    // 删除单个优惠代码
    public function dSingleCoupon(Request $request)
    {
        $coupon = $request->post('coupon');
        $cpRt = CouponModel::dSingleCoupon($coupon);
        if ($cpRt['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => true, 'message' => $cpRt['message']));
        }
    }

    // 生成充值卡
    public function addCard(Request $request)
    {
        $cash = $request->post('cash');
        $credit = $request->post('credit');
        $num = $request->post('number');
        $time = $request->post('time'); // 有效期
        $cRt = CardModel::addCards($cash, $credit, $num, $time);
        $this->success('生成完毕');
    }

    public function getAllCards()
    {
        $cRt = CardModel::getAllCards();
        return $cRt['cObj'];
    }
}