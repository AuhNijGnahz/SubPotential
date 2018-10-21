<?php
namespace app\admin\controller;

use think\Request;
use app\admin\model\AdminModel;
use app\admin\controller\WebControl;
use think\Session;
use app\admin\controller\BasicControl;

class Index extends BasicControl
{
    public function index(Request $request){
            return $this->fetch();
    }
    public function chart(){
        return $this->fetch();
    }
    public function adminLogin(){
        return $this->fetch('login');
    }
}
