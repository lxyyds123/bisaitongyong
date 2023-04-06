<?php

namespace App\Http\Controllers\YmController;

use App\Http\Controllers\Controller;
use App\Http\Requests\YYM\AnverageRequest;
use App\Http\Requests\YYM\ModRequest;
use App\Http\Requests\YYM\MRequest;
use App\Http\Requests\YYM\RecordRequest;
use App\Models\YmModel\Project;
use App\Models\YmModel\Scorer;
use App\Models\YmModel\Stu_pro;
use App\Models\YmModel\Tea_pro;

class ScorerController extends Controller
{

    /**
     *  获取比赛项目列表
     *
     * @param AnverageRequest $request
     * @author yym
     */
    public function GetPro(AnverageRequest $request)

    {
        $scorer_id = auth('scorer')->user()->id;
//        $scorer_id = $request['scorer_id'];
        $group = $request['group'];

        $res = Project::get_pro($scorer_id,$group);

        return $res?
            json_success("操作成功!",$res,200):
            json_fail("操作失败!",null,100);

    }

    /**
     *项目条件查询参赛人员
     * @author yym
     * @return \Illuminate\Http\JsonResponse
     */
    public function Select_Pro(MRequest $request)
    {

        $group = $request['group'];
        $pro_name = $request['pro_name'];
        $status = $request['status'];

        if ($group == 1)
        {
            $res =Tea_pro::select_pro($pro_name,$status);
        }elseif ($group == 2)
        {
            $res =Stu_pro::select_pro($pro_name,$status);
        }else
        {
            return json_fail('未有该组别！', null,100);
        }

        return $res?
            json_success("操作成功!",$res,200):
            json_fail("此项目该级别暂未有选手参加，请检查是否查询错误！",null,100);

    }

    /**
     * 录入分数
     * @param RecordRequest $request
     * @return \Illuminate\Http\JsonResponse
     *@author yym
     */
    public function Record(RecordRequest $request)
    {
        $group = $request['group'];
        $id = $request['id'];
        $scores = $request['scores'];
        $scores = explode(',',$scores);
        $scoresLen = count($scores);

//        $scoresLen = substr_count($scores,',') + 1;

        $grade = 0;
        for ($i=0;$i<$scoresLen;$i++)
        {
            $grade  += $scores[$i];
        }
        $grade /= $scoresLen;

        if ($group == 1)
        {
            $res =Tea_pro::record($id,$grade);

        }elseif ($group == 2)
        {
            $res =Stu_pro::record($id,$grade);

        }else
        {
            return json_fail('未有该组别！', null,100);
        }

        return $res?
            json_success("操作成功!",$res,200):
            json_fail("操作失败!",null,100);

    }

    /**
     * 对新密码重新加密
     * @author yym
     * @param $new_pw
     * @return string
     */
    protected static function userHandleUpdate($new_pw)
    {
        $red = bcrypt($new_pw);
        return $red;
    }

    /**
     * 记分员修改密码
     * @param AnverageRequest $request
     * @author yym
     */
    public function Mod_PW(ModRequest $request)
    {
        $scorer_id = auth('scorer')->user()->id;
        $pw = $request['pw'];
        $new_pw = $request['new_pw'];
        $new_pw = self::userHandleUpdate($new_pw);

        $credentials = [
            'id' => $scorer_id,
            'password' => $pw
        ];
        $re = auth( 'scorer')->attempt($credentials );


        if($re != null)
        {
            $res = Scorer::mod_pw($scorer_id,$new_pw);
            return $res ?
                json_success('修改成功!', $res, 200) :
                json_fail('修改失败!', null, 100);
        }else
        {
            return json_fail('密码错误，修改失败!', null, 100);
        }

//        return $res ?
//            json_success('修改成功!', $res, 200) :
//            json_fail('修改失败!', null, 100);
    }







}
