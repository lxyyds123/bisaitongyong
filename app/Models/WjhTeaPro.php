<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WjhTeaPro extends Model
{
    protected $table = "tea_pro";
    public $timestamps = true;
    protected $primaryKey = "id";
    protected $guarded = [];

    /**
     * 查询教师组个人单项成绩
     * @param $pro_name
     * @param $status
     * @return false
     */
    public static function wjh_get_item($pro_name, $status)
    {
        try {
            $res = self::leftjoin('user_tea', 'user_tea.id', 'tea_pro.tea_id')
                ->select("user_tea.user_name", "tea_pro.tea_id",
                    "user_tea.school", "tea_pro.pro_name", "tea_pro.grade")
                ->where('tea_pro.pro_name', $pro_name)
                ->where('tea_pro.status', $status)
                ->orderBy('tea_pro.grade', 'desc')
                ->get();
            return $res;
        } catch (\Exception $e) {
            logError("查询教师组个人单项成绩失败", [$e->getMessage()]);
            return false;
        }
    }

    public static function wjh_get_teachers($status)
    {
        try {
            $res = self::select('tea_id')
                ->where('status', $status)
                ->get();
            return $res;
        } catch (\Exception $e) {
            logError("查询教师信息失败", [$e->getMessage()]);
            return false;
        }
    }

    public static function wjh_get_detail($tea_id, $status)
    {
        try {
            $res = self::join("user_tea", "tea_pro.tea_id", 'user_tea.id')
                ->select("user_tea.user_name", "user_tea.id",
                    "user_tea.school", "user_tea.all_average")
                ->where("user_tea.id", $tea_id)
                ->where("user_tea.status", $status)
                ->get();
            return $res;
        } catch (\Exception $e) {
            logError("查询教师成绩详情失败", [$e->getMessage()]);
            return false;
        }
    }
}
