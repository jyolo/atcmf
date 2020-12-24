<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Request;
use think\exception\HttpResponseException;
use think\facade\Db;


//获取唯一的id
function get_uniqid() {
    $id = uniqid('',true).uniqid('',true);
    return md5($id);
}


//模板中调用
function FormMaker($type){
    return FormMaker::build($type);
    //return FormMaker::build($type);
}



//获取js脚本的钩子
function FormMakerStaticHook(){
    return FormMaker::getStaticHook();
}


//监听sql语句 需在sql语句之前调用
function showSql(){
    Db::listen(function($sql, $time, $explain){
        // 记录SQL
        echo $sql. ' ['.$time.'s]';
        // 查看性能分析结果
        dump($explain);
    });
}


/**
 * 解析post where 条件
 */
function parseWhere($postWhere){
    $where = [];

    foreach($postWhere as $k => $v){

        if(is_array($v)){ //数组的形式 where[open_time][between time][]

            array_walk($v,function($sv ,$sk)use($k,$v,&$where){

                //范围选择 两个值都为true  between
                if(strlen($sv[0]) && strlen($sv[1])){

                    $where[$k] = [$sk,[$sv[0] ,$sv[1]] ];
                }


                //范围选择 第一个值为true  > 大于
                if(strlen($sv[0]) && !strlen($sv[1])){
                    $where[$k] = ['>',$sv[0] ];
                }
                //范围选择 第二个值为true  < 小于
                if(!strlen($sv[0]) && strlen($sv[1])){
                    $where[$k] = ['<',$sv[1] ];
                }
            });


        }else{ //非数组的形式 where[admin_name]
            if(strlen($v)){
                //如果是自动生成的path 字段则左右两侧加上逗号
                $where[] = ['' ,'exp' ,'instr('.$k.',\''.$v.'\')'];
            }
        }



    }

    return $where;
}





/**
 * 随机字符串生成
 * @param $length
 * @return null|string
 */
function getRandChar($length = 4){
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol)-1;

    for($i=0;$i<$length;$i++){
        $str .= $strPol[mt_rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return $str;
}

/**
 * 返回封装后的API数据到客户端
 * @access protected
 * @param mixed     $data 要返回的数据
 * @param integer   $code 返回的code
 * @param mixed     $msg 提示信息
 * @param string    $type 返回数据格式
 * @param array     $header 发送的Header信息
 * @return void
 */
function result($data, $code = 1, $msg = '', $type = '', array $header = []){
    if(isset($data['count'])){
        $count = $data['count'];
        unset($data['count']);
    }else{
        $count = 0;
    }
    $result = [
        'code' => $code,
        'msg'  => $msg,
        'time' => $_SERVER['REQUEST_TIME'],
        'data' => $data,
        'count' =>$count ,
    ];

    $isAjax = Request::isAjax();

    $ResponseType =  $isAjax ? Config::get('default_ajax_return') : Config::get('default_return_type');

    $type     = $type ?: $ResponseType;
    $response = Response::create($result, $type)->header($header);
    throw new HttpResponseException($response);
}


function get_custom_form($form_id){

    $res = Db::name('custom_form_component')
        ->alias('a')
        ->field('action_name,component_name,setting,b.values')
        ->leftJoin('setting b','a.action_name = b.keys')
        ->where('form_id','=',intval($form_id))->order('sorts asc')->select();

    $str = '';

    foreach($res as $k => $v){
        $set = json_decode($v['setting'] ,true);
        $obj = CMaker\Maker::build($v['component_name']);
        $obj->name($v['action_name']);
        $obj->value($v['values']);
        foreach($set['base'] as $sk => $sv){
            $obj->$sk($sv);
        }
        $str .= $obj->render();
    }

    return $str;

}

/**
 * 获取系统配置
 * @param $key
 * @return string
 */
function _config($key){
    $at_setting = cache('system_setting');
    if(!$at_setting){
        $all_config = Db::name('setting')->select();
        if(!$all_config) return '';
        $at_setting = [];
        foreach($all_config as $k => $v){
            $at_setting[$v['keys']] = $v['values'];
           // $all_config[$v['keys']] = $v['values'];
        }
        cache('system_setting',$at_setting);
    }

    return key_exists($key,$at_setting) ? $at_setting[$key] : '';
}

/**
 * 修复在php 7.2 中 count 不是数组 就会报错的问题
 */
function _count($var){
    if(is_array($var)){
        return count($var);
    }

    return 0 ;

}

function getNow(){
    return date('Y-m-d H:i:s' ,time());
}

