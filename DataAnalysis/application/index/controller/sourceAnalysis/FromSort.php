<?php


namespace app\index\controller\sourceAnalysis;

use app\common\Constant;
use app\index\controller\Base;
use app\index\controller\Zlog;
use app\index\model\SourceAnalysis;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class FromSort extends Base
{
    public function init()
    {
        $request = Request::instance()->post();
        $start_time = $request['searchData']['dateTime'][0];
        $end_time = $request['searchData']['dateTime'][1];
        $start_time = date('Y-m-d', strtotime($start_time));
        $end_time = date('Y-m-d', strtotime($end_time) + 86400);
        $type = Session::get("type");
        $where = '';
        if ($type != "M") {
            $where = "  `dstype`=1 and ";
        } else {
            $where = "  `dstype`=2 and ";
        }
        $sql1 = "SELECT from_mark as from_mark_raw,from_engines as from_engines_raw,from_other as from_other_raw,concat(from_mark,'(',from_mark_rate,'%)') as from_mark,concat(from_engines,'(',from_engines_rate,'%)') as from_engines,concat(from_other,'(',from_other_rate,'%)') as from_other,num,DATE_FORMAT(create_time,'%Y-%m-%d') as time FROM `source_domain_sort` where" . $where . "  create_time BETWEEN '{$start_time}' and '{$end_time}'";
        $sql2 = "SELECT SUM(num),SUM(from_engines),SUM(from_mark),SUM(from_other)  FROM `source_domain_sort` where" . $where . "  create_time BETWEEN '{$start_time}' and '{$end_time}'";
        $res1 = Db::query($sql1);
        $res2 = Db::query($sql2);
        $res3 = ['mark' => [], 'enj' => [], 'other' => [], 'dates' => []];
        foreach ($res1 as $res1_value) {
            $res3['dates'][] = $res1_value['time'];
            $res3['mark'][] = $res1_value['from_mark_raw'];
            $res3['enj'][] = $res1_value['from_engines_raw'];
            $res3['other'][] = $res1_value['from_other_raw'];
        }
        array_unshift(
            $res1,
            [
                'time' => '总计', 'num' => $res2[0]['SUM(num)'], 'from_mark' => $res2[0]['SUM(from_mark)'],
                'from_engines' => $res2[0]['SUM(from_engines)'], 'from_other' => $res2[0]['SUM(from_other)']
            ]
        );

        $data = [
            "keywordDataDetail" => [
                ["name" => "访问次数", "value" => $res2[0]['SUM(num)']],
                ["name" => "直接输入网址或书签", "value" => $res2[0]['SUM(from_mark)']],
                ["name" => "搜索引擎", "value" => $res2[0]['SUM(from_engines)']],
                ["name" => "其他外部链接", "value" => $res2[0]['SUM(from_other)']],
            ],
            "searchEngineChartData" => [
                ["name" => "直接输入网址或书签", "value" => $res2[0]['SUM(from_mark)']],
                ["name" => "搜索引擎", "value" => $res2[0]['SUM(from_engines)']],
                ["name" => "其他外部链接", "value" => $res2[0]['SUM(from_other)']],
            ],
            "tableData" => $res1,
            "total" => count($res1) - 1,
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
        $data['buttonControl'] = buttonDisable($this->permission,$this->route['sourceAnalysisFromSort']);
        return json($data);
    }

    public function page()
    {
        $mdl = new \app\index\model\FromSort();
        $data = $mdl->getList();
        return json($data);
    }

    public function export()
    {
        $request = Request::instance()->get();
        $start_time = $request['start_time'];
        $end_time = $request['end_time'];
        $prop = empty($request['prop']) ? "time" : $request['prop'];
        $order = isset($request['order']) ? $request['order'] == "descending" ? " desc" : " asc" : " desc";

        $start_time = date('Y-m-d', strtotime($start_time));
        $end_time = date('Y-m-d', strtotime($end_time) + 86400);
        $type = Session::get("type");
        $where = '';
        if ($type == "PC") {
            $where = "  `dstype`=1 and ";
        } else {
            $where = "  `dstype`=2 and ";
        }
        $sql1 = "SELECT DATE_FORMAT(create_time,'%Y-%m-%d') as time,num,concat(from_mark,'(',from_mark_rate,'%)') as from_mark,concat(from_engines,'(%',from_engines_rate,')') as from_engines,concat(from_other,'(%',from_other_rate,')') as from_other FROM `source_domain_sort` where" . $where . "  create_time BETWEEN '{$start_time}' and '{$end_time}'";
        $sql1 .= "ORDER BY {$prop} {$order}";
        $res1 = Db::query($sql1);

        $title_name = "<tr><th colspan='4'>来源分析-来源分类</th></tr>
<tr>
<th>日期</th>
<th>访问次数</th>
<th>直接输入网址或书签</th>
<th>搜索引擎</th>
<th>其他外部链接</th>
</tr>";
        $str = "<html xmlns:o='urn:schemas-microsoft-com:office:office'\r\nxmlns:x='urn:schemas-microsoft-com:office:excel'\r\nxmlns='http://www.w3.org/TR/REC-html40'>\r\n<head>\r\n<meta http-equiv=Content-Type content='text/html; charset=utf-8'>\r\n</head>\r\n<body>";
        $str .= "<table border=1><thead>" . $title_name . "</thead>";
        foreach ($res1 as $value) {
            $str .= "<tr>";
            foreach ($value as $item) {
                $str .= "<td>" . $item . "</td>";
            }
            $str .= "</tr>\n";
        }
        $str .= "</table></body></html>";
        header("Content-Type: application/vnd.ms-excel; name='excel'");
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=来源分类.xls");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        exit($str);
    }
}