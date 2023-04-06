<?php

namespace App\Http\Controllers;

use App\Models\WjhStuPro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;

class LX_AnalysisController extends Controller
{

    /**
     * 优秀指导奖
     */
    public function LX_get_guidance(Request $request)
    {
        $status = $request['status'];
        $pro_list = DB::table('project')->select('pro_name')->get()->toArray();
        $res1 = [];  // 暂时存放所有获奖学生的指导教师和学校
        $ins = [];  // 存放所有指导老师名称的列表
        $res_final = [];  // 最终返回结果的存储数组

        // 遍历传入的项目名称列表
        for ($j = 0; $j < count($pro_list); $j++) {
            // 取出项目名称
            $pro_name = $pro_list[$j]->pro_name;
            // 获取相关信息
            $res = WjhStuPro::join('user_stu', 'user_stu.id', 'stu_pro.stu_id')
                ->select("user_stu.school",
                    "stu_pro.grade", "user_stu.instructor")
                ->where('stu_pro.pro_name', $pro_name)
                ->where('stu_pro.status', $status)
                ->orderBy('stu_pro.grade', 'desc')
                ->get();
            if (!$res) {
                return false;
            }

            $res = $res->toArray();  // 转为数组
            $cnt = count($res);  // 统计个数

            // 遍历结果取出获奖学生的指导老师和学生，用数组$res1存储
            for ($i = 0; $i < $cnt; $i++) {
                if ($res[$i]['grade'] > 0) {
                    if ($i <= (int)($cnt * 0.6)) {
                        $res1[] = [
                            'instructor' => $res[$i]['instructor'],
                            'school' => $res[$i]['school']
                        ];
                    }
                }
            }
        }
        // 取出所有的指导教师的名字，存放到数组 $ins（允许重复）
        for ($i = 0; $i < count($res1); $i++) {
            $ins[$i] = $res1[$i]['instructor'];
        }
        // 统计每个指导教师名字出现的次数
        $ins = array_count_values($ins);

        // 进行指导老师、学校、获奖学生数量字段的拼接
        foreach ($ins as $k => $v) {
            for ($i = 0; $i < count($res1); $i++) {
                if ($res1[$i]['instructor'] == $k) {
                    $res_final[] = [
                        'instructor' => $k,
                        'school' => $res1[$i]['school'],
                        'count' => $v,
                    ];
                    break;
                }
            }
        }

        // 排序
        $flag = array_column($res_final, 'count');
        array_multisort($flag, SORT_DESC, $res_final);

        // 拼接排名和获奖信息
        for ($i = 0; $i < count($res_final); $i++) {
            $res_final[$i]['rank'] = $i + 1;
            $res_final[$i]['award'] = '指导教师奖';
        }

        return $res_final ?
            json_success("操作成功", $res_final, 200) :
            json_fail('操作失败', null, 100);
    }
    /**
     * 导出优秀指导奖
     */
    public function LX_get_export(Request $request){
        $status = $request['status'];
        $pro_list = DB::table('project')->select('pro_name')->get()->toArray();
        $res1 = [];  // 暂时存放所有获奖学生的指导教师和学校
        $ins = [];  // 存放所有指导老师名称的列表
        $res_final = [];  // 最终返回结果的存储数组

        // 遍历传入的项目名称列表
        for ($j = 0; $j < count($pro_list); $j++) {
            // 取出项目名称
            $pro_name = $pro_list[$j]->pro_name;
            // 获取相关信息
            $res = WjhStuPro::join('user_stu', 'user_stu.id', 'stu_pro.stu_id')
                ->select("user_stu.school",
                    "stu_pro.grade", "user_stu.instructor")
                ->where('stu_pro.pro_name', $pro_name)
                ->where('stu_pro.status', $status)
                ->orderBy('stu_pro.grade', 'desc')
                ->get();
            if (!$res) {
                return false;
            }

            $res = $res->toArray();  // 转为数组
            $cnt = count($res);  // 统计个数

            // 遍历结果取出获奖学生的指导老师和学生，用数组$res1存储
            for ($i = 0; $i < $cnt; $i++) {
                if ($res[$i]['grade'] > 0) {
                    if ($i <= (int)($cnt * 0.6)) {
                        $res1[] = [
                            'instructor' => $res[$i]['instructor'],
                            'school' => $res[$i]['school']
                        ];
                    }
                }
            }
        }
        // 取出所有的指导教师的名字，存放到数组 $ins（允许重复）
        for ($i = 0; $i < count($res1); $i++) {
            $ins[$i] = $res1[$i]['instructor'];
        }
        // 统计每个指导教师名字出现的次数
        $ins = array_count_values($ins);

        // 进行指导老师、学校、获奖学生数量字段的拼接
        foreach ($ins as $k => $v) {
            for ($i = 0; $i < count($res1); $i++) {
                if ($res1[$i]['instructor'] == $k) {
                    $res_final[] = [
                        '姓名' => $k,
                        '学校' => $res1[$i]['school'],
                        '获奖学生人数' => $v,
                    ];
                    break;
                }
            }
        }

        // 排序
        $flag = array_column($res_final, '获奖学生人数');
        array_multisort($flag, SORT_DESC, $res_final);

        // 拼接排名和获奖信息
        for ($i = 0; $i < count($res_final); $i++) {
            $res_final[$i]['排名'] = $i + 1;
            $res_final[$i]['获奖信息'] = '指导教师奖';
        }

        return (new FastExcel($res_final))->download( $status.'组--优秀指导教师奖获奖名单.xlsx');
    }
}

