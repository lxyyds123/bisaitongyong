<?php

namespace App\Models\YmModel;

use Illuminate\Database\Eloquent\Model;

class Tea_pro extends Model
{
    protected $primaryKey = "id";
    protected $table = 'tea_pro';
    protected $guarded =[];
    public $timestamps = true;


    /**
     * 记分员端  教师组查找对应项目的参赛人员
     *
     * @author yym
     * @param $pro_name
     * @param $status
     * @return false
     */
    public static function select_pro($pro_name,$status)
    {
        try
        {
            $res = self::leftjoin('user_tea','tea_pro.tea_id','user_tea.id')
                ->where('tea_pro.pro_name',$pro_name)
                ->where('tea_pro.status',$status)
                ->select
                (
                    "tea_pro.id",
                    "user_tea.user_name",
                    "tea_pro.tea_id",
                    "user_tea.school",
                    "user_tea.status",
                    "tea_pro.pro_name",
                    "tea_pro.merge_id",
                    "tea_pro.status1"
                )
                //按照降序排列
                ->orderBy('tea_pro.status1','asc')
                ->get();

            return $res;


        }catch (\Exception $exception){
            logError('查找失败',[$exception->getMessage()]);
            return false;
        }
    }


    /**
     * 记分员端  教师组查找对应项目的参赛人员 记录分数
     *
     * @author yym
     * @param $id
     * @param $grade
     * @return false
     */

    public static function record($id,$grade)
    {
        try {

            $res = self::where('id',$id)->update([
                'grade' => $grade ,
                'status1' => 1
            ]);
            return $res;

        }catch (\Exception $exception){
            logError('查找失败',[$exception->getMessage()]);
            return false;
        }
    }

    /**
     * 成绩分析端  查询某项目参赛人员的成绩
     *
     * @author yym
     * @param $pro_name
     * @param $status
     * @return false
     */
    public static function select_grade($pro_name,$status)
    {

        try {

            $res = self::leftjoin('user_tea','tea_pro.tea_id','user_tea.id')
                ->where('tea_pro.pro_name',$pro_name)
                ->where('tea_pro.status',$status)
                ->select
                (
                    "user_tea.user_name",
                    "user_tea.id_card",
                    "tea_pro.tea_id",
                    "user_tea.school",
                    "user_tea.status",
                    "tea_pro.grade"

                )
                //按照降序排列
                ->orderBy('tea_pro.grade','asc')
                ->get();


            $res = $res->toArray();
            $lenth = count($res);
            for ($i = 0; $i < $lenth; $i++) {
                $res[$i]['排名'] = $i + 1;}

            return $res;


        }catch (\Exception $exception)
        {
            logError('教师组此项目该级别暂未有选手参加，请检查是否查询错误！', [$exception->getMessage()]);
            return false;
        }

    }


    /**
     *
     * 成绩分析端   查询项目平均分
     * @author yym
     * @param $pro_name
     * @param $status
     */
    public static function average($pro_name,$status)
    {
        try {

            $re = self::where('pro_name',$pro_name)->where('status',$status)->avg('grade');
            return $re;

        } catch (\Exception $exception) {
            logError('查找失败', [$exception->getMessage()]);
            return false;
        }

    }


    /**
     *成绩分析端  导出Excel
     * @author yym
     * @param $pro_name
     * @param $status
     * @return false
     */
    public static function export($pro_name,$status)
    {
        try {

            $res = self::leftjoin('user_tea','tea_pro.tea_id','user_tea.id')

                ->where('stu_pro.pro_name',$pro_name)
                ->where('stu_pro.status',$status)
                ->select
                (
                    "user_tea.user_name as 姓名",
                    "user_tea.id_card as 身份证号",
                    "tea_pro.tea_id as 身份标识码",
                    "user_tea.school as 学校",
                    "user_tea.status as 级别"

                )
                //按照降序排列
                ->orderBy('tea_pro.grade','asc')
                ->get();

            $res = $res->toArray();
            $lenth = count($res);
            for ($i = 0; $i < $lenth; $i++) {
                $res[$i]['排名'] = $i + 1;}

            return $res;

        }catch (\Exception $exception)
        {
            logError('查找失败', [$exception->getMessage()]);
            return false;
        }

    }

}
