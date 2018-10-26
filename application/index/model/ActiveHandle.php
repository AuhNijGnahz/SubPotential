<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/10/10
 * Time: 17:36
 */

namespace app\index\model;

use think\Model;

class ActiveHandle extends Model
{
    static function getTemplet($type)
    {
        $tem = new ActiveHandle();
        $t = $tem->table('ca_templet')->where('type', '=', $type)->select();
        return $t[0]->content;
    }

    static function createActiveCode($uid, $email, $verify = false)
    {
        $active = new ActiveHandle();
        $a = $active->table('ca_active')->where('uid', '=', $uid)->select();
        //未激活
        $frame = time() - strtotime($a[0]->createtime);
//        var_dump($frame);
        if ($verify) {
            // 如果是用来验证的则直接返回
            $active_code = md5(date('Y-m-d') . $uid . $email) . config('sault');
            return $active_code;
        }
        if ($frame < 300 && $frame > 0) {
            return false; // 限制发送时间
        }
        $a = $active->table('ca_active')->where('uid', '=', $uid)->update([
            'createtime' => date('Y-m-d H:i:s')
        ]);
        $active_code = md5(date('Y-m-d') . $uid . $email) . config('sault');
        return $active_code;
    }

    static function activeEmail($uid)
    {
        $active = UserHandle::activeStatus($uid);
        if ($active['email']) {
            //已激活
            return array('status' => false, 'message' => '邮箱已经激活过！');
        }

        $active = new ActiveHandle();
        $a = $active->table('ca_active')->where('uid', '=', $uid)->select();
        $frame = time() - strtotime($a[0]->createtime);
//        var_dump($frame);
        if ($frame > 120 * 60) {
            //超时
            return array('status' => false, 'message' => '激活链接已过期！');
        }
        $a = $active->table('ca_active')->where('uid', '=', $uid)->update([
            'createtime' => date('Y-m-d H:i:s', 000000),
            'email' => 1
        ]);
        return array('status' => true);
    }
}