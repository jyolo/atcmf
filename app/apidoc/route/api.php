<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;


// 企业客服系统 接口文档
Route::get('company', 'app\apidoc\controller\Index@company')->allowCrossDomain();
// crm系统 接口文档
Route::get('crm', 'app\apidoc\controller\Index@crm')->allowCrossDomain();

