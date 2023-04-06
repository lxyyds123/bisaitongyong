<?php

namespace App\Http\Controllers;

use App\Models\Wyh_Analysis;
use App\Models\Wyh_Scorer;
use Illuminate\Http\Request;

class Wyh_ScorerController extends Controller
{
    /**
     * 注册
     * @param Request $registeredRequest
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function registered(Request $registeredRequest)
    {

            $scorer = Wyh_Scorer::createUser(self::userHandle($registeredRequest));

            return   $scorer?
                json_success('注册成功!',$scorer,200  ) :
                json_fail('注册失败!',null,100  ) ;
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        $credentials = self::credentials($request);   //从前端获取账号密码
        $token = auth('scorer')->attempt($credentials);   //获取token
        return $token?
            json_success('登录成功!',$token,  200):
            json_fail('登录失败!账号或密码错误',null, 100 ) ;
        //       json_success('登录成功!',$this->respondWithToken($token,$user),  200);
    }
    //封装token的返回方式
    protected function respondWithToken($token, $msg)
    {
        // $data = Auth::user();
        return json_success( $msg, array(
            'token' => $token,
            //设置权限  'token_type' => 'bearer',
            'expires_in' => auth('analyst')->factory()->getTTL() * 60
        ),200);
    }
    protected function credentials($request)   //从前端获取账号密码
    {
        return ['id' => $request['id'], 'password' => $request['password']];
    }

    protected function userHandle($request)   //对密码进行哈希256加密
    {
        $registeredInfo = $request->except('password_confirmation');
        $registeredInfo['password'] = bcrypt($registeredInfo['password']);
        $registeredInfo['id'] = $registeredInfo['id'];
        return $registeredInfo;
    }
}
