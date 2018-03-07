<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/21
 * Time: 11:18
 */
namespace app\index\model;

use think\Db;
use think\Log;
use think\Model;
use think\Request;
use think\Session;
use think\Zlog;

class MtrendAnalysis extends Model
{
    public static function getDatainfo()
    {
        $request = Request::instance()->post();
        $datetime_1["start_time"] = !empty($request["dateTime"][0])?$request["dateTime"][0]:date("Y-m-d");
        $datetime_1["end_time"] = !empty($request["dateTime"][1])?$request["dateTime"][1]:date("Y-m-d");
        $datetime_2["start_time"] = !empty($request["dateTime2"][0])?$request["dateTime2"][0]:date("Y-m-d");
        $datetime_2["end_time"] = !empty($request["dateTime2"][1])?$request["dateTime2"][1]:date("Y-m-d");
        $compare = !empty($request["compare"])?$request["compare"]:false;
        $type = Session::get("type");
        if ($type == "PC"){
            $table = "url_count_pc_detail";
            $table_1 = "url_count_pc";
        }else{
            $table = "url_count_m_detail";
            $table_1 = "url_count_m";
        }
        if($compare === true){
            if ($datetime_1["start_time"] == $datetime_1["end_time"] && $datetime_2["start_time"] == $datetime_2["end_time"]){
                $result = self::oneToOneAnalysis($datetime_1,$datetime_2,$table);$style = 1;
                $data = self::countArrToTotal($datetime_1,$datetime_2);
                $xdata = self::setCoordinateX($datetime_1,$compare);
                $lineChartData = self::setPvAndUv($result,$datetime_1,2);
                $newtableData = self::setListInfo($result,$data,3);
                $tableData = self::analysisTableArr($newtableData);
                return ["flowDataDetail"=>$data['one'],"flowDataDetail2"=>$data['two'],"compare"=>$compare,"style"=>$style,"xdata"=>$xdata,"lineChartData"=>["left"=>$lineChartData['one'],"right"=>$lineChartData['two'],],"tableData"=>$tableData];
            }elseif($datetime_1["start_time"] == $datetime_1["end_time"] && $datetime_2["start_time"] != $datetime_2["end_time"]){
                $result = self::oneToMoreAnalysis($datetime_1,$datetime_2,$table_1);$style = 2;
                $data = self::countArrToTotal($datetime_1,$datetime_2);
                $tableData = self::setListInfo($result,$data,4);
            }elseif($datetime_1["start_time"] != $datetime_1["end_time"] && $datetime_2["start_time"] == $datetime_2["end_time"]){
                $result = self::moreToOneAnalysis($datetime_1,$datetime_2,$table_1);$style = 3;
                $data = self::countArrToTotal($datetime_1,$datetime_2);
                $tableData = self::setListInfo($result,$data,4);
            }else{
                $result = self::moreToMoreAnalysis($datetime_1,$datetime_2,$table_1);$style = 4;
                $data = self::countArrToTotal($datetime_1,$datetime_2);
                $tableData = self::setListInfo($result,$data,4);
            }
            return ["flowDataDetail"=>$data['one'],"flowDataDetail2"=>$data['two'],"compare"=>$compare,"style"=>$style,"tableData"=>$tableData];
        }else{  //不对比
            if ($datetime_1["start_time"] == $datetime_1["end_time"]){  //单天
                $result = self::oneAnalysis($datetime_1,$table);$style = 5;
                $data = self::countArrToTotal($datetime_1,$datetime_2,false);
                $xdata = self::setCoordinateX($datetime_1,$compare);
                $lineChartData = self::setPvAndUv($result,$datetime_1,1);
                $tableData = self::setListInfo($result,$data,1);
            }else{  //多天
                $result = self::moreAnalysis($datetime_1,$table);$style = 6;
                $data = self::countArrToTotal($datetime_1,$datetime_2,false);
                $xdata = self::setCoordinateX($datetime_1,$compare);
                $lineChartData = self::setPvAndUv($result,$datetime_1,1);
                $tableData = self::setListInfo($result,$data,2);
            }
            return ["flowDataDetail"=>$data,"compare"=>$compare,"style"=>$style,"xdata"=>$xdata,"lineChartData"=>$lineChartData,"tableData"=>$tableData];
        }
    }

    public static function oneToOneAnalysis($datetime_1,$datetime_2,$table)
    {
        $result['one'] = Db::table($table)->where("create_time",$datetime_1["start_time"])->select();
        $result['two'] = Db::table($table)->where("create_time",$datetime_2["start_time"])->select();
        return $result;
    }

    public static function oneToMoreAnalysis($datetime_1,$datetime_2,$table)
    {
        $result['one'] = Db::table($table)->where("create_time",$datetime_1["start_time"])->select();
        $result['two'] = Db::table($table)->whereTime("create_time",'between',[$datetime_2["start_time"],$datetime_2["end_time"]])->select();
        return $result;
    }

    public static function moreToOneAnalysis($datetime_1,$datetime_2,$table)
    {
        $result['one'] = Db::table($table)->whereTime('create_time','between',[$datetime_1["start_time"],$datetime_1["end_time"]])->select();
        $result['two'] = Db::table($table)->where("create_time",$datetime_2["start_time"])->select();
        return $result;
    }

    public static function moreToMoreAnalysis($datetime_1,$datetime_2,$table)
    {
        $result['one'] = Db::table($table)->whereTime('create_time','between',[$datetime_1["start_time"],$datetime_1["end_time"]])->select();
        $result['two'] = Db::table($table)->where('create_time','between time',[$datetime_2["start_time"],$datetime_2["end_time"]])->select();
        return $result;
    }

    public static function oneAnalysis($datetime_1,$table)
    {
        return Db::table($table)->where("create_time",$datetime_1["start_time"])->select();
    }

    public static function moreAnalysis($datetime_1,$table)
    {
        return Db::table($table)->whereTime('create_time','between',[$datetime_1["start_time"],$datetime_1["end_time"]])->select();
    }

    public static function countArrToTotal($datetime_1,$datetime_2,$compare = true)
    {
        $type = Session::get("type");
        if ($type == "PC"){
            $table = "url_count_pc";
        }else{
            $table = "url_count_m";
        }
        if ($compare == false){
            $timediff = (strtotime($datetime_1['end_time'])-strtotime($datetime_1['start_time']))/86400;
            if ($datetime_1["start_time"] == $datetime_1["end_time"]) {
                $result = Db::table($table)->where('create_time',$datetime_1["start_time"])->select();
            } else {
                $result = Db::table($table)->whereTime('create_time','between',[$datetime_1["start_time"],$datetime_1["end_time"]])->select();
            }
            $data = self::zharrBycountArrtototal($result,$timediff);
        }else{
            $timediff_one = (strtotime($datetime_1['end_time'])-strtotime($datetime_1['start_time']))/86400;
            $timediff_two = (strtotime($datetime_2['end_time'])-strtotime($datetime_2['start_time']))/86400;
            if ($datetime_1["start_time"] == $datetime_1["end_time"]) {
                $result_one = Db::table($table)->where('create_time',$datetime_1["start_time"])->select();
            } else {
                $result_one = Db::table($table)->whereTime('create_time','between',[$datetime_1["start_time"],$datetime_1["end_time"]])->select();
            }
            if ($datetime_2["start_time"] == $datetime_2["end_time"]) {
                $result_two = Db::table($table)->where('create_time',$datetime_2["start_time"])->select();
            } else {
                $result_two = Db::table($table)->whereTime('create_time','between',[$datetime_2["start_time"],$datetime_2["end_time"]])->select();
            }
            $data = ["one"=>[],"two"=>[]];
            $data['one'] = self::zharrBycountArrtototal($result_one,$timediff_one);
            $data['two'] = self::zharrBycountArrtototal($result_two,$timediff_two);
        }
        return $data;
    }           //无问题

