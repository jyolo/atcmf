<?php


namespace app\admin\controller;

use app\admin\model\Menu;
use CMaker\Component;
use think\facade\View;

class Index
{
    public function index()
    {

        $where[] = ['isshow','=','1'];
        //不是超管则只显示已拥有的菜单
//        if(!in_array(1 ,explode(',',session('role.id')))){
//            $where[] = ['id','in',session('role.auth_ids')];
//        }

        //showSql();
        $config['treeData'] = Menu::where($where)->select()->toArray();

        $config['field'] = 'id,pid,name';

        $menu = Component::get_tree_array($config ,true ,true);

        View::assign('menu',$menu);
        return View::fetch();

    }

    public function main(){

        return View::fetch();
    }

    /**
     * 清空缓存
     */
    public function clear_cache(){
        $runtimePath = RUNTIME_PATH .  'cache';
        //异步同步前端 清除缓存
        if(config('runtime.domain')){
            AnsyncCurlTask('clearCache');
        }

        $ingore_dir = ['.','..','log'];
        $func = function ($dir) use(&$func ,$ingore_dir){
            $dirs = scandir($dir);
            foreach ($dirs as $k  => $v){
                if(in_array($v,$ingore_dir))continue;
                if(is_dir($dir.DIRECTORY_SEPARATOR.$v)) $func($dir.DIRECTORY_SEPARATOR.$v);
                is_file($dir.DIRECTORY_SEPARATOR.$v) ? unlink($dir.DIRECTORY_SEPARATOR.$v) : rmdir($dir.DIRECTORY_SEPARATOR.$v);
            }
        };

        $func($runtimePath);

        $this->success('清除成功');


    }
}