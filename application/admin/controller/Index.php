<?php

namespace app\admin\controller;

use app\admin\model\SubModel;
use app\admin\model\TicketModel;
use app\admin\model\UserModel;
use think\Request;
class Index extends BasicControl
{
    public function index(Request $request)
    {
        $admin = [];
        $user = UserModel::getAlluser();
        $sub = SubModel::getSidList();
        $ticket = TicketModel::getAllTicket();
        $admin['usercount'] = count($user['userObj']);
        $admin['subcount'] = count($sub['subObj']);
        $admin['ticketcount'] = count($ticket['list']);
        $this->assign([
            'panel' => $admin
        ]);
        return $this->fetch();
    }

    public function chart()
    {
        return $this->fetch();
    }

    public function adminLogin()
    {
        return $this->fetch('login');
    }
}
