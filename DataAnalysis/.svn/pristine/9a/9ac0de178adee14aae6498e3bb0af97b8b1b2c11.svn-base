<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2017/11/1
 * Time: 11:28
 */
namespace app\index\model;

use app\common\Constant;
use app\common\Utility;
use app\index\controller\Zlog;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Analysis extends Base
{
    protected $network_access;
    protected $operating_system;
    protected $browser_type;

    public static function getArea()
    {
        $areas = [];
        $data = Db::table('sys_area')->where('level',1)->select();
//        $areas[0]["value"] = "0";
//        $areas[0]["label"] = "        全部        ";
        foreach ($data as $key=>$val){
            $areas[$key]["value"] = $val["Id"];
            $areas[$key]["label"] = $val["AreaName"];
        }
        return $areas;
    }

    /**
     * @return array
     */
    public function getList()
    {
        $this->network_access = Constant::$network_access;
        $this->operating_system = Constant::$operating_system;
        $this->browser_type = Constant::$browser_type;
        $return_arr = [];
        $request = Request::instance()->post();
        $show = isset($request["search"]["show"])?trim($request["search"]["show"]):"";
        $query = self::getFilter();
        $result = $this->autoPaginate($query)->toArray();
        if($show == "pv"){
            foreach ($result['data'] as $key=>$value){
                $return_arr[$key]["log_time"] = $value["log_time"];
                $return_arr[$key]["url_from"] = $value["url_from"];
                $return_arr[$key]["web_from"] = $value["web_from"];
                $return_arr[$key]["ip_address"] = $value["ip_address"];
                $return_arr[$key]["ip_area"] = !empty($value["ip_area"])?$value["ip_area"]:"未知";
                $return_arr[$key]["domain_area"] = !empty($value["domain_area"])?$value["domain_area"]:"未知";
                $return_arr[$key]["expandInfo"]["listInfo"]["netAccessProvider"] = $this->network_access[$value["network_access"]];
                $return_arr[$key]["expandInfo"]["listInfo"]["language"] = "汉语";
                $return_arr[$key]["expandInfo"]["listInfo"]["equipmentType"] = $value["dstype"]==2?"移动设备":"电脑设备";
                $return_arr[$key]["expandInfo"]["listInfo"]["operateSystem"] = $this->operating_system[$value["operating_system"]];
                $return_arr[$key]["expandInfo"]["listInfo"]["resolvePower"] = $value["display_size"];
                $return_arr[$key]["expandInfo"]["listInfo"]["browser"] = $this->browser_type[$value["browser_type"]];
            }
            return ["result"=>$return_arr,"total"=>$result['total'],"show"=>"pv"];
        }else{
            foreach ($result['data'] as $sub=>$value){
                $return_arr[$sub]["log_time"] = $value["log_time"];
                $return_arr[$sub]["url_from"] = $value["url_from"];
                $return_arr[$sub]["web_from"] = $value["web_from"];
                $return_arr[$sub]["ip_address"] = $value["ip_address"];
                $return_arr[$sub]["ip_area"] = !empty($value["ip_area"])?$value["ip_area"]:"未知";
                $return_arr[$sub]["domain_area"] = !empty($value["domain_area"])?$value["domain_area"]:"未知";
                $return_arr[$sub]["expandInfo"]["listInfo"]["netAccessProvider"] = $this->network_access[$value["network_access"]];
                $return_arr[$sub]["expandInfo"]["listInfo"]["language"] = "汉语";
                $return_arr[$sub]["expandInfo"]["listInfo"]["equipmentType"] = $value["dstype"]==2?"移动设备":"电脑设备";
                $return_arr[$sub]["expandInfo"]["listInfo"]["operateSystem"] = $this->operating_system[$value["operating_system"]];
                $return_arr[$sub]["expandInfo"]["listInfo"]["resolvePower"] = $value["display_size"];
                $return_arr[$sub]["expandInfo"]["listInfo"]["browser"] = $this->browser_type[$value["browser_type"]];
                $item = explode(',',$value['lwu']);
                $items = [];
                foreach ($item as $key => $val){
                    $v = explode('|',$val);
                    $items[strtotime($v[0])]=$v;
                }
                krsort($items);
                $items=array_values($items);
                $end_time = 0;
                foreach ($items as $key=>$v){
//                    $v = explode('|',$val);
                    $return_arr[$sub]["expandInfo"]["tableInfo"][$key]["accessTrajectory"] = isset($v[1])?$v[1]:'';
                    $return_arr[$sub]["expandInfo"]["tableInfo"][$key]["openTime"] = $v[0];
                    $return_arr[$sub]["expandInfo"]["tableInfo"][$key]["stayTime"] = $end_time == 0?0:$end_time - strtotime($v[0]);
                    $return_arr[$sub]["expandInfo"]["tableInfo"][$key]["pageArea"] = isset($v[2])?$v[2]:'';
                    $end_time = strtotime($v[0]);
                }
                if(count($return_arr[$sub]["expandInfo"]["tableInfo"])>1){
                    krsort($return_arr[$sub]["expandInfo"]["tableInfo"]);
                    $return_arr[$sub]["expandInfo"]["tableInfo"] = array_values($return_arr[$sub]["expandInfo"]["tableInfo"]);
                }
            }
            return ["result"=>$return_arr,"total"=>$result["total"],"show"=>"uv"];
        }
    }

    public function getFilter()
    {
        $t = Session::get("type");
        $dstype = $t == "M"?2:1;
        $request = Request::instance()->post();
        if ($request) {
            $show = isset($request["search"]["show"])?trim($request["search"]["show"]):"pv";
            $date_start = isset($request["search"]["dateTime"][0])?trim($request["search"]["dateTime"][0]):date("Y-m-d 00:00:00",time()-86400);
            $date_end = isset($request["search"]["dateTime"][1])?trim($request["search"]["dateTime"][1]):date("Y-m-d 23:29:29",time()-86400);
//            $start_time = isset($request["search"]["time"]["start"])&&$request["search"]["time"]["start"] !=''?trim($request["search"]["time"]["start"]):"00:00";
//            $end_time = isset($request["search"]["time"]["end"])&&$request["search"]["time"]["end"]!=''?trim($request["search"]["time"]["end"]):"23:59";
            $time = isset($request["search"]["time"])&&$request["search"]["time"]!='全部'?explode(' - ',$request["search"]["time"]):['00:00','23:59'];
            $start_time = $time[0];$end_time=$time[1];
            $area = isset($request["search"]["areaValue"])?$request["search"]["areaValue"]:[];
            $type = isset($request["search"]["areaType"])?trim($request["search"]["areaType"]):"ip";
            $ip   = isset($request["search"]["ip"])?trim($request["search"]["ip"]):"";
            $fromUrl = isset($request["search"]["newFromUrl"])?$request["search"]["newFromUrl"]:"";
            $resUrl  = isset($request["search"]["resUrl"])?trim($request["search"]["resUrl"]):"";
        } else {
            $request = Request::instance()->get();
            $show  = isset($request["show"])?trim($request["show"]):"pv";
            $date_start = isset($request["dateTime"][0])?trim($request["dateTime"][0]):date("Y-m-d 00:00:00",time()-86400);
            $date_end = isset($request["dateTime"][1])?trim($request["dateTime"][1]):date("Y-m-d 23:29:29",time()-86400);
//            $start_time = isset($request["starttime"])&&$request["starttime"] !=''?trim($request["starttime"]):"00:00";
//            $end_time = isset($request["endtime"])&&$request["endtime"]!=''?trim($request["endtime"]):"23:59";
            $time = isset($request["time"])&&$request["time"]!='全部'?explode(' - ',$request["time"]):['00:00','23:59'];
            $start_time = $time[0];$end_time=$time[1];
            $area = isset($request["value"])?$request["value"]:[];
            $type = isset($request["type"])?trim($request["type"]):"ip";
            $ip   = isset($request["ip"])?trim($request["ip"]):"";
            $fromUrl['from'] = isset($request["newFromUrl_from"])?$request["newFromUrl_from"]:"";
            $fromUrl['searchEngine']['input'] = isset($request["newFromUrl_se_input"])?$request["newFromUrl_se_input"]:"";
            $fromUrl['searchEngine']['select'] = isset($request["newFromUrl_se_select"])?$request["newFromUrl_se_select"]:"";
            $fromUrl['zdy']['input'] = isset($request["newFromUrl_zdy_input"])?$request["newFromUrl_zdy_input"]:"";

            $resUrl  = isset($request["resUrl"])?trim($request["resUrl"]):"";
        }
        $query = Db::table("url_statis_log".Utility::getLogSuffix($date_start))
            ->alias('usl')->force('log_time');

        if ($date_start != "" && $date_end != "") {
            if ($start_time != "" && $end_time != "") {
                $date_start_true = date("Y-m-d ".$start_time.":00",strtotime($date_start));
                $date_end_true = date("Y-m-d ".$end_time.":59",strtotime($date_end));
            }else{
                $date_start_true = $date_start;
                $date_end_true = $date_end;
            }
            $query = $query->where('usl.log_time','BETWEEN',[$date_start_true,$date_end_true]);
        }
        $query->where('usl.dstype','=',$dstype);

        if($show == "uv"){
            $sub=$query->group('usl.cookie')->buildSql();
            $in = str_replace('*','MIN(log_id)as log_id',$sub);
            $join = str_replace('*','MIN(log_id)as log_id,GROUP_CONCAT(CONCAT_WS(\' | \',usl.log_time,usl.url_from,usl.web_from))lwu',$sub);
            $query =  Db::table("url_statis_log".Utility::getLogSuffix($date_start))
                ->alias('usl')->force('PRIMARY')
                ->join($join.' as usl2','usl.log_id = usl2.log_id','left')
                ->field('usl2.lwu as lwu')
                ->group('usl.cookie')
                ->where('usl.log_id','EXP','IN'.$in);
        }elseif($show == 'number'){
//            $sub=$query->group('usl.session_id')->buildSql();
//            $in = str_replace('*','MIN(log_id)as log_id',$sub);
//            $join = str_replace('*','MIN(log_id)as log_id,GROUP_CONCAT(CONCAT_WS(\' | \',usl.log_time,usl.url_from,usl.web_from))lwu',$sub);
//            $query =  Db::table("url_statis_log".Utility::getLogSuffix($date_start))->alias('usl')->force('PRIMARY')
//                ->join($join.' AS usl2','usl.log_id = usl2.log_id','left')
//                ->field('usl2.lwu as lwu')
//                ->group('usl.session_id')
//                ->where('usl.log_id','EXP','IN'.$in);

            $sub=$query->select(false);
            $in = str_replace('*','usl.log_id',$sub);
            $in .=' AND usl.session_live = 1 ';
            $join = str_replace('*','usl.session_id,GROUP_CONCAT(CONCAT_WS(\' | \',usl.log_time,usl.url_from,usl.web_from))lwu',$sub);
            $join.= ' GROUP BY usl.session_id ';
            $query =  Db::table("url_statis_log".Utility::getLogSuffix($date_start))->alias('usl')->force('PRIMARY')
                ->join('( '.$join.' ) AS usl2','usl.session_id = usl2.session_id','left')
                ->field('usl2.lwu as lwu')
                ->where('usl.log_id','EXP','IN( '.$in.' )');
        }

        $query->field('sa1.AreaName AS ip_area,sa2.AreaName AS domain_area,usl.*')
            ->join("sys_area sa1","sa1.id = usl.area_id",'LEFT')
            ->join("sys_area sa2","sa2.id = usl.domain_area_id",'LEFT');

        if($type != "" && !empty($area)){
            $num = count($area);
            if ($type == "domain"){
                if ($num == 1) {
                    $query = $query->where("usl.domain_area_id","=",$area[0]);
                }else {
                    $query = $this->createquery($query,"usl.domain_area_id",$area);
                }
            }else{
                if ($num == 1) {
                    $query = $query->where("usl.area_id","=",$area[0]);
                }else {
                    $query = $this->createquery($query,"usl.area_id",$area);
                }
            }
        }
        if($ip != ""){
            $query = $query->where("usl.ip_address","like",'%'.$ip.'%');
        }
        if($fromUrl != ""&&isset($fromUrl['from'])&&$fromUrl['from']!=''){
            switch ($fromUrl['from']){
                case 'from-direct':
                    $query = $query->where("usl.search_engines","=",13);
                    break;
                case 'from-searchEngine':
                    if($fromUrl['searchEngine']['select'] == 'shenma'){
                        $query = $query->where("usl.search_engines","=",8);
                    }elseif($fromUrl['searchEngine']['select'] == 'baidu'){
                        $query = $query->where("usl.search_engines","=",3);
                    }elseif($fromUrl['searchEngine']['select'] == '360'){
                        $query = $query->where("usl.search_engines","=",2);
                    }elseif($fromUrl['searchEngine']['select'] == 'sougou'){
                        $query = $query->where("usl.search_engines","=",4);
                    }elseif($fromUrl['searchEngine']['select'] == 'google'){
                        $query = $query->where("usl.search_engines","=",7);
                    }else{
                        $query = $query->where("usl.search_engines","in",[2,3,4,7,8]);
                    }
                    break;
                case 'from-other':
                        $query = $this->createquery($query,"usl.search_engines",[0,1,5,6,9,10,11,12]);
//                        $query = $query->where("usl.search_engines","=",0);
                    break;
                case 'from-zdy':
                    if(isset($fromUrl['zdy']['input'])&&$fromUrl['zdy']['input'] !=''){
                        $query = $query->where("usl.url_from","like",'%'.$fromUrl['zdy']['input'].'%');
                    }else{
                        $query = $query->where("usl.url_from","=",'通过书签或输入链接直接访问');
                    }
                    break;
            }
            if(isset($fromUrl['searchEngine']['input'])&&$fromUrl['searchEngine']['input'] != ''){
                $query = $query->where("usl.keyworks","=",$fromUrl['searchEngine']['input']);
            }
        }
        if($resUrl != ""){
            $query = $query->where("usl.web_from","like",'%'.$resUrl.'%');
        }
//        Log::error($query->select(false));exit;
        return $query;
    }

    public function importData()
    {
        $query = self::getFilter();
        $request = Request::instance()->get();
        $prop = isset($request['prop'])?$request['prop']:'log_time';
        $order = isset($request['order'])&&$request['order'] == 'descending'?'desc':'asc';
        $current = isset($request['current'])?$request['current']:1;
        $page = isset($request['page'])?$request['page']:50;
        $query->order($prop,$order)->limit(($current - 1)*$page,$page);
//        $show = isset($request["show"])?trim($request["show"]):"";
//        if($show == "uv"){
//            $query->group('usl.cookie')->field('GROUP_CONCAT(CONCAT_WS(\'|\',usl.log_time,usl.url_from,usl.web_from))lwu,
//            sa1.AreaName AS ip_area,sa2.AreaName AS domain_area');
//        }elseif($show == 'number'){
//            $query->group('usl.session_id')->field('GROUP_CONCAT(CONCAT_WS(\'|\',usl.log_time,usl.url_from,usl.web_from))lwu,
//            sa1.AreaName AS ip_area,sa2.AreaName AS domain_area');
//        }
        $result = $query->select();
        $data_result = [];
        foreach ($result as $key=>$value){
            $data_result[] = [
                $value["log_time"],
                $value["ip_address"],
                $value["url_from"],
                $value["domain_area"],
                $value["session_id"],
                $value["long_time"],
                $value["cookie"],
                $value["web_from"],
                $value["domain_from"],
                $value["keyworks"],
                $value["user_agent"],
                $this->browser_type[$value["browser_type"]],
                $value["search_engines"],
                $value["display_size"],
                $value["domain_area"],
                $value["ip_area"],
                $value["dstype"]==2?"移动设备":"电脑设备",
                $value["source_from"],
                $this->operating_system[$value["operating_system"]],
                $this->network_access[$value["network_access"]],
            ];
        }
        $title_name = "<tr><th colspan='11'>流量访问-访问详情</th></tr>
<tr>
<th>访问时间</th>
<th>IP地址</th>
<th>访问页面</th>
<th>访问域名</th>
<th>SessionID</th>
<th>访问时长</th>
<th>Cookie</th>
<th>来源网站</th>
<th>来源域名</th>
<th>搜索关键字</th>
<th>用户代理</th>
<th>访问浏览器</th>
<th>搜索引擎</th>
<th>分辨率</th>
<th>域名地址</th>
<th>ip地址</th>
<th>访问终端</th>
<th>访问来源</th>
<th>操作系统</th>
<th>网络接入商</th>
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
        header("Content-Disposition: attachment; filename=访问详情.xls");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        exit( $str );
    }



}