    public static function zharrBycountArrtototal($result,$num)
    {
        $total_arr = [
            ["title"=>"浏览次数(PV)","number"=>0],
            ["title"=>"独立访客(UV)","number"=>0],
            ["title"=>"IP","number"=>0],
            ["title"=>"新独立访客","number"=>0],
            ["title"=>"访问次数","number"=>0],
            ["title"=>"平均访问频度","number"=>0],
            ["title"=>"平均访问时长","number"=>0],
            ["title"=>"平均访问深度","number"=>0],
            ["title"=>"人均浏览页数","number"=>0],
            ["title"=>"跳出率","number"=>0],
        ];
        $total_all = ["pv"=>0,"uv"=>0,"ip"=>0,"new_uv"=>0,"num"=>0,"num_av"=>0,"time_av"=>0,"deep_av"=>0,"user_av"=>0,"num_jump"=>0,"time"=>0,];
        foreach ($result as $key=>$value){
            $total_all["pv"] += $value["pv"];
            $total_all["uv"] += $value["uv"];
            $total_all["ip"] += $value["ip"];
            $total_all["new_uv"] += $value["new_uv"];
            $total_all["num"] += $value["num"];
            $total_all["num_av"] += $value["num_av"];
            $total_all["time_av"] += $value["time_av"];
            $total_all["deep_av"] += $value["deep_av"];
            $total_all["user_av"] += $value["user_av"];
            $total_all["num_jump"]  += $value["num_jump"] ;
            $total_all["time"]  += $value["time"] ;
        }
        $total_arr[0]["number"] = $total_all["pv"];
        $total_arr[1]["number"] = $total_all["uv"];
        $total_arr[2]["number"] = $total_all["ip"];
        $total_arr[3]["number"] = $total_all['new_uv'];
        $total_arr[4]["number"] = $total_all['num'];
        $total_arr[5]["number"] = $total_all["uv"] == 0 ? 0 : round($total_all['num'] / $total_all["uv"],2);
        $total_arr[6]["number"] = $total_all["num"] == 0 ? 0 : round($total_all["time"] / $total_all["num"],2);
        $total_arr[7]["number"] = $total_all['num'] == 0 ? 0 : round($total_all['pv'] / $total_all['num'],2);
        $total_arr[8]["number"] = $total_all["uv"] == 0 ? 0 : round($total_all["pv"] / $total_all["uv"],2);
        $total_arr[9]["number"] = $total_all["uv"] == 0 ? "0(0%)" : (round($total_all["num_jump"] / $total_all["uv"],4)*100)."%";
        return $total_arr;
    }

    public static function setCoordinateX($datetime_1,$compare)
    {
        if ($compare === false){
            if ($datetime_1["start_time"] == $datetime_1["end_time"]){
                return ['00:00', '', '', '', '04:00', '', '', '', '08:00', '', '', '', '12:00', '', '', '',
                    '16:00', '', '', '', '20:00', '', '', ''];
            }else{
                $time_start = strtotime($datetime_1["start_time"]);
                $time_end = strtotime($datetime_1["end_time"]);
                $days = ($time_end - $time_start) / 86400 +1;
                $time = [];
                for ($i=0;$i<$days*24;$i++){
                    if ($i%24 == 0){
                        $day = $i/24;
                        $time[$i] = date("Y-m-d",strtotime("{$datetime_1["start_time"]} +{$day} days"));
                    }elseif ($i%4 == 0 && $i%24 != 0){
                        if ($i >24){
                            if ($i >10){
                                $time[$i] = ($i%24).":00";
                            }else{
                                $time[$i] = "0".($i%24).":00";
                            }
                        }else{
                            if ($i >10){
                                $time[$i] = $i.":00";
                            }else{
                                $time[$i] = "0".$i.":00";
                            }
                        }
                    }else{
                        $time[$i] = " ";
                    }
                }
                return $time;
            }
        }else{
            return ['00:00', '', '', '', '04:00', '', '', '', '08:00', '', '', '', '12:00', '', '', '',
                '16:00', '', '', '', '20:00', '', '', ''];
        }
    }

    public static function setPvAndUv($result,$datetime_1,$rule)
    {
        $pv = $uv = $ip = [];
        $num = count($result);
        if ($num == 0){
            return false;
        }
        $time_start = strtotime($datetime_1["start_time"]);
        $time_end = strtotime($datetime_1["end_time"]);
        $days = ($time_end - $time_start) / 86400 +1;
        for ($i=0;$i<$days*24;$i++){
            $pv[$i] = $uv[$i] = $ip[$i] = "0";
        }
        switch ($rule) {
            case 1: //不对比,单天与多天
                foreach ($result as $key=>$value){
                    for ($j = 0;$j <$days*24;$j++){
                        $time_start = strtotime($datetime_1["start_time"]);
                        $time_end = strtotime($value["create_time"]);
                        $minus = ($time_end - $time_start) / 86400;
                        $hours = $minus *24 +$value["hour"];
                        if ($j == $hours){
                            $pv[$hours] = $pv[$hours]+$value['pv'];
                            $uv[$hours] = $uv[$hours]+$value['uv'];
                            $ip[$hours] = $ip[$hours]+$value['ip'];
                        }
                    }
                }
                $doublearr = ["pv"=>$pv,"uv"=>$uv,"ip"=>$ip];
                break;
            case 2: //对比,单天对单天
                $doublearr = ["one"=>["pv"=>[],"uv"=>[],"ip"=>[],],"two"=>["pv"=>[],"uv"=>[],"ip"=>[],],];
                foreach ($result as $key=>$value){
                    foreach ($value as $n=>$m){
                        for ($j = 0;$j <24;$j++){
                            if ($j == $m["hour"]){
                                $pv[$j] = $m['pv'];
                                $uv[$j] = $m['uv'];
                                $ip[$j] = $m['ip'];
                            }
                        }
                    }
                    $doublearr[$key]["pv"] = $pv;
                    $doublearr[$key]["uv"] = $uv;
                    $doublearr[$key]["ip"] = $ip;
                }
                break;
            default:
                $doublearr = ["pv"=>$pv,"uv"=>$uv,"ip"=>$ip];
                break;
        }
        return $doublearr;
    }

