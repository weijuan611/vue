<?php
namespace app\index\model;

use think\Db;
use think\Log;
use think\Request;
use think\Session;

class Moriginal extends Base
{
    public function getlist()
    {
        $request = Request::instance()->post();
        $datetime_1["start_time"] = !empty($request["dateTime"][0])?$request["dateTime"][0]:date("Y-m-d");
        $datetime_1["end_time"] = !empty($request["dateTime"][1])?$request["dateTime"][1]:date("Y-m-d");
        $datetime_2["start_time"] = !empty($request["dateTime2"][0])?$request["dateTime2"][0]:date("Y-m-d");
        $datetime_2["end_time"] = !empty($request["dateTime2"][1])?$request["dateTime2"][1]:date("Y-m-d");
        $datetime1 = ["start_time"=>date("Y-m-d 00:00:00",strtotime($datetime_1['start_time'])),"end_time"=>date("Y-m-d 23:59:59",strtotime($datetime_1['end_time']))];
        $datetime2 = ["start_time"=>date("Y-m-d 00:00:00",strtotime($datetime_2['start_time'])),"end_time"=>date("Y-m-d 23:59:59",strtotime($datetime_2['end_time']))];
        $compare = !empty($request["compare"])?$request["compare"]:false;
        $table = 'source_order_count';
        if($compare === true){
            $result = self::compareAnalysis($datetime1,$datetime2,$table);
            $total = self::getTotal($result,2);
            $tableData = self::getTableData($result,$total,2);
            return ['tableData'=>$tableData,"originalOrder1"=>$total['one'],"originalOrder2"=>$total['two']];
        }else{  //不对比
            if ($datetime_1["start_time"] == $datetime_1["end_time"]){  //单天
                $result = self::oneAnalysis($datetime1,$table);
                $total = self::getTotal($result,1);
                $tableData = self::getTableData($result,$total,1);
                return ['tableData'=>$tableData,"originalOrder1"=>$total];
            }else{  //多天
                $result = self::oneAnalysis($datetime_1,$table);
                $total = self::getTotal($result,1);
                $x_axis = self::setCoordinateX($datetime1);
                $lineChartData = self::getchartData($result,$x_axis);
                $tableData = self::getTableData($result,$total,1);
                return ['tableData'=>$tableData,"originalOrder1"=>$total,"xdata"=>$x_axis,"lineChartData"=>$lineChartData];
            }
        }
    }

    public static function compareAnalysis($datetime1,$datetime2,$table)
    {
        $result['one'] = Db::table($table)->field("create_time,sum,order_sum_alone,order_sum,CONCAT(order_rate,'%') as order_rate,uv,valid_num,invalid_num,auto_invalid_num,hand_invalid_num")->whereTime("create_time",'between',[$datetime1["start_time"],$datetime1["end_time"]])->select();
        $result['two'] = Db::table($table)->field("create_time,sum,order_sum_alone,order_sum,CONCAT(order_rate,'%') as order_rate,uv,valid_num,invalid_num,auto_invalid_num,hand_invalid_num")->whereTime("create_time",'between',[$datetime2["start_time"],$datetime2["end_time"]])->select();
        return $result;
    }

    public static function oneAnalysis($datetime1,$table)
    {
        return Db::table($table)->field("create_time,sum,order_sum_alone,order_sum,CONCAT(order_rate,'%') as order_rate,uv,valid_num,invalid_num,auto_invalid_num,hand_invalid_num")->whereTime('create_time','between',[$datetime1["start_time"],$datetime1["end_time"]])->select();
    }

    public static function getTotal($result,$rule)
    {
        if ($rule == 1){
            $total = self::setTotalList($result);
        } else {
            $total['one'] = self::setTotalList($result['one']);
            $total['two'] = self::setTotalList($result['two']);
        }
        return $total;
    }

