<?php

namespace App\Models\YmModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Scorer extends Model
{
    protected $primaryKey = "id";
    protected $table = 'scorer';
    protected $guarded =[];
    public $timestamps = true;


    /**
     * 记分员端修改密码
     *
     * @author yym
     * @param $scorer_id
     * @param $new_pw
     * @return false
     */
    public static function mod_pw($scorer_id,$new_pw)
    {

        try {

            $res =self::where('id',  $scorer_id)->update([
                'password' => $new_pw,
            ]);
            return $res;


        }catch (\Exception $exception)
        {
            logError('查找失败', [$exception->getMessage()]);
            return false;
        }


    }
}
