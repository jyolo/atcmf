<?php
namespace app\crm\controller;



use app\crm\model\ServiceGoodsRule as ServiceGoodsRuleModel;
use app\crm\validate\ServiceGoodsRuleValidate;

/**
 * @des 服务商品权限管理
 * @package app\crm\controller
 */
class ServiceGoodsRule extends Base
{


    /**
     * @OA\Post(path="/v1/service_goods_rule/create",
     *   tags={"套餐权限管理 [not_menu]"},
     *   summary="套餐权限绑定",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *         @OA\Schema(
     *           @OA\Property(description="套餐产品id", property="service_goods_id", type="string", default="1"),
     *           @OA\Property(description="权限功能id", property="module_id", type="string", default="4,5,7"),
     *           required={"service_goods_id","module_id"})
     *       )
     *     ),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function create(){

        $data = input('post.');
        try{

            Validate(ServiceGoodsRuleValidate::class)->check($data);

            $flag = (new ServiceGoodsRuleModel())->add($data['service_goods_id'] , $data['module_id']);

            return ($flag) ? $this->jsonSuccess('添加成功') : $this->jsonError('添加失败');

        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }
    }
    /**
     * @OA\Put(path="/v1/service_goods_rule/edit",
     *   tags={"套餐权限管理 [not_menu]"},
     *   summary="修改套餐权限",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *        mediaType="application/x-www-form-urlencoded" ,
     *         @OA\Schema(
     *           @OA\Property(description="套餐产品id", property="service_goods_id", type="string", default="1"),
     *           @OA\Property(description="权限功能id", property="module_id", type="string", default="4,5,7"),
     *           required={"service_goods_id","module_id"})
     *       )
     *     ),
     *   @OA\Response(response="200",description="修改成功")
     * )
     */
    public function edit(){

        try{
            $data = input('post.');

            Validate(ServiceGoodsRuleValidate::class)->check($data);

            $flag = (new ServiceGoodsRuleModel())->edit($data['service_goods_id'] ,$data['module_id']);

            return ($flag) ? $this->jsonSuccess('修改成功') : $this->jsonError('修改失败');

        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }catch (\Exception $e){
            return $this->jsonError($e->getMessage());
        }

    }

    /**
     * @OA\Delete(path="/v1/service_goods_rule/delete",
     *   tags={"套餐权限管理 [not_menu]"},
     *   summary="删除套餐权限",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="service_goods_id",in="query",description="套餐产品id",required=true,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="修改成功")
     * )
     */
    public function delete(){
        $id = intval(input('get.service_goods_id'));
        if(!$id) return $this->jsonError('id 参数必填');

        $flag = (new ServiceGoodsRuleModel())->del($id);

        return ($flag) ? $this->jsonSuccess('删除成功') : $this->jsonError('删除失败');
    }



}
