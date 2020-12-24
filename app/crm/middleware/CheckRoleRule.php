<?php

namespace app\crm\middleware;

use app\crm\model\ManagerRoleMap as ManagerRoleMapModel;
use app\crm\model\ManagerRoleMenus;
use Jwt\JwtToken;
use think\facade\Db;

class CheckRoleRule
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

            $menus = Db::name('manager_role_rule')->alias('r')
                ->leftJoin('xfb_manager_role_menus m','r.menu_id = m.id')
                ->whereIn('r.role_id',$role)
                ->where('m.un_check',0)
                ->column('m.request_route');

            $request_url = explode('?',$request->url())[0];
            // un_check 的直接pass
            $un_check = ManagerRoleMenus::where('request_route',$request_url)->value('un_check');
            if($un_check && $un_check == 1) return $next($request);

            if(!in_array($request_url,$menus)) throw new \Exception('没有权限进行该操作');


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
