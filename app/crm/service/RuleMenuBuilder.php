<?php


namespace app\crm\service;

use app\crm\model\ManagerRoleMenus;
use app\crm\model\ServiceRuleMenu as ServiceRuleMenuModel;
use app\crm\model\RuleMenu as RuleMenuModel;
use think\Exception;
use think\exception\HttpException;
use think\facade\Cache;
use think\facade\Db;

/**
 * 权限菜单 / 权限节点
 * Class RuleMenu
 * @package app\crm\service
 */
class RuleMenuBuilder
{
    private const APP_LIST = [
        'crm' => ManagerRoleMenus::class,
        'company' => ServiceRuleMenuModel::class
    ];
    private $swaggerArr ;
    private $topMenu = [] ;
    public $appName ;

    public function __construct($appName)
    {
        if (!in_array($appName,array_keys(self::APP_LIST))) throw new Exception('未知的应用');
        $scanPath = root_path() . 'app/'.$appName;
        $s = \OpenApi\scan($scanPath);
        $this->appName = $appName;
        $this->swaggerArr = json_decode($s->toJson(),true);
        $modelClass = self::APP_LIST[$appName];
        $this->model = (new $modelClass());
        $this->menuCacheKey = $appName.'_rule_menu';
        $this->moduleCacheKey = $appName.'_module_tree';
    }

    /**
     * 获取树形菜单数据结构
     */
    public function getAllMenuTree($reCache = 0){

        $data = Cache::get($this->menuCacheKey);
        if($data && $reCache === 0) return $data;

        $data = $this->model->field('id,pid,menu_name,sort,is_show')->where([
            ['is_show' ,'=' ,1],
            ['pid' ,'=' ,0],
        ])->order('sort','desc')->select()->toArray();

        foreach($data as $k => $v){
            $sub = $this->model->field('id,pid,menu_name,sort,is_show')->where([
                ['is_show' ,'=' ,1],
                ['pid' ,'=' , $v['id']],
            ])->order('sort','desc')->select()->toArray();
            $data[$k]['son'] = $sub;
        }

        Cache::set($this->menuCacheKey,$data);

        return $data;

    }

    /**
     * 获取树形权限功能节点
     */
    public function getModuleTree($reCache = 0){

        $data = Cache::get($this->moduleCacheKey);

        if($data && $reCache === 0) return $data;

        $data = $this->model->field('id,pid,menu_name,sort,is_show')->where([
            ['un_check' ,'=' ,0],
            ['pid' ,'=' ,0],
        ])->order('sort','desc')->select()->toArray();

        foreach($data as $k => $v){
            $sub = $this->model->field('id,pid,menu_name,sort,is_show')->where([
                ['un_check' ,'=' ,0],
                ['pid' ,'=' , $v['id']],
            ])->order('sort','desc')->select()->toArray();
            $data[$k]['son'] = $sub;
        }

        Cache::set($this->moduleCacheKey,$data);

        return $data;

    }



    /**
     * 初始化权限菜单
     */
    public function initMenu(){

        try{
            Db::startTrans();

            $this->buildTopMenu();
            $this->buildSubMenu();

            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            return $e->getMessage();
//            throw new HttpException($e->getMessage());
        }

    }

    private function getTopMenuNodeInfo(){
        $topNode = $topNodeTemp = [];

        foreach($this->swaggerArr['paths'] as $k => $v){
            $v = array_values($v)[0];

            $data = [];
            if(strpos($v['tags'][0],'[not_menu]') !== false){
                $data['pid'] = $data['is_show'] = 0;
                $data['menu_name'] = str_replace(['[not_menu]',' '],'',$v['tags'][0]);
            }else{
                $data['pid'] = 0;
                $data['is_show'] = 1;
                $data['menu_name'] = $v['tags'][0];
            }
            $data['app_name'] = $this->appName;
            $controller = explode('::',$v['operationId'])[0];
            $controllerName = explode('\\',$controller);
            $controllerName = array_pop($controllerName);
            $data['controller_name'] = [];
            $topNodeTemp[$data['menu_name'] .'_'. $controllerName] =  [];
            $topNode[$data['menu_name']] = $data;
        }

        foreach($topNodeTemp as $k => $v){
            $var = explode('_',$k);
            foreach ($topNode as $sk => $sv){
                if($var[0] == $sk){
                    array_push($topNode[$sk]['controller_name'],$var[1]);
                }
            }
        }
        unset($topNodeTemp);
        foreach($topNode as $k =>$v){
            $topNode[$k]['controller_name'] = join(',',$topNode[$k]['controller_name']);
        }

        sort($topNode);
        return $topNode;
    }

    private function getSubMenuInfo(){
        $node = [];
        foreach($this->swaggerArr['paths'] as $k => $v){

            $v = array_values($v)[0];
            $data = [] ;
            $data['app_name'] = $this->appName;
            $top_menu_name =str_replace(['[not_menu]',' '],'',$v['tags'][0]);
            $data['pid'] = $this->topMenu[$top_menu_name];
            $oprateId = explode('::',$v['operationId']);
            $data['controller_name'] = $oprateId[0];
            preg_match('/\[(\S+)\]/',$v['summary'],$match);
            if($match){
                $data['menu_name'] = $match[1] ;
                $data['is_show'] = 1;
            }else{
                $data['menu_name'] = $v['summary'];
                $data['is_show'] = 0 ;
            }

            $data['method_name'] = $oprateId[1];
            $data['request_route'] = $k;


            array_push($node,$data);

        }

        return $node;

    }

    /**
     * 创建一级菜单
     */
    private function buildTopMenu(){
        $topNode = $this->getTopMenuNodeInfo();


        foreach($topNode as $k => $v){

            $isset = $this->model->field('id,menu_name,controller_name')
                ->whereOr('menu_name',$v['menu_name'])
                ->whereOr('controller_name',$v['controller_name'])
                ->find();


            if($isset){
                $this->model->where([
                    ['id','=',$isset['id']],
                ])->save($v);
            }else{
                $v['create_time'] = $v['update_time'] = date('Y-m-d H:i:s');
                $this->model->insert($v);

            }
        }



        $topMenu = $this->model->field('id,menu_name')->where([
            ['pid','=',0],
        ])->select()->toArray();
        $this->topMenu = [];
        foreach($topMenu as $k => $v){
            $this->topMenu[$v['menu_name']] = $v['id'];
        }

    }

    /**
     * 创建二级菜单
     */
    private function buildSubMenu(){
        $subNode = $this->getSubMenuInfo();
//        p($subNode);
//        throw new Exception('zzz');
        foreach($subNode as $k => $v){

            $isset = $this->model->where([
                ['request_route','=',$v['request_route']],
                ['pid','>',0],
            ])->find();

            if($isset){
                $this->model->where([
                    ['id','=',$isset['id']],
                ])->update($v);
            }else{
                $v['create_time'] = $v['update_time'] = date('Y-m-d H:i:s');
                $this->model->insert($v);

            }
        }

    }

}