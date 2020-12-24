<?php
namespace app\crm\controller;


use app\crm\controller\Base;
use think\facade\Cache;
use app\crm\model\Manager as ManagerModel;
use app\crm\model\ManagerRoleMap as ManagerRoleMapModel;
use app\crm\validate\ManagerRoleMapValidate;


/**
 * @des 企业公告
 * @package app\crm\controller
 */
class Manager extends Base
{



    /**
     * @OA\Get(path="/v1/manager/list",
     *   tags={"权限管理"},
     *   summary="获取管理员列表 [管理员管理]",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="pageSize",in="query",description="分页数量 默认10",required=false,@OA\Schema(type="integer")),
     *   @OA\Parameter(name="page",in="query",description="分页页码",required=false,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function lists(){

        $pageSize = (int) input('get.pageSize',10) ;
        $page = (int) input('get.page',0) ;

        $total = ManagerModel::where('status', '=', 1)->count();

        $list = ManagerModel::where('status', '=', 1)
            ->with(['roleInfo'=>function($query){
                $query->field('id,role_name,status');
            }])
            ->field('id,name,nickname,real_name,tel,email')->page($page,$pageSize)->select();


        $return['list'] = $list;
        $return['total'] = $total;

        return (count($list)) ? $this->jsonSuccess($return) : $this->jsonError('暂无数据');
    }

    /**
     * @OA\Post(path="/v1/manager/bindRole",
     *   tags={"权限管理"},
     *   summary="管理员绑定角色 ",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *         @OA\Schema(
     *           @OA\Property(description="管理员id", property="manager_id", type="string", default="1"),
     *           @OA\Property(description="角色id 可多选 最多2个 逗号分隔", property="role_ids", type="integer", default="1"),
     *           required={"manager_id","role_ids"})
     *       )
     *     ),
     *   @OA\Response(response="200",description="绑定成功"),
     * )
     */
    public function bindRole(){
        $data = input('post.');
        try{

            Validate(ManagerRoleMapValidate::class)->check($data);

            $model = new ManagerRoleMapModel();

            $isset = $model->where('manager_id',$data['manager_id'])->column('id');
            if($isset) return $this->jsonError('该管理员已经绑定过角色');

            $arr = [];
            foreach(explode(',',$data['role_ids']) as $k => $v){
                $arr[$k]['manager_id'] = $data['manager_id'];
                $arr[$k]['role_id'] = $v;
            }

            $flag = $model->saveAll($arr);

            return ($flag) ? $this->jsonSuccess('添加成功') : $this->jsonError('添加失败');

        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * @OA\Put(path="/v1/manager/editBindRole",
     *   tags={"权限管理"},
     *   summary="管理员修改角色 ",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *        mediaType="application/x-www-form-urlencoded" ,
     *         @OA\Schema(
     *           @OA\Property(description="管理员id", property="manager_id", type="string", default="1"),
     *           @OA\Property(description="角色id 可多选 最多2个 逗号分隔", property="role_ids", type="integer", default="1"),
     *           required={"manager_id","role_ids"})
     *       )
     *     ),
     *   @OA\Response(response="200",description="绑定成功"),
     * )
     */
    public function editBindRole(){
        $data = input('post.');
        try{

            Validate(ManagerRoleMapValidate::class)->check($data);

            $model = new ManagerRoleMapModel();

            $arr = [];

            foreach(explode(',',$data['role_ids']) as $k => $v){
                $arr[$k]['manager_id'] = $data['manager_id'];
                $arr[$k]['role_id'] = $v;
            }

            try{
                $model->startTrans();
                $flag = $model->where('manager_id',$data['manager_id'])->delete();
                var_dump($flag);
                $flag = $model->saveAll($arr);
                if($flag){
                    $model->commit();
                    return $this->jsonSuccess('修改成功');
                }

                return $this->jsonError('修改失败');

            }catch (\Exception $e){
                $model->rollback();
                return $this->jsonError('修改失败');
            }

        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }
    }



}
