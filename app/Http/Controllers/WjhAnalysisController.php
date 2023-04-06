<?php

namespace App\Http\Controllers;

use App\Http\Requests\WJH\ItemRequest;
use App\Http\Requests\WJH\OmnipotenceRequest;
use App\Models\WjhStuPro;
use App\Models\WjhTeaPro;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;

class WjhAnalysisController extends Controller
{
    /**
     * 个人单项奖查询
     * @param ItemRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function wjh_get_item(ItemRequest $request)
    {
        $group = $request['group'];
        $pro_name = $request['pro_name'];
        $status = $request['status'];
        if ($group == 1) {
            $res = WjhTeaPro::wjh_get_item($pro_name, $status);
        } elseif ($group == 2) {
            $res = WjhStuPro::wjh_get_item($pro_name, $status);
        } else {
            return json_fail("查询失败，不存在的组别标识", null, 100);
        }

        if (!$res) {
            return json_fail("查询失败", null, 100);
        }

        $res = $res->toArray();
        $cnt = count($res);
        for ($i = 0; $i < $cnt; $i++) {
            $res[$i]['rank'] = $i + 1;
            if ($res[$i]['grade'] > 0) {
                if ($cnt > 3) {
                    if ($i <= (int)($cnt * 0.18)) {
                        $res[$i]['award'] = "一等奖";
                    } elseif ($i > (int)($cnt * 0.18) && $i <= (int)($cnt * 0.42)) {
                        $res[$i]['award'] = "二等奖";
                    } elseif ($i > (int)($cnt * 0.42) && $i <= (int)($cnt * 0.6)) {
                        $res[$i]['award'] = "三等奖";
                    }
                } else {
                    if ($i == 0) {
                        $res[$i]['award'] = "一等奖";
                    } elseif ($i == 1) {
                        $res[$i]['award'] = "二等奖";
                    } elseif ($i == 2) {
                        $res[$i]['award'] = "三等奖";
                    }
                }
            } else {
                $res[$i]['award'] = "无";
            }
        }

        return json_success("查询成功", $res, 200);
    }

    /**
     * 个人单项奖导出
     * @param ItemRequest $request
     * @return \Illuminate\Http\JsonResponse|string|\Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \OpenSpout\Common\Exception\IOException
     * @throws \OpenSpout\Common\Exception\InvalidArgumentException
     * @throws \OpenSpout\Common\Exception\UnsupportedTypeException
     * @throws \OpenSpout\Writer\Exception\WriterNotOpenedException
     */
    public function wjh_export_item(ItemRequest $request)
    {
        $group = $request['group'];
        $pro_name = $request['pro_name'];
        $status = $request['status'];
        if ($group == 1) {
            $res = WjhTeaPro::leftjoin('user_tea', 'user_tea.id', 'tea_pro.tea_id')
                ->select("user_tea.user_name as 姓名", "tea_pro.tea_id as 身份识别码",
                    "user_tea.school as 学校", "tea_pro.pro_name as 参与项目", "tea_pro.grade as 总分")
                ->where('tea_pro.pro_name', $pro_name)
                ->where('tea_pro.status', $status)
                ->orderBy('tea_pro.grade', 'desc')
                ->get();

        } elseif ($group == 2) {
            $res = WjhStuPro::leftjoin('user_stu', 'user_stu.id', 'stu_pro.stu_id')
                ->select("user_stu.user_name as 姓名", "stu_pro.stu_id as 身份识别码",
                    "user_stu.school as 学校", "stu_pro.pro_name as 参与项目", "stu_pro.grade as 总分")
                ->where('stu_pro.pro_name', $pro_name)
                ->where('stu_pro.status', $status)
                ->orderBy('stu_pro.grade', 'desc')
                ->get();
        } else {
            return json_fail("导出失败，不存在的组别标识", null, 100);
        }

        if (!$res) {
            return json_fail("导出失败", null, 100);
        }

        $res = $res->toArray();
        $cnt = count($res);
        for ($i = 0; $i < $cnt; $i++) {
            $res[$i]['排名'] = $i + 1;
            if ($res[$i]['总分'] > 0) {
                if ($cnt > 3) {
                    if ($i <= (int)($cnt * 0.18)) {
                        $res[$i]['获奖信息'] = "一等奖";
                    } elseif ($i > (int)($cnt * 0.18) && $i <= (int)($cnt * 0.42)) {
                        $res[$i]['获奖信息'] = "二等奖";
                    } elseif ($i > (int)($cnt * 0.42) && $i <= (int)($cnt * 0.6)) {
                        $res[$i]['获奖信息'] = "三等奖";
                    }
                } else {
                    if ($i == 0) {
                        $res[$i]['获奖信息'] = "一等奖";
                    } elseif ($i == 1) {
                        $res[$i]['获奖信息'] = "二等奖";
                    } elseif ($i == 2) {
                        $res[$i]['获奖信息'] = "三等奖";
                    }
                }
            } else {
                $res[$i]['获奖信息'] = "无";
            }
        }
        if ($group == 1) {
            return (new FastExcel($res))->download($pro_name . '-教师-' . $status . '组-' . '个人单项奖获奖名单.xlsx');
        } else {
            return (new FastExcel($res))->download($pro_name . '-学生-' . $status . '组-' . '个人单项奖获奖名单.xlsx');
        }
    }

