<?php

namespace app\common\model\crebo;

use app\common\exception\LoginError;
use think\Exception;
use think\facade\Session;
use think\Model;

class Auth extends Model
{

    /**
     * 用户注册方法
     * @param $username
     */
    public function add($uuid,$share_id){
		
        if(self::where('uuid',$uuid)->count() > 0){
            throw new Exception('二维码已失效');
        }
 
        $user = [
            'uuid' => $uuid,
			'share_id' => $share_id,
            'openid' =>'',
            'userid' =>0,
            'create_time' => time(),
            'status' =>0
        ];

        self::insert($user);

        $user_id = self::getLastInsID();

        return true;

    }


 

 

 
}