<?php


namespace app\crm\validate;


use app\crm\model\Enterprises;
use app\crm\model\ServiceGoods;
use think\Validate;

class ServiceApplyValidate extends Validate
{

    protected $rule = [
        'company_id'  =>  'require|number|isExistCompany',
        'goods_id'  =>  'require|number|isExistGoods',
        'apply_realname'  =>  'require|max:10',
        'apply_mobile'  =>  'require|mobile',
        'apply_email'  =>  'require|email',
        'real_pay_money' => 'checkRealPayMongy',
        'status' => 'number|max:1'
    ];

    protected $message  =   [
        'company_id.require' => '企业id必填',
        'company_id.number' => '企业id必须是数字',

        'goods_id.require' => '产品套餐id必填',
        'goods_id.number' => '产品套餐id必须是数字',

        'apply_realname.require'     => '申请人姓名必填',
        'apply_realname.max'        => '真实姓名最多10个字符',

        'apply_mobile.require'   => '申请人手机号必填',
        'apply_mobile.mobile'   => '申请人手机号不合法',

        'apply_email.require'  => '申请人邮箱必填',
        'apply_email.email'  => '申请人邮箱不合法',

        'status.number'  => '状态必须是数字',
        'status.max'  => '状态最多1个字符',

    ];

    protected function checkRealPayMongy($value ,$rule ,$data){

        if(floatval($value) < 0.00) return '真实付款金额不能为负数';
        return true;
    }

    protected function isExistCompany($value ,$rule ,$data){
        $company = Enterprises::where('id',$value)
            ->where('audited_time','>',0)
            ->where('record_status', '=', 2)
            ->where('status', '=', 1)
            ->find();

        if (!$company) return '企业不存在或者未通过审核';

        return true;
    }

    protected function isExistGoods($value ,$rule ,$data){
        $goods = ServiceGoods::where('id',intval($value))
            ->where('is_up',1)
            ->find();
        if (!$goods) return '商品不存在或者未上架';

        return true;
    }




}