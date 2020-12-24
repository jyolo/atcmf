<?php

namespace app\crm\middleware;


use Jwt\JwtToken;
use think\facade\Db;
use think\facade\Log;
use think\facade\Response;
use think\facade\Request;
use MongoDB\Driver\Exception as MongoDbException;


class ActionLogger
{
    // ip 黑名单
    const BLACK_IP = [

    ];

    const TABLE_PRRFIX = 'crm_visit_log_';

    public function handle($request, \Closure $next)
    {
       try{
            // 不记录 options 请求
           if ($request->method() == 'OPTIONS') return $next($request);

           $actionData = [];
           $actionData['request_url'] = $request->url();
           $actionData['domain'] = $request->domain();
           $actionData['rule'] = $request->rule()->getRule();
           $actionData['route'] = $request->rule()->getRoute();
           $actionData['method'] = $request->method();
           $actionData['params'] = $request->param();
           $controllerWithAction = explode('@' ,$request->rule()->getOption('prefix') . $request->rule()->getRoute()) ;
           $actionData['controller'] = $controllerWithAction[0];
           $actionData['action'] = $controllerWithAction[1];
           $actionData['ip'] = $request->ip();

           try{
               $ip2regin = app('ip2Region');
               $info = $ip2regin->memorySearch($actionData['ip']);
               $arr = explode('|', $info['region'] );
               $actionData['country'] = $arr[0];
               $actionData['province'] = $arr[2];
               $actionData['city'] = $arr[3];
               $actionData['isp'] = $arr[4];

           }catch (\Exception $e){
               $actionData['country'] = $e->getMessage();
               $actionData['province'] = null;
               $actionData['city'] = null;
               $actionData['isp'] = null;
           }



           $actionData['action_time'] = date('Y-m-d H:i:s' , $request->time());

           $jwt = $request->header('authorization');
           if($jwt){
               $jwtArr = JwtToken::parseToken($jwt);
               $actionData['uid'] = $jwtArr['uid'];
               $actionData['nickname'] = $jwtArr['nickname'];
               $actionData['token_type'] = $jwtArr['sub'];
               $actionData['token_iss'] = $jwtArr['iss'];

               $request->manager_id = $jwtArr['uid'];
               $request->manager_name = $jwtArr['nickname'];


           }else{
               $actionData['uid'] = $actionData['token_type'] = $actionData['token_iss'] = $actionData['nickname'] = '';
           }

           $collection = self::TABLE_PRRFIX . date('Y-m-d');
           Db::connect('mongo')->table($collection)->insert($actionData);

       }catch (MongoDbException $e){

            Log::error($e->getMessage());
       }catch (\Exception $e){

           Log::error($e->getMessage());
       }

        return $next($request);
    }
}
