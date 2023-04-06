<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
/**
 * 成绩分析端/获奖评比查看
 * @author WJH
 */
Route::prefix('analysis')->group(function () {
    Route::post('guide','LX_AnalysisController@LX_get_guidance');//优秀指导教师奖
    Route::get('guide/export','LX_AnalysisController@LX_get_export');//导出优秀指导教师名单
});

/**
 * 管理员端：项目管理
 */
Route::prefix('/admin/project')->group(function (){
    Route::get('select','Lx_ProjectController@LX_Select_pro');//查询项目信息
    Route::get('get_scorers','Lx_ProjectController@LX_Select_scorer');//获取记分员列表
    Route::post('modify','Lx_ProjectController@LX_Update_modify');//修改项目信息
    Route::post('delete','Lx_ProjectController@LX_delete_pro');//删除项目信息
    Route::post('select_by_name','Lx_ProjectController@LX_Select_pro_by_name');//通过项目名称查找比赛项目
});
Route::prefix('admin')->group(function (){
    Route::post('registered','AdminController@registered');
    Route::post('login','AdminController@login');
});

/**
 * 管理员端：记分员管理
 * @author WJH
 */
Route::prefix('/admin/scorer')->group(function (){
    Route::get('/select','WjhScorerManageController@wjh_get_scorers');  // 查询所有记分员的信息
    Route::post('/modify','WjhScorerManageController@wjh_modify_scorer');  // 修改某个记分员的信息
    Route::post('/reset','WjhScorerManageController@wjh_reset_scorer');  // 重置某个记分员的密码
    Route::post('/delete','WjhScorerManageController@wjh_delete_scorer');  // 删除某个记分员
    Route::post('/create','WjhScorerManageController@wjh_create_scorer');  // 创建一个新的记分员
    Route::post('/select_by_name','WjhScorerManageController@wjh_select_by_name');  // 通过姓名对记分员进行模糊查找
    Route::get('/export','WjhScorerManageController@wjh_export');  // 记分员信息导出为excel
});

/**
 * 成绩分析端/获奖评比查看
 * @author WJH
 */
Route::prefix('/analysis')->group(function () {
    Route::post('/item', 'WjhAnalysisController@wjh_get_item');  // 查询某项目个人单项奖获奖信息
    Route::get('/item/export', 'WjhAnalysisController@wjh_export_item');  // 导出某项目个人单项奖获奖信息
    Route::post('/omnipotence', 'WjhAnalysisController@wjh_get_omnipotence');  // 查询个人全能奖获奖信息
    Route::get('/omnipotence/export', 'WjhAnalysisController@wjh_export_omnipotence');  // 查询个人全能奖获奖信息
    Route::post('login','Wyh_AnalysisController@login');//记分员登录
    Route::post('organization','Wyh_AnalysisController@wyh_organization');//优秀组织奖
    Route::get('organization/export','Wyh_AnalysisController@wyh_organizationexpor');//优秀组织奖的excel导出
    Route::post('group','Wyh_AnalysisController@wyh_group');//优秀团体奖
    Route::get('group/export','Wyh_AnalysisController@wyh_groupexport');//优秀团体奖excel导出

});
Route::prefix('admin')->group(function (){
    Route::post('registered','AdminController@registered');//注册
    Route::post('login','AdminController@login');//管理员登录
    Route::post('users/import','AdminController@wyh_Upload');//参赛人员信息的导入
    Route::post('users/select','AdminController@wyh_Select');//查询参赛人员信息
    Route::post('users/mod_stu','AdminController@wyh_fixstu');//修改学生的参赛信息
    Route::post('users/mod_teacher','AdminController@wyh_fixtea');//修改老师的参赛信息
    Route::post('users/del_stu','AdminController@wyh_deletstu');//删除学生信息
    Route::post('users/del_teacher','AdminController@wyh_delettea');//删除老师信息

});
//记分员端
Route::prefix('scorer')->group(function (){
    Route::prefix('/manage')->group(function (){
        Route::post('/get_pros','YmController\ScorerController@GetPro');//获取比赛项目列表
        Route::post('/select','YmController\ScorerController@Select_Pro');//项目条件查询
        Route::post('/record','YmController\ScorerController@Record');//录入分数
    });
    Route::post('login','Wyh_ScorerController@login');//管理员登录
    Route::post('mod_pw','YmController\ScorerController@Mod_PW');//修改记分员的密码
});


/**
 * 成绩分析端成绩详情查看
 */

Route::prefix('/analysis')->group(function (){
    Route::prefix('/detail')->group(function (){
        Route::post('get_pros','YmController\AnaController@Get_Pros');//获取分组下的项目信息
        Route::post('select','YmController\AnaController@Select_grade');//查询某个项目参赛人员的成绩
        Route::post('average','YmController\AnaController@Average');//获取项目平均分
        Route::get('export','YmController\AnaController@Export');//导出某个项目参赛人员成绩Excel
    });
});

