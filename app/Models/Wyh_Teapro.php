<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wyh_Teapro extends Model
{
    protected $table = 'tea_pro';
    protected $guarded = [];
    //
    public static function wyh_cretee1($request)
    {
        try {
            $date= self::create(['tea_id'=>$request['7'],
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

    public static function wyh_jiancha1($merge_id)
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

    public static function wyh_selet2()
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