    private function wjh_check_pros($id, $group)
    {
        // 检查某人是否参与所有的项目，全部参与返回true，反之返回false
        if ($group == "1") {  // 教师
            // 获取指定分组（教师组和全部分组）下的所有项目的名称列表
            $pros = DB::table('project')
                ->select("pro_name")
                ->where("status1", 1)
                ->where(function ($query) {
                    $query->where('group', 1)
                        ->orWhere('group', 3);
                })
                ->get()
                ->toArray();
            $cnt = count($pros);  // 对名称列表计数
            $temp = 0;  // 记录参与个数的变量
            for ($i = 0; $i < $cnt; $i++) {
                $t_cnt = DB::table('tea_pro')
                    ->select("*")
                    ->where('pro_name', $pros[$i]->pro_name)
                    ->where('tea_id', $id)
                    ->get()
                    ->count();
                // 如果参与，记录参与个数的变量+1
                if ($t_cnt != 0) {
                    $temp++;
                }
            }
            if ($temp == $cnt) {
                return true;
            } else {
                return false;
            }
        } elseif ($group == "2") {
            $pros = DB::table('project')
                ->select("pro_name")
                ->where("status1", 1)
                ->where(function ($query) {
                    $query->where('group', 2)
                        ->orWhere('group', 3);
                })
                ->get()
                ->toArray();
            $cnt = count($pros);  // 对名称列表计数
            $temp = 0;  // 记录参与个数的变量
            for ($i = 0; $i < $cnt; $i++) {
                $t_cnt = DB::table('stu_pro')
                    ->select("*")
                    ->where('pro_name', $pros[$i]->pro_name)
                    ->where('stu_id', $id)
                    ->get()
                    ->count();
                // 如果参与，记录参与个数的变量+1
                if ($t_cnt != 0) {
                    $temp++;
                }
            }
            if ($temp == $cnt) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function wjh_get_omnipotence(OmnipotenceRequest $request)
    {
        $group = $request['group'];
        $status = $request['status'];
        $res = [];
        if ($group == 1) {  // 教师组
            $ids = WjhTeaPro::wjh_get_teachers($status)->toArray();
            if ($ids === false) {  // 查询tea_id列表失败
                return json_fail("查询失败", null, 100);
            }
            for ($i = 0; $i < count($ids); $i++) {
                self::wjh_check_pros($ids[$i]['tea_id'], 1);
                if (self::wjh_check_pros($ids[$i]['tea_id'], 1)) {
                    // 此人已参加所有的项目，获取相关信息
                    $t_res = WjhTeaPro::wjh_get_detail($ids[$i]['tea_id'], $status);
                    $res[$ids[$i]['tea_id']] = $t_res->toArray()[0];
                }
            }
        } elseif ($group == 2) {  // 学生组
            $ids = WjhStuPro::wjh_get_stus($status)->toArray();
            if ($ids === false) {  // 查询stu_id列表失败
                return json_fail("查询失败", null, 100);
            }
            for ($i = 0; $i < count($ids); $i++) {
                if (self::wjh_check_pros($ids[$i]['stu_id'], 2)) {
                    // 此人已参加所有的项目，获取相关信息
                    $t_res = WjhStuPro::wjh_get_detail($ids[$i]['stu_id'], $status);
                    $res[$ids[$i]['stu_id']] = $t_res->toArray()[0];
                }
            }
        } else {
            return json_fail("查询失败，不存在的组别标识", null, 100);
        }


        // 依照成绩进行排序
        $flag = array_column($res, 'all_average');
        array_multisort($flag, SORT_DESC, $res);
        $cnt = count($res);
        for ($i = 0; $i < $cnt; $i++) {
            $res[$i]['rank'] = $i + 1;
            if ($res[$i]['all_average'] > 0) {
                if ($cnt > 3) {
                    if ($i <= (int)($cnt * 0.18)) {
                        $res[$i]['award'] = "一等奖";
                    } elseif ($i > (int)($cnt * 0.18) && $i <= (int)($cnt * 0.42)) {
                        $res[$i]['award'] = "二等奖";
                    } elseif ($i > (int)($cnt * 0.42) && $i <= (int)($cnt * 0.6)) {
                        $res[$i]['award'] = "三等奖";
                    }
                } else {
                    if ($i == 0) {
                        $res[$i]['award'] = "一等奖";
                    } elseif ($i == 1) {
                        $res[$i]['award'] = "二等奖";
                    } elseif ($i == 2) {
                        $res[$i]['award'] = "三等奖";
                    }
                }
            } else {
                $res[$i]['award'] = "无";
            }
        }
        return json_success("获取成功", $res, 200);
    }

    public function wjh_export_omnipotence(OmnipotenceRequest $request)
    {
        $group = $request['group'];
        $status = $request['status'];
        $res = [];
        if ($group == 1) {  // 教师组
            $ids = WjhTeaPro::wjh_get_teachers($status)->toArray();
            if ($ids === false) {  // 查询tea_id列表失败
                return json_fail("查询失败", null, 100);
            }
            for ($i = 0; $i < count($ids); $i++) {
                if (self::wjh_check_pros($ids[$i]['tea_id'], 1, $status)) {
                    // 此人已参加所有的项目，获取相关信息
                    $t_res = WjhTeaPro::join("user_tea", "tea_pro.tea_id", 'user_tea.id')
                        ->select("user_tea.user_name as 姓名", "user_tea.id as 身份识别码",
                            "user_tea.school as 学校", "user_tea.all_average as 总分")
                        ->where("user_tea.id", $ids[$i]['tea_id'])
                        ->where("user_tea.status", $status)
                        ->get();;
                    $res[$ids[$i]['tea_id']] = $t_res->toArray()[0];
                }
            }
        } elseif ($group == 2) {  // 学生组
            $ids = WjhStuPro::wjh_get_stus($status)->toArray();
            if ($ids === false) {  // 查询stu_id列表失败
                return json_fail("查询失败", null, 100);
            }
            for ($i = 0; $i < count($ids); $i++) {
                if (self::wjh_check_pros($ids[$i]['stu_id'], 2, $status)) {
                    // 此人已参加所有的项目，获取相关信息
                    $t_res = WjhStuPro::join("user_stu", "stu_pro.stu_id", 'user_stu.id')
                        ->select("user_stu.user_name as 姓名", "user_stu.id as 身份识别码",
                            "user_stu.school as 学校", "user_stu.all_average as 总分")
                        ->where("user_stu.id", $ids[$i]['stu_id'])
                        ->where("user_stu.status", $status)
                        ->get();;
                    $res[$ids[$i]['stu_id']] = $t_res->toArray()[0];
                }
            }
        } else {
            return json_fail("查询失败，不存在的组别标识", null, 100);
        }

        // 依照成绩进行排序
        $flag = array_column($res, '总分');
        array_multisort($flag, SORT_DESC, $res);
        $cnt = count($res);
        for ($i = 0; $i < $cnt; $i++) {
            $res[$i]['排名'] = $i + 1;
            if ($res[$i]['总分'] > 0) {
                if ($cnt > 3) {
                    if ($i <= (int)($cnt * 0.18)) {
                        $res[$i]['获奖信息'] = "一等奖";
                    } elseif ($i > (int)($cnt * 0.18) && $i <= (int)($cnt * 0.42)) {
                        $res[$i]['获奖信息'] = "二等奖";
                    } elseif ($i > (int)($cnt * 0.42) && $i <= (int)($cnt * 0.6)) {
                        $res[$i]['获奖信息'] = "三等奖";
                    }
                } else {
                    if ($i == 0) {
                        $res[$i]['获奖信息'] = "一等奖";
                    } elseif ($i == 1) {
                        $res[$i]['获奖信息'] = "二等奖";
                    } elseif ($i == 2) {
                        $res[$i]['获奖信息'] = "三等奖";
                    }
                }
            } else {
                $res[$i]['获奖信息'] = "无";
            }
        }
        if ($group == 1) {
            return (new FastExcel($res))->download('教师-' . $status . '组-' . '个人全能奖获奖名单.xlsx');
        } else {
            return (new FastExcel($res))->download('学生-' . $status . '组-' . '个人全能奖获奖名单.xlsx');
        }
    }
}
