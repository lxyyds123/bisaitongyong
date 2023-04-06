<?php

namespace App\Models\YmModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Stu_pro extends Model
{
    protected $primaryKey = "id";
    protected $table = 'stu_pro';
    protected $guarded =[];
    public $timestamps = true;


    /**
     * 记分员端  学生组查找对应项目的参赛人员
     *
     * @param $pro_name
     * @param $status
     * @return false
     *@author yym
     */
    public static function select_pro($pro_name,$status)
    {
        try {
            $res = self::leftjoin('user_stu','stu_pro.stu_id','user_stu.id')
//                ->select('stu_pro.stu_id')
                ->where('stu_pro.pro_name',$pro_name)
                ->where('stu_pro.status',$status)
                ->select
                (
                    "stu_pro.id",
                    "user_stu.user_name",
                    "stu_pro.stu_id",
                    "user_stu.school",
                    "user_stu.status",
                    "stu_pro.pro_name",
                    "stu_pro.merge_id",
                    "stu_pro.status1"
                )
                //按照降序排列
                ->orderBy('stu_pro.status1','asc')
                ->get();


            return $res;


        }catch (\Exception $exception){
            logError('查找失败',[$exception->getMessage()]);
            return false;
        }
    }


    /**
     * 记分员端 学生组录入成绩
     *
     * @author yym
     * @param $id
     * @param $grade
     * @return false
     */
    public static function record($id,$grade)
    {
        try {

            $res = self::where('id',$id)->update
            ([
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
     * 成绩分析端  查询某项目参赛人员的成绩
     *
     * @author yym
     * @param $pro_name
     * @param $status
     * @return false
     */
    public static function select_grade($pro_name,$status)
    {

        try
        {
            $res = self::leftjoin('user_stu','stu_pro.stu_id','user_stu.id')

                ->where('stu_pro.pro_name',$pro_name)
                ->where('stu_pro.status',$status)
                ->select
                (
                    "user_stu.user_name",
                    "user_stu.id_card",
                    "stu_pro.stu_id",
                    "user_stu.school",
                    "user_stu.status",
                    "stu_pro.grade"

                )
                //按照降序排列
                ->orderBy('stu_pro.grade','asc')
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


    /**
     * 成绩分析端 导出Excel
     * @author yym
     * @param $pro_name
     * @param $status
     * @return false
     */
    public static function export($pro_name,$status)
    {

        try {

            $res = self::leftjoin('user_stu','stu_pro.stu_id','user_stu.id')

                ->where('stu_pro.pro_name',$pro_name)
                ->where('stu_pro.status',$status)
                ->select
                (
                    "user_stu.user_name as 姓名",
                    "user_stu.id_card as 身份证号",
                    "stu_pro.stu_id as 身份标识码",
                    "user_stu.school as 学校",
                    "user_stu.status as 级别",
                    "stu_pro.grade as 总分"

                )
                //按照降序排列
                ->orderBy('stu_pro.grade','asc')
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
