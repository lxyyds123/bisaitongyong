<?php

namespace App\Http\Controllers;

use App\Http\Requests\LX\UpdateRequest;
use App\Models\Lx_project;
use App\Models\Lx_Scorer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Lx_ProjectController extends Controller
{
    /**
     * 查询比赛项目信息
     */

    public function LX_Select_pro(Request $request){
        $res = [];
        $id1 = DB::table('project')->select('pro_name','scorer_id')->get()->toArray();
        for ($i = 0; $i < count($id1); $i++) {
            $t_res = DB::table('project')
                ->leftJoin('scorer', 'project.scorer_id', 'scorer.id')
                ->select('scorer.scorer_name', 'project.scorer_id', 'project.pro_name',
                    'project.id','project.group','project.pro_date',
                    'project.pro_address','project.host','project.status1')
                ->where('project.scorer_id', $id1[$i]->scorer_id)
                ->get()
                ->toArray();
            $res[$id1[$i]->pro_name] =[$id1[$i]->scorer_id,$t_res[0]] ;
        }
        return $res?
            json_success('查询成功',$res,200):
            json_fail('查询失败',null,100);




    }

    /**
     * 查询记分员姓名列表
     */
    public function LX_Select_scorer(){
        $data = Lx_Scorer::lx_select_scorer();
        return $data?
            json_success('查询成功',$data,200):
            json_fail('查询失败',null,100);
    }

    /**
     *通过比赛名称查找比赛项目
     */
    public function LX_Select_pro_by_name(Request $request){
        $pro_name = $request['pro_name'];
        $data = Lx_Project::lx_select_pro_by_name($pro_name);
        return $data?
            json_success('查询成功',$data,200):
            json_fail('查询失败',null,00);
    }

    /**
     * 修改项目信息
     */
    public function LX_Update_modify(UpdateRequest $request){
        $id = $request['id'];
        $pro_name = $request['pro_name'];
        $pro_date = $request['pro_date'];
        $pro_address = $request['pro_address'];
        $host = $request['host'];
        $scorer_id = $request['scorer_id'];
        $group = $request['group'];
        $pro_name1 = DB::table('project')->select('pro_name')->where('id',$id)->get();
        $pro_name2 = json_encode($pro_name1);
        $pro_name3 = json_decode($pro_name2);
        $pro_name4 = $pro_name3[0]->pro_name;
        $data = Lx_Project::lx_update_modify($id,$pro_name,$pro_date,$pro_address,$host,$scorer_id,$group,$pro_name4);
        return $data?
            json_success('修改成功',$data,200):
            json_fail('修改失败',null,100);
    }

    /**
     * 删除项目
     */
    public function LX_delete_pro(Request $request){
        $id = $request['id'];
        $pro_name1 = DB::table('project')->select('pro_name')->where('id',$id)->get();
        $pro_name2 = json_encode($pro_name1);
        $pro_name3 = json_decode($pro_name2);
        $pro_name4 = $pro_name3[0]->pro_name;
        $data = Lx_Project::lx_delete_pro($id,$pro_name4);
        return $data?
            json_success('删除成功',$data,200):
            json_fail('删除失败',null,100);
    }

}
