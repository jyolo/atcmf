<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-10
 * Time: 17:25
 */

namespace app\admin\model;

use think\Model;
use think\facade\Request;

class AdminLog extends Model
{
    protected $autoWriteTimestamp = 'datetime';//开启自动写入时间字段
    protected $createTime = 'action_time'; //定义创建时间字段
    protected $insert = ['manager_id','login_name','module','controller','action','method','client_ip'];

    protected function setManagerIdAttr(){
        return session('manager.id');
    }
    protected function setLoginNameAttr(){
        return session('manager.login_name');
    }
    protected function setModuleAttr(){
        return Request::module();
    }
    protected function setControllerAttr(){
        return Request::controller();
    }
    protected function setActionAttr(){
        return Request::action();
    }
    protected function setMethodAttr(){
        return Request::method();
    }
    protected function setClientIpAttr(){
        return Request::ip();
    }
}