<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Lx_Project extends Model
{
    protected $table = "project";
    public $timestamps = true;
    protected $primaryKey = "id";
    protected $guarded = [];



    /**
     * 通过比赛名称查找比赛项目
     * @param $pro_name
     * @return void
     */
    public static function lx_select_pro_by_name($pro_name)
    {
        try {
            $data =self::select()
                ->where('pro_name',$pro_name)
                ->orWhere('pro_name','like','%'.$pro_name.'%')
                ->get();
            return $data;
        }catch (\Exception $e) {
            logError('注册失败', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * 修改项目信息
     *
     */

    public static function lx_update_modify($id, $pro_name, $pro_date, $pro_address, $host, $scorer_id, $group,$pro_name4)
    {
        $da1 = DB::table('stu_pro')->where('pro_name',$pro_name4)->count();
        $da2 = DB::table('tea_pro')->where('pro_name',$pro_name4)->count();
        $da3 = DB::table('project')->where('pro_name',$pro_name4)->count();
        if($da1 != 0 || $da2 != 0 || $da3 != 0){
            try {
                self::where('id',$id)
                    ->update([
                        'pro_name' => $pro_name,
                        'pro_date' => $pro_date,
                        'pro_address' => $pro_address,
                        'host' => $host,
                        'scorer_id' => $scorer_id,
                        'group' => $group,
                    ]);
                DB::table('stu_pro')->where('pro_name',$pro_name4)
                    ->update([
                        'pro_name' => $pro_name,
                    ]);
                DB::table('tea_pro')->where('pro_name',$pro_name4)
                    ->update([
                        'pro_name' => $pro_name,
                    ]);
                return true;
            }catch (\Exception $e) {
                logError('操作失败', [$e->getMessage()]);
                return false;
            }
        }else{
            return false;
        }

    }

    /**
     * 获得项目信息
     * @param  $id1
     * @return void
     */

    public static function lx_select_pro($scorer_id)
    {
        try {
            $data = self::where('scorer_id',$scorer_id)->get();
            return $data;
        }catch (\Exception $e) {
            logError('操作失败', [$e->getMessage()]);
            return false;
        }

    }


    /**
     * 删除比赛项目信息
     * @param $id
     * @param $pro_name4
     * @return void
     */
    public static function lx_delete_pro($id, $pro_name4)
    {
        $da1 = DB::table('stu_pro')->where('pro_name',$pro_name4)->count();
        $da2 = DB::table('tea_pro')->where('pro_name',$pro_name4)->count();
        $da3 = DB::table('project')->where('pro_name',$pro_name4)->count();
        if($da1 != 0 || $da2 != 0 || $da3 != 0){
            try {
                self::where('id',$id)->delete();
                DB::table('stu_pro')->where('pro_name',$pro_name4)->delete();
                DB::table('tea_pro')->where('pro_name',$pro_name4)->delete();
                return true;
            }catch (\Exception $e) {
                logError('操作失败', [$e->getMessage()]);
                return false;
            }
        }else{
            return false;
        }
    }
}
