<?php

namespace App\Models\YmModel;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $primaryKey = "id";
    protected $table = 'project';
    protected $guarded =[];
    public $timestamps = true;

    /**
     * 记分员端 查找项目
     * @author yym
     * @param $scorer_id
     * @param $group
     * @return false|string
     */
    public static function get_pro($scorer_id,$group){
        try {
            if ($group == "1"){
                $re = self::select('pro_name')
                    ->where('group','<>',2)
                    ->where('scorer_id',$scorer_id)
                    ->get();
            }elseif ($group == "2"){
                $re = self::select('pro_name')
                    ->where('group','>=',2)
                    ->where('scorer_id',$scorer_id)
                    ->get();
            }
            return $re;
        }catch (\Exception $exception){
            logError('查找失败！', [$exception->getMessage()]);
            return false;
        }
    }


    /**
     * 成绩分析端  查找项目
     *
     * @author yym
     * @param $group
     * @return false
     */
    public static function get_pros($group)
    {
        try {
            if ($group == "1"){
                $res = self::select('pro_name')
                    ->where('group','<>',2)
                    ->get();
            }elseif ($group == "2"){
                $res = self::select('pro_name')
                    ->where('group','>=',2)
                    ->get();
            }
            return $res;
        }catch (\Exception $exception){
            logError('查找失败！', [$exception->getMessage()]);
            return false;
        }
    }
}
