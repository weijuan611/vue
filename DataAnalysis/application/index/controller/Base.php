<?php
namespace app\index\controller;

use think\Controller;
use think\Session;
use think\Request;
use think\Log;
class Base extends Controller
{
    protected $beforeActionList = [
        'before'=>['except'=>'login,logout,home,index,workbench/index,super_login'],
    ];

    protected $permission = [];

    protected $route=[
        'trend'=>['trend_analysis/import'],//趋势对比
        'visit_detail'=>['visit_detail/import'],
        'lexicon'=>['lexicon/import','lexicon/refresh','lexicon/keywords/add','lexicon/keywords/edit','lexicon/keywords/editbench'
            ,'lexicon/opponent/list'],
        'adminRole'=>['roles/add','roles/edit','roles/delete'],//权限管理
        'adminUser'=>['roles/usertorole','userinfo/delete','userinfo/add','userinfo/edit','roles/category'],//员工管理
        'visitAnalysisArea'=>['area/export'],//地区分析
        'sourceAnalysisKeyWord'=>['keyword/export'],//关键词
        'sourceAnalysisOriginSort'=>['originanalysissort/export'],//来源域名升降
        'sourceAnalysisFromSort'=>['from_sort/export'],//来源分类
        'sourceAnalysisSearchEngine'=>['search_engine/import'],//搜索引擎
        'sourceAnalysisOriginAnalysis'=>['originanalysis/export'],//来路域名
        'observe'=>['observe/index'],//指数观察
        'keyworkdcheck'=>['keywordcheck/check','keywordcheck/index','keywordcheck/exportdaily'],//关键词审核
        'workbench'=>['workbench/index']//工作台
    ];

    protected function before(){
        //检查Session
        if (!Session::has('org_user_id')) {
            response('登录超时,请重新登录！', 203, [], 'json')->send();
            exit;
        }
        //检查权限
        $rule = Request::instance()->path();
        $user_id = Session::get('org_user_id');
        if ($user_id!=SUPER_USER_ID){
            $this->permission = getAllPermissions($user_id);
            if (!check($rule, $this->permission)){
                response('bcbc64917f64d100313d1f15ccc191fb')->send();
                exit();
            }
        }
    }
}