    public static function setListInfo($result,$total,$rule)
    {
        $res = [];
        $num = count($result);
        if ($num == 0) {
            return self::setListInfoarr($total,1);
        }
        switch ($rule) {
            case 1://单天数据分析
                $data = self::setListInfoarr($total,1);
                foreach ($result as $key => $value) {
                    $data[$key + 1]["period"] = $value["hour"] > 9 ? $value['hour'] . ":00-" . $value['hour'] . ":59" : '0' . $value['hour'] . ":00-0" . $value['hour'] . ":59";
                    $data[$key + 1]['pv'] = $total[0]["number"] == 0 ? 0 : $value['pv'] . '(' . (round($value['pv'] / $total[0]["number"], 4) * 100) . '%)';
                    $data[$key + 1]['uv'] = $total[1]["number"] == 0 ? 0 : $value['uv'] . '(' . (round($value['uv'] / $total[1]["number"], 4) * 100) . '%)';
                    $data[$key + 1]['ip'] = $total[2]["number"] == 0 ? 0 : $value['ip'] . '(' . (round($value['ip'] / $total[2]["number"], 4) * 100) . '%)';
                    $data[$key + 1]['newUv'] = $total[3]["number"] == 0 ? 0 : $value['new_uv'] . '(' . (round($value['new_uv'] / $total[3]["number"], 4) * 100) . '%)';
                    $data[$key + 1]['visitNumber'] = $total[4]["number"] == 0 ? 0 : $value['num'] . '(' . (round($value['num'] / $total[4]["number"], 4) * 100) . '%)';
                    $data[$key + 1]['visitFreq'] = $value['num_av'];
                    $data[$key + 1]['visitTime'] = $value['time_av'];
                    $data[$key + 1]['visitDepth'] = $value['deep_av'];
                    $data[$key + 1]['viewPages'] = $value['user_av'];
                    $data[$key + 1]['bounceRate'] = $value['uv'] == 0 ? '0%' : (round($value['num_jump'] / $value['uv'], 4)*100)."%";
                }
                $res = $data;
                break;
            case 2://多天数据分析
                $info = array();
                foreach ($result as $key => $value) {
                    $info[$value['create_time']][] = $value;
                }
                $arrnum = count($info);
                $data = self::setListInfoarr($total,$arrnum);
                $new_info = array_values($info);
                foreach ($new_info as $key => $value) {
                    $count = count($value);
                    foreach ($value as $i => $j) {
                        $data[$key + 1]["period"] = $j["create_time"];
                        $data[$key + 1]["pv"] = isset($data[$key + 1]["pv"]) ? $data[$key + 1]["pv"] + $j["pv"] : $j["pv"];
                        $data[$key + 1]["uv"] = isset($data[$key + 1]["uv"]) ? $data[$key + 1]["uv"] + $j["uv"] : $j["uv"];
                        $data[$key + 1]["ip"] = isset($data[$key + 1]["ip"]) ? $data[$key + 1]["ip"] + $j["ip"] : $j["ip"];
                        $data[$key + 1]["newUv"] = isset($data[$key + 1]["newUv"]) ? $data[$key + 1]["newUv"] + $j["new_uv"] : $j["new_uv"];
                        $data[$key + 1]["visitNumber"] = isset($data[$key + 1]["visitNumber"]) ? $data[$key + 1]["visitNumber"] + $j["num"] : $j["num"];
                        $data[$key + 1]["visitFreq"] = isset($data[$key + 1]["visitFreq"]) ? $data[$key + 1]["visitFreq"] + $j["num_av"] : $j["num_av"];
                        $data[$key + 1]["visitTime"] = isset($data[$key + 1]["visitTime"]) ? $data[$key + 1]["visitTime"] + $j["time_av"] : $j["time_av"];
                        $data[$key + 1]["visitDepth"] = isset($data[$key + 1]["visitDepth"]) ? $data[$key + 1]["visitDepth"] + $j["deep_av"] : $j["deep_av"];
                        $data[$key + 1]["viewPages"] = isset($data[$key + 1]["viewPages"]) ? $data[$key + 1]["viewPages"] + $j["user_av"] : $j["user_av"];
                        $data[$key + 1]["bounceRate"] = isset($data[$key + 1]["bounceRate"]) ? $data[$key + 1]["bounceRate"] + $j["num_jump"] : $j["num_jump"];
                        $data[$key + 1]["time"] = isset($data[$key + 1]["time"]) ? $data[$key + 1]["time"] + $j["time"] : $j["time"];
                    }
                    $data[$key + 1]["pv"] = $data[$key + 1]["pv"] == 0 ? '0(0%)' : $data[$key + 1]["pv"] . '(' . (round($data[$key + 1]["pv"] / $data['0']["pv"], 4) * 100) . "%)";
                    $data[$key + 1]["uv"] = $data[$key + 1]["uv"] == 0 ? '0(0%)' : $data[$key + 1]["uv"] . '(' . (round($data[$key + 1]["uv"] / $data['0']["uv"], 4) * 100) . "%)";
                    $data[$key + 1]["ip"] = $data[$key + 1]["ip"] == 0 ? '0(0%)' : $data[$key + 1]["ip"] . '(' . (round($data[$key + 1]["ip"] / $data['0']["ip"], 4) * 100) . "%)";
                    $data[$key + 1]["visitFreq"] =  $data[$key + 1]["uv"] == 0 ? 0 : round($data[$key + 1]['newUv'] /  $data[$key + 1]['uv'], 2);
                    $data[$key + 1]["newUv"] = $data[$key + 1]["newUv"] == 0 ? '0(0%)' : $data[$key + 1]["newUv"] . '(' . (round($data[$key + 1]["newUv"] / $data[0]["newUv"], 4) * 100) . "%)";
                    $data[$key + 1]["visitNumber"] = $data[$key + 1]["visitNumber"] == 0 ? '0(0%)' : $data[$key + 1]["visitNumber"] . '(' . (round($data[$key + 1]["visitNumber"] / $data[0]["visitNumber"], 4) * 100) . "%)";
                    $data[$key + 1]["visitTime"] = $data[$key + 1]["newUv"] == 0 ? 0 : round($data[$key + 1]['time'] / $data[$key + 1]["newUv"], 2);
                    $data[$key + 1]["visitDepth"] = $data[$key + 1]["newUv"] == 0 ? 0 : round($data[$key + 1]['pv'] / $data[$key + 1]["newUv"], 2);
                    $data[$key + 1]["viewPages"] = $data[$key + 1]["uv"] == 0 ? 0 : round($data[$key + 1]['pv'] / $data[$key + 1]['uv'], 2);
                    $data[$key + 1]["bounceRate"] = $data[$key + 1]["uv"] == 0 ? 0 : (round($data[$key + 1]['bounceRate']  / $data[$key + 1]["uv"], 4) * 100) . "%";
                }
                $res = $data;
                break;
            case 3://单天数据[对比]
                $data = ["total"=>["总计"],"list"=>[]];
                $doublearr = $totalarr = [];
                foreach ($total as $item=>$info){
                    foreach ($info as $key=>$value){
                        $totalarr[$item][] = $value["number"];
                    }
                }
                foreach ($result as $i=>$j){
                    foreach ($j as $key=>$value){
                        $data["list"][$key]["period"] = $value["hour"]>9?$value['hour'].":00-".$value['hour'].":59":'0'.$value['hour'].":00-0".$value['hour'].":59";
                        $data['list'][$key]['pv'] = $total[$i][0]["number"] == 0?"0(0%)":$value['pv'].'('. (round($value['pv'] / $total[$i][0]["number"],4)*100).'%)';
                        $data['list'][$key]['uv'] = $total[$i][1]["number"] == 0?"0(0%)":$value['uv'].'('. (round($value['uv'] / $total[$i][1]["number"],4)*100).'%)';
                        $data['list'][$key]['ip'] = $total[$i][2]["number"] == 0?"0(0%)":$value['ip'].'('. (round($value['ip'] / $total[$i][2]["number"],4)*100).'%)';
                        $data['list'][$key]['newUv'] = $total[$i][3]["number"] == 0?"0(0%)":$value['new_uv'].'('. (round($value['new_uv'] / $total[$i][3]["number"],4)*100).'%)';
                        $data['list'][$key]['visitNumber'] = $total[$i][4]["number"] == 0?"0(0%)":$value['num'].'('. (round($value['num'] / $total[$i][4]["number"],4)*100).'%)';
                        $data['list'][$key]['visitFreq'] = $value['num_av'];
                        $data['list'][$key]['visitTime'] = $value['time_av'];
                        $data['list'][$key]['visitDepth'] = $value['deep_av'];
                        $data['list'][$key]['viewPages'] = $value['user_av'];
                        $data['list'][$key]['bounceRate'] = $value["uv"] == 0 ? 0 : (round($value['num_jump']  / $value["uv"], 4) * 100) . "%";
                    }
                    $doublearr[$i]['list'] = $data['list'];
                    $doublearr[$i]['total'] = $totalarr[$i];
                }
                $res = $doublearr;
                break;
            case 4://多天数据(单对多,多对多,多对多)[对比]
                foreach ($result as $item=>$info){
                    foreach ($info as $key=>$value){
                        $time_now = date("Y-m-d",strtotime($value["create_time"]));
                        $listarr[$item][$time_now] = $value;
                    }
                }
                $one_info = !empty($listarr['one'])?array_values($listarr['one']):[];
                $two_info = !empty($listarr['two'])?array_values($listarr['two']):[];
                $one_count = count($one_info);
                $two_count = count($two_info);
                $data = self::setListInfoarr($total,["one"=>$one_count,"two"=>$two_count],2);
                $one_count >= $two_count?$new_count = $one_count:$new_count = $two_count;
                for ($i=0;$i<$new_count;$i++) {
                    if (empty($one_info[$i])) {
                        $one_info[$i] = ["create_time"=>"-","pv"=>"0","uv"=>"0","ip"=>"0","new_uv"=>"0","num"=>"0","num_av"=>"0","time_av"=>"0","deep_av"=>"0","user_av"=>"0","num_jump"=>"0"];
                    }
                    if (empty($two_info[$i])) {
                        $two_info[$i] = ["create_time"=>"-","pv"=>"0","uv"=>"0","ip"=>"0","new_uv"=>"0","num"=>"0","num_av"=>"0","time_av"=>"0","deep_av"=>"0","user_av"=>"0","num_jump"=>"0"];
                    }
                    if($one_info[$i]['create_time'] != "-") {
                        $one_info[$i]['create_time'] = date("Y-m-d",strtotime($one_info[$i]['create_time']));
                    }
                    if($two_info[$i]['create_time'] != "-") {
                        $two_info[$i]['create_time'] = date("Y-m-d",strtotime($two_info[$i]['create_time']));
                    }
                    $data[$i+1]["period"] = $one_info[$i]['create_time'].' VS '. $two_info[$i]['create_time'];
                    $data[$i+1]["pv"]['one'] = $one_info[$i]['pv'];
                    $data[$i+1]["pv"]['two'] = $two_info[$i]['pv'];
                    $pvdom = $one_info[$i]['pv']-$two_info[$i]['pv'];
                    $data[$i+1]["pv"]['type'] = $pvdom >= 0?$pvdom>0?"up":"equality":"down";
                    $newpvdom = $pvdom>0?'+'.$pvdom:$pvdom;
                    $data[$i+1]["pv"]['change'] = $two_info[$i]['pv'] == 0 ?$newpvdom."(100%)":$newpvdom."(".(round($newpvdom/$two_info[$i]['pv'],4)*100)."%)";

                    $data[$i+1]["uv"]['one'] = $one_info[$i]['uv'];
                    $data[$i+1]["uv"]['two'] = $two_info[$i]['uv'];
                    $uvdom = $one_info[$i]['uv']-$two_info[$i]['uv'];
                    $data[$i+1]["uv"]['type'] = $uvdom >= 0?$uvdom>0?"up":"equality":"down";
                    $newuvdom = $uvdom>0?'+'.$uvdom:$uvdom;
                    $data[$i+1]["uv"]['change'] = $two_info[$i]['uv'] == 0 ?$newuvdom."(100%)":$newuvdom."(".(round($newuvdom/$two_info[$i]['uv'],4)*100)."%)";

                    $data[$i+1]["ip"]['one'] = $one_info[$i]['ip'];
                    $data[$i+1]["ip"]['two'] = $two_info[$i]['ip'];
                    $ipdom = $one_info[$i]['ip']-$two_info[$i]['ip'];
                    $data[$i+1]["ip"]['type'] = $ipdom >= 0?$ipdom>0?"up":"equality":"down";
                    $newipdom = $ipdom>0?'+'.$ipdom:$ipdom;
                    $data[$i+1]["ip"]['change'] = $two_info[$i]['ip'] == 0 ?$newipdom."(100%)":$newipdom."(".(round($newipdom/$two_info[$i]['ip'],4)*100)."%)";

                    $data[$i+1]["newUv"]['one'] = $one_info[$i]['new_uv'];
                    $data[$i+1]["newUv"]['two'] = $two_info[$i]['new_uv'];
                    $newUvdom = $one_info[$i]['new_uv']-$two_info[$i]['new_uv'];
                    $data[$i+1]["newUv"]['type'] = $newUvdom >= 0?$newUvdom>0?"up":"equality":"down";
                    $newnewUvdom = $newUvdom>0?'+'.$newUvdom:$newUvdom;
                    $data[$i+1]["newUv"]['change'] = $two_info[$i]['new_uv'] == 0 ?$newnewUvdom."(100%)":$newnewUvdom."(".(round($newnewUvdom/$two_info[$i]['new_uv'],4)*100)."%)";


                    $data[$i+1]["visitNumber"]['one'] = $one_info[$i]['num'];
                    $data[$i+1]["visitNumber"]['two'] = $two_info[$i]['num'];
                    $visitNumberdom = $one_info[$i]['num']-$two_info[$i]['num'];
                    $data[$i+1]["visitNumber"]['type'] = $visitNumberdom >= 0?$visitNumberdom>0?"up":"equality":"down";
                    $newvisitNumberdom = $visitNumberdom>0?'+'.$visitNumberdom:$visitNumberdom;
                    $data[$i+1]["visitNumber"]['change'] = $two_info[$i]['num'] == 0 ?$newvisitNumberdom."(100%)":$newvisitNumberdom."(".(round($newvisitNumberdom/$two_info[$i]['num'],4)*100)."%)";


                    $data[$i+1]["visitFreq"]['one'] = $one_info[$i]['num_av'];
                    $data[$i+1]["visitFreq"]['two'] = $two_info[$i]['num_av'];
                    $visitFreqdom = $one_info[$i]['num_av']-$two_info[$i]['num_av'];
                    $data[$i+1]["visitFreq"]['type'] = $visitFreqdom >= 0?$visitFreqdom>0?"up":"equality":"down";
                    $newvisitFreqdom = $visitFreqdom>0?'+'.$visitFreqdom:$visitFreqdom;
                    $data[$i+1]["visitFreq"]['change'] = $two_info[$i]['num_av'] == 0 ?$newvisitFreqdom."(100%)":$newvisitFreqdom."(".(round($newvisitFreqdom/$two_info[$i]['num_av'],4)*100)."%)";


                    $data[$i+1]["visitTime"]['one'] = $one_info[$i]['time_av'];
                    $data[$i+1]["visitTime"]['two'] = $two_info[$i]['time_av'];
                    $visitTimedom = $one_info[$i]['time_av']-$two_info[$i]['time_av'];
                    $data[$i+1]["visitTime"]['type'] = $visitTimedom >= 0?$visitTimedom>0?"up":"equality":"down";
                    $newvisitFreqdom = $visitTimedom>0?'+'.$visitTimedom:$visitTimedom;
                    $data[$i+1]["visitTime"]['change'] = $two_info[$i]['time_av'] == 0 ?$newvisitFreqdom."(100%)":$newvisitFreqdom."(".(round($newvisitFreqdom/$two_info[$i]['time_av'],4)*100)."%)";


                    $data[$i+1]["visitDepth"]['one'] = $one_info[$i]['deep_av'];
                    $data[$i+1]["visitDepth"]['two'] = $two_info[$i]['deep_av'];
                    $visitDepthdom = $one_info[$i]['deep_av']-$two_info[$i]['deep_av'];
                    $data[$i+1]["visitDepth"]['type'] = $visitDepthdom >= 0?$visitDepthdom>0?"up":"equality":"down";
                    $newvisitDepthdom = $visitDepthdom>0?'+'.$visitDepthdom:$visitDepthdom;
                    $data[$i+1]["visitDepth"]['change'] = $two_info[$i]['deep_av'] == 0 ?$newvisitDepthdom."(100%)":$newvisitDepthdom."(".(round($newvisitDepthdom/$two_info[$i]['deep_av'],4)*100)."%)";


                    $data[$i+1]["viewPages"]['one'] = $one_info[$i]['user_av'];
                    $data[$i+1]["viewPages"]['two'] = $two_info[$i]['user_av'];
                    $viewPagesdom = $one_info[$i]['user_av']-$two_info[$i]['user_av'];
                    $data[$i+1]["viewPages"]['type'] = $viewPagesdom >= 0?$viewPagesdom>0?"up":"equality":"down";
                    $newvisitDepthdom = $visitDepthdom>0?'+'.$visitDepthdom:$visitDepthdom;
                    $data[$i+1]["viewPages"]['change'] = $two_info[$i]['user_av'] == 0 ?$newvisitDepthdom."(100%)":$newvisitDepthdom."(".(round($newvisitDepthdom/$two_info[$i]['user_av'],4)*100)."%)";


                    $data[$i+1]["bounceRate"]['one'] = $one_info[$i]['uv'] == 0 ? "0(0%)":(round($one_info[$i]['num_jump']/$one_info[$i]['uv'],4)*100)."%";
                    $data[$i+1]["bounceRate"]['two'] = $two_info[$i]['uv'] == 0 ? "0(0%)":(round($two_info[$i]['num_jump']/$two_info[$i]['uv'],4)*100)."%";
                    $bounceRatedom = round($data[$i+1]["bounceRate"]['one']-$data[$i+1]["bounceRate"]['two'],2);
                    $data[$i+1]["bounceRate"]['type'] = $bounceRatedom >= 0?$bounceRatedom>0?"up":"equality":"down";
                    $newbounceRatedom = $bounceRatedom>0?'+'.$bounceRatedom:$bounceRatedom;
                    if ($two_info[$i]['num_jump'] == 0) {
                        $data[$i+1]["bounceRate"]['change'] = (round($one_info[$i]['num_jump']/$one_info[$i]['uv'],4)*100)."%(100%)";
                    } elseif ($one_info[$i]['num_jump'] == 0) {
                        $data[$i+1]["bounceRate"]['change'] = (round($two_info[$i]['num_jump']/$two_info[$i]['uv'],4)*100)."%(100%)";
                    } else {
                        $data[$i+1]["bounceRate"]['change'] = round($newbounceRatedom/100,2)."%(".(round($newbounceRatedom/$two_info[$i]['num_jump'],4)*100)."%)";
                    }
                }
                $res = $data;
                break;
        }
        return $res;
    }

