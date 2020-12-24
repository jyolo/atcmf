<?php
namespace app\crm\controller;


use app\crm\controller\Base;
use app\crm\model\BetterLiveArticleConf;
use app\crm\model\Manager as ManagerModel;
use think\Db;
use think\facade\Cache;
use think\Request;
use Uploader\Uploader;
use Upyun\Config;
use Upyun\Upyun;
use \app\crm\model\ServiceApply as ServiceApplyModel;
use \app\crm\model\Enterprises as EnterprisesModel;


class BetterLive extends Base
{

    /**
     * @OA\Get(path="/v1/better_live/company_list",
     *      tags={"优质生活 [not_menu]"},
     *      summary="优质生活企业列表",
     *     security={{"api_key": {}}},
     *     @OA\Response(response=200,description="成功"),
     * )
     */
    public function companyList(Request $request)
    {
        $pageNum = $request->get('page',0);
        $limit = $request->get('limit',10);
        $company_name = $request->get('company_name',null);

        $list = ServiceApplyModel::alias('sa')
            ->field('sa.company_id,sg.name as goods_name,sa.end_time,blac.max_article_num,blac.manager_name')
            ->leftJoin('xfb_service_goods sg','sg.id = sa.goods_id')
            ->leftJoin('xfb_service_goods_rule ru','sg.id = ru.service_goods_id')
            ->leftJoin('xfb_better_live_article_conf blac' ,'blac.company_id = sa.company_id')
            ->whereIn('ru.module_id',[44])
            ->where('sa.end_time','>',date('Y-m-d H:i:s'))
            ->order('sa.apply_time','desc');

        if($company_name){
            $company_ids = EnterprisesModel::whereRaw(' (instr(corporate_name,"'.$company_name.'")) ')->column('id');
            $list->whereIn('sa.company_id',$company_ids);
        }

        $total = $list->count();
        $listItem = $list->page($pageNum,$limit)->select();


        foreach ($listItem as $k => $v){

            $listItem[$k]->company_name = EnterprisesModel::where('id',$v->company_id)->value('corporate_name');
            if(!$listItem[$k]->max_article_num) {
                $listItem[$k]->max_article_num = env('BETTER_LIVE_COMPANY_DEFUALT_ARTICLE_NUM',5);
            }
        }

        return $this->jsonSuccess(['list' => $listItem ,'total' => $total]);

    }

    /**
     * @OA\Post(path="/v1/better_live/saveArticleNum",
     *   tags={"修改优质生活发布文章数量"},
     *   summary="修改优质生活发布文章数量 ",
     *   security={{"api_key": {}}},
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *         @OA\Schema(
     *           @OA\Property(description="数量", property="num", type="string", default="5"),
     *           required={"manager_id","role_ids"})
     *       )
     *     ),
     *   @OA\Response(response="200",description="修改成功"),
     * )
     */
    public function saveArticleNum(Request $request){

        $max_article_num = (int) $request->post('max_article_num',0);
        $company_id = (int) $request->post('company_id',0);
        $company_name = (string) $request->post('company_name',0);

        if(!$max_article_num) return $this->jsonError('max_article_num 参数必填');

        $id = BetterLiveArticleConf::where('company_id',$company_id)->value('id');
        $data = [
            'company_id' => $company_id,
            'company_name' => $company_name,
            'max_article_num' => $max_article_num,
            'manager_id' => $request->manager_id ,
            'manager_name' => \app\crm\model\Manager::where('id',$request->manager_id)->value('real_name') ,
        ];
        $redis = app('redisClient');
        $key = 'better_live_article_conf';
        if($id){
            $data['update_time'] = date('Y-m-d H:i:s');
//            // 存入有序集合
            $redis->zadd($key,$data['max_article_num'],$data['company_id']);
            $res = BetterLiveArticleConf::where('id',$id)->save($data);
        }else{
            // 存入有序集合
            $redis->zadd($key,$data['max_article_num'],$data['company_id']);
            $res = BetterLiveArticleConf::create($data);
        }

        return ($res) ? $this->jsonSuccess('修改成功'): $this->jsonError('修改成功');
    }









}
