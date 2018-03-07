<?php
namespace app\script\command;

use app\common\Constant;
use app\common\Utility;
use app\index\controller\Zlog;
use think\Db;
use think\Log;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\Exception;

class Order extends Command
{
    protected $time;
    protected $url = 'http://api.houxue.com/jsonapi/order/list';
    protected $url_order = 'http://api.houxue.com/jsonapi/order/order';
    protected $ordrawtotal = 0;
    protected $per = 500;
    protected $order_sum=0;//订单总量(未去重)
    protected $order_sum_alone=0;//订单总量(已去重)
    protected $valid_num = 0;//有效原始单数量
    protected $invalid_num = 0;//无效原始单数量
    protected $auto_invalid_num = 0;//自动无效单数量
    protected $hand_invalid_num = 0;//手动无效单数量

    /**
     * 面板
     */
    protected function configure()
    {
        $this->setName('order')->setDescription('get order and raworder data');
        $this->addOption('time','t',Argument::OPTIONAL,'download date',date('Y-m-d',strtotime('-1day')));
    }

    /**
     * 入口
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        ini_set('memory_limit', '1024M');
        $this->time = $input->getOption('time');
        $this->output->writeln('time:'.$this->time);
        $this->output->writeln("Task start!");
        //source_order
        $first_data = $this->getApiData(['page'=>1,'time'=>$this->time]);
        if ($first_data['code']==200){
            $this->ordrawtotal = $first_data['data']['ordrawtotal'];
        }else{
            $this->output->writeln("code is not 200 in first time!");
            exit();
        }
        $this->output->writeln('start raw data:'.$this->ordrawtotal);
        $times = $this->ordrawtotal/$this->per+1;
        $this->output->writeln('times:'.$times);
        for ($i=1;$i<$times;$i++){
            $this->output->writeln($i.'time begin ');
            $per_data = $this->getApiData(['page'=>$i,'pagesize'=>$this->per,'time'=>$this->time]);
            $this->output->writeln($i.'handle begin ');
            $this->handleData($per_data);
            $this->output->writeln($i.'handle end ');
        }
        //order
        $this->output->writeln("begin order!");
        $order_first_data = $this->getOrderData(['page'=>1,'time'=>$this->time]);
        if ($order_first_data['code']!=200){
            $this->output->writeln("empty order data !");
            exit();
        }else{
            $this->order_sum = $order_first_data['data']['ordtotal'];
            $this->order_sum_alone = $order_first_data['data']['ordtotal_alone'];
        }
        $this->output->writeln('start order data:'.$order_first_data['data']['ordtotal']);
        $times = $order_first_data['data']['ordtotal']/$this->per+1;
        $this->output->writeln('times:'.$times);
        for ($i=1;$i<$times;$i++){
            $this->output->writeln($i.'time begin ');
            $per_data = $this->getOrderData(['page'=>$i,'pagesize'=>$this->per,'time'=>$this->time]);
            $this->handleDataOrder($per_data);
        }
        $this->output->writeln("Task end!");
    }

    /**
     * @param $post_data
     * @return mixed
     */
    protected function getApiData($post_data)
    {
        $res = curl_request($this->url,$post_data,false);
        return json_decode($res,true);
    }
    protected function getOrderData($post_data)
    {
        $res = curl_request($this->url_order,$post_data,false);
        return json_decode($res,true);
    }

    /**
     * 插入数据库
     * @param $per_data
     * @return bool
     */
    public function handleData($per_data)
    {
        $data = $per_data['data']['ordrawdata'];
        foreach ($data as $key => $rawValue){
            if ($rawValue['unvalidnum']>0){
                //无效单
                //派单状态：0:未处理，1：自动派单，2：手工派单,3：自动无效单
                $this->invalid_num++;
                if ($rawValue['status']==2)
                    $this->hand_invalid_num++;
                if ($rawValue['status']==3)
                    $this->auto_invalid_num++;
            }else{
                //有效单
                $this->valid_num++;
            }
            $area_name = $rawValue['AreaName'];
            $cate_name = $rawValue['CategoryName'];
            $area_id = DB::table('sys_area')->where('AreaName','like','%'.$area_name.'%')->value('Id',0);
            $ct_id = DB::table('categories')->where('catename','like','%'.$cate_name.'%')->value('c_id',0);
            $data[$key]['areaid'] = $area_id;
            $data[$key]['categoryid'] = $ct_id;
            $data[$key]['create_at'] = $this->time;
            unset($data[$key]['AreaName']);
            unset($data[$key]['CategoryName']);
        }
        try {
            Db::startTrans();
            $sql = $this->getInsertAllSql("source_order",$data);
            Db::execute($sql);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            $this->output->writeln('fail database insert error');
            return false;
        }
        $this->output->writeln('insert success');
    }

    public function handleDataOrder($per_data)
    {
        $data = $per_data['data']['orddata'];
        foreach ($data as $key => $rawValue){
            $area_name = $rawValue['AreaName'];
            $cate_name = $rawValue['CategoryName'];
            $area_id = DB::table('sys_area')->where('AreaName','like','%'.$area_name.'%')->value('Id',0);
            $ct_id = DB::table('categories')->where('catename','like','%'.$cate_name.'%')->value('c_id',0);
            $data[$key]['areaid'] = $area_id;
            $data[$key]['categoryid'] = $ct_id;
            $data[$key]['create_at'] = $this->time;
            $data[$key]['link_phone'] = $rawValue['linkphone'];
            unset($data[$key]['AreaName']);
            unset($data[$key]['CategoryName']);
        }
        try {
            Db::startTrans();
            $sql = $this->getInsertAllSql("order",$data);
            Db::execute($sql);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            $this->output->writeln('fail database insert error');
            return false;
        }
        $this->output->writeln('insert success');
    }

    public function getInsertAllSql($tableName, $data)
    {
        $data = array_values($data);
        $fields = implode(',', array_keys($data[0]));

        $tmp = array();
        foreach($data as $value)
        {
            $tmp[] = "'" . implode("','", $value) . "'";
        }
        $values = "(" . implode("),(", $tmp) . ")";
        $sql = "INSERT INTO `{$tableName}` ({$fields}) VALUES {$values}";
        return $sql;
    }

}