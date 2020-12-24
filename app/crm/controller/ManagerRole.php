<?php
namespace app\crm\controller;



use app\crm\model\ManagerRoleMap;
use app\crm\model\ManagerRoleRule as ManagerRoleRuleModel;
use app\crm\validate\ManagerRoleMenusValidate;
use app\crm\validate\ManagerRoleValidate;
use app\crm\model\ManagerRole as ManagerRoleModel;
use think\exception\ValidateException;
use think\facade\Db;


/**
 * @des 权限角色
 * @package app\crm\controller
 */
class ManagerRole extends Base
{

    /**
     * @OA\Get(path="/v1/manager_role/list",
     *   tags={"权限管理"},
     *   summary="获取管理员角色列表 [角色管理]",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="pageSize",in="query",description="分页数量 默认10",required=false,@OA\Schema(type="integer")),
     *   @OA\Parameter(name="page",in="query",description="分页页码",required=false,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function lists(){
        $page = (int) input('page',0);
        $pageSize = (int) input('pageSize',10);

        $total = ManagerRoleModel::count();
        $list = ManagerRoleModel::page($page,$pageSize)->select();
        $_return['list'] = $list;
        $_return['total'] = $total;

        return ($list) ? $this->jsonSuccess($_return) : $this->jsonError('暂无数据');
    }

    /**
     * @OA\Post(path="/v1/manager_role/create",
     *   tags={"权限管理"},
     *   summary="创建管理员角色 ",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *         @OA\Schema(
     *           @OA\Property(description="角色名称", property="role_name", type="string", default="超级管理员"),
     *           required={"role_name"})
     *       )
     *     ),
     *   @OA\Response(response="200",description="添加成功"),
     * )
     */
    public function create(){
        $data = input('post.');
        try{

            Validate(ManagerRoleValidate::class)->check($data);

            $model = new ManagerRoleModel();
            $isset = $model->where('role_name',$data['role_name'])->find();
            if($isset) throw new ValidateException($data['role_name'] .' 已存在');
            $flag = $model->save($data);

            return ($flag) ? $this->jsonSuccess('添加成功') : $this->jsonError('添加失败');

        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }

    }

    /**
     * @OA\Post(path="/v1/manager_role/bind_rule_menu",
     *   tags={"权限管理"},
     *   summary="角色绑定权限节点",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *         @OA\Schema(
     *          @OA\Property(description="角色id", property="role_id", type="string", default="7"),
     *           @OA\Property(description="menu_ids", property="menu_ids", type="string", default="176,188,189"),
     *           required={"role_id","menu_ids"})
     *       )
     *     ),
     *   @OA\Response(response="200",description="添加成功"),
     * )
     */
    public function bindRuleMenu(){
        $data = input('post.');
        try{

            Validate(ManagerRoleMenusValidate::class)->check($data);
            $model = new ManagerRoleRuleModel();
            $isset = $model->where('role_id',$data['role_id'])->column('id');
            if($isset) return $this->jsonError('该角色已绑定过权限菜单节点');

            $arr = [] ;
            $menu_id_arr = explode(',',$data['menu_ids']);
            foreach($menu_id_arr as $k => $v){
                $arr[$k]['role_id'] = $data['role_id'];
                $arr[$k]['menu_id'] = $v;
            }

            $flag = $model->saveAll($arr);
            return ($flag) ? $this->jsonSuccess('添加成功') : $this->jsonError('添加失败');

        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * @OA\Put(path="/v1/manager_role/edit_bind_rule_menu",
     *   tags={"权限管理"},
     *   summary="修改角色权限节点",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/x-www-form-urlencoded" ,
     *         @OA\Schema(
     *          @OA\Property(description="角色id", property="role_id", type="string", default="7"),
     *           @OA\Property(description="menu_ids", property="menu_ids", type="string", default="176,188,189"),
     *           required={"role_id","menu_ids"})
     *       )
     *     ),
     *   @OA\Response(response="200",description="添加成功"),
     * )
     */
    public function editBindRuleMenu(){
        $data = input('post.');
        try{

            Validate(ManagerRoleMenusValidate::class)->check($data);
            $arr = [] ;
            $menu_id_arr = explode(',',$data['menu_ids']);
            foreach($menu_id_arr as $k => $v){
                $arr[$k]['role_id'] = $data['role_id'];
                $arr[$k]['menu_id'] = $v;
            }
            $model = new ManagerRoleRuleModel();
            try{
                $model->startTrans();
                $model->where('role_id',$data['role_id'])->delete();
                $flag = $model->saveAll($arr);
                if($flag){
                    $model->commit();
                    return $this->jsonSuccess('修改成功');
                }

                return  $this->jsonError('修改失败');
            }catch (\Exception $e){
                $model->rollback();
                return ($flag) ? $this->jsonSuccess('修改成功') : $this->jsonError('修改失败');
            }


        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }
    }


    /**
     * @OA\Get(path="/v1/manager_role/get_rule_menu",
     *   tags={"权限管理"},
     *   summary="获取管理员角色权限节点 ",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="role_id",in="query",description="角色id",required=true,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function getRuleMenu(){
        $role_id = (int) input('role_id',0);

        if(!$role_id) return $this->jsonError('缺少 角色id');

        $menus = Db::name('manager_role_rule')->alias('r')->field('m.menu_name,m.id')
            ->leftJoin('xfb_manager_role_menus m','r.menu_id = m.id')
            ->where('r.role_id',$role_id)
            ->select();

        return ($menus) ? $this->jsonSuccess($menus) : $this->jsonError('暂无数据');
    }


    /**
     * @OA\Put(path="/v1/manager_role/edit",
     *   tags={"权限管理"},
     *   summary="修改管理员角色",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *        mediaType="application/x-www-form-urlencoded" ,
     *         @OA\Schema(
     *           @OA\Property(description="角色id", property="id", type="integer", default="2"),
     *           @OA\Property(description="角色名称", property="role_name", type="string", default="kkk"),
     *           required={"id","role_name"})
     *       )
     *     ),
     *   @OA\Response(response="200",description="修改成功"),
     * )
     */
    public function edit(){
        $data = input('post.');
        try{
            if(intval($data['id']) == 1) throw new ValidateException('超级管理员 角色不允许修改');
            Validate(ManagerRoleValidate::class)->check($data);
            $model = new ManagerRoleModel();
            $isset = $model->where('id',$data['id'])->find();
            if(!$isset)  throw new ValidateException('数据不存在 修改失败');
            $flag = $isset->save($data);

            return ($flag) ? $this->jsonSuccess('修改成功') : $this->jsonError('修改失败');

        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }

    }

    /**
     * @OA\Delete(path="/v1/manager_role/delete",
     *   tags={"权限管理"},
     *   summary="删除管理员角色 ",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="id",in="query",description="角色id",required=true,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="添加成功"),
     * )
     */
    public function delete(){
        $id = intval(input('get.id'));
        try{
            if(! $id ) throw new ValidateException('角色id 必填');
            if($id == 1) throw new ValidateException('超管角色不允许删除');

            $model = new ManagerRoleModel();
            $isset = $model->where('id',$id)->find();
            if(!$isset)  throw new ValidateException('数据不存在 修改失败');
            $flag = $isset->delete();

            return ($flag) ? $this->jsonSuccess('删除成功') : $this->jsonError('删除失败');

        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }
    }

}
