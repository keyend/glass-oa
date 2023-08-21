<?php
use think\facade\Route;

// 控制台首页
Route::get('', 'Index/index')->middleware('ConsoleAuthorize', false)->name('sysIndex');

// 无身份校验
Route::group(function() {
    Route::rule('login', 'system.auth/login', 'POST|GET')->name('sysLogin');
    Route::group('auth', function() {
        Route::any('verifyImage', '/verifyImage')->name('sysVerifyCode');
    })->prefix('system.auth');
});

// 权限验证
Route::group(function() {
    // 系统管理 -> 账户安全
    Route::group('security', function() {
        Route::group('user', function() {
            Route::get('', '/index')->name('sysUser');
            Route::rule('/add', '/add', 'GET|POST')->name('sysUserAdd');
            Route::rule('/edit', '/edit', 'GET|POST')->name('sysUserEdit');
            Route::rule('/status', '/status', 'GET|POST')->name('sysUserStatus');
            Route::rule('/update', '/update', 'GET|POST')->name('sysUserUpdate');
            Route::post('/delete', '/delete')->name('sysUserDelete');
        })->prefix('system.user');
        Route::group('group', function() {
            Route::get('', '/index')->name('sysGroup');
            Route::rule('/add', '/add', 'GET|POST')->name('sysGroupAdd');
            Route::rule('/edit', '/edit', 'GET|POST')->name('sysGroupEdit');
            Route::post('/delete', '/delete')->name('sysGroupDelete');
        })->prefix('system.group');
        Route::group('role', function() {
            Route::rule('', '/index', 'GET|POST')->name('sysRole');
            Route::rule('/add', '/add', 'GET|POST')->name('sysRoleAdd');
            Route::rule('/edit', '/edit', 'GET|POST')->name('sysRoleEdit');
            Route::post('/delete', '/delete')->name('sysRoleDelete');
        })->prefix('system.role');
        Route::group('rule', function() {
            Route::rule('', '/index', 'GET|POST')->name('sysRule');
            Route::rule('/add', '/add', 'GET|POST')->name('sysRuleAdd');
            Route::rule('/edit', '/edit', 'GET|POST')->name('sysRuleEdit');
            Route::post('/delete', '/delete')->name('sysRuleDelete');
        })->prefix('system.rule');
    });
    // 系统管理 -> 日志
    Route::group('logs', function() {
        Route::get('', '/index')->name('sysLogs');
        Route::rule('/operator', '/operator', 'GET|POST')->name('sysLogsOperator');
        Route::rule('/login', '/login', 'GET|POST')->name('sysLogsLogin');
    })->prefix('system.logs');
    // 系统管理 -> 参数配置
    Route::group('config', function() {
        Route::get('', '/index')->name('sysConfig');
        Route::rule('/basic', '/basic', 'GET|POST')->name('sysConfigBasic');
        Route::rule('/register', '/register', 'GET|POST')->name('sysConfigConnect');
        Route::rule('/pay', '/pay', 'GET|POST')->name('sysConfigPay');
        Route::rule('/mail', '/email', 'GET|POST')->name('sysConfigMail');
        Route::rule('/vip', '/vip', 'GET|POST')->name('sysConfigVip');
        Route::rule('/api', '/api', 'GET|POST')->name('sysConfigApi');
        Route::rule('/login', '/login', 'GET|POST')->name('sysConfigLogin');
    })->prefix('system.config');
    // 组件
    Route::group('util', function() {
        Route::post('/album/uploadImage', '/uploadImage')->name('sysAttachUpload');
        Route::post('/album/deleteImage', '/deleteImage')->name('sysAttachDelete');
        Route::post('/album/addGroup', '/addAlbumGroup')->name('sysAttachGroup');
        Route::rule('/album', '/album', 'POST|GET')->name('sysAttach');
    })->prefix('util');
    // 会员用户 / 用户组
    Route::group(function() {
        // 类目
        Route::group('category', function() {
            Route::get('', '/category')->name('category');
            Route::rule('add', '/addCategory', 'POST|GET')->name('categoryAdd');
            Route::rule('edit', '/editCategory', 'POST|GET')->name('categoryEdit');
            Route::rule('delete', '/deleteCategory', 'POST|GET')->name('categoryDel');
        });
        // 工艺
        Route::group('craft', function() {
            Route::get('', '/craft')->name('craft');
            Route::rule('add', '/addCraft', 'POST|GET')->name('craftAdd');
            Route::rule('edit', '/editCraft', 'POST|GET')->name('craftEdit');
            Route::rule('delete', '/deleteCraft', 'POST|GET')->name('craftDel');
        });
        // 用户
        Route::group('member', function() {
            Route::get('', '/index')->name('member');
            Route::rule('add', '/add', 'POST|GET')->name('memberAdd');
            Route::rule('edit', '/edit', 'POST|GET')->name('memberEdit');
            Route::rule('delete', '/delete', 'POST|GET')->name('memberDel');
            Route::rule('pay', '/pay', 'POST|GET')->name('memberPay');
            Route::post('category/update', '/updateCategory')->name('memberCategoryUpdate');
            Route::post('update', '/update')->name('memberUpdate');
            Route::rule("palst", '/palst')->name("memberPalst");
        });
    })->prefix('member');
    // 订单管理
    Route::group('order', function() {
        Route::rule('', '/index', 'POST|GET')->name('order');
        Route::rule('create', '/add', 'POST|GET')->name('orderAdd');
        Route::rule('edit', '/edit', 'POST|GET')->name('orderEdit');
        Route::get('list/:id', '/getList')->name('orderList');
        Route::rule('detail', '/detail', 'GET|POST')->name('orderDetail');
        Route::post('trash', '/trash')->name('orderTrash');
        Route::rule("delivery/list", '/deliveryList', 'POST|GET')->name('delivery');
        Route::rule('delivery/print', '/deliveryPrint', 'POST|GET')->name('orderDeliveryPrintList');
        Route::post('delivery/update', '/deliveryUpdate')->name("orderDeliveryUpdate");
        Route::rule('delivery', '/delivery', 'POST|GET')->name('orderDelivery');
        Route::rule('converge', '/converge')->name("orderConverge");
        Route::rule('label', '/label')->name("orderLabel");
        Route::post('print/delivery/record', '/printDeliveryRecord')->name('orderDeliveryPrint');
        Route::post('print/record', '/printRecord')->name('orderGoodsPrint');
        Route::get('print', '/print')->name('orderPrint');
        Route::rule('supplement', '/supplement', 'GET|POST')->name('orderSupplement');
        Route::post('pay/update', '/payUpdate')->name("orderPayUpdate");
        Route::get('pay/logs', '/payLogs')->name("orderPayLogs");
        Route::post('pay', '/pay')->name("orderPay");
        Route::post('update', '/update')->name("orderUpdate");
    })->prefix('order');
})->middleware('ConsoleAuthorize', true);
// 必需登录
Route::group(function() {
    Route::group('user', function() {
        Route::get('info', '/accountInfo')->name('sysUserProfile');
        Route::rule('security', '/accountSecurity', 'GET|POST')->name('sysUserSecurity');
        Route::get('update', '/accountUpdate')->name('sysUserInfoUpdate');
    })->prefix('system.user');
    Route::post('user/logout', 'system.auth/adminLogout')->name('sysLogout');
    Route::get('index/dashboard', 'index/dashboard')->name('sysDashboard');
    Route::get('index/clear_cache', 'index/clearCache')->name('sysClearCache');
    Route::get('security', '/index')->prefix('system.security')->name('sysSecurity');
})->middleware('ConsoleAuthorize', false);

// 定义MISS
Route::miss('Index/miss');