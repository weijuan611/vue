<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

Route::rule([
    'index'=>'index/admin.Home/index',
    'login'=>'index/admin.Home/login',
    'logout'=>'index/admin.Home/logout',
    'menu'=>'index/admin.Home/menu',
    'super_login'=>'index/admin.Home/super_login',
    'department/list'=>'index/admin.Department/getList',
    'department/add'=>'index/admin.Department/postAdd',
    'department/delete'=>'index/admin.Department/getDelete',
    'visit_detail/init'=>'index/analysis.VisitDetail/init',
    'visit_detail/index'=>'index/analysis.VisitDetail/index',
    'visit_detail/import'=>'index/analysis.VisitDetail/import',
    'userinfo/index'=>'index/admin.userinfo/index',
    'userinfo/add'=>'index/admin.userinfo/add',
    'userinfo/edit'=>'index/admin.userinfo/edit',
    'userinfo/delete'=>'index/admin.userinfo/delete',
    'workbench/index'=>'index/workbench.index/index',
    'keyword/index'=>'index/sourceAnalysis.KeyWord/index',
    'keyword/export'=>'index/sourceAnalysis.KeyWord/export',
    'originanalysis/index'=>'index/sourceAnalysis.OriginAnalysis/index',
    'originanalysis/export'=>'index/sourceAnalysis.OriginAnalysis/export',
    'originanalysissort/index'=>'index/sourceAnalysis.OriginAnalysisSort/index',
    'originanalysissort/export'=>'index/sourceAnalysis.OriginAnalysisSort/export',
    'trend_analysis/index'=>'index/analysis.TrendAnalysis/index',
    'trend_analysis/import'=>'index/analysis.TrendAnalysis/import',
    'search_engine/init'=>'index/sourceAnalysis.SearchEngine/init',
    'search_engine/import'=>'index/sourceAnalysis.SearchEngine/import',
    'from_sort/init'=>'index/sourceAnalysis.FromSort/init',
    'from_sort/page'=>'index/sourceAnalysis.FromSort/page',
    'from_sort/export'=>'index/sourceAnalysis.FromSort/export',
    'workbench/changeselecttype'=>'index/workbench.index/changeselecttype',
    'area/index'=>'index/visitAnalysis.Area/index',
    'area/init'=>'index/visitAnalysis.Area/init',
    'area/changearea'=>'index/visitAnalysis.Area/changearea',
    'area/export'=>'index/visitAnalysis.Area/export',
    'spider_statistics/index'=>'index/analysis.SpiderStatistics/index',
    'lexicon/setting/list'=>'index/keywords.Lexicon/setting_list',
    'lexicon/setting/add'=>'index/keywords.Lexicon/setting_add',
    'lexicon/setting/edit'=>'index/keywords.Lexicon/setting_edit',
    'lexicon/setting/delete'=>'index/keywords.Lexicon/setting_delete',
    'lexicon/index/list'=>'index/keywords.Lexicon/index_list',
    'lexicon/index/query'=>'index/keywords.Lexicon/index_query',
    'lexicon/index/importquery'=>'index/keywords.Lexicon/index_importquery',
    'lexicon/categories/init'=>'index/keywords.Lexicon/categories_init',
    'lexicon/import'=>'index/keywords.Lexicon/import',
    'lexicon/refresh'=>'index/keywords.Lexicon/refresh',
    'lexicon/trend'=>'index/keywords.Lexicon/trend',
    'lexicon/keywords/add'=>'index/keywords.Lexicon/keywords_add',
    'lexicon/keywords/edit'=>'index/keywords.Lexicon/keywords_edit',
    'lexicon/keywords/editbench'=>'index/keywords.Lexicon/keywords_editbench',
    'lexicon/opponent/init'=>'index/keywords.Lexicon/opponent_init',
    'lexicon/opponent/list'=>'index/keywords.Lexicon/opponent_list',
    'lexicon/opponent/add'=>'index/keywords.Lexicon/opponent_add',
    'lexicon/opponent/edit'=>'index/keywords.Lexicon/opponent_edit',
    'lexicon/opponent/delete'=>'index/keywords.Lexicon/opponent_delete',
    'lexicon/keywords/delete'=>'index/keywords.Lexicon/keywords_delete',
    'lexicon/keywords/schoollabel'=>'index/keywords.Lexicon/keywords_schoollabel',
    'lexicon/init'=>'index/keywords.Lexicon/init',
    'roles/index'=>'index/admin.roles/index',
    'roles/add'=>'index/admin.roles/add',
    'roles/edit'=>'index/admin.roles/edit',
    'roles/usertorole'=>'index/admin.roles/usertorole',
    'roles/delete'=>'index/admin.roles/delete',
    'roles/category'=>'index/admin.roles/category',
    'observe/index'=>'index/keywords.Observe/index',
    'observe/changeTab'=>'index/keywords.Observe/changetab',
    'observe/included'=>'index/keywords.Observe/included',
    'user_account/add'=>'index/admin.Account/add',
    'user_account/index'=>'index/admin.Account/index',
    'user_account/info'=>'index/admin.Account/info',
    'user_account/login'=>'index/admin.Account/login',
    'user_account/code'=>'index/admin.Account/code',
    'user_account/img'=>'index/admin.Account/img',
    'original/index'=>'index/original.original/index',
    'original_order/index'=>'index/original.order/index',
    'original_source/add_lable'=>'index/original.Originalsource/add_lable',
    'original_source/del_lable'=>'index/original.Originalsource/del_lable',
    'original_source/list_lable'=>'index/original.Originalsource/list_lable',
    'original_source/init_lable'=>'index/original.Originalsource/init_lable',
    'keywordcheck/index'=>'index/keywords.check/index',
    'keywordcheck/check'=>'index/keywords.check/check',
    'keywordcheck/exportdaily'=>'index/keywords.check/exportdaily',
]);

