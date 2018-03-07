<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2017/12/28
 * Time: 16:28
 */
namespace app\index\model;

use app\common\Constant;
use think\Db;
use think\Log;
use think\Session;

class SearchEnginesCount extends Base
{
    public function  getList($start_time,$end_time){
        if (Session::get("type") == "PC"){
            $table1 = Db::table('search_engines_count_pc');
            $table2 = Db::table("url_count_pc");
        }else{
            $table1 = Db::table('search_engines_count_m');
            $table2 = Db::table("url_count_m");
        }
        $all_engs = Constant::$search_engines;
        $use_engs = Constant::$sourceAnalysisSearchEngine;
        $engines = [];
        foreach ($use_engs as $value) {
            $engines[$value] = $all_engs[$value];
        }
        $res1 = $table1->field('DATE_FORMAT(create_time,\'%Y-%m-%d\') as time')
            ->field('SUM(CASE WHEN se_id = 2 THEN count ELSE 0 END) AS 360search')
            ->field('SUM(CASE WHEN se_id = 3 THEN count ELSE 0 END) AS baidu')
            ->field('SUM(CASE WHEN se_id = 4 THEN count ELSE 0 END) AS sougou')
            ->field('SUM(CASE WHEN se_id = 7 THEN count ELSE 0 END) AS google')
            ->field('SUM(CASE WHEN se_id = 8 THEN count ELSE 0 END) AS shenma')
            ->field('SUM(CASE WHEN se_id = 8 or se_id =7 or se_id=4 or se_id=3 or se_id=2 THEN count ELSE 0 END) AS search_num')
            ->where('create_time','between',[$start_time,$end_time])->group('DATE_FORMAT(create_time,\'%Y-%m-%d\')');
        $res1 = $this->autoPaginate($res1)->toArray();
//        Log::error(var_export($res1,1));exit;
        $res2 = $table2->field("DATE_FORMAT(create_time,'%Y-%m-%d') as time,num")->where('create_time','between',[$start_time,$end_time])->select();
        $data1=['fangwencishu'=>0,'sousuo'=>0,'baidu'=>0,
            'sanliu'=>0,'sougou'=>0,'guge'=>0,'shenma'=>0];
        $data2_sort=$data2=[];
        $data3=['baidu'=>[],'sanliu'=>[],'sougou'=>[],'guge'=>[],'shenma'=>[]];
        $dates = [];
        foreach ($res1['data'] as $res1_value){
                $data1['sanliu'] += $res1_value['360search'];
                $data1['baidu'] += $res1_value['baidu'];
                $data1['sougou'] += $res1_value['sougou'];
                $data1['guge'] += $res1_value['google'];
                $data1['shenma'] += $res1_value['shenma'];
                $data2_sort[$res1_value['time']]=$data2[$res1_value['time']] = $res1_value;
            }
            ksort($data2_sort);
        foreach ($data2_sort as $item){
            $data3['shenma'][] = $item['shenma'];
            $data3['guge'][] = $item['google'];
            $data3['sougou'][] = $item['sougou'];
            $data3['baidu'][] = $item['baidu'];
            $data3['sanliu'][] = $item['360search'];
            $dates[]=$item['time'];
        }
        foreach ($res2 as $res2_value){
            $data1['fangwencishu'] += $res2_value['num'];
        }
        foreach ($data2 as $k=>$v){
            $rate = $v['search_num'] > 0 ? round($v['baidu']/$v['search_num'],4)*100:0;
            $data2[$k]['baidu'] = $v['baidu'].'('.$rate.'%)';

            $rate = $v['search_num'] > 0 ? round($v['360search']/$v['search_num'],4)*100:0;
            $data2[$k]['360search'] = $v['360search'].'('.$rate.'%)';

            $rate = $v['search_num'] > 0 ? round($v['shenma']/$v['search_num'],4)*100:0;
            $data2[$k]['shenma'] = $v['shenma'].'('.$rate.'%)';

            $rate = $v['search_num'] > 0 ? round($v['google']/$v['search_num'],4)*100:0;
            $data2[$k]['google'] = $v['google'].'('.$rate.'%)';

            $rate = $v['search_num'] > 0 ? round($v['sougou']/$v['search_num'],4)*100:0;
            $data2[$k]['sougou'] = $v['sougou'].'('.$rate.'%)';
        }

        $data1['sousuo'] = $data1['baidu']+$data1['sanliu']+$data1['shenma']+$data1['guge']+$data1['sougou'];
        array_unshift($data2,['time'=>'总计','search_num'=>$data1['sousuo'],
            'baidu'=>$data1['baidu'] == 0?"0(0%)":$data1['baidu']."(".(round($data1['baidu']/$data1['sousuo'],4)*100)."%)",
            '360search'=>$data1['sanliu'] == 0?"0(0%)":$data1['sanliu']."(".(round($data1['sanliu']/$data1['sousuo'],4)*100)."%)",
            'sougou'=>$data1['sougou'] == 0?"0(0%)":$data1['sougou']."(".(round($data1['sougou']/$data1['sousuo'],4)*100)."%)",
            'google'=>$data1['guge'] == 0?"0(0%)":$data1['guge']."(".(round($data1['guge']/$data1['sousuo'],4)*100)."%)",
            'shenma'=>$data1['shenma'] == 0?"0(0%)":$data1['shenma']."(".(round($data1['shenma']/$data1['sousuo'],4)*100)."%)",
            ]);
        $baidu_rate = $data1['sousuo']>0?round($data1['baidu']/$data1['sousuo'],4) * 100:0;
        $sanliu_rate = $data1['sousuo']>0?round($data1['sanliu']/$data1['sousuo'],4) * 100:0;
        $sougou_rate = $data1['sousuo']>0?round($data1['sougou']/$data1['sousuo'],4) * 100:0;
        $guge_rate = $data1['sousuo']>0?round($data1['guge']/$data1['sousuo'],4) * 100:0;
        $shenma_rate = $data1['sousuo']>0?round($data1['shenma']/$data1['sousuo'],4) * 100:0;
        return
            [
                "keywordDataDetail" => [
                    ["name" => "访问次数", "value" => $data1['fangwencishu']],
                    ["name" => "搜索引擎", "value" => $data1['sousuo']],
                    ["name" => "百度", "value" => $data1['baidu']],
                    ["name" => "360搜索", "value" => $data1['sanliu']],
                    ["name" => "搜狗", "value" => $data1['sougou']],
                    ["name" => "谷歌", "value" => $data1['guge']],
                    ["name" => "神马", "value" => $data1['shenma']],
                ],
                "searchEngineChartData" => [
                    ["name" => "百度:{$data1['baidu']}(".$baidu_rate.'%)', "value" => $data1['baidu']],
                    ["name" => "360搜索:{$data1['sanliu']}(".$sanliu_rate.'%)', "value" => $data1['sanliu']],
                    ["name" => "搜狗:{$data1['sougou']}(".$sougou_rate.'%)', "value" => $data1['sougou']],
                    ["name" => "谷歌:{$data1['guge']}(".$guge_rate.'%)', "value" => $data1['guge']],
                    ["name" => "神马:{$data1['shenma']}(".$shenma_rate.'%)', "value" => $data1['shenma']],
                ],
                "tableData" => array_values($data2),
                "total"=>(int)$res1['total'],
                "searchEngineLineChartData" => [
                    "title" => ['百度', '360', '搜狗', '谷歌', '神马'],
                    "time" => $dates,
                    "source" => [
                        [   "name" => '百度',
                            "type" => 'line',
                            "data" => $data3['baidu']
                        ],
                        [   "name" => '360',
                            "type" => 'line',
                            "data" => $data3['sanliu']
                        ],
                        [   "name" => '搜狗',
                            "type" => 'line',
                            "data" => $data3['sougou']
                        ],
                        [   "name" => '谷歌',
                            "type" => 'line',
                            "data" => $data3['guge']
                        ],
                        [   "name" => '神马',
                            "type" => 'line',
                            "data" => $data3['shenma']
                        ],

                    ]
                ],
            ]
        ;
    }
}