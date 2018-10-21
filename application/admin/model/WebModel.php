<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/8/31
 * Time: 17:49
 */

namespace app\admin\model;

use app\admin\controller\WebControl;
use think\exception\DbException;
use think\Model;

class WebModel extends Model
{
    protected $table = 'ca_settings';

    static function getWebSettings()
    {
        try {
            $webObj = WebModel::get(1);
        } catch (DbException $e) {
        }
        if ($webObj) {
            return array('status' => true, 'webObj' => $webObj); //找到了返回数据库对象
        } else {
            return array('status' => false);
        }
    }

    static function setWebSettings($title, $subtitle, $url, $seo, $sitedesc, $qq)
    {
        try {
            $webObj = WebModel::get(1);
            $webObj->title = $title;
            $webObj->subtitle = $subtitle;
            $webObj->url = $url;
            $webObj->seo = $seo;
            $webObj->sitedesc = $sitedesc;
            $webObj->qq = $qq;

        } catch (DbException $e) {
        }

        try {
            if ($webObj->save()) {
                return array('status' => true);
            } else {
                return array('status' => false);
            }
        } catch (\Exception $e) {
        }
    }

    static function getEmailTemplet()
    {
        $webObj = new WebModel();
        $w = $webObj->table('ca_templet')->select();
        return array('status' => true, 'webObj' => $w);
    }

    static function setRegSettings($regallow, $groupid, $emailcheck, $showpolicy, $policyname, $logincap, $regcap, $findcap)
    {
        $webObj = new WebModel();
        $w = $webObj->table('ca_regsetting')->where('id', '=', 1)->update([
            'regallow' => $regallow,
            'groupid' => $groupid,
            'emailcheck' => $emailcheck,
            'showpolicy' => $showpolicy,
            'policyname' => $policyname,
            'logincap' => $logincap,
            'regcap' => $regcap,
            'findcap' => $findcap
        ]);
        return array('status' => true);
    }

    static function getRegSettings()
    {
        $webObj = new WebModel();
        $w = $webObj->table('ca_regsetting')->where('id', '=', 1)->select();
        return array('status' => true, 'webObj' => $w);
    }

    static function getAllPurchaseMethod()
    {
        $purchase = new WebModel();
        $p = $purchase->table('ca_purchasemethod')->select();
        return array('status' => true, 'pObj' => $p);
    }

    static function setPurchaseMethod($id, $secureid, $securekey, $thirdkey)
    {
        $purchase = new WebModel();
        $p = $purchase->table('ca_purchasemethod')->where('id', '=', $id)->update([
            'secureid' => $secureid,
            'securekey' => $securekey,
            'thirdkey' => $thirdkey
        ]);
        if ($p) {
            return array('status' => true);
        } else {
            return array('status' => false, 'message' => '更改失败，数据未改变！');
        }
    }
}