    public static function setTotalList($result)
    {
        $total = [
            ["title"=>"原始单总量","number"=>"0"],
            ["title"=>"订单总量（去重）","number"=>"0"],
            ["title"=>"原始单-订单转化率","number"=>"0"],
            ["title"=>"UV总量","number"=>"0"],
            ["title"=>"有效原始单","number"=>"0"],
            ["title"=>"无效原始单","number"=>"0"],
            ["title"=>"自动无效单","number"=>"0"],
            ["title"=>"手动无效单","number"=>"0"],
        ];
        foreach ($result as $key=>$value) {
            $total[0]['number'] += $value['sum'];
            $total[1]['number'] += $value['order_sum_alone'];
            $total[3]['number'] += $value['uv'];
            $total[4]['number'] += $value['valid_num'];
            $total[5]['number'] += $value['invalid_num'];
            $total[6]['number'] += $value['auto_invalid_num'];
            $total[7]['number'] += $value['hand_invalid_num'];
        }
        $total[2]['number'] = $total[0]['number'] == 0 ? "0%":(round($total[1]['number'] / $total[0]['number'],4)*100)."%";
        return $total;
    }

    public static function setCoordinateX($datetime_1)
    {
        $time_start = strtotime($datetime_1["start_time"]);
        $time_end = strtotime($datetime_1["end_time"]);
        $days = ($time_end - $time_start) / 86400 +1;
        $time = [];
        for ($i=0;$i<$days;$i++) {
            $time[$i] = date("Y-m-d",$time_start + 86400 * $i);
        }
        return $time;
    }

    public static function getchartData($result,$x_axis)
    {
        $listarr = [];
        $validOriginalList = ["name"=>"有效原始单","type"=>"line","yAxisIndex"=>"1","data"=>[]];
        $originalList = ["name"=>"原始单","type"=>"line","yAxisIndex"=>"1","data"=>[]];
        $uv = ["name"=>"UV","type"=>"bar","data"=>[]];
        foreach ($result as $item=>$info){
            $time_now = date("Y-m-d",strtotime($info["create_time"]));
            $listarr[$time_now] = $info;
        }
        foreach ($x_axis as $key=>$value){
            if (isset($listarr[$value])) {
                $uv["data"][$key] = $listarr[$value]['uv'];
                $originalList["data"][$key] = $listarr[$value]['sum'];
                $validOriginalList["data"][$key] = $listarr[$value]['valid_num'];
            } else {
                $uv["data"][$key] = "0";
                $originalList["data"][$key] = "0";
                $validOriginalList["data"][$key] = "0";
            }
        }
        return [$uv,$originalList,$validOriginalList];
    }

    public static function setListInfoarr($total,$rule = 1)
    {
        $data = [0=>["datetime"=>"总计"]];
        if ($rule == 1) {
            $data[0]["sum"] = $total[0]["number"];
            $data[0]["order_sum"] = $total[1]["number"];
            $data[0]["order_rate"] = $total[2]["number"];
            $data[0]["uv"] = $total[3]["number"];
            $data[0]["valid_num"] = $total[4]["number"];
            $data[0]["invalid_num"] = $total[5]["number"];
            $data[0]["auto_invalid_num"] = $total[6]["number"];
            $data[0]["hand_invalid_num"] = $total[7]["number"];
        } else {
            foreach ($total as $key=>$value) {
                $data[0]["sum"][$key] = $value[0]["number"];
                $data[0]["order_sum"][$key] = $value[1]["number"];
                $data[0]["order_rate"][$key] = $value[2]["number"];
                $data[0]["uv"][$key] = $value[3]["number"];
                $data[0]["valid_num"][$key] = $value[4]["number"];
                $data[0]["invalid_num"][$key] = $value[5]["number"];
                $data[0]["auto_invalid_num"][$key] = $value[6]["number"];
                $data[0]["hand_invalid_num"][$key] = $value[7]["number"];
            }
            foreach ($data[0] as $key=>$value) {
                if (is_array($value)) {
                    $data[0][$key] = self::setChangeData($value);
                } else {
                    $data[0][$key] = $value;
                }
            }
        }
        return $data;
    }

