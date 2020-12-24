<?php
namespace app\crm\controller;


use app\crm\service\RuleMenu;
use app\crm\service\RuleMenuBuilder;
use think\Db;
use think\Exception;
use think\exception\ValidateException;
use app\crm\validate\ServiceModuleValidate;
use app\crm\model\ServiceRuleMenu as ServiceRuleMenuModel;
use app\crm\model\ServiceGoodsRule as ServiceGoodsRuleModel;
use think\Request;

/**
 * @des 企业客服权限菜单
 * @package app\crm\controller
 */
class ServiceRuleMenu extends Base
{

    /**
     * @OA\Get(path="/v1/service_rule_menu/lists",
     *   tags={"套餐权限管理 [not_menu]"},
     *   summary="获取所有企业客服应用权限菜单节点",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="refreshCache",in="query",description="刷新缓存 [1 , 0 ] 默认 0",required=false,@OA\Schema(type="string")),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function lists(){
        $refreshCache = (int) input('refreshCache',0);
        $ruleMenu = new RuleMenuBuilder('company');
        $data = $ruleMenu->getAllMenuTree($refreshCache);
        return ($data ) ?  $this->jsonSuccess($data,'success') : $this->jsonError($data);

    }

    /**
     * @OA\Post(path="/v1/service_rule_menu/init",
     *   tags={"套餐权限管理 [not_menu]"},
     *   summary="初始化/刷新 企业客服权限菜单节点",
     *   security={{"api_key": {}}},
     *   @OA\Response(response="200",description="同步成功")
     * )
     */
    public function initMenu(){

//        $ruleMenu = new RuleMenuBuilder('company');
//        $data = $ruleMenu->initMenu();
//        return ($data === true) ?  $this->jsonSuccess($data,'success') : $this->jsonError($data);
        return $this->jsonError('关闭自动化; 需手动修改表');
    }






}
