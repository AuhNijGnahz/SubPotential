<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/9/2
 * Time: 11:08
 */

namespace app\index\model;

use think\exception\DbException;
use think\Model;
use app\admin\model\AnnounceModel;
use app\admin\model\WebModel;

class IndexHandle extends Model
{
    static function getWebSettings()
    {
        $web = new WebModel();
        $webObj = $web::getWebSettings();
        if ($webObj['status']) {
            return array('status' => true, 'webObj' => $webObj['webObj']); //找到了返回数据库对象
        } else {
            return array('status' => false);
        }
    }

    static function getAnnounce()
    {
        $web = new AnnounceModel();
        $anObj = $web::getAnnounce('1');
        return array('status' => true, 'anObj' => $anObj['anObj']); //找到了返回数据库对象
    }

    static function getRegSettings()
    {
        $webObj = new WebModel();
        $w = $webObj->table('ca_regsetting')->where('id', '=', 1)->select();
        return array('status' => true, 'webObj' => $w);
    }
}