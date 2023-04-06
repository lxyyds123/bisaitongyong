<?php

namespace App\Http\Controllers;

use App\Exports\UserExport;
use App\Models\Wyh_Analysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class Wyh_AnalysisController extends Controller
{
    /**
     * 注册
     * @param Request $registeredRequest
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function registered(Request $registeredRequest)
    {
        $count = Wyh_Analysis::checknumber($registeredRequest);   //检测账号密码是否存在

        if($count == 0)
        {
            $admin = Wyh_Analysis::createUser(self::userHandle($registeredRequest));

            return  $admin ?
                json_success('注册成功!',$admin,200  ) :
                json_fail('注册失败!',null,100  ) ;
        }
        else{
            return
                json_success('注册失败!该工号已经注册过了！',null,100  ) ;
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        $credentials = self::credentials($request);   //从前端获取账号密码
        $token = auth('analyst')->attempt($credentials);   //获取token
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
        return ['analyst' => $request['analyst'], 'password' => $request['password']];
    }

    protected function userHandle($request)   //对密码进行哈希256加密
    {
        $registeredInfo = $request->except('password_confirmation');
        $registeredInfo['password'] = bcrypt($registeredInfo['password']);
        $registeredInfo['analyst'] = $registeredInfo['analyst'];
        return $registeredInfo;
    }
    //优秀组织奖
    public function wyh_organization(Request $request)
    {
        $group=$request['group'];
        $count=[];
        if ($group=="学生组")
        {
            $school=DB::table('user_stu')->select('school')->get();
           foreach ($school as $k=>$v)
           {
               $a=array('school'=>$v->school,'count'=>1);
               array_push($count,$a);
           }
            $count1=array();
            for ($i=0;$i<sizeof($count);$i++)
            {
                if (empty($count1))
                {
                    array_push($count1,$count["$i"]);
                }
                else
                {
                    $schooll=$count["$i"]['school'];
                    $found_school = array_column($count1, 'school');//获取已存在的school的所有值
                    $found_key = array_search("$schooll", $found_school);//查找school的某一值
                    if ($found_key!==false)
                    {
                        foreach ($count1 as $k=>&$v)
                        {
                            if ($schooll==$v['school'])//如果已经有这个学就加1
                            {
                                $v['count']+=1;
                                break;
                            }
                        }
                    }
                    else{
                        array_push($count1,$count["$i"]);//没有的话直接创建一个
                    }
                }
            }


        }
        else{
            $school=DB::table('user_tea')->select('school')->get();
            foreach ($school as $k=>$v)
            {
                $a=array('school'=>$v->school,'count'=>1);
                array_push($count,$a);
            }
            $count1=array();
            for ($i=0;$i<sizeof($count);$i++)
            {
                if (empty($count1))
                {
                    array_push($count1,$count["$i"]);
                }
                else
                {
                    $schooll=$count["$i"]['school'];
                    $found_school = array_column($count1, 'school');//获取已存在的school的所有值
                    $found_key = array_search("$schooll", $found_school);//查找school的某一值
                    if ($found_key!==false)
                    {
                        foreach ($count1 as $k=>&$v)
                        {
                            if ($schooll==$v['school'])
                            {
                                $v['count']+=1;
                                break;
                            }
                        }
                    }
                    else{
                        array_push($count1,$count["$i"]);
                    }
                }
            }
        }
        $countall=array_column($count1,'count');
        array_multisort($countall, SORT_DESC,$count1);//对数组进行排序
        $huojian=sizeof($count1)*0.3;//获奖最少排名
        if ($huojian<1)
        {
            for ($j=0;$j<sizeof($count1);$j++)
            {

                if ($j==0)
                {
                    $re=array('paiming'=>$j+1,'huojian'=>'优秀组织奖');
                    $count1[$j]=array_merge( $count1[$j], $re);
                }
                else
                {
                    $re=array('paiming'=>$j+1,'huojian'=>'null');
                    $count1[$j]=array_merge( $count1[$j], $re);
                }

           }
        }

            else
            {
                for ($j=0;$j<sizeof($count1);$j++)
                {

                    if ($j<=$huojian-1)
                    {
                        $re=array('paiming'=>$j+1,'huojian'=>'优秀组织奖');
                        $count1[$j]=array_merge( $count1[$j], $re);
                    }
                    else
                    {
                        $re=array('paiming'=>$j+1,'huojian'=>'null');
                        $count1[$j]=array_merge( $count1[$j], $re);
                    }

                }
            }
         return $count1?
             json_success("操作成功",$count1,200):
             json_fail('操作失败',null,100);

    }
    //优秀组织奖的excel的导出
    public function wyh_organizationexpor(Request $request)
    {
        $date=self::wyh_organization($request);
        /**
         * 用户列表导出
         * @param Request $request
         */
        $row = [[
            "school"=>'学校名称',
            "count"=>'参与人数',
            "paiming"=>'排名',
            "huojian"=>'获奖信息',
        ]];
      $data=$date->original['data'];
        return Excel::download(new UserExport($row,$data), date('Y:m:d ') . '用户列表.xls');
    }
    //优秀团体奖
    public function wyh_group(Request $request)
    {
        $group=$request['group'];
        $count=[];
        if ($group=="学生组") {
            $school = DB::table('user_stu')->select('school', 'all_average')->get();
            foreach ($school as $k => $v) {
                $a = array('school' => $v->school, 'count' => 1, 'all_average' => $v->all_average);
                array_push($count, $a);
            }
            $count1 = array();
            for ($i = 0; $i < sizeof($count); $i++) {
                if (empty($count1)) {
                    array_push($count1, $count["$i"]);
                }

                else
                    {
                        $schooll = $count["$i"]['school'];
                        $found_school = array_column($count1, 'school');//获取已存在的school的所有值
                        $found_key = array_search("$schooll", $found_school);//查找school的某一值
                        if ($found_key !== false) {
                            foreach ($count1 as $k => &$v) {
                                if ($schooll == $v['school']) {
                                    $v['count'] += 1;
                                    $v['all_average'] += $count["$i"]['all_average'];
                                    break;
                                }
                            }
                        } else {
                            array_push($count1, $count["$i"]);
                        }
                    }
                }
            }
        else
        {

            $school = DB::table('user_tea')->select('school', 'all_average')->get();
            foreach ($school as $k => $v) {
                $a = array('school' => $v->school, 'count' => 1, 'all_average' => $v->all_average);
                array_push($count, $a);
            }
            $count1 = array();
            for ($i = 0; $i < sizeof($count); $i++) {
                if (empty($count1)) {
                    array_push($count1, $count["$i"]);
                }

                else
                {
                    $schooll = $count["$i"]['school'];
                    $found_school = array_column($count1, 'school');//获取已存在的school的所有值
                    $found_key = array_search("$schooll", $found_school);//查找school的某一值
                    if ($found_key !== false) {
                        foreach ($count1 as $k => &$v) {
                            if ($schooll == $v['school']) {
                                $v['count'] += 1;
                                $v['all_average'] += $count["$i"]['all_average'];
                                break;
                            }
                        }
                    } else {
                        array_push($count1, $count["$i"]);
                    }
                }
            }
        }
        $countall=array_column($count1,'all_average');
        array_multisort($countall, SORT_DESC,$count1);//对数组进行排序
        $huojian=sizeof($count1)*0.6;//获奖最少排名
        if ($huojian<=3)
        {
            for ($j=0;$j<sizeof($count1);$j++)
            {

                if ($j==0)
                {
                    $re=array('paiming'=>$j+1,'huojian'=>'一等奖');
                    $count1[$j]=array_merge( $count1[$j], $re);
                }
                else if($j==1)
                {
                    $re=array('paiming'=>$j+1,'huojian'=>'二等奖');
                    $count1[$j]=array_merge( $count1[$j], $re);
                }
                else if($j==2)
                {
                    $re=array('paiming'=>$j+1,'huojian'=>'三等奖');
                    $count1[$j]=array_merge( $count1[$j], $re);
                }
                else
                {
                    $re=array('paiming'=>$j+1,'huojian'=>'null');
                    $count1[$j]=array_merge( $count1[$j], $re);
                }

            }
        }
        else
        {
            for ($j=0;$j<sizeof($count1);$j++)
            {

                if ($j<=$huojian*0.3-1)
                {
                    $re=array('paiming'=>$j+1,'huojian'=>'一等奖');
                    $count1[$j]=array_merge( $count1[$j], $re);
                }
                else if($j<=$huojian*0.7-1)
                {
                    $re=array('paiming'=>$j+1,'huojian'=>'二等奖');
                    $count1[$j]=array_merge( $count1[$j], $re);
                }
                else if ($j<=$huojian-1)
                {
                    $re=array('paiming'=>$j+1,'huojian'=>'三等奖');
                    $count1[$j]=array_merge( $count1[$j], $re);
                }
                else
                {
                    $re=array('paiming'=>$j+1,'huojian'=>'null');
                    $count1[$j]=array_merge( $count1[$j], $re);
                }

            }
        }
        return $count1?
            json_success('操作成功!',$count1,200):
            json_fail('操作失败!',null,100);

    }
    //优秀团体奖的excel的导出
    public function wyh_groupexport(Request $request)
    {
        $date=self::wyh_group($request);
        /**
         * 用户列表导出
         * @param Request $request
         */
        $row = [[
            "school"=>'学校名称',
            "count"=>'参与人数',
            "all_average"=>'总分',
            "paiming"=>'排名',
            "huojian"=>'获奖信息',
        ]];
        $data=$date->original['data'];
        return Excel::download(new UserExport($row,$data), date('Y:m:d ') . '优秀团体奖.xls');
    }

}
