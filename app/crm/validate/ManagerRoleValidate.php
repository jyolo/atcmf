<?php


namespace app\crm\validate;


use app\crm\model\ManagerRole;
use think\Validate;

class ManagerRoleValidate extends Validate
{
    protected $rule = [
        'role_name'  =>  'require|max:30',
    ];

    protected $message  =   [
        'role_name.require' => '角色名称必填',
        'role_name.max' => '角色名称最多30个字符',

    ];


}