<?php
namespace app\apidoc\controller;


use app\common\controller\BaseController;


class Index extends BaseController
{
    public function company()
    {

        $scanPath = root_path() . 'app/company';
        $s = \OpenApi\scan($scanPath);
        $arr = json_decode($s->toJson(),true);
        // 根据配置文件中的 替换api文档 请求的server url
        $arr['servers'][0]['url'] = preg_replace('/\/\/(\S+)/' , '//'.env('domain_bind.company_domain') , $arr['servers'][0]['url']);
        $json = json_encode($arr);


        echo $json;

    }

    public function crm()
    {

        $scanPath = root_path() . 'app/crm';
        $s = \OpenApi\scan($scanPath);

        $arr = json_decode($s->toJson(),true);
        // 根据配置文件中的 替换api文档 请求的server url
        $arr['servers'][0]['url'] = preg_replace('/\/\/(\S+)/' , '//'.env('domain_bind.crm_domain') , $arr['servers'][0]['url']);
        $json = json_encode($arr);


        echo $json;

    }

}
