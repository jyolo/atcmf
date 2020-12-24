<?php


namespace app\crm\validate;


use think\Validate;

class ServiceGoodsValidate extends Validate
{

    protected $rule = [
        'name'  =>  'require|min:3|max:12',
        'type'  =>  'require|number|max:2',
        'expiry_type'  =>  'require|number|max:2',
        'expiry_num'  =>  'require|number|max:2',
        'desc'  =>  'require|min:6|max:200',

    ];

    protected $message  =   [
        'name.require' => '产品套餐名称必填',
        'name.max' => '产品套餐名称最长12个字符',
        'name.min' => '产品套餐名称最少3个字符',

        'type.require'     => '产品套餐类型必填',
        'type.number'     => '产品套餐类型必须是数字',
        'type.max'     => '产品套餐类型最多2个字符',

        'expiry_type.require'   => '有效期类型必填',
        'expiry_type.number'   => '有效期类型必须是数字',
        'expiry_type.max'   => '有效期类型最多2个字符',

        'expiry_num.require'  => '有效期时长必填',
        'expiry_num.number'  => '有效期时长必须是数字',
        'expiry_num.max'  => '有效期时长最多2个字符',

        'desc.require'        => '产品套餐描述必填',
        'desc.min'        => '产品套餐描述名最少3个字符',
        'desc.max'        => '产品套餐描述名最多200个字符',
    ];

}