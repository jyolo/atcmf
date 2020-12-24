<?php


namespace app\crm\validate;


use app\crm\model\ServiceRuleMenu;
use think\Validate;
use app\crm\model\ServiceGoods;


class ServiceGoodsRuleValidate extends Validate
{

    protected $rule = [
        'service_goods_id'  =>  'require|number|checkGoodsIdExists',
        'module_id'  =>  'require|checkRuleMenuIdExists',


    ];

    protected $message  =   [
        'service_goods_id.require' => '产品套餐id 必填',
        'service_goods_id.number' => '产品套餐id 必须是数字',
        'module_id.require'     => '权限功能id 必填',

    ];

    // 自定义验证规则 检查商品 套餐 id  是否存在
    protected function checkGoodsIdExists($value, $rule, $data=[])
    {

        $flag = ServiceGoods::where('id',intval($value))->column('id');
        if(!$flag) return  '套餐不存在';

        return true;

    }

    // 自定义验证规则 检查权限 id 是否有 不存在的
    protected function checkRuleMenuIdExists($value, $rule, $data=[])
    {

        $ids = explode(',',$value);
        $union = '';
        foreach ($ids as $k => $v){
            $union .= ' select '.$v.' as id from xfb_service_rule_menu union';
        }

        $union = rtrim($union,'union');

        $sql = 'select B.id from (
                    '.$union.'
                ) as B
                left join (
                    select id from xfb_service_rule_menu where id in ('.$value.')
                ) as A
                on A.id = B.id
                where A.id is null
                ';

        $res = ServiceRuleMenu::query($sql);
        if (count($res)){
            $notFoundIds = [] ;
            foreach($res as $k => $v){
                array_push($notFoundIds, $v['id']);
            }
            return join(',',$notFoundIds) . '  权限 id 不存在';
        }


        return true;


    }

}