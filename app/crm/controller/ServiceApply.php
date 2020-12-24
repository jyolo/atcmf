<?php
namespace app\crm\controller;



use app\crm\model\Enterprises;
use app\crm\model\EnterprisesPrivate;
use app\crm\model\ServiceApply as ServiceAppluModel;
use app\crm\model\ServiceGoods as ServiceGoodsModel;
use app\crm\validate\ServiceApplyValidate;
use app\crm\validate\ServiceGoodsValidate;
use app\crm\model\ServiceGoodsRule as ServieGocodsRuleModel;
use think\Request;

/**
 * @des 套餐申请管理
 * @package app\crm\controller
 */
class ServiceApply extends Base
{

    /**
     * @OA\Get(path="/v1/service_apply/lists",
     *   tags={"套餐申请管理"},
     *   summary="申请列表  [申请列表]",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="pageSize",in="query",description="取多少条 默认 10",required=false,@OA\Schema(type="integer")),
     *   @OA\Parameter(name="pageNum",in="query",description="分页条数 默认 1 ",required=false,@OA\Schema(type="integer")),
     *   @OA\Parameter(name="apply_realname",in="query",description="申请人姓名",required=false,@OA\Schema(type="string")),
     *   @OA\Parameter(name="company_name",in="query",description="公司名称",required=false,@OA\Schema(type="string")),
     *   @OA\Parameter(name="goods_id",in="query",description="服务等级 （套餐商品） 名称 ",required=false,@OA\Schema(type="integer")),
     *   @OA\Parameter(name="manager_id",in="query",description="选择申请对接的专员 ",required=false,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function lists(){
        $pageSize = input('get.pageSize',10) ;
        $pageNum = input('get.pageNum',1);
        $apply_realname = input('get.apply_realname',null);
        $company_name = input('get.company_name',null);
        $goods_id = input('get.goods_id',null);
        $manager_id = input('get.manager_id',null);

        $model = new ServiceAppluModel();
        $countModel = null;
        if($company_name){
            $company_ids = Enterprises::where('corporate_name','like','%'.$company_name.'%')
                ->column('id');

            if(count($company_ids)){
                $countModel = $model = $model->whereIn('company_id',$company_ids);
            }else{
                return $this->jsonError('暂无数据');
            }
        }


        $countModel = $model = $model->withJoin([
            'serviceGoodsInfo' => ['name','type','price','expiry_type','expiry_num'] ,
            'managerInfo' => ['name','real_name','nickname'],
            'applyManagerInfo' => ['name','real_name','nickname'],
        ],'LEFT');

        if ($apply_realname){
            $countModel = $model = $model->where('apply_realname','like','%'.$apply_realname.'%');
        }
        if ($manager_id){
            $countModel = $model = $model->where('manager_id',intval($manager_id));
        }
        if ($goods_id){
            $countModel = $model = $model->where('goods_id',intval($goods_id));
        }

        $total = $countModel->count();
        
        $list = $model->page(intval($pageNum) , intval($pageSize))->order('create_time','desc')->select();

        // 跨库查询
        foreach($list as $k => $v){
            $list[$k]['companyInfo'] = Enterprises::field('id,user_name,contacts,contact_number,corporate_name')->where('id',$v['company_id'])->find();
        }

        $return['list'] = $list;
        $return['total'] = $total;
        return ($list) ? $this->jsonSuccess($return) : $this->jsonError('暂无数据');
    }

    /**
     * @OA\Get(path="/v1/service_apply/detail",
     *   tags={"套餐申请管理"},
     *   summary="申请详情",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="id",in="query",description="申请的 id",required=true,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function detail(){

        $id = intval(input('get.id',0));
        if(!$id ) return $this->jsonError('缺少id');

        $data = ServiceAppluModel::with(['serviceGoodsInfo'])->find($id);

        if($data){
            $data['companyInfo'] = Enterprises::field('user_name,corporate_name,legal_person_name')->where('id',$data['company_id'])->find();
        }

        return ($data) ? $this->jsonSuccess($data) : $this->jsonError('暂无数据');
    }

    /**
     * @OA\Post(path="/v1/service_apply/create",
     *   tags={"套餐申请管理"},
     *   summary="提交申请",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *         @OA\Schema(
     *           @OA\Property(description="企业 id", property="company_id", type="integer", default="1"),
     *           @OA\Property(description="服务套餐 id", property="goods_id", type="integer", default="1"),
     *           @OA\Property(description="申请人真实姓名", property="apply_realname", type="string",default="赵四"),
     *           @OA\Property(description="申请人真手机号", property="apply_mobile", type="string",default="15926900789"),
     *           @OA\Property(description="申请人真邮箱", property="apply_email", type="string",default="2829156353@qq.com"),
     *           @OA\Property(description="合同编号", property="contract_sn", type="string",default=""),
     *           @OA\Property(description="实收金额", property="real_pay_money", type="string",default=""),
     *           @OA\Property(description="有效时长", property="apply_expiry_num", type="integer",default=""),
     *           @OA\Property(description="有效期类型 1 天  2 月 3 年", property="apply_expiry_type", type="integer",default=""),
     *           required={"company_id","goods_id", "apply_realname","apply_mobile","apply_email"})
     *       )
     *     ),
     *   @OA\Response(response="200",description="添加成功")
     * )
     */
    public function create(Request $request){

        $data = input('post.');

        try{

            $data['manager_id'] = $request->manager_id;
            Validate(ServiceApplyValidate::class)->check($data);

            $goods_info = (new ServiceGoodsModel())->find($data['goods_id']);
            if($goods_info['is_up'] == 0 ) return $this->jsonError('该套餐商品 未上架');

            $company = (new Enterprises())
                    ->where('id','=',intval($data['company_id']))
                    ->where('audited_time','>',0)
                    ->where('record_status', '=', 2)
                    ->where('status', '=', 1)
                ->find();


            if(!$company) return $this->jsonError('企业不存在 或 未通过审核');


            $data['order_sn'] = ServiceAppluModel::getOrderSn();
            $model = new ServiceAppluModel();
            // 检查该企业是否已有 已通过审核的 申请订单
            $isset = $model->where('company_id',intval($data['company_id']))
                ->where('status' ,1)
                ->where('end_time' ,'>', date('Y-m-d H:i:s' ,time()))
                ->find();

            if($isset) return $this->jsonError('该企业已经申请过套餐且还未到期');

            if(!isset($data['apply_expiry_num']) || !(intval($data['apply_expiry_num']) > 0) || !(intval($data['apply_expiry_type']) > 0) )
            {

                return $this->jsonError('申请订单缺少 有效时长 和 有效时长类型');
            }


            // 录入的时候 状态默认是 0
            $data['status'] = 0 ;

            $flag = $model->save($data);

            return ($flag) ? $this->jsonSuccess('添加成功') : $this->jsonError('添加失败');

        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }
    }


