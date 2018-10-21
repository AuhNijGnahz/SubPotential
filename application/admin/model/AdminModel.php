<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/8/31
 * Time: 15:25
 */
namespace app\admin\model;

use think\exception\DbException;
use think\Model;

class AdminModel extends Model
{
    protected $table = 'ca_admin';
    static function adminLogin($username,$password){
        try {
            $admin = AdminModel::get([
                'username' => $username,
                'password' => $password
            ]);
        } catch (DbException $e) {
        }
        if ($admin){
            return array('status'=>true,'adminObj'=>$admin);
        }
        else{
            return array('status'=>false);
        }
    }
}