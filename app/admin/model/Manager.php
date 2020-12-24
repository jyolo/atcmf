<?php
namespace app\admin\model;
use think\Model;

/**
 * 自动化模型的model模板文件
 */
class Manager extends Model
{

    protected $autoWriteTimestamp = 'datetime';//开启自动写入时间字段
    protected $createTime = 'add_time'; //定义创建时间字段
    protected $updateTime = 'update_time'; //定义更新时间字段

    protected $encrypt = null;
    protected $insert = ['encrypt'];
    protected $update = ['encrypt'];


//    public static function init()
//    {
//        self::event('before_update',function($data){
//            p($data);
//            die();
//        });
//    }

    //自动完成
    protected function setRoleIdAttr($value)
    {
        if(!$value) return 0;
        return join(',',$value);
    }
    protected function setLoginPwdAttr($value,$data)
    {
        //过滤所有的空白字符（空格、全角空格、换行等）
        $search = array(" ","　","\n","\r","\t");
        $replace = array("","","","","");
        $value = str_replace($search, $replace, $value);
        //没有修改则返回原来的值
        if(strlen($value) == 0){
            $old = $this->field('login_pwd,encrypt')->where($this->pk .' = '. $data[$this->pk])->find();

            $this->encrypt = $old['encrypt'];
            return $old['login_pwd'];
        }else{
            $this->encrypt = uniqid('',true);
            $pwd = md5(md5($value).$this->encrypt);
            return $pwd;
        }





    }
    protected function setEncryptAttr($value,$data)
    {
        return $this->encrypt;
    }


}