<?php
namespace app\admin\model;
use think\Model;
use think\Db;
/**
 * 自动化模型的model模板文件
 */
class Menu extends Model
{

    protected $autoWriteTimestamp = 'datetime';//开启自动写入时间字段
    protected $createTime = 'add_time'; //定义创建时间字段
    protected $updateTime = 'update_time'; //定义更新时间字段
    protected $auto = ['path'];

        //获取器 值得转化
    public function setPidAttr($value)
    {
        if(!strlen($value)) return 0;
        return $value;
    }
    //获取器 值得转化
    public function getIsshowAttr($value)
    {
        $status = json_decode('["\u662f","\u5426"]',true);
        if($value == 1)return $status[0];
        return $status[1];
    }


    public function setPathAttr($value,$data){
            if(!isset($data['pid']))return $this->where('id', $data['id'])->value('path');
            //if($data['pid'] == 0 || $data['pid'] == null) return '0,';
            if($data['pid'] == 0 || $data['pid'] == null) return '0';
            $parent_path = $this->where('id', $data['pid'])->value('path');
            $parent_path = trim($parent_path ,',');
            //return $parent_path.','.$data['pid'] .',' ;
            return $parent_path.','.$data['pid']  ;

    }

    public function _save($post){

        $oldpath = $this->where('id','=',$post['id'])->value('path');
        $path = $this->setPathAttr($post['pid'],$post);
        //没有改变层级
        if($oldpath == $path)return $this->isUpdate(true)->save($post);

        $all_update_data = [];
        $son = $this->field('id,path')
            ->where('','exp','instr(path,",'.$post['id'].',")')
            ->select()->toArray();

        //组装子元素要更新的数据
        foreach($son as $k => $v){
            $new_path = str_replace($oldpath,$path ,$v['path']);
            $v['path'] = $new_path;
            $all_update_data[$k] = $v;
        }
        $parent_update = [
            'id' => $post['id'] ,
            'pid' => (intval($post['pid']) ?  $post['pid'] : 0) ,
            'path' => $path
        ];
        $parent_update = array_merge($post,$parent_update);
        //压入要更新parent的数据
        array_unshift($all_update_data , $parent_update );


        Db::startTrans();
        try{
            foreach($all_update_data as $k => $v){
                $flag = Db::table($this->getTable())->update($v);
                if(!$flag){
                    Db::rollback();
                    return false;
                }
            }
            Db::commit();
            return true;
        }catch (Exception $e){
            Db::rollback();
            throw new Exception($e->getMessage());
            return false;
        }


    }
}