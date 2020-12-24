<?php

namespace app\crm\middleware;

use Jwt\JwtToken;
use think\exception\HttpException;

class CheckToken
{
    //检查token的合法性
    public function handle($request, \Closure $next)
    {

        try{
            if(!$request->header('Authorization')) throw new \Exception('Token not found');

            $tokenStr = $request->header('Authorization');

            // 验证 令牌的
            $jwtToken = new JwtToken();
            $jwtToken->setRedis(app('redisClient'));
            $jwtToken->useHook('defualtHook')->verify($tokenStr);

            return $next($request);

        }catch (\Exception $e){

            return json([
                'code' => 0,
                'msg'  => 'Authorization fail :' . $e->getMessage(),
                'data' => [],
            ],401);

        }


    }
}
