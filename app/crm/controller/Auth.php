<?php
namespace app\crm\controller;


use app\crm\controller\Base;
use app\crm\model\Manager;
use Auth\MemberLib;
use Jwt\Exception\TokenExistsException;
use Jwt\JwtToken;
use think\facade\Request;



class Auth extends Base
{
    // token过期 时间 24小时
//    const TOKEN_EXP_SECENDS = 86400;
    // 临时满足产品的 骚操作  一年 过期 新旧token(共存)
    const TOKEN_EXP_SECENDS = 31536000;


    /**
     * @OA\Post(path="/v1/auth/login",
     *   tags={"用户认证 [not_menu]"},
     *   summary="登录",
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *         @OA\Schema(
     *           @OA\Property(description="登录账号", property="account", type="string", default="438608613@qq.com"),
     *           @OA\Property(description="账号类型  [email,mobile,uname]", property="account_type", type="string", default="email"),
     *           @OA\Property(description="密码", property="pwd", type="string",default="123123"),
     *           @OA\Property(description="客户端类型 [web,wechat,andriod,ios]", property="client_type", type="string",default="web"),
     *           @OA\Property(description="设备id [仅针对andriod,ios]", property="device_id", type="string",default=""),
     *           required={"account","account_type", "pwd","client_type","device_id"})
     *       )
     *     ),
     *
     *   @OA\Response(response="200",description="登录成功",
     *     @OA\Header( header="Authorization",description="授权token", @OA\Schema(type="string") ),

     *    ),
     *
     *  )
     */

    public function login()
    {

        $jwt = new JwtToken();
        $param = input('post.');

        $platform = $param['client_type'];
        $deviceId = $param['device_id'];

        try{
            $jwt->setRedis(app('redisClient'));
            $jwt->sub($platform) //颁发给客端类型
            ->aud($deviceId) // 客户端设备id 针对安卓 或和 ios 的设备id
            ->exp(self::TOKEN_EXP_SECENDS) // token过期时间
            ->useHook('defualtHook'); // 使用钩子

            # 注入用户model
            MemberLib::bindModel(Manager::class ,[
                'uname' => 'name',
                'account' => ['email' => 'email','uname' => 'name','mobile' => 'tel'],
                'pwd' => 'password',
                'pwd_encrypt' => 'md5',
                'pwd_salt' => 'salt',
                'user_status' => 'status',
                'frozen_value' => 0
            ],'id,name,real_name as truename,password,email,mobile,salt,status');

            MemberLib::bindRedis(app('redisClient'));

            // 登录
            $memberInfo = MemberLib::login($param);

            $jwt->payload($memberInfo); // 装载非敏感信息

            try{
                $token = $jwt->getToken();
                return $this->jsonSuccess(['login' => 'ok','Authorization'=>$token] );
            }catch (TokenExistsException $e){
                return $this->jsonError('登录失败：您已登录请不要重复登录',['login'=>'fail'] ,['Authorization'=>$e->getMessage() ]);
            }

        }catch (\Exception $e){
            return $this->jsonError('登录失败'.$e->getMessage());
        }



    }


    /**
     * @OA\Post(path="/v1/auth/loginout",
     *      tags={"用户认证 [not_menu]"},
     *      summary="退出",
     *     security={{"api_key": {}}},
     *     @OA\Response(response=200,description="退出成功"),
     * )
     *
     */
    public function loginOut()
    {

        $jwt = new JwtToken();
        $jwt->setRedis(app('redisClient'))->useHook('defualtHook');
        $token = Request::header('Authorization');

        if(strlen(trim($token)) == 0 ) return $this->jsonError('token not found');

        $flag = $jwt->delToken($token);

        return $flag ? $this->jsonSuccess('退出成功') : $this->jsonError('退出失败');

    }

    /**
     * @OA\Post(path="/v1/auth/sendVerifyCode",
     *   tags={"用户认证 [not_menu]"},
     *   summary="发送验证码",
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *         @OA\Schema(
     *           @OA\Property(description="登录账号", property="account", type="string", default="百事可乐"),
     *           @OA\Property(description="账号类型  [email,mobile,uname]", property="account_type", type="string", default="uname"),
     *           required={"account","account_type", "pwd","client_type","device_id"}
     *          )
     *       )
     *     ),
     *   @OA\Response(response="200",description="登录成功")
     * )
     */
    public function sendVerifyCode($account ,$account_type){

        MemberLib::bindRedis(app('redisClient'));
        $type = MemberLib::getAccountType($account ,$account_type);

        switch ($type){
            case 'mobile':
                $flag = MemberLib::sendMobileVerifyCode($account);
                break;
            case 'email':
                $flag = MemberLib::sendEmailVerifyCode($account);
                break;
            case 'uname':
                return $this->jsonError('uname 账户类型不支持发送验证码');
                break;
        }

        return ($flag) ? $this->jsonSuccess('验证码已发送') : $this->jsonError('验证码发送失败！请重试！');

    }


    /**
     * @OA\Post(path="/v1/auth/checktoken",
     *      tags={"用户认证 [not_menu]"},
     *      summary="验证token是否正确",
     *     security={{"api_key": {}}},
     *     @OA\Response(response=200,description="验证成功"),
     * )
     */
    public function checktoken(){
        $token = Request::header('Authorization');
        $jwt = new JwtToken();
        $jwt->setRedis(app('redisClient'))->useHook('defualtHook');

        try{
            return ($jwt->verify($token)) ? $this->jsonSuccess('success') : $this->jsonError('error');
        }catch (\Exception $e){
            return $this->jsonError('error');
        }

    }

}
