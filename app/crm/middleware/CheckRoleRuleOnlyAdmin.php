<?php

namespace app\crm\middleware;

use app\crm\model\ManagerRoleMap as ManagerRoleMapModel;
use Jwt\JwtToken;
use think\facade\Db;

class CheckRoleRuleOnlyAdmin
{
    //检查token的合法性
    public function handle($request, \Closure $next)
    {

        try{
            if(!$request->header('Authorization')) throw new \Exception('Token not found');

            $tokenStr = $request->header('Authorization');

            // 验证 令牌的
            $info = JwtToken::parseToken($tokenStr);

            $manger_id = ($info['uid']);
            $role = ManagerRoleMapModel::where('manager_id',$manger_id)->column('role_id');
            //超管直接pass
            if(in_array(1,$role))  return $next($request);

            throw new \Exception('没有权限访问该页面');


            return $next($request);

        }catch (\Exception $e){

            return json([
                'code' => 0,
                'msg'  => 'no permission : ' . $e->getMessage(),
                'data' => [],
            ],403);

        }


    }
}
