<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/9/13
 * Time: 13:27
 */

namespace app\admin\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Model;

class logModel extends Model
{
    static function getAllBuyRecord()
    {
        $record = new logModel();
        $r = $record->table('ca_buyrecord')->order('purchasetime', 'asc')->select();
        return array('status' => true, 'recordObj' => $r);
    }

    static function getBuyRecordByUid($uid)
    {
        $record = new logModel();
        $r = $record->table('ca_buyrecord')->where('uid', '=', $uid)->select();
        return array('status' => true, 'recordObj' => $r);
    }

    static function getAllLoginRecord()
    {
        $record = new logModel();
        $r = $record->table('ca_loginrecord')->order('id', 'asc')->select();
        return array('status' => true, 'recordObj' => $r);
    }

    static function getLoginRecordByUid($uid)
    {
        $record = new logModel();
        $r = $record->table('ca_loginrecord')->where('uid', '=', $uid)->order('id','desc')->select();
        return array('status' => true, 'recordObj' => $r);
    }
}