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

Route::get('/',function (){
    echo 'crm index';
});

//// 定时器相关
//Route::group('crontab',function(){
//    // 检查套餐申请是否过期
//    Route::get('checkApplyOverTime','Crontab@checkApplyOverTime');
//
//
//})->prefix('app\crm\controller\\');

//Route::get('crontab','Crontab@checkApplyOverTime');
//
//Route::group('v1',function(){
//
//    // 首页相关
//    Route::get('index','Index@index');
//
//    // 测试上传
//    Route::post('upload','Index@upload');
//
//
//
//    // 登录相关
//    Route::group('auth',function (){
//        // 登录
//        Route::post('login','Auth@login');
//        // 注册
//        Route::post('register','Auth@register');
//        // 发送验证码
//        Route::post('sendVerifyCode','Auth@sendVerifyCode');
//        // 退出
//        Route::post('loginout','Auth@loginOut')->middleware([\middleware\CheckToken::class]);
//        // 检查token合法性  for test
//        Route::post('checktoken','Auth@checktoken')->middleware([\middleware\CheckToken::class]);
//    });
//
//    // 必须要登录的 token的 认证的接口
//    Route::group(function(){
//
//        Route::group(function(){
//
//            // 套餐服务商品
//            Route::group('service_goods',function (){
//                // 添加
//                Route::post('create','ServiceGoods@create');
//                // 修改
//                Route::put('edit','ServiceGoods@edit');
//                // 获取列表
//                Route::get('lists','ServiceGoods@lists');
//                // 获取详情
//                Route::get('detail','ServiceGoods@detail');
//                // 删除
//                Route::delete('delete','ServiceGoods@delete');
//            });
//
//            // 套餐服务商品对应的权限
//            Route::group('service_goods_rule',function (){
//                // 添加
//                Route::post('create','ServiceGoodsRule@create');
//                // 修改
//                Route::put('edit','ServiceGoodsRule@edit');
//                // 获取详情
//                Route::get('detail','ServiceGoodsRule@detail');
//                // 删除
//                Route::delete('delete','ServiceGoodsRule@delete');
//            });
//
//
//            // 套餐服务对应功能权限模块
//            Route::group('service_rule_menu',function (){
//                // 初始化 / 刷新 企业客服应用权限菜单节点
//                Route::post('init','ServiceRuleMenu@initMenu');
//                // 获取所有企业客服应用权限菜单节点
//                Route::get('lists','ServiceRuleMenu@lists');
//            });
//
//
//            // 套餐商品服务申请管理
//            Route::group('service_apply',function (){
//                // 添加
//                Route::post('create','ServiceApply@create');
//                // 修改
//                Route::put('edit','ServiceApply@edit');
//                // 修改
//                Route::put('apply','ServiceApply@apply');
//                // 获取列表
//                Route::get('lists','ServiceApply@lists');
//                // 获取详情
//                Route::get('detail','ServiceApply@detail');
//                // 删除
//                Route::delete('delete','ServiceApply@delete');
//            });
//
//            // 套餐商品服务申请管理
//            Route::group('enterprises',function (){
//                // 获取审核通过的 企业列表
//                Route::get('passed_lists','Enterprises@passedLists');
//                // 获取企业详情
//                Route::get('detail','Enterprises@detail');
//
//                // 获取私有企业认证申请列表
//                Route::get('private_list','Enterprises@privateCompanyList');
//
//            });
//
//            // 后台管理员
//            Route::group('manager',function (){
//                // 获取管理员列表
//                Route::get('list','Manager@lists');
//                // 管理员绑定角色
//                Route::post('bindRole','Manager@bindRole');
//                // 管理员修改绑定角色
//                Route::put('editBindRole','Manager@editBindRole');
//            });
//
//            // 后台管理员角色管理
//            Route::group('manager_role',function (){
//                // 获取管理员角色列表
//                Route::get('list','ManagerRole@lists');
//                // 创建管理员角色列表
//                Route::post('create','ManagerRole@create');
//                // 角色绑定权限节点
//                Route::post('bind_rule_menu','ManagerRole@bindRuleMenu');
//                // 获取角色已绑定权限节点
//                Route::get('get_rule_menu','ManagerRole@getRuleMenu');
//                // 修改角色权限节点
//                Route::put('edit_bind_rule_menu','ManagerRole@editBindRuleMenu');
//                // 修改管理员角色列表
//                Route::put('edit','ManagerRole@edit');
//                // 删除管理员角色列表
//                Route::delete('delete','ManagerRole@delete');
//            });
//
//            // 后台管理员角色权限菜单
//            Route::group('manager_role_menus',function (){
//                // 获取管理员权限菜单初始化 // 只允许超管操作
//                Route::get('init','ManagerRoleMenus@init')->middleware(\app\crm\middleware\CheckRoleRuleOnlyAdmin::class);
//                // 获取管理员权限菜单
//                Route::get('getMenu','ManagerRoleMenus@getMenu');
//                Route::get('getModuleTree','ManagerRoleMenus@getModuleTree');
//            });
//
//
//            // 后台优质生活
//            Route::group('better_live',function (){
//                // 获取管理员权限菜单
//                Route::get('company_list','BetterLive@companyList');
//                Route::post('saveArticleNum','BetterLive@saveArticleNum');
//
//            });
//
//
//        })->middleware([
//            \app\crm\middleware\CheckRoleRule::class
//        ]);
//
//
//
//    })->middleware([
//        \app\crm\middleware\CheckToken::class
//    ]);
//
//
//
//})->prefix('app\crm\controller\\')
//    ->middleware([
//        \middleware\CheckRequsetMethod::class,
//        \app\crm\middleware\ActionLogger::class,
//    ])
//    ->allowCrossDomain();