    /**
     * @OA\Put(path="/v1/service_apply/edit",
     *   tags={"套餐申请管理"},
     *   summary="修改申请",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/x-www-form-urlencoded" ,
     *         @OA\Schema(
     *           @OA\Property(description="申请 id", property="id", type="integer", default="30"),
     *           @OA\Property(description="企业 id", property="company_id", type="integer", default=""),
     *           @OA\Property(description="服务套餐 id", property="goods_id", type="integer", default=""),
     *           @OA\Property(description="申请人真实姓名", property="apply_realname", type="string",default=""),
     *           @OA\Property(description="申请人真手机号", property="apply_mobile", type="string",default=""),
     *           @OA\Property(description="申请人真邮箱", property="apply_email", type="string",default=""),
     *           @OA\Property(description="合同编号", property="contract_sn", type="string",default=""),
     *           @OA\Property(description="实收金额", property="real_pay_money", type="integer",default=""),
     *           required={"id","company_id","goods_id", "apply_realname","apply_mobile","apply_email"}
     *          )
     *       )
     *     ),
     *   @OA\Response(response="200",description="修改成功")
     * )
     */
    public function edit(Request $request){

        try{
            $data = input('post.');

            if(!intval($data['id'])) return $this->jsonError('id 参数必填');

            Validate(ServiceApplyValidate::class)->check($data);


            $model = new ServiceAppluModel();
            $now = date('Y-m-d H:i:s' ,time());

            $isSameApply = $model->where('company_id',intval($data['company_id']))
                ->where('status' ,1)
                ->where('end_time' ,'>', $now)
                ->find();

            if($isSameApply) return $this->jsonError('该企业已经申请过套餐且还未到期');

            $isset = $model->withJoin([
                'serviceGoodsInfo' => ['name','type','price','expiry_type','expiry_num'] ,
            ])->find(intval($data['id']));

            if (!$isset) return $this->jsonError('申请数据不存在');

            $data = filterEmptyVars($data);

            // 审核通过 算出服务截止时间
            if(isset($data['status']) ) {
                unset($data['status']);
            }

            // 管理员id 录入的时候 归属就定了
            $data['manager_id'] = $isset['manager_id'];
            $data['update_time'] = $now;



            $flag = $model->where('id',$isset['id'])->update($data);

            return ($flag) ? $this->jsonSuccess('修改成功') : $this->jsonError('修改失败');

        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * @OA\Put(path="/v1/service_apply/apply",
     *   tags={"套餐申请管理"},
     *   summary="审核申请",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/x-www-form-urlencoded" ,
     *         @OA\Schema(
     *           @OA\Property(description="申请 id", property="id", type="integer", default="30"),
     *           @OA\Property(description="订单状态 0：待审核 1 ：已审核", property="status", type="integer",default=""),
     *           required={"id","status"}
     *          )
     *       )
     *     ),
     *   @OA\Response(response="200",description="修改成功")
     * )
     */
    public function apply(Request $request){
        try{
            $id = input('post.id');
            $status = (int)input('post.status');


            if(!$id) return $this->jsonError('id 参数必填');
            if(!in_array($status,[0,1]) ) return $this->jsonError('status 参数不正确');


            $model = new ServiceAppluModel();
            $isset = $model->withJoin([
                'serviceGoodsInfo' => ['name','type','price','expiry_type','expiry_num'] ,
            ])->find($id);

            if (!$isset) return $this->jsonError('数据不存在');

            // 审核的时候 才 判断 该企业已有审过通过且还未到期的订单
            if($status == 1) {

                $hasApplyOrder = ServiceAppluModel::where('company_id',$isset['company_id'])
                    ->where('status',1)
                    ->where('end_time','>=',date('Y-m-d H:i:s'))
                    ->count();
                if($hasApplyOrder > 0 ) return $this->jsonError('审核失败：该企业已有审过通过且还未到期的订单');

            }

            $company = (new Enterprises())
                ->where('id','=',$isset['company_id'])
                ->where('audited_time','>',0)
                ->where('record_status', '=', 2)
                ->where('status', '=', 1)
                ->find();
            if(!$company) return $this->jsonError('企业不存在 或 未通过审核');



            //检查企业是否是 申请认证 私有企业
//            $private_company_rule_id = env('PRIVATE_COMPANY_RUEL_ID', 45) ; // 固定
            # 不再从套餐权限中判断 是否是 私有企业
//            $is_private_company =  ServieGocodsRuleModel::where('service_goods_id',$isset->goods_id)->where('module_id',$private_company_rule_id)->count();


            $update_company_data = [];


            switch ($status){
                case 0:
                    $data['end_time'] = $data['apply_time'] = null;

                    $update_company_data['self_support'] = 0;


                    break;
                case 1:
                    if($isset->apply_expiry_num && $isset->apply_expiry_type){
                        //获取截止日 根据 goods 套餐来
//                    $time_arr = $model->getEndTime($isset,$isset->serviceGoodsInfo);
                        //获取截止日 根据 apply 申请订单来
                        $time_arr = $model->getEndTimeByApply($isset);
                        $data['apply_time'] = $time_arr['apply_time'];
                        $data['end_time'] = $time_arr['end_time'];

                        $update_company_data['self_support'] = 1;
                        $update_company_data['self_support_number'] = EnterprisesPrivate::getEnterprisesPrivateApplySn($isset->company_id);

                    }else{
                        return $this->jsonError('申请订单缺少 有效时长 和 有效时长类型');
                    }

                    break;
            }


            // 管理员id 录入的时候 归属就定了
            $data['apply_manager_id'] = $request->manager_id;
            $data['update_time'] = date('Y-m-d H:i:s');
            $data['status'] = $status;

            // 有私有企业认证标识的
            if($isset['is_private_company'] == 1){
                try{
                    Enterprises::where('id',$isset->company_id)->update($update_company_data);
                }catch (\Exception $e){
                    return $this->jsonError('修改私有企业认证标识失败');
                }
            }


            $flag = $model->where('id',$isset['id'])->update($data);
            return ($flag) ? $this->jsonSuccess('修改成功') : $this->jsonError('修改失败');

        }catch (ValidateException $e){
            return $this->jsonError($e->getMessage());
        }
    }


    /**
     * @OA\Delete(path="/v1/service_apply/delete",
     *   tags={"套餐申请管理"},
     *   summary="删除申请",
     *   security={{"api_key": {}}},
     *   @OA\Parameter(name="id",in="query",description="申请id",required=true,@OA\Schema(type="integer")),
     *   @OA\Response(response="200",description="修改成功")
     * )
     */
    public function delete(){
        $id = intval(input('get.id'));
        if(!$id) return $this->jsonError('id 参数必填');

        $info = ServiceAppluModel::find($id);
        if (!$info) return $this->jsonError('数据不存在');

        $now = date('Y-m-d H:i:s' ,time());

        if($info['status'] == 1 && $info['end_time'] > $now) return $this->jsonError('该申请已通过审核且还未到期 禁止删除');


        $flag = $info->delete($id);
        return ($flag) ? $this->jsonSuccess('删除成功') : $this->jsonError('删除失败');
    }



}
