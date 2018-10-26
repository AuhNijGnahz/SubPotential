<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/9/13
 * Time: 13:17
 */

namespace app\admin\controller;

use app\admin\controller\BasicControl;
use app\admin\model\logModel;
use think\Request;

class logControl extends BasicControl
{
    public function buyRecord(Request $request)
    {
        $uid = $request->get('uid');
        if ($uid === "" || $uid === null) {
            $r = logModel::getAllBuyRecord();
            $this->assign([
                'record' => $r['recordObj'],
                'recount' => count($r['recordObj'])
            ]);
        } else {
            $r = logModel::getBuyRecordByUid($uid);
            $this->assign([
                'record' => $r['recordObj'],
                'recount' => count($r['recordObj'])
            ]);
        }
        return $this->fetch('subpurchaserecord');
    }
    public function loginRecord(Request $request){
        $uid = $request->get('uid');
        if ($uid === "" || $uid === null) {
            $r = logModel::getAllLoginRecord();
            $this->assign([
                'record' => $r['recordObj'],
                'recount' => count($r['recordObj'])
            ]);
        } else {
            $r = logModel::getLoginRecordByUid($uid);
            $this->assign([
                'record' => $r['recordObj'],
                'recount' => count($r['recordObj'])
            ]);
        }
        return $this->fetch('loginrecord');
    }
}