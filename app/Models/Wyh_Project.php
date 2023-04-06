<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wyh_Project extends Model
{
    protected $table = 'project';
    protected $guarded = [];
    //
    public static function wyh_check($pro_name)
    {

        try{
            $count =self::select('pro_name')
                ->where('pro_name',$pro_name)
                ->count();
            return $count;
        }catch (\Exception $e) {
            logError("项目查询失败！", [$e->getMessage()]);
            return false;
        }
    }

    public static function wyh_create2($pro_name,$group)
    {
        try{
            $count =self::create([
                'pro_name'=>$pro_name,
                 'group'=>$group,
                'status1'=>0]);
            return $count;
        }catch (\Exception $e) {
            logError("账号查询失败！", [$e->getMessage()]);
            return false;
        }
    }
}
