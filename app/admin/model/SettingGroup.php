<?php
namespace app\admin\model;
use think\Model;

/**
 * 自动化模型的model模板文件
 */
class SettingGroup extends Model
{

    protected $autoWriteTimestamp = 'datetime';//开启自动写入时间字段
    protected $createTime = 'add_time'; //定义创建时间字段
    protected $updateTime = 'update_time'; //定义更新时间字段

        //获取器 值得转化
    public function getFormIdAttr($value)
    {
        if(!$value) return '暂无';
        if(strpos($value,',')){
            $arr = explode(',' ,$value);
            $res = $this->name('role')->where('id','in',$arr)->select();
            $return  = '';
            foreach($res as $k => $v){
               $return .= $v['form_title'].',';
            }
            return trim($return ,',');
        }else{
            return $this->name('custom_form')->where('id',$value)->value('form_title');
        }
        
    }

    //获取器 值得转化
    public function setFormIdAttr($value)
    {
        $value = is_array($value) ? join(',',$value) : $value;
        return $value;
    }
    //获取器 值得转化
    public function getRoleIdsAttr($value)
    {
        if(!$value) return '暂无';
        if(strpos($value,',')){
            $arr = explode(',' ,$value);
            $res = $this->name('role')->where('id','in',$arr)->select();
            $return  = '';
            foreach($res as $k => $v){
               $return .= $v['role_name'].',';
            }
            return trim($return ,',');
        }else{
            return $this->name('role')->where('id',$value)->value('role_name');
        }
        
    }

    //获取器 值得转化
    public function setRoleIdsAttr($value)
    {
        $value = is_array($value) ? join(',',$value) : $value;
        return $value;
    }


}