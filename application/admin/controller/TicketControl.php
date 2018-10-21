<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/10/5
 * Time: 21:53
 */

namespace app\admin\controller;

use app\admin\model\TicketModel;
use app\admin\model\UserModel;
use think\Request;

class TicketControl extends BasicControl
{
    public function ticketSetting()
    {
        $t = TicketModel::getTicketSetting();
        $this->assign([
            't' => $t['tObj']
        ]);
        return $this->fetch('ticketSetting');
//        $this->error('工单系统暂时无需任何配置');
    }
    public function setTicketSetting(Request $request){
        $uid = $request->post('uid');
//        var_dump($allowgroup);
        $sRt = TicketModel::setTicketSetting($uid);
        $this->success('设置成功！');
//        var_dump($sRt['tObj']);
    }

    public function ticketClass()
    {
        $c = TicketModel::getAllClass();
        $this->assign([
            'class' => $c['cObj']
        ]);
        return $this->fetch('ticketClass');
    }

    public function classDo(Request $request) //如果存在就编辑，不存在则新增
    {
        $classname = $request->post('classname');
        $fatherclass = $request->post('fatherclass');
        $aRt = TicketModel::classDo($classname, $fatherclass);
        if ($aRt['status']) {
            $this->success('操作分类成功！');
        } else {
            $this->error($aRt['message']);
        }
    }

    public function ticketList()
    {
        $list = TicketModel::getAllTicket();
        $this->assign([
            'ticketcount' => count($list['list']),
            'ticket' => $list['list']
        ]);
        return $this->fetch('ticketList');
    }
}