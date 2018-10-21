<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/9/8
 * Time: 22:17
 */

namespace app\admin\model;

use think\exception\DbException;
use think\Model;

class PriceModel extends Model
{
    protected $table = 'ca_price';

    static function getPriceList()
    {
        try {
            $price = PriceModel::all((new PriceModel)->order('sid','asc'));
        } catch (DbException $e) {
        }
            return array('status' => true, 'priceObj' => $price);
    }
    static function changePriceStatus($id){
        try {
            $price = PriceModel::get($id);
        } catch (DbException $e) {
        }
        if(!$price){
            return array('status'=>false,'message'=>'价格方案不存在');
        }
        else if ($price->status === '启用'){
            $price->status = '停用';
        }
        else{
            $price->status = '启用';
        }
        if($price->isUpdate(true)->save()){
            return array('status'=>true);
        }
        else{
            return array('status'=>false,'message'=>'系统错误，修改失败');
        }
    }
    static function dSinglePrice($id){
        try {
            $price = PriceModel::get($id);
        } catch (DbException $e) {
        }
        if(!$price){
            return array('status'=>false,'message'=>'价格方案不存在');
        }
        else if($price->delete()){
            return array('status'=>true);
        }
        else{
            return array('status'=>false,'message'=>'系统错误，删除失败');
        }
    }
    static function addPrice($sid,$name,$method,$price1,$time,$status){
        $price = new PriceModel();
        $price->sid = $sid;
        $price->pname = $name;
        $price->method = $method;
        $price->price = $price1;
        $price->time = $time;
        $price->status = $status;
        if($price->save()){
            return array('status'=>true);
        }
        else{
            return array('status'=>false,'message'=>'系统错误，新增方案失败！');
        }
    }
}