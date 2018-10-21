<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/8/29
 * Time: 13:16
 */

namespace app\index\model;

use app\index\controller\User;
use think\Model;
use think\exception\DbException;
use think\Session;

class UserHandle extends Model
{
    protected $table = 'ca_user';

    static function reg($username, $password, $email)
    {
        $groupid = self::getDefaultGroupId();
        $user = new UserHandle();
        $user->username = $username;
        $user->password = $password;
        $user->email = $email;
        $user->expiretime = date('Y-m-d H:i:s', strtotime('3099-12-31 23:59:59'));
        $user->groupid = $groupid;
        $user->avatar = '/static/uploads/avatar/default/default.png';
        if ($user->username != "" && $user->password != "" && $user->email != "") {
            if (self::checkuser($user->email)['status']) {
                $user->regdate = date('Y-m-d H:i:s');
                if ($user->save()) {
                    return array('status' => true);
                } else {
                    return array('status' => false, 'message' => '系统错误，注册失败！', 'url' => '/index');
                }
            } else {
                return array('status' => false, 'message' => '邮箱已被注册！', 'url' => '/index/index/reg');
            }
        } else {
            return array('status' => false, 'message' => '信息填写不完整！', 'url' => '/index/index/reg');
        }
    }

    static function checkuser($email)
    {
        try {
            $user = UserHandle::get(['email' => $email]);
        } catch (DbException $e) {
        }
        if ($user == null) {
            return array('status' => true);  // 用户不存在，可以注册
        } else {
            return array('status' => false);  //1 用户存在
        }
    }

    static function login($email, $password, $ip, $location)
    {
        try {
            $record = new UserHandle();
            $user = UserHandle::get([
                'email' => $email,
                'password' => $password
            ]);
        } catch (DbException $e) {
        }
        if ($user) {
            $user->save(['lastlogindate' => date("Y-m-d H:i:s")]);
            $record->table('ca_loginrecord')->insert([
                'uid' => $user->uid,
                'logindate' => date("Y-m-d H:i:s"),
                'ip' => $ip,
                'location' => $location
            ]);
            return array('status' => true, 'userObj' => $user);
        } else {
            return array('status' => false);
        }
    }

    static function recovery($uid, $code)
    {
        $user = UserHandle::get($uid);
        $verify = new UserHandle();
        $v = $verify->table('ca_active')->where('uid', '=', $uid)->select();
        if ($v[0]->recover != $code) {
            return array('status' => false, 'message' => '找回密码链接已失效！');
        }
        $frame = time() - strtotime($v[0]->createtime);
//        var_dump($frame);
        if ($frame > 120 * 60) {
            //超时
            return array('status' => false, 'message' => '找回密码链接已过期！');
        }
        $newpwd = time();
        $user->password = md5($newpwd . config('sault'));
        if ($user->isUpdate(true)->save()) {
            $v = $verify->table('ca_active')->where('uid', '=', $uid)->update([
                'createtime' => date('Y-m-d H:i:s', 000000),
                'recover' => ''
            ]);
            return array('status' => true, 'newpwd' => $newpwd);
        } else {
            return array('status' => false);
        }
    }

    static function createRecoverCode($uid, $verify = false)
    {
        $active = new ActiveHandle();
        $a = $active->table('ca_active')->where('uid', '=', $uid)->select();
        //未激活
        $frame = time() - strtotime($a[0]->createtime);
//        var_dump($frame);
        if ($verify) {
            // 如果是用来验证的则直接返回
            $active_code = md5(date('Y-m-d') . $uid) . config('sault');
            return $active_code;
        }
        if ($frame < 300 && $frame > 0) {
            return false;
        }
        $active_code = md5(date('Y-m-d') . $uid) . config('sault');
        $a = $active->table('ca_active')->where('uid', '=', $uid)->update([
            'createtime' => date('Y-m-d H:i:s'),
            'recover' => $active_code
        ]);
        return $active_code;
    }

    static function getuserinfo($uid, $email = "")
    {
        try {
            if (empty($email)) {
                $user = UserHandle::get($uid);
            } else {
                $user = UserHandle::get(['email' => $email]);
            }
        } catch (DbException $e) {
        }
        if ($user != null) {
            return array('status' => true, 'userObj' => $user);
        }
    }

    static function getLoginRecordByUid($uid)
    {
        $record = new UserHandle();
        $r = $record->table('ca_loginrecord')->where('uid', '=', $uid)->order('id', 'desc')->select();
        return array('status' => true, 'recordObj' => $r);
    }

    static function changePwd($uid, $oldpwd, $newpwd)
    {
        $user = UserHandle::get(['uid' => $uid, 'password' => $oldpwd]);
        if ($user) {
            $user->password = $newpwd;
            $user->isUpdate(true)->save();
            return array('status' => true, 'message' => '修改密码成功');
        } else {
            return array('status' => false, 'message' => '原密码不正确！');
        }

    }

    static function getGroupByid($id)
    {
        $group = new UserHandle();
        $g = $group->table('ca_ugroup')->where('id', '=', $id)->select();
        return array('status' => true, 'groupObj' => $g);
    }

