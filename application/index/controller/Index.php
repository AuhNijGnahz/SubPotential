<?php

namespace app\index\controller;

use app\admin\model\AnnounceModel;
use app\index\model\ChargeHandle;
use app\index\model\IndexHandle;
use app\index\model\UserHandle;
use app\index\model\Payment;
use Captcha\GeetestLib;
use think\Request;
use app\index\model\TicketHandle;
use app\index\model\SubHandle;
use app\index\model\OrderHandle;
use think\Session;

// 极验验证码
define("CAPTCHA_ID", "dde829b15d76d42eab536c2c20452919");
define("PRIVATE_KEY", "1a03b9e704bbc25e036db7715dfdf402");

class Index extends BasicControl
{
    // 极验API1
    public function getCaptcha(Request $request)
    {
        $ip = $request->ip();
        $uid = $request->get('uid');
        if (empty($uid)) {
            $uid = 0;
        }
        $GtSdk = new GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
        $data = array(
            "user_id" => $uid, # 网站用户id
            "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => $ip # 请在此处传输用户请求验证时所携带的IP
        );
        $status = $GtSdk->pre_process($data, 1);
        Session::set('gtserver', $status);
        Session::set('user_id', $data['user_id']);
        return $GtSdk->get_response_str();
    }

    // 极验API2
    public function checkCaptcha($data = [])
    {
//        var_dump($data);
//        var_dump($data['geetest_challenge']);
        $GtSdk = new GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
        $data2 = array(
            "user_id" => Session::get('user_id'), # 网站用户id
            "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => $data['ip'] # 请在此处传输用户请求验证时所携带的IP
        );
        if (Session::get('gtserver') == 1) {   //服务器正常
            $result = $GtSdk->success_validate($data['geetest_challenge'], $data['geetest_validate'], $data['geetest_seccode'], $data2);
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {  //服务器宕机,走failback模式
            if ($GtSdk->fail_validate($data['geetest_challenge'], $data['geetest_validate'], $data['geetest_seccode'])) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function index(Request $request)
    {
        $uid = Session::get('uid');
        $sublist = SubHandle::getMySubList($uid);
        $ticket = TicketHandle::getMyTicket($uid);
        $threedayleft = 0;// 3天内到期个数
        $avaliablenum = count($sublist['mysub']); //有效个数；计算方法：先复制全部数目，然后下面判断减去过期的
        foreach ($sublist['mysub'] as $item) {
            $leftday = (strtotime($item->expiretime) - time()) / 86400;
            if ($leftday > 0 && $leftday <= 3) {
                $threedayleft += 1;
            } else if ($leftday < 0) { //过期的，有效数目减去1
                $avaliablenum -= 1;
            }
        }
        $this->assign([
            'subcount' => $avaliablenum,
            'willexpire' => $threedayleft,
            'ticketcount' => count($ticket['tObj']),
            't3' => count($ticket['t3'])
        ]);
        return $this->fetch('index2');
    }

    public function register()
    {
        $webRt = IndexHandle::getRegSettings();
        $this->assign([
            'reg' => $webRt['webObj'][0],
        ]);
        return $this->fetch('register');
    }

    public function login()
    {
        $webRt = IndexHandle::getRegSettings();
        $this->assign([
            'login' => $webRt['webObj'][0],
        ]);
        return $this->fetch('login1');
    }

    public function account()
    {
        $uid = Session::get('uid');
        $record = UserHandle::getLoginRecordByUid($uid);
        $this->assign([
            'loginrecord' => $record['recordObj']
        ]);
        return $this->fetch('account');
    }

    public function charge()
    {
        $uid = Session::get('uid');
        $p = ChargeHandle::getAllMethod();
        $r = ChargeHandle::getRecordByUid($uid);
        $this->assign([
            'method' => $p['pObj'],
            'record' => $r['rObj'],
        ]);
//        var_dump($r['rObj']);
        return $this->fetch('charge');
    }

    public function ticket()
    {
        $uid = Session::get('uid');
        $t = TicketHandle::getMyTicket($uid);
        $agent = TicketHandle::getAgent(); //获取所有客服UID
        if (in_array($uid, $agent['agent'])) {//当前用户UID是否在客服UID列表里
            $this->assign([
                'agent' => true,
            ]);
        } else {
            $this->assign([
                'agent' => false,
            ]);
        }
        $this->assign([
            'ticket' => $t['tObj'],
            't0' => count($t['tObj']),
            't2' => count($t['t2']),
            't3' => count($t['t3']),
            't5' => count($t['t5'])
        ]);
        return $this->fetch('ticket');
    }

    public function agentTicket()
    {
        $uid = Session::get('uid');
        $agent = TicketHandle::getAgent(); //获取所有客服UID
        if (in_array($uid, $agent['agent'])) { //当前用户UID是否在客服UID列表里
            $t = TicketHandle::getAgentTicket($uid);
            $this->assign([
                'agent' => true,
                'ticket' => $t['tObj'],
            ]);
            return $this->fetch('agentTicket');
        } else {
            $this->redirect('/index/index/ticket');
        }
    }

    public function newTicket()
    {
        $token = md5(rand(1, 999));
        Session::set('token', $token);
        return $this->fetch('newticket');
    }

    public function viewTicket(Request $request)
    {
        $uid = Session::get('uid');
        $tid = $request->get('tid');
        $ticket = TicketHandle::getTicketByTid($tid);
        $token = md5(rand(1, 999));
        Session::set('token', $token);
        if (!$ticket['status']) {
            $this->error($ticket['message']);
        } else { //获取到订单信息
            $client = UserHandle::getuserinfo($ticket['tObj']->uid);
            $clientgroup = UserHandle::getGroupByid($client['userObj']->groupid);
            $reply = TicketHandle::getReplyByTid($tid);
            $agent = TicketHandle::getAgent(); //获取所有客服UID
            if (in_array($uid, $agent['agent'])) { //判断是否是客服模式
                $isagent = true;
                $this->assign([
                    'agent' => $isagent,
                ]);
            } else {
                $isagent = false;
                $this->assign([
                    'agent' => $isagent,
                ]);
            }
            if ($uid == $ticket['tObj']->uid) { //判断是否是自己的工单
                $this->assign([
                    'belong' => true
                ]);
            } else if ($isagent) { //不是自己的工单，判断是否是客服
                $this->assign([
                    'belong' => false
                ]);
            } else { //不是客服，非法操作
                $this->error('非法操作！', '/index/index/ticket');
            }
            $this->assign([
                't' => $ticket['tObj'],
                'reply' => $reply['rObj'],
                'clientavatar' => $this->getAvatar($ticket['tObj']->uid), //获取客户的头像
                'client' => $client['userObj'], // 客户的信息
                'clientgroup' => $clientgroup['groupObj'][0]
            ]);
            return $this->fetch('viewticket');
        }
    }

    public function editAcc()
    {
        return $this->fetch('editacc');
    }

    // 获取一条公告的对象
    public function getSingleAn(Request $request)
    {
        $id = $request->post('id');
        $announce = AnnounceModel::getSingleAnnounce($id);
        if ($announce['status']) {
            if ($announce['anObj']->status === '已发布') {
                return json_encode(array('status' => true, 'anObj' => $announce['anObj']));
            } else {
                return json_encode(array('status' => false));
            }
        } else {
            return json_encode(array('status' => false));
        }
    }

    public function mySubscription()
    {
        $uid = Session::get('uid');
        $mysub = SubHandle::getMySubList($uid);
        $this->assign([
            'mysub' => $mysub['mysub']
        ]);
        return $this->fetch('mysubscription');
    }

    public function bindGroup(Request $request)
    {
        $id = $request->post('id');
        $groupnum = $request->post('groupnum');
        $bRt = SubHandle::bindGroup($id, $groupnum);
        if ($bRt['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => false, 'message' => $bRt['message']));
        }
    }

    public function subscriptions()
    {
        $subRt = SubHandle::getSubList(); //获取所有订阅商品
        $uid = Session::get('uid');
        $mysub = SubHandle::getMySubList($uid);
        $this->assign([
            'sublist' => $subRt['subObj'],
            'mysub' => $mysub['mysub'],
            'mysubnum' => count($mysub['mysub'])
        ]);
        return $this->fetch('subscription');
    }

    public function order(Request $request)
    {
        $sid = $request->get('sid');
        $type = $request->get('type'); //续费
        $renewid = $request->get('renewid'); //续费
        // 用户组折扣
        $user = new User();
        $u = $user->getuserinfo(Session::get('uid'));
        $g = $user->getGroupInfo($u['userObj']->groupid);
        $groupdis = $g['groupObj']->groupdiscount;
        // 以上是获取用户组折扣
        $order = SubHandle::getOrderInfo($sid);
//        var_dump($order['subObj']);
        if ($sid === "" || $sid == null) {
            $this->error('订单信息错误', '/index/index/subscriptions');
        } elseif (!$order['status']) {
            $this->error('订单信息错误', '/index/index/subscriptions');
        } elseif ($type === 'renew') { //判断当前订单为续费
            //1、首先查看我的订阅列表是否存在这个商品且没过期
            $status = SubHandle::mySubExist($renewid, $sid);
            if ($status['status']) { //存在，判断是否到期
                $subinfo = $status['sub']['subObj'][0];
                $mysub = $status['mysub'][0];
                if (strtotime($mysub->expiretime) - time() > 0) {
                    // 没到期
                    $renewdiscount = $subinfo->renewdiscount;
                } else {
                    //已到期
                    $renewdiscount = 1;
                }
                $this->assign([
                    'sub' => $order['subObj'],
                    'price' => $order['priceObj'],
                    'renewdiscount' => $renewdiscount,
                    'groupdiscount' => $groupdis
                    // 前台要 $sub.0.sid 才能读取数据 必须要加0
                ]);
                return $this->fetch('order');
                // 目前订单页获取信息写好了，为了能更好使用 先写订单系统。
            } else { // 不存在 说明续费ID是伪造的
                $this->assign([
                    'sub' => $order['subObj'],
                    'price' => $order['priceObj'],
                    'renewdiscount' => 1,
                    'groupdiscount' => $groupdis
                    // 前台要 $sub.0.sid 才能读取数据 必须要加0
                ]);
                return $this->fetch('order');
                // 目前订单页获取信息写好了，为了能更好使用 先写订单系统。
            }
        } else {
            $this->assign([
                'sub' => $order['subObj'],
                'price' => $order['priceObj'],
                'renewdiscount' => 1,
                'groupdiscount' => $groupdis
                // 前台要 $sub.0.sid 才能读取数据 必须要加0
            ]);
            return $this->fetch('order');
            // 目前订单页获取信息写好了，为了能更好使用 先写订单系统。
        }
    }

    public function getPrice(Request $request)
    {
        $pid = $request->post('pid');
        $priceRt = OrderHandle::getPrice($pid);
//        var_dump($priceRt['price']);
        if ($priceRt['status']) {
            return json_encode(array('status' => true, 'price' => $priceRt['price']));
        } else {
            return json_encode(array('status' => false, 'message' => $priceRt['message']));
        }
    }

    public function checkCoupon(Request $request)
    {
        $coupon = $request->post('coupon');
        $sid = $request->post('sid');
        if (empty($sid)) {
            return json_encode(array('status' => false, 'message' => '订单信息错误！'));
        } else {
            if (!empty($coupon)) {
                $cp = OrderHandle::checkCoupon($coupon, $sid);
                if ($cp['status']) { //查询到存在优惠码
                    $currentdate = time();
                    $begintime = strtotime($cp['cpObj'][0]->begintime);
                    $expiretime = strtotime($cp['cpObj'][0]->expiretime);
                    // 判断是否生效 大于0表示还没到生效时间
                    if ($begintime - $currentdate > 0) {
                        return json_encode(array('status' => false, 'message' => '该优惠代码还未生效！'));
                    } // 已过生效时间（已生效)，判断是否过期，过期时间 - 现在时间 < 0 表示过期了
                    elseif ($expiretime - $currentdate < 0) {
                        return json_encode(array('status' => false, 'message' => '该优惠代码已过期！'));
                    } // 在有效期内，可用
                    else {

                        return json_encode(array('status' => true, 'cpObj' => $cp['cpObj']));
                    }
                } else {
                    return json_encode(array('status' => false, 'message' => $cp['message']));
                }
            }
        }
    }

    public function purchase(Request $request)
    {
        $uid = Session::get('uid');
        $email = Session::get('email');
        $sid = $request->post('sid');
        $pid = $request->post('pid');
        $coupon = $request->post('coupon');
        $type = $request->post('type');
        $renewid = $request->post('renewid'); //续费的ID
        // 用户组折扣
        $user = new User();
        $u = $user->getuserinfo(Session::get('uid'));
        $g = $user->getGroupInfo($u['userObj']->groupid);
        $groupdis = $g['groupObj']->groupdiscount;
        // 以上是获取用户组折扣
        $isrenew = false;
        $discount = 1;
        // 已收到订单
        // 判断是否是续费
        if ($type === 'renew') {
            $status = SubHandle::mySubExist($renewid, $sid);
            if ($status['status']) { //存在，判断是否到期
                $subinfo = $status['sub']['subObj'][0];
                $mysub = $status['mysub'][0];
                if (strtotime($mysub->expiretime) - time() > 0) {
                    // 没到期 到这里才是判断为续费
                    $discount *= $subinfo->renewdiscount;
                    $isrenew = true;
                } else {
                    //已到期
                    $discount += 0;
                }
            }
        }
        // 1、根据pid 获取价格信息
        $priceRt = OrderHandle::getPrice($pid);
        if ($priceRt['status']) {
            $method = $priceRt['price'][0]->method; //支付方式 积分/余额
            $price = $priceRt['price'][0]->price;  //原始价格
            $pname = $priceRt['price'][0]->pname;  // 价格名称
            $time = $priceRt['price'][0]->time; // 有效期
        } else {
            return json_encode(array('status' => false, 'message' => '系统错误，支付失败，请联系管理员！'));
        }
        // 2、获取优惠代码信息
        $cp = OrderHandle::checkCoupon($coupon, $sid);
        if ($cp['status']) { //查询到存在优惠码
            $currentdate = time();
            $begintime = strtotime($cp['cpObj'][0]->begintime);
            $expiretime = strtotime($cp['cpObj'][0]->expiretime);
            if ($expiretime - $currentdate > 0 && $currentdate - $begintime > 0) { // 优惠券可用
                $discount *= $cp['cpObj'][0]->discount; //优惠幅度
            }
        }
        // 3、确认最终价格信息
        $discount *= $groupdis;
        $price = $price * $discount; //最终价格
        // 4、读取用户余额并支付
        $user = OrderHandle::getUserCash($uid);
        if ($user['status']) {
            if ($method === '余额') { //余额支付
                $method = 'cash';
                $cash = $user['userObj'][0]->cash;
            } elseif ($method === '积分') {
                $method = 'credit';
                $cash = $user['userObj'][0]->credit;
            }
            $newcash = $cash - $price;
            if ($newcash < 0) { // 余额不足
                return json_encode(array('status' => false, 'message' => '账户余额不足！', 'pname' => $pname));
            } else { //这未来还可以判断用户状态，$user 返回了 status
                $payRt = OrderHandle::purchase($isrenew, $renewid, $uid, $sid, $method, $price, $time, $newcash, $pname, $email); //余额足够 扣款入库
                if ($payRt['status']) {
                    return json_encode(array('status' => true, 'pname' => $pname));
                } else {
                    return json_encode(array('status' => false, 'message' => $payRt['message']));
                }
            }
        }
    }

    public function buyRecord()
    {
        $uid = Session::get('uid');
        $rRt = OrderHandle::getBuyRecordById($uid);
        $this->assign([
            'record' => $rRt['recordObj']
        ]);
        return $this->fetch('buyrecord');
    }

    public function chargeDo(Request $request)
    {
        $uid = Session::get('uid');
        $email = Session::get('email');
        $methodid = $request->post('methodid');
        $chargecount = $request->post('chargecount'); // 乘100变成元
        $type = $request->post('type');
        $card = $request->post('card');
        $mid = null; // 接口ID
        $mkey = null; // 接口密钥
        $tkey = null; // 三方参数值
        if ($type === 'normal') {  // 接口充值
            if (!empty($chargecount) && !empty($methodid)) {
                $mRt = ChargeHandle::getMethodById($methodid);
                if ($mRt['status']) { // 获取到支付接口信息
                    switch ($mRt['pObj']->mname) {
                        case '有赞云支付':
                            $yzRt = (new Payment())->createYouZanOrder($uid, $chargecount);
                            if ($yzRt['status']) {
                                return json_encode(array('status' => true, 'uid' => $uid, 'method' => 'youzan', 'qrcode' => $yzRt['qrcode'], 'qrurl' => $yzRt['qrurl'], 'qrid' => $yzRt['qrid']));
                            } else {
                                return json_encode(array('status' => false, 'message' => $yzRt['message']));
                            }
                            break;
                    }
                }
            } else {
                return json_encode(array('status' => false, 'message' => '支付方式或支付金额非法！'));
            }

        } else if ($type === 'card') {
            $vRt = UserHandle::verifyCard($uid, $card, $email);
            if ($vRt['status']) {
                return json_encode(array('status' => true));
            } else {
                return json_encode(array('status' => false, 'message' => $vRt['message']));
            }
        } else {
            return json_encode(array('status' => false, 'message' => '支付方式或支付金额非法！'));
            //充值方式错误
        }
    }

    public function checkOrder(Request $request)
    {
        $orderid = $request->get('orderid'); // 这个id目前没用，预留吧
        $cRt = (new Payment())->checkOrder();
        if ($cRt['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => false));
        }
    }

    public function createTicket(Request $request)
    {
        if ($request->post('token') != Session::get('token')) {
            return json_encode(array('status' => false, 'message' => '非法操作！'));
        }
        $uid = Session::get('uid');
        $title = $request->post('title');
        if (strlen($title) > 50 || empty($title)) {
            return json_encode(array('status' => false, 'message' => '事件描述长度不合法！'));
        }
        $content = RemoveXSS($request->post('content'));
        if (strlen($content) < 20) {
            return json_encode(array('status' => false, 'message' => '事件内容长度不合法！'));
        }
        $phone = $request->post('phone');
        if (!preg_match('/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$/', $phone)) {
            return json_encode(array('status' => false, 'message' => '手机号码格式有误！'));
        }
        $tRt = TicketHandle::createTicket($uid, $title, $content, $phone);
        if ($tRt['status']) {
            Session::set('token', null);
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => false, 'message' => $tRt['message']));
        }
    }

    public function replyTicket(Request $request)
    {
        $uid = Session::get('uid');
        $tid = $request->post('tid');
        $token = $request->post('token');
        if ($token != Session::get('token')) {
            return json_encode(array('status' => false, 'message' => '非法操作！'));
        }
        $ticket = TicketHandle::getTicketByTid($tid);
        $content = RemoveXSS($request->post('content'));
        if (strlen($content) < 20) {
            return json_encode(array('status' => false, 'message' => '内容长度不合法！'));
        }
        $agent = TicketHandle::getAgent();
        if (!in_array($uid, $agent['agent'])) { //非客服，客户回复
            $type = 0; // 0客户回复，1管理员回复
        } else {
            $type = 1;
        }
        if ($uid == $ticket['tObj']->uid) { //判断是否是自己的工单
            //是自己的工单，如果状态为3（待您处理），则回复后要将状态改为2（处理中），否则不改状态
            $reply = TicketHandle::replyTickety($tid, $uid, $content, $type);
            if ($ticket['tObj']->status === 3 && $ticket['tObj']->status != 4 && $ticket['tObj']->status != 5) {
                $ticket = TicketHandle::changeStatus($tid, 2);
            }
            Session::set('token', null);
            return json_encode(array('status' => true));
        } else if ($uid == $ticket['tObj']->agent) { //不是自己的工单，判断是否是接单客服
            //是接单客服 ,如果订单状态不是3，则回复后将状态改为3
            $reply = TicketHandle::replyTickety($tid, $uid, $content, $type);
            if ($ticket['tObj']->status != 3 && $ticket['tObj']->status != 4 && $ticket['tObj']->status != 5) {
                $ticket = TicketHandle::changeStatus($tid, 3);
            }
            Session::set('token', null);
            return json_encode(array('status' => true));
        } else { //不是客服，非法操作
            return json_encode(array('status' => false, 'message' => '非法操作'));
        }
    }

    public function acceptTicket(Request $request)
    {
        $uid = Session::get('uid');
        $tid = $request->post('tid');
        $token = $request->post('token');
        if ($token != Session::get('token')) {
            return json_encode(array('status' => false, 'message' => '非法操作！'));
        }
        $agent = TicketHandle::getAgent();
        if (!in_array($uid, $agent['agent'])) {
            // 非客服,非法草走
            return json_encode(array('status' => false, 'message' => '非法操作'));
        } else {
            // 是客服，接单
            $aRt = TicketHandle::acceptTicket($tid, $uid);
            if ($aRt['status']) {
                return json_encode(array('status' => true));
            } else {
                return json_encode(array('status' => false, 'message' => $aRt['message']));
            }
        }
    }

    public function ticketRate(Request $request)
    {
        $uid = Session::get('uid');
        $tid = $request->post('tid');
        $token = $request->post('token');
        if ($token != Session::get('token')) {
            return json_encode(array('status' => false, 'message' => '非法操作！'));
        }
        $rate = $request->post('rate');
        if ($rate >= 1 && $rate <= 5) { //正常评分范围
            $rRt = TicketHandle::rate($tid, $rate);
            if ($rRt['status']) {
                return json_encode(array('status' => true));
            } else {
                return json_encode(array('status' => false, 'message' => $rRt['message']));
            }
        } else {
            return json_encode(array('status' => false, 'message' => '评分分值错误！'));
        }
    }

    public function closeTicket(Request $request)
    {
        $uid = Session::get('uid');
        $tid = $request->post('tid');
        $token = $request->post('token');
        if ($token != Session::get('token')) {
            return json_encode(array('status' => false, 'message' => '非法操作！'));
        }
        $ticket = TicketHandle::getTicketByTid($tid);
        $agent = TicketHandle::getAgent();
        if (!in_array($uid, $agent['agent'])) { // 检查是客户操作还是客服操作
            // 客户操作
            if ($uid == $ticket['tObj']->uid) { //判断是否是自己的工单
                //是自己的工单，如果状态不为4或5，则回复后要将状态改为4（待评价），否则不改状态
                if ($ticket['tObj']->status != 4 && $ticket['tObj']->status != 5) {
                    $ticket = TicketHandle::changeStatus($tid, 4);
                    Session::set('token', null);
                    return json_encode(array('status' => true));
                } else {
                    Session::set('token', null);
                    return json_encode(array('status' => false, 'message' => '该工单已进入结束流程'));
                }
            }
        } else if ($uid == $ticket['tObj']->agent) { //不是自己的工单，判断是否是接单客服
            //是接单客服 ,如果订单状态不是4或5，则回复后将状态改为4（待客户评价）
            if ($ticket['tObj']->status != 4 && $ticket['tObj']->status != 5) {
                $ticket = TicketHandle::changeStatus($tid, 4);
                Session::set('token', null);
                return json_encode(array('status' => true));
            } else {
                Session::set('token', null);
                return json_encode(array('status' => false, 'message' => '该工单已进入结束流程'));
            }
        } else {
            return json_encode(array('status' => false, 'message' => '非法操作'));
        }
    }
}
