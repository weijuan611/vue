<?php

namespace app\index\model;

use think\Db;
use think\Log;
use think\Request;
use think\Session;

class MFromSort extends Base
{
    protected $searchColumn = [
        'dateTime'=>['sds.create_time','between','time'],
    ];

    public function getList()
    {
        $type = Session::get("type");
        $type == "PC"?$dstype = 1:$dstype = 2;
        $query = $this->fromSortFilter($dstype);
        $result = $this->autoPaginate($query)->toArray();
        Log::write($result);
        $query_sum = Db::table("source_domain_sort")->alias("sds")
            ->field("SUM(num),SUM(from_engines),SUM(from_mark),SUM(from_other)")
            ->where("sds.dstype",$dstype);
        $this->checkSearch($query_sum);
        $sum = $query_sum->find();
        $res3 = ['mark' => [], 'enj' => [], 'other' => [], 'dates' => []];
        foreach ($result['data'] as $res1_value) {
            $res3['dates'][] = $res1_value['time'];
            $res3['mark'][] = $res1_value['from_mark_raw'];
            $res3['enj'][] = $res1_value['from_engines_raw'];
            $res3['other'][] = $res1_value['from_other_raw'];
        }
        array_unshift(
            $result['data'],
            [
                'time' => '总计', 'num' => $sum['SUM(num)'], 'from_mark' => $sum['SUM(from_mark)'],
                'from_engines' => $sum['SUM(from_engines)'], 'from_other' => $sum['SUM(from_other)']
            ]
        );

        $data = [
            "keywordDataDetail" => [
                ["name" => "访问次数", "value" => $sum['SUM(num)']],
                ["name" => "直接输入网址或书签", "value" => $sum['SUM(from_mark)']],
                ["name" => "搜索引擎", "value" => $sum['SUM(from_engines)']],
                ["name" => "其他外部链接", "value" => $sum['SUM(from_other)']],
            ],
            "searchEngineChartData" => [
                ["name" => "直接输入网址或书签", "value" => $sum['SUM(from_mark)']],
                ["name" => "搜索引擎", "value" => $sum['SUM(from_engines)']],
                ["name" => "其他外部链接", "value" => $sum['SUM(from_other)']],
            ],
            "tableData" => $result['data'],
            "total" => $result['total'],
            "searchEngineLineChartData" => [
                "title" => ['直接输入网址或书签', '搜索引擎', '其他外部链接'],
                "time" => $res3['dates'],
                "source" => [
                    ["name" => '直接输入网址或书签',
                        "type" => 'line',
                        "data" => $res3['mark']
                    ],
                    ["name" => '搜索引擎',
                        "type" => 'line',
                        "data" => $res3['enj']
                    ],
                    ["name" => '其他外部链接',
                        "type" => 'line',
                        "data" => $res3['other']
                    ],
                ]
            ],
        ];
        return $data;
    }

    public function fromSortFilter($dstype)
    {
        $query = Db::table("source_domain_sort")->alias("sds")
            ->field("from_mark AS from_mark_raw,from_engines AS from_engines_raw,from_other AS from_other_raw,CONCAT(from_mark,'(',`from_mark_rate`,'%)') AS from_mark,CONCAT(from_engines,'[',`from_engines_rate`,'%)') AS from_engines,CONCAT(from_other,'{',`from_other_rate`,'%)') AS from_other,num,DATE_FORMAT(create_time, '%Y-%m-%d') AS time")
            ->where("sds.dstype",$dstype);
        $this->checkSearch($query);
        return $query;
    }
}