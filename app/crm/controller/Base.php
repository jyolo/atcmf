<?php
declare (strict_types = 1);

namespace app\crm\controller;

use app\common\controller\BaseController;
use think\App;


/**
 * @OA\Info(title="客服CRM系统文档", version="0.1")
 * @OA\Server(
 *      url="{schema}://crm.local.company.com",
 *      description="客服CRM系统接口文档",
 *      @OA\ServerVariable(
 *          serverVariable="schema",
 *          enum={"https", "http"},
 *          default="http"
 *      )
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="api_key",
 *   type="apiKey",
 *   in="header",
 *   name="Authorization"
 * )
 */
class Base extends BaseController
{
    /**
     * 重写 error 方法
     * @param string $msg
     * @param null $url
     * @param string $data
     * @param int $wait
     * @param array $header
     */
    protected function jsonError($msg = 'fail',  $data = '', array $header = [])
    {

        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
        ];

        return json($result,203,$header);


    }

    /**
     * 重写 Success 方法
     * @access protected
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function jsonSuccess( $data = '',$msg = 'success', array $header = [])
    {
        if( is_string($data)){
            $msg = $data;
            $data = [];
        }
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
        ];
        return json($result,200,$header);
    }
}
