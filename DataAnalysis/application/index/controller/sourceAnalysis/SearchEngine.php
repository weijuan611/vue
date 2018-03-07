<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/20
 * Time: 11:12
 */

namespace app\index\controller\sourceAnalysis;

use app\common\Constant;
use app\index\controller\Base;
use app\index\model\SearchEnginesCount;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class SearchEngine extends Base
{
    public function init()
    {
        $request = Request::instance()->post();
//        Log::error(var_export($request,1));
        $start_time = date('Y-m-d',strtotime($request['search']['dateTime'][0]));
        $end_time = date('Y-m-d',strtotime($request['search']['dateTime'][1]));
        $model = new SearchEnginesCount();
        $data = $model->getList($start_time,$end_time);
        $data['buttonControl'] = buttonDisable($this->permission,$this->route['sourceAnalysisSearchEngine']);
        return json($data);
    }

    public function import(){
        if (Session::get("type") == "PC"){
            $table1 = Db::table('search_engines_count_pc');
        }else{
            $table1 = Db::table('search_engines_count_m');
        }
        $data_result = [];
        $request = Request::instance()->get();
//        Log::error(var_export($request,1));
        $start_time = date('Y-m-d',strtotime($request['start']));
        $end_time = date('Y-m-d',strtotime($request['end']));
        $prop = empty($request['prop'])?"time":$request['prop'];
        $order = isset($request['order'])?$request['order'] == "descending"?" desc":" asc" :" desc";
        $data = $res1 = $table1->field('DATE_FORMAT(create_time,\'%Y-%m-%d\') as time')
            ->field('SUM(CASE WHEN se_id = 2 THEN count ELSE 0 END) AS 360search')
            ->field('SUM(CASE WHEN se_id = 3 THEN count ELSE 0 END) AS baidu')
            ->field('SUM(CASE WHEN se_id = 4 THEN count ELSE 0 END) AS sougou')
            ->field('SUM(CASE WHEN se_id = 7 THEN count ELSE 0 END) AS google')
            ->field('SUM(CASE WHEN se_id = 8 THEN count ELSE 0 END) AS shenma')
            ->field('SUM(CASE WHEN se_id = 8 or se_id =7 or se_id=4 or se_id=3 or se_id=2 THEN count ELSE 0 END) AS search_num')
            ->where('create_time','between',[$start_time,$end_time])->order($prop,$order)->group('create_time')->select();
        foreach ($data as $key=>$value){
            $data_result[] = [
                $value["time"],
                $value["search_num"],
                $value["baidu"],
                $value["360search"],
                $value["sougou"],
                $value["google"],
                $value["shenma"],
            ];
        }
        $title_name = "<tr><th colspan='11'>来源分析-搜索引擎</th></tr>
<tr>
<th>日期</th>
<th>访问次数</th>
<th>百度</th>
<th>360搜索</th>
<th>搜狗</th>
<th>谷歌</th>
<th>神马</th>
</tr>";
        $str = "<html xmlns:o='urn:schemas-microsoft-com:office:office'\r\nxmlns:x='urn:schemas-microsoft-com:office:excel'\r\nxmlns='http://www.w3.org/TR/REC-html40'>\r\n<head>\r\n<meta http-equiv=Content-Type content='text/html; charset=utf-8'>\r\n</head>\r\n<body>";
        $str .= "<table border=1><thead>".$title_name."</thead>";
        foreach ($data_result as $value) {
            $str .= "<tr>";
            foreach ($value as $item) {
                $str .= "<td>".$item."</td>";
            }
            $str .= "</tr>\n";
        }
        $str .= "</table></body></html>";
        header("Content-Type: application/vnd.ms-excel; name='excel'");
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=搜索引擎.xls");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        exit( $str );
    }
}