    public static function analysisTableArr($result)
    {
        $tableone = $result['one'];
        $tabletwo = $result['two'];
        $newtable = [0=>["period"=>"总计"]];
        $listnum  = count($tableone['list']);
        $totalnum = count($tableone['total']);
        $tableone['total_new'][0]['pv'] = $tableone['total'][0];
        $tabletwo['total_new'][0]['pv'] = $tabletwo['total'][0];
        $tableone['total_new'][0]['uv'] = $tableone['total'][1];
        $tabletwo['total_new'][0]['uv'] = $tabletwo['total'][1];
        $tableone['total_new'][0]['ip'] = $tableone['total'][2];
        $tabletwo['total_new'][0]['ip'] = $tabletwo['total'][2];
        $tableone['total_new'][0]['newUv'] = $tableone['total'][3];
        $tabletwo['total_new'][0]['newUv'] = $tabletwo['total'][3];
        $tableone['total_new'][0]['visitNumber'] = $tableone['total'][4];
        $tabletwo['total_new'][0]['visitNumber'] = $tabletwo['total'][4];
        $tableone['total_new'][0]['visitFreq'] = $tableone['total'][5];
        $tabletwo['total_new'][0]['visitFreq'] = $tabletwo['total'][5];
        $tableone['total_new'][0]['visitTime'] = $tableone['total'][6];
        $tabletwo['total_new'][0]['visitTime'] = $tabletwo['total'][6];
        $tableone['total_new'][0]['visitDepth'] = $tableone['total'][7];
        $tabletwo['total_new'][0]['visitDepth'] = $tabletwo['total'][7];
        $tableone['total_new'][0]['viewPages'] = $tableone['total'][8];
        $tabletwo['total_new'][0]['viewPages'] = $tabletwo['total'][8];
        $tableone['total_new'][0]['bounceRate'] = $tableone['total'][9];
        $tabletwo['total_new'][0]['bounceRate'] = $tabletwo['total'][9];
        unset($tableone['total']);unset($tabletwo['total']);
        foreach ($tableone['list'] as $key=>$value){
            if ($tableone['list'][$key]['pv']){
                $tableone['list'][$key]['pv'] = strstr($tableone['list'][$key]['pv'], '(', TRUE);
            }
            if ($tableone['list'][$key]['uv']){
                $tableone['list'][$key]['uv'] = strstr($tableone['list'][$key]['uv'], '(', TRUE);
            }
            if ($tableone['list'][$key]['ip']){
                $tableone['list'][$key]['ip'] = strstr($tableone['list'][$key]['ip'], '(', TRUE);
            }
            if ($tableone['list'][$key]['newUv']){
                $tableone['list'][$key]['newUv'] = strstr($tableone['list'][$key]['newUv'], '(', TRUE);
            }
            if ($tableone['list'][$key]['visitNumber']){
                $tableone['list'][$key]['visitNumber'] = strstr($tableone['list'][$key]['visitNumber'], '(', TRUE);
            }
        }
        foreach ($tabletwo['list'] as $key=>$value){
            if ($tabletwo['list'][$key]['pv']){
                $tabletwo['list'][$key]['pv'] = strstr($tabletwo['list'][$key]['pv'], '(', TRUE);
            }
            if ($tabletwo['list'][$key]['uv']){
                $tabletwo['list'][$key]['uv'] = strstr($tabletwo['list'][$key]['uv'], '(', TRUE);
            }
            if ($tabletwo['list'][$key]['ip']){
                $tabletwo['list'][$key]['ip'] = strstr($tabletwo['list'][$key]['ip'], '(', TRUE);
            }
            if ($tabletwo['list'][$key]['newUv']){
                $tabletwo['list'][$key]['newUv'] = strstr($tabletwo['list'][$key]['newUv'], '(', TRUE);
            }
            if ($tabletwo['list'][$key]['visitNumber']){
                $tabletwo['list'][$key]['visitNumber'] = strstr($tabletwo['list'][$key]['visitNumber'], '(', TRUE);
            }
        }
        for ($j=0;$j<=$listnum;$j++){
            if ($j != 0){
                $dataarrone = $tableone['list'];
                $dataarrtwo = $tabletwo['list'];
                $newtable[$j]['period'] = $dataarrone[$j-1]['period'];
                $k = $j;
                $newtable[$j]['bounceRate']["one"] = $dataarrone[$k-1]['uv'] == 0?"0(0%)":(round($dataarrone[$k-1]['bounceRate']/$dataarrone[$k-1]['uv'],4)*100)."%";
                $newtable[$j]['bounceRate']["two"] = $dataarrone[$k-1]['uv'] == 0?"0(0%)":(round($dataarrtwo[$k-1]['bounceRate']/$dataarrone[$k-1]['uv'],4)*100)."%";

                $bounceRatedom = $newtable[$j]['bounceRate']["one"]-$newtable[$j]['bounceRate']["two"];
                $newbounceRatedom = $bounceRatedom>0?'+'.$bounceRatedom:$bounceRatedom;
                $newtable[$j]['bounceRate']["change"] = $newtable['0']['bounceRate']['two'] == 0 ?"100(100%)":$newbounceRatedom."%(".(round($newbounceRatedom/$newtable['0']['bounceRate']['two'],2))."%)";

            } else {
                $dataarrone = $tableone['total_new'];
                $dataarrtwo = $tabletwo['total_new'];
                $k = $j+1;
                $newtable[$j]['bounceRate']["one"] = $dataarrone[$k-1]['bounceRate'];
                $newtable[$j]['bounceRate']["two"] = $dataarrtwo[$k-1]['bounceRate'];

                $bounceRatedom = round($dataarrone[$k-1]['bounceRate']-$dataarrtwo[$k-1]['bounceRate'],2);
                $newbounceRatedom = $bounceRatedom>0?'+'.$bounceRatedom:$bounceRatedom;
                $newtable[$j]['bounceRate']["change"] = $newtable['0']['bounceRate']['two'] == 0 ?"100(100%)":$newbounceRatedom."%(".(round($newbounceRatedom/$newtable['0']['bounceRate']['two'],4)*100)."%)";

            }
            $newtable[$j]['pv']["one"] = $dataarrone[$k-1]['pv'];
            $newtable[$j]['pv']["two"] = $dataarrtwo[$k-1]['pv'];
            $pvdom = $dataarrone[$k-1]['pv']-$dataarrtwo[$k-1]['pv'];
            $newpvdom = $pvdom>0?'+'.$pvdom:$pvdom;
            $newtable[$j]['pv']["change"] = $newtable['0']['pv']['two'] == 0 ?"100(100%)":$newpvdom."(".(round($newpvdom/$newtable['0']['pv']['two'],4)*100)."%)";
            $newtable[$j]['pv']['type'] = $pvdom >= 0?$pvdom>0?"up":"equality":"down";

            $newtable[$j]['uv']["one"] = $dataarrone[$k-1]['uv'];
            $newtable[$j]['uv']["two"] = $dataarrtwo[$k-1]['uv'];
            $uvdom = $dataarrone[$k-1]['uv']-$dataarrtwo[$k-1]['uv'];
            $newuvdom = $uvdom>0?'+'.$uvdom:$uvdom;
            $newtable[$j]['uv']["change"] = $newtable['0']['uv']['two'] == 0 ?"100(100%)":$newuvdom."(".(round($newuvdom/$newtable['0']['uv']['two'],4)*100)."%)";
            $newtable[$j]['uv']['type'] = $uvdom >= 0?$uvdom>0?"up":"equality":"down";

            $newtable[$j]['ip']["one"] = $dataarrone[$k-1]['ip'];
            $newtable[$j]['ip']["two"] = $dataarrtwo[$k-1]['ip'];
            $ipdom = $dataarrone[$k-1]['ip']-$dataarrtwo[$k-1]['ip'];
            $newipdom = $ipdom>0?'+'.$ipdom:$ipdom;
            $newtable[$j]['ip']["change"] = $newtable['0']['ip']['two'] == 0 ?"100(100%)":$newipdom."(".(round($newipdom/$newtable['0']['ip']['two'],4)*100)."%)";
            $newtable[$j]['ip']['type'] = $ipdom >= 0?$ipdom>0?"up":"equality":"down";

            $newtable[$j]['newUv']["one"] = $dataarrone[$k-1]['newUv'];
            $newtable[$j]['newUv']["two"] = $dataarrtwo[$k-1]['newUv'];
            $newUvdom = $dataarrone[$k-1]['newUv']-$dataarrtwo[$k-1]['newUv'];
            $newnewUvdom = $newUvdom>0?'+'.$newUvdom:$newUvdom;
            $newtable[$j]['newUv']["change"] = $newtable['0']['newUv']['two'] == 0 ?"100(100%)":$newnewUvdom."(".(round($newnewUvdom/$newtable['0']['newUv']['two'],4)*100)."%)";
            $newtable[$j]['newUv']['type'] = $newUvdom >= 0?$newUvdom>0?"up":"equality":"down";


            $newtable[$j]['visitNumber']["one"] = $dataarrone[$k-1]['visitNumber'];
            $newtable[$j]['visitNumber']["two"] = $dataarrtwo[$k-1]['visitNumber'];
            $visitNumberdom = $dataarrone[$k-1]['visitNumber']-$dataarrtwo[$k-1]['visitNumber'];
            $newvisitNumberdom = $visitNumberdom>0?'+'.$visitNumberdom:$visitNumberdom;
            $newtable[$j]['visitNumber']["change"] = $newtable['0']['visitNumber']['two'] == 0 ?"100(100%)":$newvisitNumberdom."(".(round($newvisitNumberdom/$newtable['0']['visitNumber']['two'],4)*100)."%)";
            $newtable[$j]['visitNumber']['type'] = $visitNumberdom >= 0?$visitNumberdom>0?"up":"equality":"down";

            $newtable[$j]['visitFreq']["one"] = $dataarrone[$k-1]['visitFreq'];
            $newtable[$j]['visitFreq']["two"] = $dataarrtwo[$k-1]['visitFreq'];
            $visitFreqdom = $dataarrone[$k-1]['visitFreq']-$dataarrtwo[$k-1]['visitFreq'];
            $newvisitFreqdom = $visitFreqdom>0?'+'.$visitFreqdom:$visitFreqdom;
            $newtable[$j]['visitFreq']["change"] = $newtable['0']['visitFreq']['two'] == 0 ?"100(100%)":$newvisitFreqdom."(".(round($newvisitFreqdom/$newtable['0']['visitFreq']['two'],4)*100)."%)";
            $newtable[$j]['visitFreq']['type'] = $visitFreqdom >= 0?$visitFreqdom>0?"up":"equality":"down";

            $newtable[$j]['visitTime']["one"] = $dataarrone[$k-1]['visitTime'];
            $newtable[$j]['visitTime']["two"] = $dataarrtwo[$k-1]['visitTime'];
            $visitTimedom = $dataarrone[$k-1]['visitTime']-$dataarrtwo[$k-1]['visitTime'];
            $newvisitTimedom = $visitTimedom>0?'+'.$visitTimedom:$visitTimedom;
            $newtable[$j]['visitTime']["change"] = $newtable['0']['visitTime']['two'] == 0 ?"100(100%)":$newvisitTimedom."(".(round($newvisitTimedom/$newtable['0']['visitTime']['two'],4)*100)."%)";
            $newtable[$j]['visitTime']['type'] = $visitTimedom >= 0?$visitTimedom>0?"up":"equality":"down";

            $newtable[$j]['visitDepth']["one"] = $dataarrone[$k-1]['visitDepth'];
            $newtable[$j]['visitDepth']["two"] = $dataarrtwo[$k-1]['visitDepth'];
            $visitDepthdom = $dataarrone[$k-1]['visitDepth']-$dataarrtwo[$k-1]['visitDepth'];
            $newvisitDepthdom = $visitDepthdom>0?'+'.$visitDepthdom:$visitDepthdom;
            $newtable[$j]['visitDepth']["change"] = $newtable['0']['visitDepth']['two'] == 0 ?"100(100%)":$newvisitDepthdom."(".(round($newvisitDepthdom/$newtable['0']['visitDepth']['two'],4)*100)."%)";
            $newtable[$j]['visitDepth']['type'] = $visitDepthdom >= 0?$visitDepthdom>0?"up":"equality":"down";

            $newtable[$j]['viewPages']["one"] = $dataarrone[$k-1]['viewPages'];
            $newtable[$j]['viewPages']["two"] = $dataarrtwo[$k-1]['viewPages'];
            $viewPagesdom = $dataarrone[$k-1]['viewPages']-$dataarrtwo[$k-1]['viewPages'];
            $newviewPagesdom = $viewPagesdom>0?'+'.$viewPagesdom:$viewPagesdom;
            $newtable[$j]['viewPages']["change"] = $newtable['0']['viewPages']['two'] == 0 ?"100(100%)":$newviewPagesdom."(".(round($newviewPagesdom/$newtable['0']['viewPages']['two'],4)*100)."%)";
            $newtable[$j]['viewPages']['type'] = $viewPagesdom >= 0?$viewPagesdom>0?"up":"equality":"down";


            $newtable[$j]['bounceRate']['type'] = $bounceRatedom >= 0?$bounceRatedom>0?"up":"equality":"down";
        }
        return $newtable;
    }

