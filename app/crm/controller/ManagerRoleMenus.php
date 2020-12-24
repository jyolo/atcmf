<?php
namespace app\crm\controller;



use app\crm\service\RuleMenuBuilder;
use app\crm\validate\ManagerRoleValidate;
use app\crm\model\ManagerRole as ManagerRoleModel;
use think\exception\ValidateException;


/**
 * @des 企业公告
 * @package app\crm\controller
 */
class ManagerRoleMenus extends Base
{

    /**
     * @OA\Get(path="/v1/manager_role_menus/init",
     *   tags={"权限菜单 [not_menu]"},
     *   summary="初始化权限菜单 ",
     *   security={{"api_key": {}}},
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function init(){

        $builder = new RuleMenuBuilder('crm');
        $tree = $builder->initMenu();
        return $this->jsonSuccess($tree);
    }

    /**
     * @OA\Get(path="/v1/manager_role_menus/getMenu",
     *   tags={"权限菜单 [not_menu]"},
     *   summary="获取左侧菜单 ",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="refreshCache",in="query",description="刷新缓存 默认0 ; 1 为刷新缓存",required=false,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="获取成功")
     * )
     */
    public function getMenu(){
        $refreshCache = intval(input('get.refreshCache',0)) ;
        $builder = new RuleMenuBuilder('crm');
        $menu = $builder->getAllMenuTree($refreshCache);
        return $this->jsonSuccess($menu);
    }

    /**
     * @OA\Get(path="/v1/manager_role_menus/getModuleTree",
     *   tags={"权限菜单 [not_menu]"},
     *   summary="获取所有权限节点 ",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="refreshCache",in="query",description="刷新缓存 默认0 ; 1 为刷新缓存",required=false,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="获取成功")
     * )
     */
    public function getModuleTree(){
        $refreshCache = intval(input('get.refreshCache',0)) ;

        $builder = new RuleMenuBuilder('crm');
        $menu = $builder->getModuleTree($refreshCache);
        return $this->jsonSuccess($menu);
    }


}
