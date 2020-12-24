<?php
namespace app\crm\controller;


use app\crm\controller\Base;
use think\facade\Db;


class Crontab extends Base
{

    /**
     * @OA\Get(path="/crontab/checkApplyOverTime",
     *      tags={"首页 [not_menu]"},
     *      summary="首页相关",
     *     security={{"api_key": {}}},
     *     @OA\Response(response=200,description="成功"),
     * )
     */
    public function checkApplyOverTime()
    {
        // 已审核 截止日志小于当前时间（已过期）
        $sql = '
         select company_id from (

            select * from (
                SELECT  DISTINCT company_id ,apply_time,end_time
                FROM `xfb_service_apply` FORCE INDEX ( over_time )  
                WHERE   `status` = 1    AND `xfb_service_apply`.`delete_time` IS NULL 
                ORDER BY `apply_time` DESC
             ) as a 
             GROUP BY a.company_id

        ) as b  where  UNIX_TIMESTAMP(end_time) < '.time().'
        
        ';

        $company_ids = Db::query($sql);
        $ids = [];
        foreach($company_ids as $k => $v){
            array_push($ids,$v['company_id']);
        }

        \app\crm\model\Enterprises::where('self_support',1)->whereIn('id',$ids)->update(['self_support' => 0]);


        return $this->jsonSuccess('ok');

    }









}
