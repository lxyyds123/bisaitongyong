<?php

namespace App\Http\Controllers;



use App\Imports\ImportTest;
use App\Models\Admin;
use App\Models\Wyh_Project;
use App\Models\Wyh_Stu;
use App\Models\Wyh_Stupro;
use App\Models\Wyh_Tea;
use App\Models\Wyh_Teapro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    /**
     * 注册
     * @param Request $registeredRequest
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function registered(Request $registeredRequest)
    {
        $count = Admin::checknumber($registeredRequest);   //检测账号密码是否存在
        if ($count == 0) {
            $admin = Admin::createUser(self::userHandle($registeredRequest));

            return $admin ?
                json_success('注册成功!', $admin, 200) :
                json_fail('注册失败!', null, 100);
        } else {
            return
                json_success('注册失败!该工号已经注册过了！', null, 100);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        $credentials = self::credentials($request);   //从前端获取账号密码
        $token = auth('api')->attempt($credentials);   //获取token
        return $token ?
            json_success('登录成功!', $token, 200) :
            json_fail('登录失败!账号或密码错误', null, 100);
        //       json_success('登录成功!',$this->respondWithToken($token,$user),  200);
    }

    //封装token的返回方式
    protected function respondWithToken($token, $msg)
    {
        // $data = Auth::user();
        return json_success($msg, array(
            'token' => $token,
            //设置权限  'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ), 200);
    }

    protected function credentials($request)   //从前端获取账号密码
    {
        return ['admin' => $request['admin'], 'password' => $request['password']];
    }

    protected function userHandle($request)   //对密码进行哈希256加密
    {
        $registeredInfo = $request->except('password_confirmation');
        $registeredInfo['password'] = bcrypt($registeredInfo['password']);
        $registeredInfo['admin'] = $registeredInfo['admin'];
        return $registeredInfo;
    }

    //导入excel
    public function wyh_Upload(Request $request)
    {

        $file = $request->file('file');  //获取UploadFile实例

        if (!$file->isValid()) { //判断文件是否有效
            return redirect()
                ->back()
                ->withErrors('文件上传失败,请重新上传');
        }

        $data = Excel::toArray(new ImportTest, request()->file('file'));

        $re=$data[0];//取出信息

        //录入参赛人员信息管理
        for ($i=1;$i<sizeof($re);$i++){
            $group=$re[$i][4];
            if ($group=='学生')
            {
                $id_card=$re[$i][1];
                $count=Wyh_Stu::wyh_cheack($id_card);//检查是否已录入学生信息
                if ($count==0)
                {
                    $date= Wyh_Stu::wyh_addd($re[$i]);//添加参赛学生信息
                }
            }
            else
            {
                $id_card=$re[$i][1];
                $count=Wyh_Tea::wyh_cheack1($id_card);//检查是否已录入老师信息
                if ($count==0)
                {
                    $date= Wyh_Tea::wyh_addd1($re[$i]);//添加参赛老师信息
                }
            }
        }
        //创建新的参赛项目
        for ($i=1;$i<sizeof($re);$i++)
        {
            if ($re[$i][4]=='老师')
            {
                $group=1;
            }
            else
            {
                $group=2;
            }
            $pro_name=$re[$i][5];
            $count=Wyh_Project::wyh_check($pro_name);//检查是否存在这个项目
            if ($count==0)
            {
                $data=Wyh_Project::wyh_create2($pro_name,$group);//创建一个项目
            }
            else
            {
                $ardeay=DB::table('project')->where('pro_name',$pro_name)->value('group');
                if ($ardeay!=$group)//如果已经存在判断他组别是否未同一个，如果不相同则更新为3
                {
                    $date=DB::table('project')->where('pro_name',$pro_name)->update(['group'=>3]);//
                }
            }
        }
        for ($i=1;$i<sizeof($re);$i++)//创建学生和老师的参赛信息
        {
            $group=$re[$i][4];
            $id_card=$re[$i][1];
            $pro_name=$re[$i][5];
            if ($group=='学生')
            {
                $id=DB::table('user_stu')->where('id_card',$id_card)->value('id');//取出对应学生的id（就是身份识别码）
                $pro_id=DB::table('project')->where('pro_name',$pro_name)->value('id');//取出对应项目的id
                $merge_id=$id.$pro_id;//合成参赛识别码
                $count=Wyh_Stupro::wyh_jiancha($merge_id);
                if ($count==0)
                {
                    array_push($re[$i],$id, $merge_id);
                    $date=Wyh_Stupro::wyh_createe($re[$i]);
                }
            }
            else
            {
                $id=DB::table('user_tea')->where('id_card',$id_card)->value('id');//取出对老师的id（就是身份识别码）
                $pro_id=DB::table('project')->where('pro_name',$pro_name)->value('id');//取出对应项目的id
                $merge_id=$id.$pro_id;//合成参赛识别码
                $count=Wyh_Teapro::wyh_jiancha1($merge_id);
                if ($count==0)
                {
                    array_push($re[$i],$id, $merge_id);
                    $data=Wyh_Teapro::wyh_cretee1($re[$i]);
                }

            }

        }
        return json_success('导入成功!',true,200);

    }
    //查询参赛人员信息
    public function wyh_Select(Request $request)
    {
        $group=$request['group'];
        $cansai=[];//
        if ($group=='学生组')
        {
            $date=Wyh_Stupro::wyh_selet();
            foreach ($date as $k=>$value)
            {
                $id=$value->stu_id;//取出某个参赛人员的id
                $datail=Wyh_Stu::wyh_selet1($id);//获取参赛人员下详情信息
                if ($datail!=null)
                {
                    $datail1=array('id'=>$value->stu_id,
                        'user_name'=>$datail[0]->user_name,
                        'id_card'=>$datail[0]->id_card,
                        'school'=>$datail[0]->school,
                        'status'=>$datail[0]->status,
                        'pro_name'=>$value->pro_name,
                        'merge_id'=>$value->merge_id,
                        'instructor'=>$datail[0]->instructor,);
                    array_push($cansai,$datail1);
                }
            }
            return json_success("查找成功！",$cansai,200);
        }
        else
        {
            $date=Wyh_Teapro::wyh_selet2();
            foreach ($date as $k=>$value)
            {
                $id=$value->tea_id;//取出某个参赛人员的id
                $datail=Wyh_Tea::wyh_selet3($id);//获取参赛人员下详情信息
                if ($datail!=null)
                {
                    $datail1=array(
                        'id'=>$value->tea_id,
                        'user_name'=>$datail[0]->user_name,
                        'id_card'=>$datail[0]->id_card,
                        'school'=>$datail[0]->school,
                        'status'=>$datail[0]->status,
                        'pro_name'=>$value->pro_name,
                        'merge_id'=>$value->merge_id,);
                    array_push($cansai,$datail1);
                }
            }
            return json_success("查找成功！",$cansai,200);
        }
    }
    //修改学生信息
    public function wyh_fixstu(Request $request)
    {
        $pro_name=$request['pro_name'];//新项目
        $count=Wyh_Project::wyh_check($pro_name);//检查更新的项目是否存在
        $group=2;//参赛项目的标记
        if ($count==1)
        {
            $group1=DB::table('project')->where('pro_name',$pro_name)->value('group');//获取新参赛项目的group
            if ($group1!=$group)
            {
                DB::table('project')->where('pro_name',$pro_name)->update(['group'=>3]);//该项目已经存在并且更新为老师和学生都有
            }
            $pro_id=DB::table('project')->where('pro_name',$pro_name)->value('id');//获取跟新后项目后的id
            $id=$request['id'];//学生参赛标识码
            $merge_id1=$id.$pro_id;//合成新的参赛标识码
            $update1=DB::table('stu_pro')
                ->where('merge_id',$request['merge_id'])
                ->update(['pro_name'=>$pro_name,'merge_id'=>$merge_id1,'status'=>$request['status']]);//更新新的参赛信息
        }
        else
        {
            $date=Wyh_Project::wyh_create2( $pro_name,$group);//创建改进后的新增项目
            $pro_id=DB::table('project')->where('pro_name',$pro_name)->value('id');//获取跟新后项目后的id
            $id=$request['id'];//学生参赛标识码
            $merge_id1=$id.$pro_id;//合成新的参赛标识码
            $update1=DB::table('stu_pro')->where('merge_id',$request['merge_id'])->update(['pro_name'=>$pro_name,'merge_id'=>$merge_id1,'status'=>$request['status']]);//更新新的参赛信息

        }
        DB::table('stu_pro')->where('stu_id',$request['id'])->update(['status'=>$request['status']]);//更新所有的层次
        $update=Wyh_Stu::wyh_update($request);//更新详情信息
        return json_success('操作成功!',$update,200);
    }
    //修改老师详情信息
    public function wyh_fixtea(Request $request)
    {
        $pro_name=$request['pro_name'];//新项目
        $count=Wyh_Project::wyh_check($pro_name);//检查更新的项目是否存在
        $group=1;//参赛项目的标记
        if ($count==1)
        {
            $group1=DB::table('project')->where('pro_name',$pro_name)->value('group');//获取新参赛项目的group
            if ($group1!=$group)
            {
                DB::table('project')->where('pro_name',$pro_name)->update(['group'=>3]);//该项目已经存在并且更新为老师和学生都有
            }
            $pro_id=DB::table('project')->where('pro_name',$pro_name)->value('id');//获取跟新后项目后的id
            $id=$request['id'];//老师参赛标识码
            $merge_id1=$id.$pro_id;//合成新的参赛标识码
            $update1=DB::table('tea_pro')
                ->where('merge_id',$request['merge_id'])
                ->update(['pro_name'=>$pro_name,'merge_id'=>$merge_id1,'status'=>$request['status']]);//更新新的参赛信息

        }
        else
        {
            $date=Wyh_Project::wyh_create2( $pro_name,$group);//创建改进后的新增项目
            $pro_id=DB::table('project')->where('pro_name',$pro_name)->value('id');//获取跟新后项目后的id
            $id=$request['id'];//老师参赛标识码
            $merge_id1=$id.$pro_id;//合成新的参赛标识码
            $update1=DB::table('tea_pro')
                ->where('merge_id',$request['merge_id'])
                ->update(['pro_name'=>$pro_name,'merge_id'=>$merge_id1,'status'=>$request['status']]);//更新新的参赛信息
        }
        DB::table('tea_pro')->where('tea_id',$request['id'])->update(['status'=>$request['status']]);//更新所有的层次
        $update=Wyh_Tea::wyh_update1($request);//更新详情信息
        return json_success('操作成功!',$update,200);
    }
    //删除学生信息
    public function wyh_deletstu(Request $request)
    {
        $id=$request['id'];
        $date=Wyh_Stu::wyh_delete($id);
        return $date?
            json_success('删除成功',$date,200):
            json_fail("删除失败",null,100);

    }
    //删除老师信息
    public function wyh_delettea(Request $request)
    {
        $id=$request['id'];
        $date=Wyh_Tea::wyh_delete1($id);
        return $date?
            json_success('删除成功',$date,200):
            json_fail("删除失败",null,100);

    }

}
