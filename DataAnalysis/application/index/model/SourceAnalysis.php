<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/20
 * Time: 11:29
 */
namespace app\index\model;

use think\Db;
use think\Log;
use think\Request;
use think\Session;
use think\Zlog;

class SourceAnalysis extends Base
{
    public  function getKeyWords()
    {
        $type = Session::get("type");
        $request = Request::instance()->post();
        $type == "PC" ?$deivce_type = 1:$deivce_type = 2;
        $end_time = isset($request['searchdata']["dateTime"][1])?$request['searchdata']["dateTime"][1]:date("Y-m-d");
        $start_time = isset($request['searchdata']["dateTime"][0])?$request['searchdata']["dateTime"][0]:date("Y-m-d");
        $db = Session::get("type") == 'PC'?DB::table('url_count_pc'):DB::table('url_count_m');
        $total=$db->where('create_time','between',[$start_time,$end_time])->sum('num');

        $keyword = isset($request['searchdata']["keyword"])?trim($request['searchdata']["keyword"]):"";
        $result_keyword=$this->getKeyWordsInfoFilter()->group('kst.kws_id');
        if ($keyword != ""){
            $result_keyword->where("key.keyword",'like','%'.$keyword.'%');
        }
        $result_keyword = $this->autoPaginate($result_keyword)->toArray();
        if(!empty($result_keyword['data'])){
            if(isset($request['paginate']['order'])&&$request['paginate']['order'] == 'ascending'){
                $num = $result_keyword['total'] - $result_keyword['per_page'] * ($result_keyword['current_page'] - 1);
                foreach ($result_keyword['data'] as $k=>$v){
                    $result_keyword['data'][$k]['id']=$num;
                    $num-=1;
                }
            }else{
                $num = $result_keyword['per_page'] * ($result_keyword['current_page'] - 1);
                foreach ($result_keyword['data'] as $k=>$v){
                    $num+=1;
                    $result_keyword['data'][$k]['id']=$num;
                }
            }
        }

        $keywordDataDetail = [['title'=>'访问次数','number'=>0],['title'=>'搜索引擎','number'=>0],['title'=>'百度','number'=>0],['title'=>'360搜索','number'=>0],['title'=>'搜狗','number'=>0],['title'=>'谷歌','number'=>0],['title'=>'神马','number'=>0]];
        $result_domain = $this->getKeyWordsInfoFilter()->select();
        if(!empty($result_domain)){
            $keywordDataDetail[0]['number'] = $total;
            $keywordDataDetail[1]['number'] = $result_domain[0]['num'];
            $keywordDataDetail[2]['number'] = $result_domain[0]['baidu'];
            $keywordDataDetail[3]['number'] = $result_domain[0]['360search'];
            $keywordDataDetail[4]['number'] = $result_domain[0]['sougou'];
            $keywordDataDetail[5]['number'] = $result_domain[0]['google'];
            $keywordDataDetail[6]['number'] = $result_domain[0]["sm"];
        }
        array_unshift($result_keyword['data'],[
            'id'=>'',
            'keyword'=>'总计',
            'create_time'=>'',
            'dstype'=>1,
            'num'=>$keywordDataDetail[1]['number'],
            'baidu'=>$keywordDataDetail[2]['number'],
            '360search'=>$keywordDataDetail[3]['number'],
            'sougou'=>$keywordDataDetail[4]['number'],
            'google'=>$keywordDataDetail[5]['number'],
            'sm'=>$keywordDataDetail[6]['number'],
        ]);
        return ["keywordDataDetail"=>$keywordDataDetail,"tableData"=>$result_keyword['data'],"total"=>$result_keyword['total'],'current_page'=>$result_keyword['current_page']];
    }