    public static function setListInfoarr($total,$num,$rule = 1)
    {
        $data = [0=>["period"=>"总计"]];
        if ($rule == 1) {
            $data[0]["pv"] = $total[0]["number"];
            $data[0]["uv"] = $total[1]["number"];
            $data[0]["ip"] = $total[2]["number"];
            $data[0]["newUv"] = $total[3]["number"];
            $data[0]["visitNumber"] = $total[4]["number"];
            $data[0]["visitFreq"] = $total[5]["number"];
            $data[0]["visitTime"] = $total[6]["number"];
            $data[0]["visitDepth"] = $total[7]["number"];
            $data[0]["viewPages"] = $total[8]["number"];
            $data[0]["bounceRate"] = $total[9]["number"];
        } else {
            foreach ($total as $key=>$value) {
                $data[0]["pv"][$key] = $value[0]["number"];
                $data[0]["uv"][$key] = $value[1]["number"];
                $data[0]["ip"][$key] = $value[2]["number"];
                $data[0]["newUv"][$key] = $value[3]["number"];
                $data[0]["visitNumber"][$key] = $value[4]["number"];
                $data[0]["visitFreq"][$key] = $value[5]["number"];
                $data[0]["visitTime"][$key] = $value[6]["number"];
                $data[0]["visitDepth"][$key] = $value[7]["number"];
                $data[0]["viewPages"][$key] = $value[8]["number"];
                $data[0]["bounceRate"][$key] = $value[9]["number"];
            }
            $pvdom = $data[0]["pv"]['one']-$data[0]["pv"]['two'];
            $data[0]["pv"]['type'] = $pvdom >= 0?$pvdom>0?"up":"equality":"down";
            $newpvdom = $pvdom>0?'+'.$pvdom:$pvdom;
            $data[0]['pv']["change"] = $data[0]['pv']['two'] == 0 ?"100(100%)":$newpvdom."(".(round($newpvdom/$data['0']['pv']['two'],4)*100)."%)";

            $uvdom = $data[0]["uv"]['one']-$data[0]["uv"]['two'];
            $data[0]["uv"]['type'] = $uvdom >= 0?$uvdom>0?"up":"equality":"down";
            $newuvdom = $uvdom>0?'+'.$uvdom:$uvdom;
            $data[0]['uv']["change"] = $data[0]['uv']['two'] == 0 ?"100(100%)":$newuvdom."(".(round($newuvdom/$data['0']['uv']['two'],4)*100)."%)";

            $ipdom = $data[0]["ip"]['one']-$data[0]["ip"]['two'];
            $data[0]["ip"]['type'] = $ipdom >= 0?$ipdom>0?"up":"equality":"down";
            $newipdom = $ipdom>0?'+'.$ipdom:$ipdom;
            $data[0]['ip']["change"] = $data[0]['ip']['two'] == 0 ?"100(100%)":$newipdom."(".(round($newipdom/$data['0']['ip']['two'],4)*100)."%)";

            $newUvdom = $data[0]["newUv"]['one']-$data[0]["newUv"]['two'];
            $data[0]["newUv"]['type'] = $newUvdom >= 0?$newUvdom>0?"up":"equality":"down";
            $newnewUvdom = $newUvdom>0?'+'.$newUvdom:$newUvdom;
            $data[0]['newUv']["change"] = $data[0]['newUv']['two'] == 0 ?"100(100%)":$newnewUvdom."(".(round($newnewUvdom/$data['0']['newUv']['two'],4)*100)."%)";

            $visitNumberdom = $data[0]["visitNumber"]['one']-$data[0]["visitNumber"]['two'];
            $data[0]["visitNumber"]['type'] = $visitNumberdom >= 0?$visitNumberdom>0?"up":"equality":"down";
            $newvisitNumberdom = $visitNumberdom>0?'+'.$visitNumberdom:$visitNumberdom;
            $data[0]['visitNumber']["change"] = $data[0]['visitNumber']['two'] == 0 ?"100(100%)":$newvisitNumberdom."(".(round($newvisitNumberdom/$data['0']['pv']['two'],4)*100)."%)";

            $visitFreqdom = $data[0]["visitFreq"]['one']-$data[0]["visitFreq"]['two'];
            $data[0]["visitFreq"]['type'] = $visitFreqdom >= 0?$visitFreqdom>0?"up":"equality":"down";
            $newvisitFreqdom = $visitFreqdom>0?'+'.$visitFreqdom:$visitFreqdom;
            $data[0]['visitFreq']["change"] = $data[0]['visitFreq']['two'] == 0 ?"100(100%)":$newvisitFreqdom."(".(round($newvisitFreqdom/$data['0']['visitFreq']['two'],4)*100)."%)";

            $visitTimedom = $data[0]["visitTime"]['one']-$data[0]["visitTime"]['two'];
            $data[0]["visitTime"]['type'] = $visitTimedom >= 0?$visitTimedom>0?"up":"equality":"down";
            $newvisitTimedom = $visitTimedom>0?'+'.$visitTimedom:$visitTimedom;
            $data[0]['visitTime']["change"] = $data[0]['visitTime']['two'] == 0 ?"100(100%)":$newvisitTimedom."(".(round($newvisitTimedom/$data['0']['visitTime']['two'],4)*100)."%)";

            $visitDepthdom = $data[0]["visitDepth"]['one']-$data[0]["visitDepth"]['two'];
            $data[0]["visitDepth"]['type'] = $visitDepthdom >= 0?$visitDepthdom>0?"up":"equality":"down";
            $newvisitDepthdom = $visitDepthdom>0?'+'.$visitDepthdom:$visitDepthdom;
            $data[0]['visitDepth']["change"] = $data[0]['visitDepth']['two'] == 0 ?"100(100%)":$newvisitDepthdom."(".(round($newvisitDepthdom/$data['0']['visitDepth']['two'],4)*100)."%)";

            $viewPagesdom = $data[0]["viewPages"]['one']-$data[0]["viewPages"]['two'];
            $data[0]["viewPages"]['type'] = $viewPagesdom >= 0?$viewPagesdom>0?"up":"equality":"down";
            $newviewPagesdom = $viewPagesdom>0?'+'.$viewPagesdom:$viewPagesdom;
            $data[0]['viewPages']["change"] = $data[0]['viewPages']['two'] == 0 ?"100(100%)":$newviewPagesdom."(".(round($newviewPagesdom/$data['0']['viewPages']['two'],4)*100)."%)";

            $bounceRatedom = $data[0]["bounceRate"]['one']-$data[0]["bounceRate"]['two'];
            $data[0]["bounceRate"]['type'] = $bounceRatedom >= 0?$bounceRatedom>0?"up":"equality":"down";
            $newbounceRatedom = $bounceRatedom>0?'+'.$bounceRatedom:$bounceRatedom;
            $data[0]['bounceRate']["change"] = $data[0]['bounceRate']['two'] == 0 ?"100(100%)":$newbounceRatedom."(".(round($newbounceRatedom/$data['0']['bounceRate']['two'],4)*100)."%)";
        }
        return $data;
    }

