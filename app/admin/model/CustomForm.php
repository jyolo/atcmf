<?php
namespace app\admin\model;
use think\Model;

/**
 * 自动化模型的model模板文件
 */
class CustomForm extends Model
{

    protected $autoWriteTimestamp = 'datetime';//开启自动写入时间字段
    protected $createTime = 'add_time'; //定义创建时间字段
    protected $updateTime = 'update_time'; //定义更新时间字段

    public function _find($id){
        $id = intval($id);
        $data = $this->alias('a')->join('custom_form_component b','b.form_id = a.id')->where([
            ['a.id' ,'=',$id] ,
            ['b.form_id' ,'=',$id]
        ])->order('sorts asc')->select()->toArray();
        return $data;
    }

    public function _save($post){
        $data['form_title'] = $post['form_title'];
        $this->startTrans();
        $this->isUpdate(false)->save($data);
        $form_id = $this->getLastInsID();

        if(!$form_id){
            $this->rollback();
            return false;
        }
        $component_data = [];
        foreach($post['setting'] as $k => $v){
            $setting = json_decode(cache($v) ,true);
            $component_data[$k]['component_name'] = $post['component_name'][$k];
            $component_data[$k]['action_name'] = $setting['action_name'];
            $component_data[$k]['sorts'] = $k;
            $component_data[$k]['form_id'] = $form_id;
            $component_data[$k]['setting'] = cache($v);
        }
        $custom_form_component_db = new CustomFormComponent();
        $flag = $custom_form_component_db->isUpdate(false)->saveAll($component_data);
        if(!$flag){
            $this->rollback();
            return false;
        }

        $this->commit();
        return true;
    }

    public function _edit($post){
        $update = $del = $add = [];
        foreach($post['setting'] as $k => $v){
            $setting = json_decode(cache($v) ,true);
            if(isset($post['component_id'][$k]) && intval($post['component_id'][$k])){
                $update[$k]['action_name'] = $setting['action_name'];
                $update[$k]['component_name'] = $post['component_name'][$k];
                $update[$k]['setting'] = cache($v);
                $update[$k]['sorts'] = $post['form_order'][$k];
                $update[$k]['id'] = $post['component_id'][$k];
            }else{
                $add[$k]['action_name'] = $setting['action_name'];
                $add[$k]['component_name'] = $post['component_name'][$k];
                $add[$k]['setting'] = cache($v);
                $add[$k]['form_id'] = $post['form_id'];
                $add[$k]['sorts'] = $post['form_order'][$k];
            }
        }

        $custom_form_component_db = new CustomFormComponent();
        $old = $custom_form_component_db->where('form_id','=',intval($post['form_id']))->column('id');
        $del = array_diff($old ,$post['component_id']);
        $this->startTrans();
        $flag = $this->isUpdate(true)->save(['form_title' => $post['form_title'] ,'id' => intval($post['form_id'])]);
        if(!$flag){
            $this->rollback();
            return false;
        }
        if(count($update)){
            $flag = $custom_form_component_db->saveAll($update);
            if(!$flag){
                $this->rollback();
                return false;
            }
        }
        if(count($del)){
            sort($del);
            //删除掉 setting 表中的设置选项
            $keys = $custom_form_component_db->where('id','in',$del)->column('action_name');
            $settingDb = new Setting();
            $flag = $settingDb->where('keys','in',$keys)->delete();
            if(!$flag){
                $this->rollback();
                return false;
            }
            $flag = $custom_form_component_db->where('id','in',$del)->delete();
            if(!$flag){
                $this->rollback();
                return false;
            }
        }
        if(count($add)){
            $flag = $custom_form_component_db->saveAll($add);
            if(!$flag){
                $this->rollback();
                return false;
            }
        }

        $this->commit();
        return true;
    }

    public function _del($id){
        $ids = [];
        if(is_array($id) && count($id)){
            $ids = $id;
        }else{
            array_push($ids,intval($id));
        }

        $where = [
            ['id' ,'in',$ids]
        ];


        $this->startTrans();
        showSql();
        $flag = $this->where($where)->delete();

        if(!$flag){
            $this->rollback();
            return false;
        }

        $custom_form_component_db = new CustomFormComponent();
        $component_db_where = [
            ['form_id' ,'in' ,$ids]
        ];
        $flag = $custom_form_component_db->where($component_db_where)->delete();
        if(!$flag){
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

}