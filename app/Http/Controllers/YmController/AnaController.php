<?php

namespace App\Http\Controllers\YmController;

use App\Http\Controllers\Controller;
use App\Http\Requests\YYM\AnverageRequest;
use App\Http\Requests\YYM\ModRequest;
use App\Http\Requests\YYM\MRequest;
use App\Models\YmModel\Project;
use App\Models\YmModel\Stu_pro;
use App\Models\YmModel\Tea_pro;
use Rap2hpoutre\FastExcel\FastExcel;

class AnaController extends Controller
{
    /**
     *
     * 成绩分析端  查询下拉列表 不同组别的所有项目
     * @param AnverageRequest $request
     * @return \Illuminate\Http\JsonResponse
     *@author yym
     */
    public function Get_Pros(AnverageRequest $request)

    {

        $group = $request['group'];
        $re = Project::get_pros($group);

        return $re?
            json_success("操作成功!",$re,200):
            json_fail("该组别未有项目",null,100);


    }


    /**
     * 成绩分析端  查询对应项目的参赛人员分数
     * @author yym
     * @param MRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function Select_grade(MRequest $request)
    {
        $group = $request['group'];
        $pro_name = $request['pro_name'];
        $status = $request['status'];

        if($group == 1)
        {
            $res = Tea_pro::select_grade($pro_name,$status);
        }elseif ($group == 2)
        {
            $res = Stu_pro::select_grade($pro_name,$status);
        }else
        {
            return json_fail('未有该组别！', null,100);
        }


        return $res?
            json_success("操作成功!",$res,200):
            json_fail("此项目该级别暂未有选手参加，请检查是否查询错误！!",null,100);


    }


    /**
     *  成绩分析端 获取项目平均分
     *
     * @author yym
     * @param MRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function Average(MRequest $request)
    {
        $group = $request['group'];
        $pro_name = $request['pro_name'];
        $status = $request['status'];

        if($group == 1)
        {

            $res = Tea_pro::average($pro_name,$status);

        }elseif ($group == 2)
        {

            $res = Stu_pro::average($pro_name,$status);

        }else
        {
            return json_fail('未有该组别！', null,100);
        }

        $res = round($res,2);

        return $res?
            json_success("操作成功!",$res,200):
            json_fail("此项目该级别暂未有选手参加，请检查是否查询错误！",null,100);


    }


    /**
     *
     * 成绩分析端   导出对应项目的参赛选手成绩单
     * @param MRequest $request
     * @return string|\Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \OpenSpout\Common\Exception\IOException
     * @throws \OpenSpout\Common\Exception\InvalidArgumentException
     * @throws \OpenSpout\Common\Exception\UnsupportedTypeException
     * @throws \OpenSpout\Writer\Exception\WriterNotOpenedException
     *@author yym
     */
    public function Export(MRequest $request)
    {

        $group = $request['group'];
        $pro_name = $request['pro_name'];
        $status = $request['status'];


        if ($group == 1) {

            $res = Tea_pro::export($pro_name,$status);
            return (new FastExcel($res))->download($pro_name.'教师'.$status.'组'.'参赛人员成绩单.xlsx');
        } elseif ($group == 2) {

            $res = Stu_pro::export($pro_name,$status);
            return (new FastExcel($res))->download($pro_name.'学生'.$status.'组'.'参赛人员成绩单.xlsx');

        }else
        {
            return json_fail('未有该组别！', null,100);
        }


    }
}
