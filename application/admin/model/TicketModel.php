<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/10/5
 * Time: 22:22
 */

namespace app\admin\model;

use think\Model;

class TicketModel extends Model
{
    static function setTicketSetting($uid)
    {
        $ticket = new TicketModel();
        $t = $ticket->table('ca_ticketsetting')->where('id', '=', 1)->update([
            'uid' => $uid
        ]);
        return array('status' => true, 'tObj' => $t);
    }

    static function getTicketSetting(){
        $ticket = new TicketModel();
        $t = $ticket->table('ca_ticketsetting')->where('id', '=', 1)->select();
        return array('status' => true, 'tObj' => $t);
    }

    static function getAllClass()
    {
        $ticket = new TicketModel();
        $c = $ticket->table('ca_ticketclass')->select();
        return array('status' => true, 'cObj' => $c);
    }

    static function classDo($classname, $fatherclass = 0)
    {
        $ticket = new TicketModel();
        $c = $ticket->table('ca_ticketclass')->where('classname', '=', $classname)->select();
        if ($c) {//存在
            $c = $ticket->table('ca_ticketclass')->where('classname', '=', $classname)->update([
                'classname' => $classname,
                'fatherclass' => $fatherclass
            ]);
            return array('status' => true, 'cObj' => $c);
        } else {//不存在
            $c = $ticket->table('ca_ticketclass')->insert([
                'classname' => $classname,
                'fatherclass' => $fatherclass
            ]);
            return array('status' => true, 'cObj' => $c);
        }
    }

    static function getAllTicket()
    {
        $ticket = new TicketModel();
        $l = $ticket->table('ca_ticket')->select();
        return array('status' => true, 'list' => $l);
    }
}