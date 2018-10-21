<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/9/10
 * Time: 23:11
 */

namespace app\admin\model;

use app\index\model\SubHandle;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Model;
use app\admin\model\SubModel;

class CouponModel extends Model
{
    protected $table = 'ca_coupon';

    static function addCoupon($sid, $coupon, $discount, $begintime, $expireday)
    {
        $expiretime = strtotime($begintime) + $expireday * 24 * 3600; //到期时间换算 strtotime 为文本转成时间戳
        $expiretime = date('Y-m-d H:i:s', $expiretime); //秒转成时间
        try {
            $couponRt = CouponModel::get(['coupon' => $coupon, 'sid' => $sid]);
        } catch (DbException $e) {
        }
        if ($couponRt) {
            return array('status' => false, 'message' => '优惠码已存在，请删除后重新添加！');
        } else {
            $CObj = new CouponModel();
            $CObj->sid = $sid;
            $CObj->coupon = $coupon;
            $CObj->discount = $discount;
            $CObj->begintime = $begintime;
            $CObj->expiretime = $expiretime;
            if ($CObj->isUpdate(false)->save()) {
                return array('status' => true);
            } else {
                return array('status' => false, 'message' => '系统错误，添加失败！');
            }
        }
    }
    static function getCouponList(){
        try {
            $coupon = CouponModel::all((new CouponModel)->group('coupon'));
        } catch (DbException $e) {
        }
        return array('status'=>true,'cpObj'=>$coupon);
    }
    static function dSingleCoupon($coupon){
        $cp = new CouponModel();
            if($cp->destroy(['coupon'=>$coupon])){
                return array('status'=>true);
            }
            else{
                return array('status'=>false,'message'=>'系统错误，删除失败！');
            }
    }
    static function getCouponSub($coupon){
        try {
            $cp = new CouponModel();
            $cp = $cp->field('sid')->where('coupon', '=', $coupon)->select();
        } catch (DataNotFoundException $e) {
        } catch (ModelNotFoundException $e) {
        } catch (DbException $e) {
        }
        return array('status'=>true,'cpObj'=>$cp);
    }
}