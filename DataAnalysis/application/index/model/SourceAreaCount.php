<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2017/12/26
 * Time: 15:04
 */

namespace app\index\model;

use app\index\controller\Zlog;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class SourceAreaCount extends Base
{
    public function request()
    {
        $type = Session::get("type");
        $type == "PC"?$dstype = 1:$dstype = 2;
        $request = Request::instance()->post();
        $tongji = isset($request['searchData']['tongji'])?$request['searchData']['tongji']:"ip";
        $tongji == "ip"?$area_type = 1 :$area_type = 2;
        $starttime = isset($request['searchData']['dateTime'][0])?$request['searchData']['dateTime'][0]:date("Y-m-d");
        if (strtotime($starttime) == strtotime($request['searchData']['dateTime'][1])) {
            $endtime   = isset($request['searchData']['dateTime'][1])?date("Y-m-d",strtotime("{$request['searchData']['dateTime'][1]} +1 days")):date("Y-m-d");
        } else {
            $endtime   = isset($request['searchData']['dateTime'][1])?$request['searchData']['dateTime'][1]:date("Y-m-d");
        }
        $provinceRange = isset($request["provinceRange"])?$request["provinceRange"]:0;
        $tableDataClassify = isset($request['searchData']['tableDataClassify'])?$request['searchData']['tableDataClassify'] == "省级"?1:2:1;
        $prop =  !empty($request['paginate']['prop'])?$request['paginate']['prop']:'pv';
        $order = isset($request['paginate']['order'])&&$request['paginate']['order'] == "descending"?"desc":"asc";
        $result = ['area_type'=>$area_type,'starttime'=>$starttime,'endtime'=>$endtime,'dstype'=>$dstype,'provinceRange'=>$provinceRange,"tableDataClassify"=>$tableDataClassify,"prop"=>$prop,"order"=>$order];
        return $result;
    }
    public function entrance()
    {
        $result = $this->request();
        $result['areaDataDetail'] = $this->areaDataDetail($result);
        $result['areaAnalysis_mapChart_data'] = $this->areaAnalysisMapChartData($result);
        $result['areaAnalysis_pieChart_data'] = $this->areaAnalysisPieChartData($result);
        $result['detail'] = $this->tableData($result);
        return $result;
    }


    public function areaDataDetail($request)
    {
        $result = Db::table("source_area_count")->field("SUM(pv) AS pv,SUM(new_uv) AS new_uv,SUM(uv) AS uv,SUM(ip) AS ip,
        SUM(access_num) AS access_num,SUM(num_jump) AS num_jump")->where("dstype",$request['dstype'])->where("area_type",$request['area_type'])
            ->whereTime('create_time','between',[$request['starttime'],$request['endtime']])->select();
        return [
            ['title' => '访问次数',
                'number' => empty($result[0]['access_num'])?"0":$result[0]['access_num']],
            ['title' => '浏览次数(PV)',
                'number' => empty($result[0]['pv'])?"0":$result[0]['pv']],
            ['title' => '独立访客(UV)',
                'number' => empty($result[0]['uv'])?"0":$result[0]['uv']],
            ['title' => 'IP',
                'number' => empty($result[0]['ip'])?"0":$result[0]['ip']],
            ['title' => '新独立访客',
                'number' => empty($result[0]['new_uv'])?"0":$result[0]['new_uv']],
            ['title' => '跳出率',
                'number' => empty($result[0]['uv'])?"0%":(round($result[0]['num_jump']/$result[0]['uv'],4)*100)."%"]
        ];
    }

    public function areaAnalysisMapChartData($request)
    {
        $query = Db::table("source_area_count sac")->join("sys_area sa","sac.area_id = sa.Id","LEFT")
            ->field("SUM(pv) AS spv,sa.id_level_0")->where("dstype",$request['dstype'])->where("area_type",$request['area_type'])
            ->whereTime('create_time','between',[$request['starttime'],$request['endtime']])->group("sa.Id_Level_0")->buildSql();
        $result = Db::table($query. " a")->join("sys_area saa","a.id_level_0 = saa.Id","LEFT")
            ->field("spv AS value,saa.AreaName AS name")->select();
        return $result;
    }

    public function areaAnalysisPieChartData($request)
    {
        $areas = [];
        if ($request['provinceRange'] != 0) {
            $data = Db::table("source_area_count sac")->join("sys_area sa","sac.area_id = sa.Id","LEFT")
                ->field("SUM(pv) AS spv,sa.areaname AS name")
                ->where("area_type",$request['area_type'])->where("dstype",$request['dstype'])->where("Id_Level_0",$request['provinceRange'])
                ->whereTime('create_time','between',[$request['starttime'],$request['endtime']])->group("sac.area_id")->order("spv DESC")->select();
        }else{
            $query = Db::table("source_area_count sac")->join("sys_area sa","sac.area_id = sa.Id","LEFT")
                ->field("SUM(pv) AS spv,sa.id_level_0")->where("dstype",$request['dstype'])->where("area_type",$request['area_type'])
                ->whereTime('create_time','between',[$request['starttime'],$request['endtime']])->group("sa.Id_Level_0")->buildSql();
            $data = Db::table($query. " a")->join("sys_area saa","a.id_level_0 = saa.Id","LEFT")
                ->field("spv,saa.AreaName AS name")->order("spv DESC")->select();
        }
        if (!empty($data)){
            foreach ($data as $key=>$val){
                if ($key < 5) {
                    $areas[$key]['value'] = $val['spv'];
                    $areas[$key]['name'] = $val['name'] != ""?$val['name']. "(PV)：".$val['spv']:"不详(PV)：" .$val['spv'];
                } else {
                    $areas[5]['name'] = "";
                    $areas[5]['value'] = isset($areas[5]['value'])?$areas[5]['value'] + $val['spv'] : $val['spv'];
                }
            }
            $num = count($areas);
            $areas[$num-1]['name'] = "其他城市(PV)：".$areas[$num-1]['value'];
        }
        return $areas;
    }

    public function tableData($request)
    {
        $citys = $true_citydata = [];
        $total = Db::table("source_area_count")->field("SUM(pv) AS pv,SUM(new_uv) AS new_uv,SUM(uv) AS uv,SUM(ip) AS ip,
        SUM(access_num) AS access_num,SUM(num_jump) AS num_jump")->where("dstype",$request['dstype'])->where("area_type",$request['area_type'])
            ->whereTime('create_time','between',[$request['starttime'],$request['endtime']])->select();
        if ($request['tableDataClassify'] == 1){
            $query = Db::table("source_area_count sac")->join("sys_area sa","sac.area_id = sa.Id","LEFT")
                ->field("SUM(pv) AS pv,SUM(uv) AS uv,SUM(ip) AS ip,SUM(new_uv) AS newUv,SUM(access_num) AS visitNumber,SUM(num_jump) AS bounceRate,SUM(percent) AS proportion,sa.id_level_0")
                ->where("dstype",$request['dstype'])->where("area_type",$request['area_type'])
                ->whereTime('create_time','between',[$request['starttime'],$request['endtime']])->group("sa.Id_Level_0")->buildSql();
            $sql = Db::table($query. " a")->join("sys_area saa","a.id_level_0 = saa.Id","LEFT")
                ->field("a.*,saa.AreaName AS name");
            if ( $request['prop'] && $request['order']){
                $sql->order($request['prop'],$request['order']);
            }
            $data = $sql->select();
            foreach($data as $k => $v){
                $citys[] = $v['id_level_0'];
            }
            $citydata = Db::table("source_area_count sac")->join("sys_area sa","sac.area_id = sa.Id","LEFT")
                ->field("SUM(pv) AS pv,SUM(uv) AS uv,SUM(ip) AS ip,SUM(new_uv) AS newUv,SUM(access_num) AS visitNumber,SUM(num_jump) AS bounceRate,SUM(percent) AS proportion,sa.id_level_0,sa.areaname AS name")
                ->where("area_type",$request['area_type'])->where("dstype",$request['dstype'])->where("sa.level","1")->where("sa.id_level_0","in",$citys)
                ->whereTime('create_time','between',[$request['starttime'],$request['endtime']])
                ->group("sac.area_id")->order("pv DESC")->select();
            foreach ($citydata as $key=>$value) {
                $true_citydata[$value['id_level_0']][] = $value;
            }
            $result = $data;$count = count($data);
        } else {
            $sql = Db::table("source_area_count sac")->join("sys_area sa","sac.area_id = sa.Id","LEFT")
                ->field("SUM(pv) AS pv,SUM(uv) AS uv,SUM(ip) AS ip,SUM(new_uv) AS newUv,SUM(access_num) AS visitNumber,SUM(num_jump) AS bounceRate,SUM(percent) AS proportion,sa.areaname AS name")
                ->where("area_type",$request['area_type'])->where("dstype",$request['dstype'])
                ->whereTime('create_time','between',[$request['starttime'],$request['endtime']])
                ->group("sac.area_id");
            $data = $this->autoPaginate($sql,"","","",$prop_p = $request['prop'],$order_p = $request['order'])->toArray();
            $result = $data['data'];$count = $data['total'];
        }
        $list = [];
        $list[0] = [
            "name"=>'全站总计',
            "pv"=>empty($total[0]['pv'])?0:$total[0]['pv'],
            "proportion"=>'100%',
            "uv"=>empty($total[0]['uv'])?0:$total[0]['uv'],
            "ip"=>empty($total[0]['ip'])?0:$total[0]['ip'],
            "newUv"=>empty($total[0]['new_uv'])?0:$total[0]['new_uv'],
            "visitNumber"=>empty($total[0]['access_num'])?0:$total[0]['access_num'],
            "bounceRate"=>empty($total[0]['uv'])?"0%":(round($total[0]['num_jump']/$total[0]['uv'],4)*100)."%"
        ];
        foreach ($result as $key=>$val) {
            $list[$key+1]['name'] = $val['name'] != ""?$val['name']:"不详";
            $list[$key+1]['pv'] = $val['pv'];
            $list[$key+1]['proportion'] = round($val['proportion']*100,4)."%";
            $list[$key+1]['uv'] = $val['uv'];
            $list[$key+1]['ip'] = $val['ip'];
            $list[$key+1]['newUv'] = $val['newUv'];
            $list[$key+1]['visitNumber'] = $val['visitNumber'];
            $list[$key+1]['bounceRate'] = $val['uv'] == 0?"0(0%)":(round($val['bounceRate']/$val['uv'],4)*100)."%";
            if ($request['tableDataClassify'] == 1 && !empty($val['id_level_0'])&&isset($true_citydata[$val['id_level_0']])){
                $arr =$true_citydata[$val['id_level_0']];
                foreach ($arr as $i=>$j) {
                    $list[$key+1]['expandInfo']['tableInfo'][$i]['name'] = $j['name'];
                    $list[$key+1]['expandInfo']['tableInfo'][$i]['pv'] = $j['pv'];
                    $list[$key+1]['expandInfo']['tableInfo'][$i]['proportion'] = round($j['proportion']*100,4)."%";
                    $list[$key+1]['expandInfo']['tableInfo'][$i]['uv'] = $j['uv'];
                    $list[$key+1]['expandInfo']['tableInfo'][$i]['ip'] = $j['ip'];
                    $list[$key+1]['expandInfo']['tableInfo'][$i]['newUv'] = $j['newUv'];
                    $list[$key+1]['expandInfo']['tableInfo'][$i]['visitNumber'] = $j['visitNumber'];
                    $list[$key+1]['expandInfo']['tableInfo'][$i]['bounceRate'] = $j['uv'] == 0?"0(0%)":(round($j['bounceRate']/$j['uv'],4)*100)."%";
                }

            }
        }
        return [
            "tableData"=>$list,
            "paginationData"=>$count
        ];
    }

    public function getArea()
    {
        $areas = [];
        $data = Db::table('sys_area')->where('level',0)->select();
        $areas[0]["value"] = "0";
        $areas[0]["label"] = "---省级---";
        foreach ($data as $key=>$val){
            $areas[$key+1]["value"] = $val["Id"];
            $areas[$key+1]["label"] = $val["AreaName"];
        }
        return $areas;
    }

    public function exportAreaCount()
    {
        $type = Session::get("type");
        $type == "PC"?$dstype = 1:$dstype = 2;
        $request = Request::instance()->get();
        $starttime = $request['start_time'];
        $endtime = $request['end_time'];
        $tableDataClassify = $request['tableDataClassify'];
        $tongji = isset($request['tongji'])?$request['tongji']:"ip";
        $tongji == "ip"?$area_type = 1 :$area_type = 2;
        $prop = empty($request['prop'])?"pv":$request['prop'];
        $order = isset($request['order'])?$request['order'] == "descending"?" desc":" asc" :" desc";
        $searchInfo = isset($tableDataClassify)?$tableDataClassify == "省级"?1:2:1;
        if ($searchInfo == 1){
            $query = Db::table("source_area_count sac")->join("sys_area sa","sac.area_id = sa.Id","LEFT")
                ->field("SUM(pv) AS pv,SUM(uv) AS uv,SUM(ip) AS ip,SUM(new_uv) AS newUv,SUM(access_num) AS visitNumber,format(SUM(num_jump)/COUNT(num_jump),2) AS bounceRate,SUM(percent) AS proportion,sa.id_level_0")->where("dstype",$dstype)->where("area_type",$area_type)
                ->whereTime('create_time','between',[$starttime,$endtime])->group("sa.Id_Level_0")->buildSql();
            $data = Db::table($query. " a")->join("sys_area saa","a.id_level_0 = saa.Id","LEFT")
                ->field("a.*,saa.AreaName AS name")->order($prop,$order)->select();
        }else{
            $data = Db::table("source_area_count sac")->join("sys_area sa","sac.area_id = sa.Id","LEFT")
                ->field("SUM(pv) AS pv,SUM(uv) AS uv,SUM(ip) AS ip,SUM(new_uv) AS newUv,SUM(access_num) AS visitNumber,format(SUM(num_jump)/COUNT(num_jump),2) AS bounceRate,SUM(percent) AS proportion,sa.areaname AS name")
                ->where("area_type",$area_type)->where("dstype",$dstype)
                ->whereTime('create_time','between',[$starttime,$endtime])
                ->group("sac.area_id")->order($prop,$order)->select();
        }


        $data_result = [];
        foreach ($data as $key=>$value){
            $data_result[$key] = [
                $value["name"],
                $value["pv"],
                round($value['proportion']*100,2)."%",
                $value["uv"],
                $value["ip"],
                $value["newUv"],
                $value["visitNumber"],
                $value["bounceRate"],
            ];
        }
        $title_name = "<tr><th colspan='8'>访客分析-地区分析</th></tr>
<tr>
<th>城市名称</th>
<th>浏览次数(PV)</th>
<th>占比</th>
<th>独立访客(UV)</th>
<th>IP</th>
<th>新独立访客</th>
<th>访问次数</th>
<th>跳出率</th>
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
        header("Content-Disposition: attachment; filename=地区分析.xls");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        exit( $str );
    }
}