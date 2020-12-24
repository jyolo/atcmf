<?php


namespace app\crm\validate;


use think\Validate;

class ServiceModuleValidate extends Validate
{

    protected $rule = [
        'app_name'  =>  'require|max:25',
        'controller_desc'  =>  'require',
        'controller_name'  =>  'require',
        'method_desc'  =>  'require',
        'method_name'  =>  'require',

    ];

    protected $message  =   [
        'app_name.require' => '应用名称必填',
        'app_name.max' => '应用名称最长25个字符',
        'controller_desc.require'     => '控制器功能描述必填',
        'controller_name.require'   => '控制器名称必填',
        'method_desc.require'  => '方法功能描述必填',
        'method_name.require'        => '方法名称必填',
    ];

}