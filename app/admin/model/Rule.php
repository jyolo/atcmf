<?php
/**
 * Created by PhpStorm.
 * User: jyolo
 * Date: 2017/2/8
 * Time: 17:49
 */

namespace app\admin\model;


use think\Model;

class Rule extends Model
{
    public function getList(){
        $list = $this->field('rule_title,module,controller')->group('controller')->select();
        $list = toArray($list);
        foreach($list as $k => $v){
            //p($v);
           $son = $this->where(
                [
                    'module' => $v['module'],
                    'controller' => $v['controller'],
                ]
            )->select();
            $list[$k]['son'] = toArray($son);
        }
        
        return $list;
    }
}