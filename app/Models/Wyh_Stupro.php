<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wyh_Stupro extends Model
{
    //
    protected $table = 'stu_pro';
    protected $guarded = [];


    public static function wyh_createe($request)
    {
        try {
            $date= self::create(['stu_id'=>$request['7'],
                'pro_name'=>$request['5'],
                'merge_id'=>$request['8'],
                'status'=>$request['3'],
                'status1'=>0]);
            return $date ?
                $date :
                false;
        } catch (\Exception $e) {
            logError('添加信息失败!', [$e->getMessage()]);
            die($e->getMessage());
            return false;
        }
    }

    public static function wyh_jiancha(string $merge_id)
    {

        try{
            $count =self::select('merge_id')
                ->where('merge_id',$merge_id)
                ->count();
            return $count;
        }catch (\Exception $e) {
            logError("参赛识别码查询失败！", [$e->getMessage()]);
            return false;
        }

    }
 //查询所有数据
    public static function wyh_selet()
    {
        try{
            $date =self::select()->get();
            return $date;
        }catch (\Exception $e) {
            logError("项目查询失败！", [$e->getMessage()]);
            return false;
        }
    }

}
