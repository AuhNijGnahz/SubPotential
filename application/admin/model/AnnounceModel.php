<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/9/1
 * Time: 17:50
 */
namespace app\admin\model;

use think\exception\DbException;
use think\Model;

class AnnounceModel extends Model
{
    protected $table="ca_announce";
    static function getAnnounce($type){ //1 前段获取，2 后端获取
        switch ($type){
            case '1':
                try {
                    $list = AnnounceModel::all(['status'=>'已发布']);
                } catch (DbException $e) {
                }
                return array('status'=>true,'anObj'=>$list);
                break;
            case '2':
                try {
                    $list = AnnounceModel::all();
                } catch (DbException $e) {
                }
                return array('status'=>true,'anObj'=>$list);
        }
    }
    static function addAnnounce($title,$author,$status,$content){
        $announce = new AnnounceModel();
        $announce->title = $title;
        $announce->content = $content;
        $announce->author = $author;
        if ($status === 'true'){
            $status = '已发布';
        }
        else{
            $status = '未发布';
        }
        $announce->status = $status;
        $announce->pubdate = date('Y-m-d H:i:s');
        if($announce->save()){
            return array('status'=>true);
        }
        else{
            return array('status'=>false);
        }
    }
    static function getSingleAnnounce($id){
        try {
            $announce = AnnounceModel::get($id);
        } catch (DbException $e) {
        }
        if($announce){
            return array('status'=>true,'anObj'=>$announce);
        }
        else{
            return array('status'=>false);
        }
    }
    static function editAnnounce($id,$title,$author,$status,$content){
        try {
            $announce = AnnounceModel::get($id);
        } catch (DbException $e) {
        }
            if($announce){
                $announce->title = $title;
                $announce->content = $content;
                $announce->author = $author;
                if ($status === 'true'){
                    $status = '已发布';
                }
                else{
                    $status = '未发布';
                }
                $announce->status = $status;
                if($announce->save()){
                    return array('status'=>true);
                }
                else{
                    return array('status'=>false,'message'=>'系统错误，保存失败');
                }
            }
            else{
            return array('status'=>false,'message'=>'你要编辑的公告已经不存在了');
            }
    }
    static function changeStatus($id){
        try {
            $announce = AnnounceModel::get($id);
        } catch (DbException $e) {
        }
        if($announce){
            $status = $announce->status;
            if($status === '已发布'){
                $announce->status = '未发布';
            }
            else{
                $announce->status = '已发布';
            }
            if($announce->save()){
                return array('status'=>true);
            }
            else{
                return array('status'=>false,'message'=>'系统错误，保存失败');
            }
        }
        else{
            return array('status'=>false,'message'=>'该公告已经不存在了！');
        }
    }
    static function dSingleAn($id){
        try {
            $announce = AnnounceModel::get($id);
        } catch (DbException $e) {
        }
        if(!$announce){
            return array('status'=>false,'message'=>'该公告已经不存在了！');
        }
        elseif($announce->delete()){
            return array('status'=>true);
        }
        else{
            return array('status'=>false,'message'=>'系统错误，删除失败');

        }
    }
}