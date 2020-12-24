<?php
namespace app\crm\controller;


use \app\crm\model\ServiceApply as ServiceApplyModel;
use app\crm\model\ServiceGoods as ServiceGoodsModel;
use app\crm\model\ServiceGoodsRule as ServiceGoodsRuleModel;
use app\crm\validate\ServiceGoodsValidate;

/**
 * @des 服务商品套餐
 * @package app\crm\controller
 */
class ServiceGoods extends Base
{

    /**
     * @OA\Get(path="/v1/service_goods/lists",
     *   tags={"套餐管理"},
     *   summary="获取套餐商品列表 [套餐列表]",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="pageSize",in="query",description="取多少条 默认 10",required=false,@OA\Schema(type="integer")),
     *   @OA\Parameter(name="pageNum",in="query",description="分页条数 默认 1 ",required=false,@OA\Schema(type="integer")),
     *   @OA\Parameter(name="is_up",in="query",description="是否上架 默认 0（下架）, 1（上架） ",required=false,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function lists(){
        $pageSize = input('get.pageSize',10) ;
        $pageNum = input('get.pageNum',1);
        $is_up = input('get.is_up',null);

        $model = new ServiceGoodsModel();
        $countModel = null;
        if (isset($is_up) && in_array(intval($is_up) ,[0,1])){

            $countModel = $model = $model->where('is_up',intval($is_up));
        }else{
            $countModel = $model;
        }

        $total = $countModel->count();
        $list = $model->page($pageNum , $pageSize)->order('create_time','desc')->select();


        $_return['list'] = $list;
        $_return['total'] = $total;

        return (count($list)) ? $this->jsonSuccess($_return) : $this->jsonError('暂无数据');
    }

    /**
     * @OA\Get(path="/v1/service_goods/detail",
     *   tags={"套餐管理"},
     *   summary="套餐详情",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="id",in="query",description="套餐商品的id",required=true,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function detail(){

        $id = intval(input('get.id',0));
        if(!$id ) return $this->jsonError('缺少id');

        $list = ServiceGoodsModel::with(['moduleInfo'])->find(intval($id));

        if($list->moduleInfo){
            $arr = $list->moduleInfo->toArray();
            $newModuleInfo = [];
            foreach($arr as $k => $v){
                if($v['pid'] != 0) continue;
                $newModuleInfo[$v['id']] = $v;
                $newModuleInfo[$v['id']]['son'] = [];
            }
            foreach($arr as $k => $v){
                if($v['pid'] == 0) continue;
                foreach($newModuleInfo as $sk => $sv){
                    if($v['pid'] == $sk){
                        array_push($newModuleInfo[$sk]['son'],$v);
                    }
                }
            }
            sort($newModuleInfo);
            unset($list->moduleInfo);
            $list->moduleInfo = $newModuleInfo;

        }


        return ($list) ? $this->jsonSuccess($list) : $this->jsonError('暂无数据');
    }

    /**
     * @OA\Post(path="/v1/service_goods/create",
     *   tags={"套餐管理"},
     *   summary="添加套餐",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *         @OA\Schema(
     *           @OA\Property(description="产品名称", property="name", type="string", default="基础套餐"),
     *           @OA\Property(description="产品类型  1 公开发售  2 业务定制", property="type", type="string", default="1"),
     *           @OA\Property(description="产品价格", property="price", type="string",default="15000"),
     *           @OA\Property(description="有效期类型 1 天  2 月 3 年", property="expiry_type", type="string",default="2"),
     *           @OA\Property(description="有效期时长", property="expiry_num", type="string",default="3"),
     *           @OA\Property(description="产品套餐描述", property="desc", type="string",default="套餐描述描述描述描述......."),
     *           @OA\Property(description="是否上架 0 下架 1 上架", property="is_up", type="string",default="0"),
     *           required={"name","type", "price","expiry_type","expiry_num"})
     *       )
     *     ),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function create(){

        $data = input('post.');
        try{

            Validate(ServiceGoodsValidate::class)->check($data);
            $flag = (new ServiceGoodsModel())->save($data);
            return ($flag) ? $this->jsonSuccess('添加成功') : $this->jsonError('添加失败');

        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }
    }
    /**
     * @OA\Put(path="/v1/service_goods/edit",
     *   tags={"套餐管理"},
     *   summary="修改套餐",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/x-www-form-urlencoded" ,
     *         @OA\Schema(
     *          @OA\Property(description="产品id", property="id", type="integer", default="1"),
     *           @OA\Property(description="产品名称", property="name", type="string", default=""),
     *           @OA\Property(description="产品类型  1 公开发售  2 业务定制", property="type", type="string", default=""),
     *           @OA\Property(description="产品价格", property="price", type="string",default=""),
     *           @OA\Property(description="有效期类型 1 天  2 月 3 年", property="expiry_type", type="string",default=""),
     *           @OA\Property(description="有效期时长", property="expiry_num", type="string",default=""),
     *           @OA\Property(description="产品套餐描述", property="desc", type="string",default=""),
     *           @OA\Property(description="是否上架 0 下架 1 上架", property="is_up", type="string",default=""),
     *           required={"id"}
     *          )
     *       )
     *     ),
     *   @OA\Response(response="200",description="修改成功")
     * )
     */
    public function edit(){

        try{
            $data = input('post.');


            if(!intval($data['id'])) return $this->jsonError('id 参数必填');

            $model = ServiceGoodsModel::find($data['id']);
            if (!$model) return $this->jsonError('数据不存在');
            $data = filterEmptyVars($data);

            $flag = $model->save($data);
            return ($flag) ? $this->jsonSuccess('修改成功') : $this->jsonError('修改失败');

        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }

    }

    /**
     * @OA\Delete(path="/v1/service_goods/delete",
     *   tags={"套餐管理"},
     *   summary="删除套餐",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="id",in="query",description="商品id",required=true,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="修改成功")
     * )
     */
    public function delete(){
        $id = intval(input('get.id'));
        if(!$id) return $this->jsonError('id 参数必填');

        $isset = ServiceApplyModel::where('goods_id',$id)->find();
        if($isset) return $this->jsonError('该套餐已被企业申请使用 禁止删除');

        $model = ServiceGoodsModel::find($id);
        if (!$model) return $this->jsonError('数据不存在');

        $flag = $model->delete($id);
        return ($flag) ? $this->jsonSuccess('删除成功') : $this->jsonError('删除失败');
    }



}
