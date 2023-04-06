<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WjhStuPro extends Model
{
    protected $table = "stu_pro";
    public $timestamps = true;
    protected $primaryKey = "id";
    protected $guarded = [];

    /**
     * 查询目标项目，目标组别，目标层次下的学生的信息和成绩
     * @param $pro_name
     * @param $status
     * @return false
     */
    public static function wjh_get_item($pro_name, $status)
    {
        try {
            $res = self::leftjoin('user_stu', 'user_stu.id', 'stu_pro.stu_id')
                ->select("user_stu.user_name", "stu_pro.stu_id",
                    "user_stu.school", "stu_pro.pro_name", "stu_pro.grade")
                ->where('stu_pro.pro_name', $pro_name)
                ->where('stu_pro.status', $status)
                ->orderBy('stu_pro.grade','desc')
                ->get();
            return $res;
        } catch (\Exception $e) {
            logError("查询学生组个人单项成绩失败", [$e->getMessage()]);
            return false;
        }
    }

    public static function wjh_get_stus($status)
    {
        try {
            $res = self::select('stu_id')
                ->where('status', $status)
                ->get();
            return $res;
        } catch (\Exception $e) {
            logError("查询学生信息失败", [$e->getMessage()]);
            return false;
        }
    }

    public static function wjh_get_detail($stu_id, $status)
    {
        try {
            $res = self::join("user_stu", "stu_pro.stu_id", 'user_stu.id')
                ->select("user_stu.user_name", "user_stu.id",
                    "user_stu.school", "user_stu.all_average")
                ->where("user_stu.id", $stu_id)
                ->where("user_stu.status", $status)
                ->get();
            return $res;
        } catch (\Exception $e) {
            logError("查询学生成绩详情失败", [$e->getMessage()]);
            return false;
        }
    }
}
