<?php


namespace app\crm\validate;


use app\crm\model\Manager;
use app\crm\model\ManagerRole;
use app\crm\model\ManagerRoleMap;
use think\Validate;

class ManagerRoleMapValidate extends Validate
{
    protected $rule = [
        'manager_id'  =>  'require|ifManagerExists',
        'role_ids'  =>  'require|MaxRoleId|ifRoleExists',
    ];

    protected $message  =   [
        'manager_id.require' => '管理员id必填',
        'role_ids.max' => '角色id 必填',
    ];

    protected function ifManagerExists($value ,$rule ,$data){
        $isset = Manager::where('id',$value)->find();
        if (!$isset) return '管理员id不存在';
        return true;
    }

    protected function MaxRoleId($value ,$rule ,$data){
        $arr = explode(',',$value);
        if(count($arr) > 2) return '一个管理员最多绑定2个角色';
        return true;
    }

    protected function ifRoleExists($value ,$rule ,$data){
        $arr = explode(',',$value);

        $isset = ManagerRole::whereIn('id',$arr)->column('id');

        if(count($arr) != count($isset)) {
            $diff = array_diff($arr,$isset);
            return join(',',$diff) . '  id 角色不存在';
        }

        return true;
    }


}