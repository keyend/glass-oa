<?php
use think\facade\Route;

Route::group(function() {
    Route::group("oauth", function() {
        Route::get("code", "/code")->name("OAuthCode");
        Route::get("access_token", "/access_token")->name("OAuthCode");
    })->prefix("oauth");
    Route::group("soclia", function() {
        Route::any("wechat", "/wechat")->name("socliaWechat");
    })->prefix("soclia");
});

Route::group(function() {
    Route::Group("util", function() {
        Route::get("verify_image", "/verifyImage")->name("userVerifyImage");
        Route::get("sms_code", "/verifyCode")->name("userSmsCode");
    })->prefix("util");
    Route::group("user", function() {
        Route::get("login/weixin/qrcode", "/login_qrcode")->name("WeixinLoginQR");
        Route::get("forget/sms_code", "/verifyForgetCode")->name("userForgetCode");
        Route::get("forget/update", "/forgetUpdate")->name("userForgetUpdate");
        Route::post("register", "/register")->name("userRegister");
        Route::post("login", "/login")->name("userLogin");
    })->prefix("user");
    Route::Group("help", function() {
        Route::post("feedback", "/feedback")->name("helpGuestbook");
        Route::get("qs", "/question")->name("helpQuestion");
        Route::get("detail", "/detail")->name("helpDetail");
        Route::get("", "/index")->name("help");
    })->prefix("help");
})->middleware('CheckAccessToken');

Route::group(function() {
    Route::Group("util", function() {
        Route::post("upload/image", "/uploadImage")->name("uploadImage");
    })->prefix("util");
    Route::group("user", function() {
        Route::get("info", "/userinfo")->name("userinfo");
        Route::post("update", "/update")->name("userUpdate");
        Route::post("change_pwd", "/changePassword")->name("userChangePassword");
        Route::post("payment", "/payment")->name("userPayment");
        Route::get("logout", "/logout")->name("userLogout");
    })->prefix("user");
    Route::group("order", function() {
        Route::post("create", "/create")->name("createOrder");
        Route::get("combo", "/combo")->name("orderCombo");
    })->prefix("order");
    Route::group("payment", function() {
        Route::get("types", "/types")->name("paymentTypeList");
        Route::post("order", "/order")->name("paymentOrder");
    })->prefix("payment");
})->middleware('CheckAccessToken', false);

// 定义MISS路由
Route::miss('Index/index');