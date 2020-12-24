<?php

/**
 * 过滤空的数组元素
 * @param $arr
 * @return mixed
 */
function filterEmptyVars($arr){

    foreach($arr as $k => $v){
        if(!strlen(trim($v))){
            unset($arr[$k]);
        }
    }
    return $arr;

}