    public function import()
    {
        $request = Request::instance()->get();
        $datetime_1["start_time"] = !empty($request["starttime"])?$request["starttime"]:date("Y-m-d");
        $datetime_1["end_time"] = !empty($request["endtime"])?$request["endtime"]:date("Y-m-d");
        $datetime_2["start_time"] = !empty($request["starttime2"])?$request["starttime2"]:date("Y-m-d");
        $datetime_2["end_time"] = !empty($request["endtime2"])?$request["endtime2"]:date("Y-m-d");
        $compare = !empty($request["compare"])?$request["compare"]:false;
        $type = Session::get("type");
        if ($type == "PC"){
            $table = "url_count_pc_detail";
            $table_1 = "url_count_pc";
        }else{
            $table = "url_count_m_detail";
            $table_1 = "url_count_m";
        }
        if($compare == true){
            if ($datetime_1["start_time"] == $datetime_1["end_time"] && $datetime_2["start_time"] == $datetime_2["end_time"]){
                $result = self::oneToOneAnalysis($datetime_1,$datetime_2,$table);$style = 1;
                $data = self::countArrToTotal($datetime_1,$datetime_2);
                $newtableData = self::setListInfo($result,$data,3);
                $tableData = self::analysisTableArr($newtableData);
            }elseif($datetime_1["start_time"] == $datetime_1["end_time"] && $datetime_2["start_time"] != $datetime_2["end_time"]){
                $result = self::oneToMoreAnalysis($datetime_1,$datetime_2,$table_1);$style = 2;
                $data = self::countArrToTotal($datetime_1,$datetime_2);
                $tableData = self::setListInfo($result,$data,4);
            }elseif($datetime_1["start_time"] != $datetime_1["end_time"] && $datetime_2["start_time"] == $datetime_2["end_time"]){
                $result = self::moreToOneAnalysis($datetime_1,$datetime_2,$table_1);$style = 3;
                $data = self::countArrToTotal($datetime_1,$datetime_2);
                $tableData = self::setListInfo($result,$data,4);
            }else{
                $result = self::moreToMoreAnalysis($datetime_1,$datetime_2,$table_1);$style = 4;
                $data = self::countArrToTotal($datetime_1,$datetime_2);
                $tableData = self::setListInfo($result,$data,4);
            }
        }else{  //不对比
            if ($datetime_1["start_time"] == $datetime_1["end_time"]){  //单天
                $result = self::oneAnalysis($datetime_1,$table);$style = 5;
                $data = self::countArrToTotal($datetime_1,$datetime_2,false);
                $tableData = self::setListInfo($result,$data,1);
            }else{  //多天
                $result = self::moreAnalysis($datetime_1,$table);$style = 6;
                $data = self::countArrToTotal($datetime_1,$datetime_2,false);
                $tableData = self::setListInfo($result,$data,2);
            }
        }
        $result = $tableData;
        $data_result = [];
        foreach ($result as $key=>$value){
            if ($compare == true){
                $data_result[$key] = [
                    $value["period"],
                    $value["pv"]['one'],
                    $value["pv"]['two'],
                    $value["pv"]['change'],
                    $value["uv"]['one'],
                    $value["uv"]['two'],
                    $value["ip"]['one'],
                    $value["ip"]['two'],
                    $value["newUv"]['one'],
                    $value["newUv"]['two'],
                    $value["visitNumber"]['one'],
                    $value["visitNumber"]['two'],
                    $value["visitFreq"]['one'],
                    $value["visitFreq"]['two'],
                    $value["visitTime"]['one'],
                    $value["visitTime"]['two'],
                    $value["visitDepth"]['one'],
                    $value["visitDepth"]['two'],
                    $value["viewPages"]['one'],
                    $value["viewPages"]['two'],
                    $value["bounceRate"]['one'],
                    $value["bounceRate"]['two'],
                ];

            } else {
                $data_result[$key] = [
                    $value["period"],
                    $value["pv"],
                    $value["uv"],
                    $value["ip"],
                    $value["newUv"],
                    $value["visitNumber"],
                    $value["visitFreq"],
                    $value["visitTime"],
                    $value["visitDepth"],
                    $value["viewPages"],
                    $value["bounceRate"],
                ];
            }
        }
        if ($compare == true) {
            $title_name = "<tr><th colspan='22'>流量分析-趋势分析</th></tr>
<tr>
<th rowspan='2'>时段</th>
<th colspan='3'>浏览次数(PV)</th>
<th colspan='2'>独立访客</th>
<th colspan='2'>IP</th>
<th colspan='2'>新独立访客</th>
<th colspan='2'>访问次数</th>
<th colspan='2'>平均访问频度</th>
<th colspan='2'>平均访问时长</th>
<th colspan='2'>平均访问深度</th>
<th colspan='2'>人均浏览页数</th>
<th colspan='2'>跳出率</th>
</tr>
<tr>
<th></th>
<th>时段一</th>
<th>时段二</th>
<th>变化情况</th>
<th>时段一</th>
<th>时段二</th>
<th>时段一</th>
<th>时段二</th>
<th>时段一</th>
<th>时段二</th>
<th>时段一</th>
<th>时段二</th>
<th>时段一</th>
<th>时段二</th>
<th>时段一</th>
<th>时段二</th>
<th>时段一</th>
<th>时段二</th>
<th>时段一</th>
<th>时段二</th>
<th>时段一</th>
</tr>";
        } else {
            $title_name = "<tr><th colspan='11'>流量分析-趋势分析</th></tr>
<tr>
<th>时段</th>
<th>浏览次数</th>
<th>独立访客</th>
<th>IP</th>
<th>新独立访客</th>
<th>访问次数</th>
<th>平均访问频度</th>
<th>平均访问时长</th>
<th>平均访问深度</th>
<th>人均浏览页数</th>
<th>跳出率</th>
</tr>";
        }
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
        header("Content-Disposition: attachment; filename=趋势分析.xls");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        exit( $str );
    }
}