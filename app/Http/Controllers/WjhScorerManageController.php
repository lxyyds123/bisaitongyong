<?php

namespace App\Http\Controllers;

use App\Http\Requests\WJH\CreateRequest;
use App\Http\Requests\WJH\IDRequest;
use App\Http\Requests\WJH\ModScorerRequest;
use App\Http\Requests\WJH\SNameRequest;
use App\Models\WjhScorer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;

class WjhScorerManageController extends Controller
{
    // 记分员管理控制器

    /**
     * 查询所有记分员信息
     * @return \Illuminate\Http\JsonResponse
     * @author WJH
     */
    public function wjh_get_scorers()
    {
        $res = WjhScorer::wjh_get_scorers();
        return $res ?
            json_success("查询成功", $res, 200) :
            json_fail("查询失败", null, 100);
    }

    /**
     * 检查记分员是否存在，true为存在，false为不存在
     * @param $id
     * @return bool
     * @author WJH
     */
    private function wjh_check_scorer($id)
    {
        $cnt = WjhScorer::wjh_check_id($id);
        if ($cnt == 0) {
            return false;
        }
        return true;
    }

    /**
     * 检查身份证号是否存在，true为存在，false为不存在
     * @param $id_card
     * @return bool
     * @author WJH
     */
    private function wjh_check_id_card($id_card)
    {
        $cnt = WjhScorer::wjh_check_id_card($id_card);
        if ($cnt == 0) {
            return false;
        }
        return true;
    }

    /**
     * 修改记分员信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @author WJH
     */
    public function wjh_modify_scorer(ModScorerRequest $request)
    {
        $id = $request['id'];
        $id_card = $request['id_card'];
        if (!self::wjh_check_scorer($id)) {
            return json_fail("修改失败，记分员不存在", false, 100);
        }

        // 判断修改后的身份证号是否重复
        if (self::wjh_check_id_card($id_card)) {
            $res = DB::table('scorer')->select("id")->where('id_card', $id_card)->get('id');
            $t_id = $res->toArray()[0]->id;
            // 并且满足目前占用此身份证号的记分员不是当前待修改的记分员
            if ($t_id != $id) {
                return json_fail("修改失败，修改后的身份证号已经存在", false, 100);
            }
        }

        $cnt = WjhScorer::wjh_modify_scorer($request);
        if ($cnt == 0) {
            return json_fail("修改失败", false, 100);
        }
        return json_success("修改成功", true, 200);
    }

    /**
     * 重置记分员密码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @author WJH
     */
    public function wjh_reset_scorer(IDRequest $request)
    {
        $id = $request['id'];
        if (!self::wjh_check_scorer($id)) {
            return json_fail("重置失败，记分员不存在", false, 100);
        }

        $cnt = WjhScorer::wjh_reset_scorer($id);
        if ($cnt == 0) {
            return json_fail("重置失败", false, 100);
        }
        return json_success("重置成功", true, 200);
    }


    /**
     * 删除记分员信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @author WJH
     */
    public function wjh_delete_scorer(IDRequest $request)
    {
        $id = $request['id'];
        if (!self::wjh_check_scorer($id)) {
            return json_fail("删除失败，记分员不存在", false, 100);
        }

        $cnt = WjhScorer::wjh_delete_scorer($id);
        if ($cnt == 0) {
            return json_fail("删除失败", false, 100);
        }
        return json_success("删除成功", true, 200);
    }

    /**
     * 新增记分员
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @author WJH
     */
    public function wjh_create_scorer(CreateRequest $request)
    {
        $id_card = $request['id_card'];
        $cnt = WjhScorer::wjh_check_id_card($id_card);
        if ($cnt == 0) {
            $res = WjhScorer::wjh_create_scorer($request);
            return $res ?
                json_success("添加成功", true, 200) :
                json_fail("添加失败", false, 100);
        }
        return json_fail("添加失败，记分员已存在", false, 100);
    }

    /**
     * 通过记分员姓名模糊查询其信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @author WJH
     */
    public function wjh_select_by_name(SNameRequest $request)
    {
        $scorer_name = $request['scorer_name'];
        $res = WjhScorer::wjh_select_by_name($scorer_name);
        return $res ?
            json_success("查询成功", $res, 200) :
            json_fail("查询失败", null, 100);
    }

    /**
     * 导出记分员信息为Excel
     * @return \Illuminate\Http\JsonResponse|string
     * @throws \OpenSpout\Common\Exception\IOException
     * @throws \OpenSpout\Common\Exception\InvalidArgumentException
     * @throws \OpenSpout\Common\Exception\UnsupportedTypeException
     * @throws \OpenSpout\Writer\Exception\WriterNotOpenedException
     * @author WJH
     */
    public function wjh_export()
    {
        $res = WjhScorer::select("id as 账号", "identity as 身份",
            "scorer_name as 姓名", "id_card as 身份证号", "gender as 性别")
            ->orderBy("id")
            ->get();
        if (!$res) {
            return json_fail("导出失败，没有记分员可以导出", null, 100);
        }
        return (new FastExcel($res))->download('记分员名单.xlsx');
    }
}