    static function getDefaultGroupId()
    {
        $group = new UserHandle();
        $g = $group->table('ca_regsetting')->field('groupid')->where('id', '=', 1)->select();
//        var_dump($g[0]->groupid);
        return $g[0]->groupid;
    }

    static function changeUserName($uid, $newname, $price)
    {

        $user = UserHandle::get($uid);
        $record = new UserHandle();
        $user->username = $newname;
        $user->cash -= $price;
        if ($user->isUpdate(true)->save()) {
            $record->table('ca_buyrecord')->insert([
                'uid' => $uid,
                'subname' => '变更用户名 - 变更为：' . $newname,
                'method' => 'cash',
                'price' => $price,
                'purchasetime' => date('Y-m-d H:i:s', time())
            ]); // 写入购买记录
            return array('status' => true);
        } else {
            return array('status' => false, 'message' => '系统错误，修改失败！');
        }
    }

    static function changeAvatar($uid, $imgpath)
    {
        $user = UserHandle::get($uid);
        $user->avatar = $imgpath;
        if ($user->isUpdate(true)->save()) {
            return array('status' => true);
        } else {
            return array('status' => false, 'message' => '系统错误，修改失败！');
        }

    }

    static function deleteMyAcc($uid)
    {
        $u = new UserHandle();
        $user = UserHandle::get($uid);
        $user->delete();
        $sub = $u->table('ca_mysubscribe')->where('uid', '=', $uid)->delete();
        $loginrecord = $u->table('ca_loginrecord')->where('uid', '=', $uid)->delete();
        $chargerecord = $u->table('ca_chargerecord')->where('uid', '=', $uid)->delete();
        return array('status' => true);
    }

    static function giveCash($uid, $cashcount = 0, $method)
    {
        $user = UserHandle::get($uid);
//        var_dump($user);
        $user->cash += $cashcount;
        if ($user->isUpdate(true)->save()) {
            //发送邮件
            $web = IndexHandle::getWebSettings();
            $cash2 = self::getuserinfo($uid)['userObj']->cash;
            self::sendEmail('charge', '充值凭证 - ', $user->email, $uid, $method, $cashcount, $user->cash);
            return array('status' => true);
        } else {
            return array('status' => false);
        }
    }

    static function giveCredit($uid, $creditcount = 0)
    {
        $user = UserHandle::get($uid);
        $user->credit += $creditcount;
        if ($user->isUpdate(true)->save()) {
            return array('status' => true);
        } else {
            return array('status' => false);
        }
    }

    static function verifyCard($uid, $card, $email)
    {
        $cards = new UserHandle();
        $c = $cards->table('ca_cards')->where('card', '=', $card)->select();
        if ($c) {
            $useruid = $c[0]->uid;
            if ($useruid == 0) { // 没有使用
                $cash = $c[0]->cash;
                if (!$cash == 0) {
                    $v1 = self::giveCash($uid, $cash, '激活序列号');
                }
                $credit = $c[0]->credit;
                if (!$credit == 0) {
                    $v2 = self::giveCredit($uid, $credit);
                }
                $cc = $cards->table('ca_cards')->where('card', '=', $card)->update([
                    'uid' => $uid,
                    'usetime' => date('Y-m-d H:i:s')
                ]);
                if (Payment::createOrder(0, $uid, '序列号', 'cash', $c[0]->cash, 1)) {
                    return array('status' => true);
                } else {
                    return array('status' => false, '创建订单失败！请联系客服！');
                }
            } else {
                return array('status' => false, 'message' => '该序列号已被使用');
            }
        } else {
            return array('status' => false, 'message' => '序列号不存在！');
        }
    }

    static function sendEmail($templet, $title, $email, $uid = 0, $method = 0, $count = 0, $cash = 0, $activeurl = 0)
    {
        $web = IndexHandle::getWebSettings();
        $webtitle = $web['webObj']->title;
        $url = $web['webObj']->url;
        $templet = ActiveHandle::getTemplet('charge');
        $t = str_replace('{webtitle}', $webtitle, $templet);
        $t = str_replace('{method}', $method, $t);
        $t = str_replace('{count}', $count, $t);
        $t = str_replace('{cash}', $cash, $t);
        $t = str_replace('{url}', $url, $t);
        $t = str_replace('{activeurl}', $activeurl, $t);
        $send = Mail::sendEmail($email, $uid, $title . $webtitle, $t);
    }

    static function activeStatus($uid)
    {
        $email = new UserHandle();
        $a = $email->table('ca_active')->where('uid', '=', $uid)->select();
        if (!$a) {
            // 数据不存在，插入未验证的数据
            $a = $email->table('ca_active')->insert([
                'uid' => $uid,
                'email' => 0,
                'phone' => 0
            ]);
            return array('status' => true, 'email' => 0, 'phone' => 0);
        } else {
            return array('status' => true, 'email' => $a[0]->email, 'phone' => $a[0]->phone);
        }
    }
}