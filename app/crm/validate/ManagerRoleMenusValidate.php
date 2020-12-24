<?php


namespace app\crm\validate;


use app\crm\model\ManagerRoleMenus as ManagerRoleMenusModel;
use app\crm\model\Manager;
use app\crm\model\ManagerRole;
use app\crm\model\ManagerRoleMap;
use think\Validate;

class ManagerRoleMenusValidate extends Validate
{
    protected $rule = [
        'role_id'  =>  'require|ifRoleExists',
        'menu_ids'  =>  'require|ifMenusExists',
    ];

    protected $message  =   [
        'role_id.require' => '角色 id必填',
        'menu_ids.max' => '权限菜单id 必填',
    ];

    protected function ifRoleExists($value ,$rule ,$data){
        $isset = ManagerRole::where('id',$value)->find();
        if (!$isset) return '角色 id不存在';
        return true;
    }



    protected function ifMenusExists($value ,$rule ,$data){
        $arr = explode(',',$value);

        $isset = ManagerRoleMenusModel::whereIn('id',$arr)->column('id');
        if(count($arr) != count($isset)) {
            $diff = array_diff($arr,$isset);
            return join(',',$diff) . '  id 权限菜单不存在';
        }
        return true;
    }


}