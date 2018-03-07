<?php

namespace app\index\model;

use app\index\controller\Zlog;
use think\Db;
use think\Log;
use think\Model;
use think\Request;
use think\Session;

class FromSort extends Model
{

    public function getList()
    {
        $request = Request::instance()->post();
        $sum_arr = empty($request['sum'])?['time' => '总计','num' => '0', 'from_mark' => '0', 'from_engines' => '0', 'from_other' => '0',]:$request['sum'];
        $start_time = $request['searchData']['dateTime'][0];
        $end_time = $request['searchData']['dateTime'][1];
        $start_time = date('Y-m-d',strtotime($start_time));
        $end_time = date('Y-m-d',strtotime($end_time)+86400);
        $type = Session::get("type");
        $dstype=$type=="PC"?1:2;
        $query = Db::table('source_domain_sort')->field(["DATE_FORMAT(create_time,'%Y-%m-%d') as time","num","concat(from_mark,'(%',from_mark_rate,')') as from_mark","concat(from_engines,'(%',from_engines_rate,')') as from_engines","concat(from_other,'(%',from_other_rate,')') as from_other"])->where('dstype',$dstype)->where('create_time','between',[$start_time,$end_time]);
        //排序
        $prop =  isset($request['paginationData']['prop'])?$request['paginationData']['prop']:'';
        $order = isset($request['paginationData']['order'])?$request['paginationData']['order']:'';
        if ( $prop && $order){
            $order=$order=='descending'?'desc':'asc';
            $query->order($prop,$order);
        }
        //分页
        $total = $request['paginationData']['total'];
        $pagesize = $request['paginationData']['pageSize'];
        $currentPage = isset($request['paginationData']['currentPage'])?$request['paginationData']['currentPage']:1;
        $data = $query->page($currentPage,$pagesize)->select();
        array_unshift($data,$sum_arr);
        return [
            "tableData" => $data,
            "total" => $total
        ];

    }
}