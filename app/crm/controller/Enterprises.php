<?php
namespace app\crm\controller;


use app\crm\controller\Base;
use app\crm\model\Enterprises as EnterprisesModel;
use app\crm\model\EnterprisesPrivate;
use app\crm\model\Manager;
use Auth\MemberLib;
use Jwt\Exception\TokenExistsException;
use Jwt\JwtToken;
use think\facade\Cache;
use think\facade\Request;


/**
 * @des 企业公告
 * @package app\crm\controller
 */
class Enterprises extends Base
{

    /**
     * @OA\Get(path="/v1/enterprises/passed_lists",
     *   tags={"企业管理[not_menu]"},
     *   summary="获取所有已审核的企业列表 [企业列表]",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="pageSize",in="query",description="分页数量 默认10",required=false,@OA\Schema(type="integer")),
     *   @OA\Parameter(name="page",in="query",description="分页页码",required=false,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function passedLists(){
        $pageSize = (int) input('get.pageSize',10) ;
        $page = (int) input('get.page',0) ;
        $company_name = input('get.company_name'); // corporate_name

        $list = EnterprisesModel::field('id,user_name,contacts,contact_number,corporate_name')
            ->where('audited_time','>',0)
            ->where('record_status', '=', 2)
            ->where('status', '=', 1);
        if($company_name){
            $list->whereRaw(' (instr(corporate_name ,"'.$company_name.'" )) ');
        }
        $list = $list->page($page,$pageSize)
            ->select();


        $total = EnterprisesModel::field('id,user_name,contacts,contact_number,corporate_name')
            ->where('audited_time','>',0)
            ->where('record_status', '=', 2)
            ->where('status', '=', 1)
            ->count();

        $return['list'] = $list;
        $return['total'] = $total;

        return (count($list)) ? $this->jsonSuccess($return) : $this->jsonError('暂无数据');
    }


    /**
     * @OA\Get(path="/v1/enterprises/detail",
     *   tags={"企业管理[not_menu]"},
     *   summary="获取企业详情 ",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="company_id",in="query",description="企业id",required=false,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function detail(){
        $id = input('get.company_id',0) ;

        $detail = EnterprisesModel::field('id,user_name,contacts,contact_number,corporate_name')->find(intval($id));

        return ($detail) ? $this->jsonSuccess($detail) : $this->jsonError('暂无数据');
    }

    /**
     * @OA\Get(path="/v1/enterprises/detail",
     *   tags={"企业管理[not_menu]"},
     *   summary="获取私有认证企业申请信息列表",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="page",in="query",description="分页页数",required=false,@OA\Schema(type="integer")),
     *   @OA\Parameter(name="pageSize",in="query",description="分页条数",required=false,@OA\Schema(type="integer")),
     *   @OA\Parameter(name="enterprise_name",in="query",description="名称搜索",required=false,@OA\Schema(type="string")),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function privateCompanyList(){
        $pageSize = (int) input('get.pageSize',10) ;
        $page = (int) input('get.page',0) ;
        $enterprise_name = input('get.enterprise_name',false);

        $m = EnterprisesPrivate::page($page,$pageSize);
        if ($enterprise_name){
            $m->whereRaw(' instr(enterprise_name,"'.$enterprise_name.'") ');
        }

        $list = $m->select();

        return ($list) ? $this->jsonSuccess($list) : $this->jsonError('暂无数据');

    }

}
