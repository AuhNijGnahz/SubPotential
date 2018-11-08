<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/8/31
 * Time: 17:11
 */

namespace app\admin\controller;

use app\admin\model\UserModel;
use think\Controller;
use think\Request;
use app\admin\model\WebModel;
use app\admin\model\AnnounceModel;
use app\admin\model\Mail;

class WebControl extends BasicControl
{
    public function setWebSettings(Request $request)
    {
        //提取所有键名
        $title = $request->post('title');
        $subtitle = $request->post('subtitle');
        $url = $request->post('url');
        $seo = $request->post('seo');
        $desc = $request->post('describe');
        $qq = $request->post('qq');

        $setRt = WebModel::setWebSettings($title, $subtitle, $url, $seo, $desc, $qq); //basic是网站基本设置
        if ($setRt['status']) {
            $this->success('保存设置成功');
        } else {
            $this->error('保存设置失败，你并没有更改任何信息');
        }
    }

    public function getWebSettings()
    {
        $webRt = WebModel::getWebSettings();
        if ($webRt['status']) {
            return array('status' => true, 'webObj' => $webRt['webObj']); //返回给Index控制器，赋值变量
        } else {
            return array('status' => false);
        }
    }

    public function emailSetting()
    {
        $templet = WebModel::getEmailTemplet();
        $this->assign([
            'fromname' => config('MAIL_FROMNAME'),
            'fromaddress' => config('MAIL_FROMADDRESS'),
            'host' => config('MAIL_HOST'),
            'encript' => config('MAIL_ENCRIPTTYPE'),
            'port' => config('MAIL_PORT'),
            'username' => config('MAIL_SMTPUSER'),
            'password' => config('MAIL_SMTPPASS'),
            'reply' => config('MAIL_REPLYTO'),
            't' => $templet['webObj']
        ]);
        return $this->fetch('emailsetting');
    }

    public function purchaseSetting()
    {
        $p = WebModel::getAllPurchaseMethod();
        $this->assign([
            'method' => $p['pObj']
        ]);
        return $this->fetch('purchasesetting');
    }

    public function emailTest(Request $request)
    {
        $to = $request->post('to');
        $name = $request->post('name');
        $title = $request->post('title');
        $content = $request->post('content');
        $send = Mail::sendEmail($to, $name, $title, $content);
        if ($send['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => false, 'message' => $send['message']));
        }
    }

    public function basicSetting()
    {
        $webRt = $this->getWebSettings();
        if ($webRt['status']) {
            $this->assign([
                'web' => $webRt['webObj'],
                'title' => $webRt['webObj']->title,
                'subtitle' => $webRt['webObj']->subtitle,
                'seo' => $webRt['webObj']->seo,
                'describe' => $webRt['webObj']->sitedesc,
                'qq' => $webRt['webObj']->qq,
            ]);
        } else {
            $this->assign([
                'web' => null
            ]);
        }
        return $this->fetch('websetting');
    }

    public function regSetting()
    {
        $webRt = WebModel::getRegSettings();
        $group = UserModel::getAllUserGroup();
        $this->assign([
            'reg' => $webRt['webObj'][0],
            'group' => $group['groupObj']
        ]);
        return $this->fetch('regsetting');
    }

    public function setRegSettings(Request $request)
    {
        $regallow = $request->post('regpolicy');
        $groupid = $request->post('groupid');
        $emailactive = $request->post('emailactive');
        $showpolicy = $request->post('showpolicy');
        $policyname = $request->post('policyname');
        $regcap = $request->post('regcap');
        $forgetcap = $request->post('forgetcap');
        $logincap = $request->post('logincap');
        if ($emailactive != '0') {
            $emailactive = 1;
        }
        if ($showpolicy != '0') {
            $showpolicy = 1;
        }
        if ($regcap != '0') {
            $regcap = 1;
        }
        if ($forgetcap != '0') {
            $forgetcap = 1;
        }
        if ($logincap != '0') {
            $logincap = 1;
        }

        $webRt = WebModel::setRegSettings($regallow, $groupid, $emailactive, $showpolicy, $policyname, $logincap, $regcap, $forgetcap);
        $this->success('成功更新设置！');
    }

    public function announceSetting()
    {
        $list = AnnounceModel::getAnnounce('2');
        if ($list['status']) {
            $this->assign([
                'announcelist' => $list['anObj'],
                'ancount' => count($list['anObj'])
            ]);
        }
        return $this->fetch('announce');
    }

    public function addAnIndex()
    {
        return $this->fetch('addannounce');
    }

    public function addAnnounce(Request $request)
    {
        $title = $request->post('title');
        $content = $request->post('content');
        $author = $request->post('author');
        $status = $request->post('status');
        $addRt = AnnounceModel::addAnnounce($title, $author, $status, $content);
        if ($addRt['status']) {
            $this->success('新增公告成功！');
        } else {
            $this->error('新增公告失败！');
        }
    }

    public function editAnIndex(Request $request)
    {
        $anRt = AnnounceModel::getSingleAnnounce($request->get('id'));
        if ($anRt['status']) {
            $anObj = $anRt['anObj'];
            $this->assign([
                'id' => $anObj->id,
                'title' => $anObj->title,
                'status' => $anObj->status,
                'content' => $anObj->content,
                'author' => $anObj->author
            ]);
        }
        return $this->fetch('editannounce');
    }

    public function editAnnounce(Request $request)
    {
        $title = $request->post('title');
        $author = $request->post('author');
        $content = $request->post('content');
        $status = $request->post('status');
        $id = $request->post('id');
        $anRt = AnnounceModel::editAnnounce($id, $title, $author, $status, $content);
        if ($anRt['status']) {
            $this->success('成功编辑公告');
        } else {
            $this->error($anRt['message'], '/admin/webControl/announceSetting');
        }
    }

    public function changeStatus(Request $request)
    {
        $id = $request->get('id');
        $anRt = AnnounceModel::changeStatus($id);
        if ($anRt['status']) {
            $this->success('成功更改状态');
        } else {
            $this->error($anRt['message']);
        }
    }

    public function dAllAn(Request $request)
    {
        $id = $request->post('id');
        $id = explode(',', $id);
        if (empty($id)) $this->error('请勾选要删除的公告');
        for ($i = 0; $i < count($id); $i++) {
            $anRt = AnnounceModel::dSingleAn($id[$i]);
        }
        return json_encode(array('status' => true));
    }

    public function dSingleAn(Request $request)
    {
        $id = $request->post('id');
        $anRt = AnnounceModel::dSingleAn($id);
        if ($anRt['status']) {
            return json_encode(array('status' => true));
        } else {
            return json_encode(array('status' => false, 'message' => $anRt['message']));
        }
    }

    public function searchAn(Request $request)
    {
        $param = $request->post('param'); //条件
        $text = $request->post('text'); //要查找的内容
        $result = AnnounceModel::searchAn($param, $text);
        return json_encode(array('status' => true, 'anObj' => $result['anObj']));
    }

    public function setPurchaseMethod(Request $request)
    {
        $id = $request->post('id');
        $apiid = $request->post('apiid');
        $apikey = $request->post('apikey');
        $thiredkey = $request->post('thirdkey');
        $setRt = WebModel::setPurchaseMethod($id, $apiid, $apikey, $thiredkey);
        if ($setRt['status']) {
            $this->success('更改支付方案成功！');
        } else {
            $this->error($setRt['message']);
        }
    }
}