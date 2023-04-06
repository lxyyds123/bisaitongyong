<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Wyh_Stu extends Model
{
    //
    protected $table = 'user_stu';
    protected $guarded = [];
    public static function wyh_addd(array $data)
    {

           $data =self::create([
               'user_name'=>$data[0],
               'id_card'=>$data[1],
               'school'=>$data[2],
               'status'=>$data[3],
               'instructor'=>$data[6],
               'all_average'=>null
           ]);
           return $data;


    }

    public static function wyh_cheack($id_card)
    {
        try{
            $count =self::select('id_card')
                ->where('id_card',$id_card)
                ->count();
            return $count;
        }catch (\Exception $e) {
            logError("账号查询失败！", [$e->getMessage()]);
            return false;
        }
    }

    public static function wyh_selet1($id)
    {
        try{
            $date =self::select()
                ->where('id',$id)
                ->get();
            if ($date[0]==null)
            {
                return false;
            }
            return $date;
        }catch (\Exception $e) {
            logError("项目查询失败！", [$e->getMessage()]);
            return false;
        }
    }

    public static function wyh_update($request)
    {
        $date=self::select('user_name','id_card','school','status','instructor')
            ->where('id',$request['id'])
            ->update(['user_name'=>$request['user_name'],
                'id_card'=>$request['id_card'],
                'school'=>$request['school'],
                'status'=>$request['status'],
                'instructor'=>$request['instructor'],]);
        return $date;
    }

    public static function wyh_delete($id)
    {
        try{
        $date=self::where('id',$id)->delete();
        $date2=DB::table('stu_pro')->where('stu_id',$id)->delete();
        $date3=$date||$date2;
        return $date3?
            $date3:
            false;
             }catch (\Exception $e) {
            logError("删除失败！", [$e->getMessage()]);
            return false;
        }
    }


}
