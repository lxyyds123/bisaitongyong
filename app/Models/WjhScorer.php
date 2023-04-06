<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\False_;

class WjhScorer extends Model
{
    protected $table = "scorer";
    public $timestamps = true;
    protected $primaryKey = "id";
    protected $guarded = [];

    /**
     * 查询所有记分员信息
     * @return false
     * @author WJH
     */
    public static function wjh_get_scorers()
    {
        try {
            $res = self::select('id', 'scorer_name', 'gender', 'id_card', 'identity')
                ->get();
            return $res;
        } catch (\Exception $e) {
            logError('查询所有记分员信息失败', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * 检查目标记分员是否存在
     * @param $id
     * @return false
     */
    public static function wjh_check_id($id)
    {
        try {
            $cnt = self::select("id")
                ->where('id', $id)
                ->count();
            return $cnt;
        } catch (\Exception $e) {
            logError('查询id为' . $id . '的记分员失败', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * 修改记分员信息
     * @param Request $request
     * @return false
     * @author WJH
     */
    public static function wjh_modify_scorer(Request $request)
    {
        try {
            $cnt = self::where('id', $request['id'])
                ->update([
                    'scorer_name' => $request['scorer_name'],
                    'identity' => $request['identity'],
                    'gender' => $request['gender'],
                    'id_card' => $request['id_card'],
                ]);
            return $cnt;
        } catch (\Exception $e) {
            logError('修改id为' . $request['id'] . '的记分员失败', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * 重置记分员密码
     * @param $id
     * @return false
     * @author WJH
     */
    public static function wjh_reset_scorer($id)
    {
        try {
            $cnt = self::where('id', $id)
                ->update([
                    'password' => bcrypt("123456")
                ]);
            return $cnt;
        } catch (\Exception $e) {
            logError('重置id为' . $id . '的记分员失败', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * 删除记分员信息
     * @param $id
     * @return false
     * @author WJH
     */
    public static function wjh_delete_scorer($id)
    {
        try {
            $cnt = self::where('id', $id)
                ->delete();
            return $cnt;
        } catch (\Exception $e) {
            logError('删除id为' . $id . '的记分员失败', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * 通过身份证号检查记分员是否已经存在
     * @param $id_card
     * @return false
     * @author WJH
     */
    public static function wjh_check_id_card($id_card)
    {
        try {
            $cnt = self::select("id")
                ->where("id_card", $id_card)
                ->count();
            return $cnt;
        } catch (\Exception $e) {
            logError('查询记分员身份证号失败', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * 创建新的记分员
     * @param Request $request
     * @return false
     * @author WJH
     */
    public static function wjh_create_scorer(Request $request)
    {
        try {
            $res = self::create([
                'password' => bcrypt("123456"),
                'identity' => $request['identity'],
                'scorer_name' => $request['scorer_name'],
                'id_card' => $request['id_card'],
                'gender' => $request['gender'],
            ]);
            return $res;
        } catch (\Exception $e) {
            logError('添加记分员失败', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * 通过记分员姓名，模糊查询其信息
     * @param $scorer_name
     * @return false
     */
    public static function wjh_select_by_name($scorer_name)
    {
        try {
            $res = self::select("id", "scorer_name", "gender", "id_card", "identity")
                ->where("scorer_name", "like", '%' . $scorer_name . '%')
                ->get();
            return $res;
        } catch (\Exception $e) {
            logError('模糊查询记分员失败', [$e->getMessage()]);
            return false;
        }
    }
}