    public  function getKeyWordsInfoFilter()
    {
        $type = Session::get("type");
        $request = Request::instance()->param();
        $type == "PC" ?$deivce_type = 1:$deivce_type = 2;
        $end_time = isset($request['searchdata']["dateTime"][1])?$request['searchdata']["dateTime"][1]:date("Y-m-d");
        $start_time = isset($request['searchdata']["dateTime"][0])?$request['searchdata']["dateTime"][0]:date("Y-m-d");
         if(isset($request['start_time'])&&isset($request['end_time'])){
             $start_time = $request['start_time'];
             $end_time = $request['end_time'];
        }
        $quert =$this->table("keyword_statistics")->alias('kst')
            ->join("keywords_search key","kst.kws_id  = key.kws_id",'LEFT')
            ->field('kst.id,kst.create_time,kst.dstype,key.keyword,SUM(`kst`.`num`) AS num,
                        SUM(CASE WHEN `kst`.`se_id` = 3 THEN `kst`.`num` ELSE 0 END) AS baidu,
                        SUM(CASE WHEN `kst`.`se_id` = 2 THEN `kst`.`num` ELSE 0 END) AS 360search,
                        SUM(CASE WHEN `kst`.`se_id` = 4 THEN `kst`.`num` ELSE 0 END) AS sougou,
                        SUM(CASE WHEN `kst`.`se_id` = 8 THEN `kst`.`num` ELSE 0 END) AS sm,
                        SUM(CASE WHEN `kst`.`se_id` = 7 THEN `kst`.`num` ELSE 0 END) AS google')
            ->where('kst.create_time','between',[$start_time,$end_time])->where("kst.dstype",$deivce_type)
            ->where('kst.se_id','IN',[2,3,4,7,8]);
        return $quert;
    }

    public function exportKeyWords()
    {
        $result = $this->getKeyWordsInfoFilter()->group('kst.kws_id');
        $request = Request::instance()->get();
        if(isset($request['keyword'])&&$request['keyword'] != ''){
            $result->where("key.keyword",'like','%'.$request['keyword'].'%');
        }
        $prop = empty($request['prop'])?"num":$request['prop'];
        $order = isset($request['order'])?$request['order'] == "descending"?" desc":" asc" :" desc";
        $result= $result->order($prop,$order)->select();
        $data_result = [];
        foreach ($result as $key=>$value){
            $data_result[$key] = [
                $value["keyword"],
                $value["create_time"],
                $value["num"],
                $value["baidu"],
                $value["360search"],
                $value["sougou"],
                $value["google"],
                $value["sm"],
            ];
        }
        $title_name = "<tr><th colspan='11'>流量分析-搜索词</th></tr>
<tr>
<th>搜索词</th>
<th>搜索时间</th>
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
        header("Content-Disposition: attachment; filename=搜索词详情.xls");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        exit( $str );
    }


    /***************************************************来路域名开始**************************************************/
    public function getOriginAnalysisList()
    {
        $type = Session::get("type");
        $type == "PC"?$dstype = 1:$dstype = 2;
        $request = Request::instance()->post();
        $prop = "d_num";
        $order = isset($request['paginate']['order'])&&$request['paginate']['order'] == "descending"?" desc":" asc";
        $originDataDetail = [['title'=>"访问次数","number"=>"0"],['title'=>"直接输入网址或书签","number"=>"0"],['title'=>"搜索引擎","number"=>"0"],['title'=>"其他外部链接","number"=>"0"]];
        $starttime = isset($request['searchData']['dateTime'][0])?$request['searchData']['dateTime'][0]:date("Y-m-d");
        $endtime   = isset($request['searchData']['dateTime'][1])?$request['searchData']['dateTime'][1]:date("Y-m-d");
        $query = $this->getOrginAnalysisFilter()->order($prop,$order);
        $result = $this->autoPaginate($query,"","","","d_name","desc")->toArray();
        $count = Db::table("source_domain_sort")
            ->field("SUM(from_mark) AS from_mark,SUM(from_engines) AS from_engines,SUM(from_other) AS from_other,SUM(num) AS total")
            ->where('create_time','between',[$starttime,$endtime])->where("dstype",'=',$dstype)->select();
        $originDataDetail[0]["number"] = $count[0]['total'];
        $originDataDetail[1]["number"] = $count[0]['from_mark'];
        $originDataDetail[2]["number"] = $count[0]['from_engines'];
        $originDataDetail[3]["number"] = $count[0]['from_other'];
        return ['tableData'=>$result['data'],'total'=>$result['total'],'originDataDetail'=>$originDataDetail];
    }
    public static function getOrginAnalysisFilter()
    {
        $type = Session::get("type");
        $type == "PC"?$dstype = 1:$dstype = 2;
        $request = Request::instance()->post();
        if (!empty($request)){
            $urldetail = isset($request['searchData']['urlDetail'])?$request['searchData']['urlDetail']:"";
            $starttime = isset($request['searchData']['dateTime'][0])?$request['searchData']['dateTime'][0]:date("Y-m-d");
            $endtime   = isset($request['searchData']['dateTime'][1])?$request['searchData']['dateTime'][1]:date("Y-m-d",strtotime("-1 days"));
        }else{
            $request = Request::instance()->get();
            $urldetail = isset($request['urldetail'])?$request['urldetail']:"";
            $starttime = isset($request['start_time'])?$request['start_time']:date("Y-m-d");
            $endtime   = isset($request['end_time'])?$request['end_time']:date("Y-m-d",strtotime("-1 days"));
        }
        $query = Db::table("source_domain_count")->alias("fdc")->join("domains dom","dom.d_id = fdc.d_id","LEFT")
            ->field('dom.domain AS d_name,SUM(fdc.num) AS d_num')->where("dstype",$dstype)
            ->where('fdc.create_time','between',[$starttime,$endtime])->group("dom.d_id");
        if ($urldetail != "") {
            $query = $query->where('dom.domain','like','%'.$urldetail.'%');
        }
        return $query;
    }
    public function exportOriginAnalysis()
    {
        $request = Request::instance()->get();
        $prop = "d_num";
        $order = isset($request['order'])&&$request['order'] == "descending"?" desc":" asc";
        $result = $this->getOrginAnalysisFilter()->order($prop,$order)->order("d_name DESC")->select();
        $data_result = [];
        foreach ($result as $key=>$value){
            $data_result[$key] = [
                $value["d_name"],
                $value["d_num"],
            ];
        }
        $title_name = "<tr><th colspan='2'>流量分析-来路域名</th></tr>
<tr>
<th>来路域名</th>
<th>访问次数</th>
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
        header("Content-Disposition: attachment; filename=来路域名.xls");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        exit( $str );
    }
    /***************************************************来路域名结束**************************************************/
    /**************************************************来路域名排序开始**************************************************/
    public function getOriginAnalysisSortFilter()
    {
        $table = "source_domain_count";
        $type = Session::get("type");
        $type == "PC" ? $dstype = 1 : $dstype = 2;
        $request = Request::instance()->post();
        if (!empty($request)){
            $time_one['starttime'] = isset($request['searchData']['dateTime'][0]) ? $request['searchData']['dateTime'][0].' 00:00:00' : date("Y-m-d",strtotime("-1 days"));
            $time_one['endtime'] = isset($request['searchData']['dateTime'][1]) ? $request['searchData']['dateTime'][1].' 23:59:59' : date("Y-m-d",strtotime("-1 days"));
            $time_two['starttime'] = isset($request['searchData']['dateTime2'][0]) ? $request['searchData']['dateTime2'][0].' 00:00:00' : date("Y-m-d",strtotime("-2 days"));
            $time_two['endtime'] = isset($request['searchData']['dateTime2'][1]) ? $request['searchData']['dateTime2'][1].' 23:59:59' : date("Y-m-d",strtotime("-2 days"));
            $sort = !empty($request['searchData']['sort'])&&$request['searchData']['sort']!=""?$request['searchData']['sort']:"";
        }else{
            $request = Request::instance()->get();
            $time_one['starttime'] = isset($request['start_time1']) ? $request['start_time1'] : "";
            $time_one['endtime'] = isset($request['end_time1']) ? $request['end_time1'] : "";
            $time_two['starttime'] = isset($request['start_time2']) ? $request['start_time2'] : "";
            $time_two['endtime'] = isset($request['end_time2']) ? $request['end_time2'] : "";
            $sort = isset($request['sort'])?$request['sort']:"";
        }
        $sql = Db::table($table)->alias("fdc")
            ->join("domains dom","dom.d_id = fdc.d_id","LEFT")
            ->field("SUM(CASE WHEN `fdc`.`create_time` BETWEEN '{$time_one['starttime']}' AND '{$time_one['endtime']}' THEN `fdc`.`num` ELSE 0 END) AS one_num,
            SUM(CASE WHEN `fdc`.`create_time` BETWEEN '{$time_two['starttime']}' AND '{$time_two['endtime']}' THEN `fdc`.`num` ELSE 0 END) AS two_num,
            domain")
            ->where("fdc.dstype",$dstype)
            ->whereTime('fdc.create_time','between',[$time_one['starttime'],$time_one['endtime']])
            ->whereOr('fdc.create_time','between',[$time_two['starttime'],$time_two['endtime']])
            ->group("dom.d_id")->buildSql();

        $query = Db::table($sql. ' a')->field("*,CASE WHEN one_num <> 0 && two_num <> 0 THEN
            CASE WHEN(one_num - two_num) >0 THEN (one_num - two_num) / one_num 
                 WHEN(one_num - two_num) <0 THEN (one_num - two_num) / one_num 
                 ELSE (one_num - two_num) / one_num END
            WHEN one_num = 0 && two_num <> 0 THEN two_num
            WHEN one_num <> 0 && two_num = 0 THEN one_num
            ELSE 0 END AS sort");
        if ($sort == "up") {
            $query1 = $query->where("(one_num - two_num)>0");
        } elseif ($sort == "down") {
            $query1 = $query->where("(one_num - two_num)<0");
        } elseif ($sort == "equal") {
            $query1 = $query->where("(one_num - two_num)=0");
        } else {
            $query1 = $query;
        }
        return $query1;
    }
    public function getOriginAnalysisSortList()
    {
        $table = "source_domain_count";
        $type = Session::get("type");
        $type == "PC" ? $dstype = 1 : $dstype = 2;
        $request = Request::instance()->post();
        $time_one['starttime'] = isset($request['searchData']['dateTime'][0]) ? $request['searchData']['dateTime'][0].' 00:00:00' : date("Y-m-d",strtotime("-1 days"));
        $time_one['endtime'] = isset($request['searchData']['dateTime'][1]) ? $request['searchData']['dateTime'][1].' 23:59:59' : date("Y-m-d",strtotime("-1 days"));
        $time_two['starttime'] = isset($request['searchData']['dateTime2'][0]) ? $request['searchData']['dateTime2'][0].' 00:00:00' : date("Y-m-d",strtotime("-2 days"));
        $time_two['endtime'] = isset($request['searchData']['dateTime2'][1]) ? $request['searchData']['dateTime2'][1].' 23:59:59' : date("Y-m-d",strtotime("-2 days"));
        $prop = "one_num";
        $order = !empty($request['paginate']['order'])&&$request['paginate']['order'] == "descending"?" desc":" asc";
        $query = $this->getOriginAnalysisSortFilter()->order("{$prop} {$order}");
        $result = $this->autoPaginate($query)->toArray();
        $key1 = $time_one['starttime'] == $time_one['endtime']?date("Y-m-d",strtotime($time_one['starttime'])):date("Y-m-d",strtotime($time_one['starttime']))." 至 ".date("Y-m-d",strtotime($time_one['endtime']));
        $key2 = $time_two['starttime'] == $time_two['endtime']?date("Y-m-d",strtotime($time_two['starttime'])):date("Y-m-d",strtotime($time_two['starttime']))." 至 ".date("Y-m-d",strtotime($time_two['endtime']));
        $tableData = $type =  [];
        $total = [["title"=>$key1,"number"=>0],["title"=>$key2,"number"=>0],["title"=>"变化情况","number"=>0]];
        foreach ($result['data'] as $key=>$value){
            $tableData[$key]['domain'] = $value['domain'];
            $tableData[$key]['one_num'] = $value['one_num'];
            $tableData[$key]['two_num'] = $value['two_num'];
            if ($value['one_num'] != 0 && $value['two_num'] != 0){
                $diff = $value['one_num']-$value['two_num'];
                if($diff > 0){
                    $tableData[$key]['sort']="+".$diff."(".(round($diff/$tableData[$key]['one_num'],4)*100)."%)";
                    $tableData[$key]['type']="up";
                }else{
                    $tableData[$key]['sort']=$diff."(".(round($diff/$tableData[$key]['one_num'],4)*100)."%)";
                    if($diff == 0){
                        $tableData[$key]['type']="equality";
                    }else{
                        $tableData[$key]['type']="down";
                    }
                }
            }else{
                if ($value['one_num'] == 0){
                    $tableData[$key]["sort"] = "-".$value['two_num']."(-100%)";
                    $tableData[$key]['type'] = "down";
                }
                if ($value['two_num'] == 0){
                    $tableData[$key]["sort"] = "+".$value['one_num']."(+100%)";
                    $tableData[$key]['type'] = "up";
                }
            }
        }
        $data = Db::table($table)->alias("fdc")
            ->join("domains dom","dom.d_id = fdc.d_id","LEFT")
            ->field("SUM(CASE WHEN `fdc`.`create_time` BETWEEN '{$time_one['starttime']}' AND '{$time_one['endtime']}' THEN `fdc`.`num` ELSE 0 END) AS one_num,
            SUM(CASE WHEN `fdc`.`create_time` BETWEEN '{$time_two['starttime']}' AND '{$time_two['endtime']}' THEN `fdc`.`num` ELSE 0 END) AS two_num,
            domain")
            ->where("fdc.dstype",$dstype)->select();

        $total[0]['number'] = $data[0]['one_num'];
        $total[1]['number'] = $data[0]['two_num'];
        $diff = $total[0]['number']-$total[1]['number'];
        $type["type"] = $diff >= 0?$diff>0?"up":"equality":"down";
        if ($total[0]['number'] != 0 && $total[1]['number'] != 0){
            if ($diff >0) {
                $total[2]['number'] = "+".$diff."(".(round($diff/$total[0]['number'],4)*100)."%)";
            } else {
                $total[2]['number'] = $diff."(".(round($diff/$total[0]['number'],4)*100)."%)";
            }
        }else{
            if ($total[0]['number'] == 0)
                $total[2]['number'] = "-".$total[1]['number']."(-100%)";
            if ($total[1]['number'] == 0)
                $total[2]['number'] = "+".$total[0]['number']."(+100%)";
        }
        return ["tableData"=>$tableData,"total"=>["total"=>$total,"type"=>$type],"page"=>$result['total']];
    }
    public function exportOriginAnalysisSort()
    {
        $request = Request::instance()->get();
        $time_one['starttime'] = isset($request['start_time1']) ? $request['start_time1'] : "";
        $time_one['endtime'] = isset($request['end_time1']) ? $request['end_time1'] : "";
        $time_two['starttime'] = isset($request['start_time2']) ? $request['start_time2'] : "";
        $time_two['endtime'] = isset($request['end_time2']) ? $request['end_time2'] : "";
        $prop = "one_num";
        $order = isset($request['order'])?$request['order'] == "descending"?" desc":" asc" :" desc";
        $key1 = $time_one['starttime'] == $time_one['endtime']?$time_one['starttime']:$time_one['starttime']." 至 ".$time_one['endtime'];
        $key2 = $time_two['starttime'] == $time_two['endtime']?$time_two['starttime']:$time_two['starttime']." 至 ".$time_two['endtime'];
        $tableData = $data_result = [];
        $result = $this->getOriginAnalysisSortFilter()->order($prop,$order)->select();
        foreach ($result as $key=>$value){
            $tableData[$key]['domain'] = $value['domain'];
            $tableData[$key]['one_num'] = $value['one_num'];
            $tableData[$key]['two_num'] = $value['two_num'];
            if ($value['one_num'] != 0 && $value['two_num'] != 0){
                $diff = $value['one_num']-$value['two_num'];
                if ($diff > 0) {
                    $tableData[$key]['sort'] = "+".$diff."(".(round($diff/$tableData[$key]['one_num'],4)*100)."%)";
                } else {
                    $tableData[$key]['sort'] = $diff."(".(round($diff/$tableData[$key]['one_num'],4)*100)."%)";
                }
            }else{
                if ($value['one_num'] == 0){
                    $tableData[$key]["sort"] = "-".$value['two_num']."(-100%)";
                }
                if ($value['two_num'] == 0){
                    $tableData[$key]["sort"] = "+".$value['one_num']."(+100%)";
                }
            }
        }
        foreach ($tableData as $key=>$value){
            $data_result[$key] = [
                $value["domain"],
                $value["one_num"],
                $value["two_num"],
                $value["sort"],
            ];
        }
        $title_name = "<tr><th colspan='4'>流量分析-来路域名升降</th></tr>
<tr>
<th>来路域名</th>
<th>{$key1}</th>
<th>{$key2}</th>
<th>变化情况</th>
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
        header("Content-Disposition: attachment; filename=来路域名升降.xls");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        exit( $str );
    }
    /**************************************************来路域名排序结束**************************************************/
    
    
    public static function getBench(){
        $query = DB::table('source_domain_count sdc')
        ->join('domains d','sdc.d_id = d.d_id','left');
        if(Session::get('type') != 'PC'){
            $query=$query->where('sdc.dstype','=',2);
        }else{
            $query=$query->where('sdc.dstype','=',1);
        }
        $day = date('Y-m-d',strtotime('-1day'));
        $data = $query->where('sdc.create_time','=',$day)->order('sdc.num','desc')->limit(5)
            ->field('sdc.num as num,d.domain as url')->select();
        $result = [
            ['color'=> '#409eff','url'=> '', 'number'=> 0],
            ['color'=> '#67c23a','url'=> '', 'number'=> 0],
            ['color'=> '#f56c6c','url'=> '', 'number'=> 0],
            ['color'=> 'orange','url'=> '', 'number'=> 0],
            ['color'=> '#2ec7c9','url'=> '', 'number'=> 0],
        ];
        if (!empty($data)){
            foreach ($data as $k=>$v){
                $result[$k]['url']=$v['url'];
                $result[$k]['number']=$v['num'];
            }
        }
        return $result;
    }
}
