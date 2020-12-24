<?php
namespace app\common;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\RouteNotFoundException;
use think\exception\ValidateException;
use think\Response;
use think\response\Json;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        if(env('app_debug')){

            if($e instanceof RouteNotFoundException){
                $code = 404;
                $msg = 'page not found';
            }elseif($e instanceof ValidateException){
                $code = 202;
                $msg = $e->getMessage();
            }elseif ($e instanceof Exception){
                $code = 203;
                $msg = $e->getMessage();
            }else{
                $code = 500;
                $msg =  $e->getMessage();
            }

            $data = [
                'error_message' => $msg,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode(),
            ];



        }else{


            if($e instanceof RouteNotFoundException){
                $code = 404;
                $msg = 'page not found';
            }elseif($e instanceof ValidateException){
                $code = 202;
                $msg = $e->getMessage();
            }elseif ($e instanceof Exception){
                $code = 203;
                $msg = $e->getMessage();
            }else{
                $code = 500;
                $msg = 'server error';
            }

            $data = [ 'error_message' => $msg ];
        }

        // todo  错误日志的收集
        // .......


//        return json($data,$code);
        // 其他错误交给系统处理
         return parent::render($request, $e);
    }

}
