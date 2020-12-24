<?php
/**
 * Created by PhpStorm.
 * User: jyolo
 * Date: 2017/2/8
 * Time: 17:49
 */

namespace app\admin\model;


use think\Model;

class Role extends Model
{
    protected $autoWriteTimestamp = 'datetime';//开启自动写入时间字段
    protected $createTime = 'add_time'; //定义创建时间字段
    protected $updateTime = 'update_time'; //定义更新时间字段



    //获取单个数据
    public function getOne($where,$field = ['*']){

        if(gettype($where) !== 'array' ){
            $where = ['id' => $where];
        }

        $return = $this->where($where)->field($field)->find();
        $return = toArray($return);
        return $return;
    }

    public function getRule($id){
        $res = $this->getOne(['id' => $id],'rule_ids');

        $rule = new Rule();
        $rules = $rule->field('module,controller,action')->where('id in('.$res['rule_ids'].')')->select();
        $rules = toArray($rules);
        if($rules) return $rules;
        return false;

    }
}