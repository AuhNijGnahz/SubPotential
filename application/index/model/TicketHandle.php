<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/10/7
 * Time: 12:24
 */

namespace app\index\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Model;

class TicketHandle extends Model
{
    static function getAllTicket()
    {
        $ticket = new TicketHandle();
        $t = $ticket->table('ca_ticket')->paginate(10);
        $t2 = $ticket->table('ca_ticket')->where('status', '=', 2)->select();
        $t3 = $ticket->table('ca_ticket')->where('status', '=', 3)->select();
        $t5 = $ticket->table('ca_ticket')->where('status', '=', 5)->select();
//        var_dump($t);
        return array('status' => true, 'tObj' => $t, 't2' => $t2, 't3' => $t3, 't5' => $t5);
    }

    static function getAgentTicket($uid) //获取分配中的工单，只有客服模式可以使用
    {
        $ticket = new TicketHandle();
        $t1 = $ticket->table('ca_ticket')->where('status', '=', 0)->order('status','asc')->select();
        $t2 = $ticket->table('ca_ticket')->where('agent', '=', $uid)->select();
        $t = array_merge($t1, $t2);
//        var_dump($t);
        return array('status' => true, 'tObj' => $t);
    }

    static function getAgent()
    {//查看是否有权限处理工单
        $agent = new TicketHandle();
        $a = $agent->table('ca_ticketsetting')->field('uid')->where('id', '=', 1)->select();
//        var_dump($a[0]->uid);
        $agent = explode(',', $a[0]->uid);
        return array('status' => true, 'agent' => $agent);
    }

    static function getMyTicket($uid, $status = null)
    {
        $ticket = new TicketHandle();
        if ($status === null) { //获取全部工单
            $t = $ticket->table('ca_ticket')->where('uid', '=', $uid)->paginate(10);
            $t2 = $ticket->table('ca_ticket')->where('uid', '=', $uid)->where('status', '=', 2)->select();
            $t3 = $ticket->table('ca_ticket')->where('uid', '=', $uid)->where('status', '=', 3)->select();
            $t5 = $ticket->table('ca_ticket')->where('uid', '=', $uid)->where('status', '=', 5)->select();
            return array('status' => true, 'tObj' => $t, 't2' => $t2, 't3' => $t3, 't5' => $t5);
        } else {
            try {
                $t = $ticket->table('ca_ticket')->where('uid', '=', $uid)->where('status', '=', $status)->paginate(10);
            } catch (DataNotFoundException $e) {
            } catch (ModelNotFoundException $e) {
            } catch (DbException $e) {
            }
            return array('status' => true, 'tObj' => $t);
        }
    }

    static function getRandomString($length = 8)
    {
        $str = null;
        $num = $length;// 字符串长度
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";//如果不需要小写字母，可以把小写字母都删除
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $num; $i++) {
            $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

    static function createTicket($uid, $title, $content, $phone)
    {
        $tid = self::getRandomString();
        $createtime = date('Y-m-d H:i:s');
        $tickte = new TicketHandle();
        // 工单列表插入数据
        $t = $tickte->table('ca_ticket')->insert([
            'tid' => $tid,
            'uid' => $uid,
            'tname' => $title,
            'phone' => $phone,
            'status' => 0,
            'rate' => 0,
            'agent' => 0,
            'createtime' => $createtime
        ]);
        // 回复列表插入数据，绑定TID
        $r = $tickte->table('ca_ticketreply')->insert([
            'tid' => $tid,
            'uid' => $uid,
            'content' => $content,
            'type' => 0,
            'replytime' => $createtime
        ]);
        if ($t && $r) {
            return array('status' => true);
        }
        {
            return array('status' => false, 'message' => '创建工单失败！');
        }
    }

    static function changeStatus($tid, $newStatus)
    {
        $ticket = new TicketHandle();
        $r = $ticket->table('ca_ticket')->where('tid', '=', $tid)->update([
            'status' => $newStatus
        ]);
        return array('status' => true);
    }

    static function getTicketByTid($tid)
    {
        $ticket = new TicketHandle();
        $t = $ticket->table('ca_ticket')->where('tid', '=', $tid)->paginate(10);
        if ($t) {
            return array('status' => true, 'tObj' => $t[0]);
        } else {
            return array('status' => false, 'message' => '工单不存在！');
        }
    }

    static function getReplyByTid($tid)
    {
        $ticket = new TicketHandle();
        $r = $ticket->table('ca_ticketreply')->where('tid', '=', $tid)->select();
        return array('status' => true, 'rObj' => $r);
    }

    static function replyTickety($tid, $uid, $content, $type)
    {
        $ticket = new TicketHandle();
        $r = $ticket->table('ca_ticketreply')->insert([
            'tid' => $tid,
            'uid' => $uid,
            'content' => $content,
            'type' => $type,
            'replytime' => date('Y-m-d H:i:s')
        ]);
        return array('status' => true);
    }

    static function acceptTicket($tid, $agent)
    {
        $ticket = new TicketHandle();
        $t = $ticket->table('ca_ticket')->where('tid', '=', $tid)->select();
        if ($t[0]->status === 0) { //判断是否是待接单状态
            $t = $ticket->table('ca_ticket')->where('tid', '=', $tid)->update([
                'status' => 1,
                'agent' => $agent
            ]);
            return array('status' => true);
        } else { // 不是待接单状态，报错
            return array('status' => false, 'message' => '该工单已被其他客服受理');
        }
    }

    static function rate($tid, $rate)
    {
        $ticket = new TicketHandle();
        $t = $ticket->table('ca_ticket')->where('tid', '=', $tid)->select();
        if ($t[0]->rate >= 1 && $t[0]->rate <= 5) { //判断是否有评分了
            // 有评分了，报错
            return array('status' => false, 'message' => '该工单已评分过');
        } else { // 没有评分，可以评分
            $t = $ticket->table('ca_ticket')->where('tid', '=', $tid)->update([
                'status' => 5,
                'rate' => $rate
            ]);
            return array('status' => true);
        }
    }
}