    public static function getTableData($result,$total,$rule)
    {
        $data = self::setListInfoarr($total,$rule);
        if ($rule == 1) {
            foreach ($result as $key => $value) {
                $data[$key + 1]["datetime"] = date("Y-m-d",strtotime($value["create_time"]));
                $data[$key + 1]['sum'] = $value['sum'];
                $data[$key + 1]['order_sum'] = $value['order_sum'];
                //TODO : order_rate数据可能有误，需要修改
                $data[$key + 1]['order_rate'] = $value['order_rate'];
                $data[$key + 1]['uv'] = $value['uv'];
                $data[$key + 1]['valid_num'] = $total[4]["number"] == 0 ? 0 : $value['valid_num'] . '(' . (round($value['valid_num'] / $total[4]["number"], 4) * 100) . '%)';
                $data[$key + 1]['invalid_num'] = $total[5]["number"] == 0 ? 0 : $value['invalid_num'] . '(' . (round($value['invalid_num'] / $total[5]["number"], 4) * 100) . '%)';
                $data[$key + 1]['auto_invalid_num'] = $total[6]["number"] == 0 ? 0 : $value['auto_invalid_num'] . '(' . (round($value['auto_invalid_num'] / $total[5]["number"], 4) * 100) . '%)';
                $data[$key + 1]['hand_invalid_num'] = $total[7]["number"] == 0 ? 0 : $value['hand_invalid_num'] . '(' . (round($value['hand_invalid_num'] / $total[5]["number"], 4) * 100) . '%)';
            }
        } else {
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
            $one_count >= $two_count?$new_count = $one_count:$new_count = $two_count;
            for ($i=0;$i<$new_count;$i++) {
                if (empty($one_info[$i])) {
                    $one_info[$i] = ["create_time"=>"-","sum"=>"0","order_sum"=>"0","order_rate"=>"0%","uv"=>"0","valid_num"=>"0","invalid_num"=>"0","auto_invalid_num"=>"0","hand_invalid_num"=>"0"];
                }
                if (empty($two_info[$i])) {
                    $two_info[$i] = ["create_time"=>"-","sum"=>"0","order_sum"=>"0","order_rate"=>"0%","uv"=>"0","valid_num"=>"0","invalid_num"=>"0","auto_invalid_num"=>"0","hand_invalid_num"=>"0"];
                }
                if($one_info[$i]['create_time'] != "-") {
                    $one_info[$i]['create_time'] = date("Y-m-d",strtotime($one_info[$i]['create_time']));
                }
                if($two_info[$i]['create_time'] != "-") {
                    $two_info[$i]['create_time'] = date("Y-m-d",strtotime($two_info[$i]['create_time']));
                }
                foreach ($one_info[$i] as $key=>$value) {
                    $data[$i+1]["datetime"] = $one_info[$i]['create_time'].' VS '. $two_info[$i]['create_time'];
                    if ($key != "create_time") {
                        $data[$i+1][$key]['one'] = $one_info[$i][$key];
                        $data[$i+1][$key]['two'] = $two_info[$i][$key];
                    }
                }
                foreach ($data[$i+1] as $key=>$value) {
                    if (is_array($value)) {
                        $data[$i+1][$key] = self::setChangeData($value);
                    } else {
                        $data[$i+1][$key] = $value;
                    }
                }
            }
        }
        return $data;
    }

    public static function setChangeData($data)
    {
//        $data_new = $data;
//        $dom = $data['one'] - $data['two'];
//        $data_new['type'] = $dom >= 0?$dom>0?"up":"equality":"down";
//        $newdom = $dom>0?'+'.$dom:$dom;
//        $data_new["change"] = $data['two'] == 0 ?$data['one']."(100%)":$newdom."(".(round($newdom/$data['two'],4)*100)."%)";
//        return $data_new;
        $data_new = $data;
        if (stristr($data['one'],"(",TRUE)) {
            $data_new['one'] = strstr($data['one'], "(", TRUE);
            $data_new['two'] = strstr($data['two'], "(", TRUE);
        } else {
            $data_new['one'] = $data['one'];
            $data_new['two'] = $data['two'];
        }
        $dom = $data_new['one'] - $data_new['two'];
        $data_new['type'] = $dom >= 0?$dom>0?"up":"equality":"down";
        $newdom = $dom>0?'+'.$dom:$dom;
        if ($data_new['two'] == 0 || $data_new['two'] == "0%") {
            $data_new["change"] = $data_new['one']."(0%)";
        }else {
            $data_new["change"] = $newdom."(".(round($newdom/$data_new['two'],4)*100)."%)";
        }
//        $data_new["change"] = $data_new['two'] == 0 ?$data_new['one']."(100%)":$newdom."(".(round($newdom/$data_new['two'],4)*100)."%)";
        $data_new['one'] = $data['one'];
        $data_new['two'] = $data['two'];
        return $data_new;
    }
}