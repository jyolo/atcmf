<?php
/**
 * Created by PhpStorm.
 * User: jyolo
 * Date: 2017/2/6
 * Time: 10:22
 */

namespace app\admin\model;


use think\Model;

class Form extends Model
{
    protected $autoWriteTimestamp = 'datetime';//开启自动写入时间字段
    protected $createTime = 'add_time'; //定义创建时间字段
    protected $updateTime = 'update_time'; //定义更新时间字段

    protected function setHtmlAttr($value){
        return htmlentities($value);
    }

}