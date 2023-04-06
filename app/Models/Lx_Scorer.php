<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lx_Scorer extends Model
{
    protected $table = "scorer";
    public $timestamps = true;
    protected $primaryKey = "id";
    protected $guarded = [];


    public static function lx_select_scorer()
    {
        try {
            $data = self::select('id as scorer_id','scorer_name')->get();
            return $data;
        }catch (\Exception $e) {
            logError('查询失败', [$e->getMessage()]);
            return false;
        }
    }
}
