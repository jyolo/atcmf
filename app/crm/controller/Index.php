<?php
namespace app\crm\controller;


use app\crm\controller\Base;
use app\crm\model\Manager as ManagerModel;
use think\Db;
use think\facade\Cache;
use Uploader\Uploader;
use Upyun\Config;
use Upyun\Upyun;


class Index extends Base
{

    /**
     * @OA\Get(path="/v1/index",
     *      tags={"首页 [not_menu]"},
     *      summary="首页相关",
     *     security={{"api_key": {}}},
     *     @OA\Response(response=200,description="成功"),
     * )
     */
    public function index()
    {

        return $this->jsonSuccess('企业客服CRM ');

    }


    public function upload()
    {

        try{
            $upfile = Uploader::init('upyun')->upfile($_FILES);
            return $this->jsonSuccess(['files' => $upfile],'上传成功');
        }catch (\Exception $e){
            return $this->jsonError($e->getMessage());
        }




    }






}
