<?php
namespace app\admin\model;
use think\Model;

/**
 * 自动化模型的model模板文件
 */
class Setting extends Model
{
    protected $pk = 'keys';

    public function _getAll(){
        $res = $this->select()->toArray();
        $set = [];
        foreach($res as $k => $v){
            $set[$v['keys']] = $v['values'];
        }
        return $set;
    }

    public function _save($post){
        $insert = $update = [];

        $i = 0;
        foreach($post as $k => $v){
            $isset = $this->where('keys','=',$k)->count('keys');
            if(!$isset){
                $insert[$i]['keys'] = $k;
                $insert[$i]['values'] = is_array($v) ? json_encode($v) : $v;
            }else{
                $update[$i]['keys'] = $k;
                $update[$i]['values'] = is_array($v) ? json_encode($v) : $v;
            }
            $i++;
        }

        $this->startTrans();

        if(count($update)){
            $flag = $this->isUpdate(true)->saveAll($update);
            if(!$flag){
                $this->rollback();
                return false;
            }
        }

        if(count($insert)){
            $flag = $this->insertAll($insert);
            if(!$flag){
                $this->rollback();
                return false;
            }
        }
        //缓存设置
        $all = array_merge($update,$insert);
        $at_setting_cache = [];
        foreach($all as $k => $v){
            $at_setting_cache[$v['keys']] = $v['values'];
        }
        cache('at_setting',$at_setting_cache,0);

        $this->commit();
        return true;

    }


    protected function setValuesAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    protected function getValuesAttr($value)
    {
        return json_decode($value,true) ? json_decode($value,true) : $value;
    }


}