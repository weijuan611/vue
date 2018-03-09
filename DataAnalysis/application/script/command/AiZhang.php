<?php
/**
 * 爱站抓取
 */
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

class AiZhang extends Command
{

    protected  $time;
    protected  $log_suffix='';
    /**
     * 面板
     */
    protected function configure()
    {
        $this->setName('aiZhang')->setDescription('aiZhang crawler! need after workBench');
        $this->addOption('time','t',Argument::OPTIONAL,'download date',date('Y-m-d',strtotime('-1day')));
    }

    /**
     * 入口
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $this->time = $input->getOption('time');
        $this->output->writeln("input time :".$input->getOption('time'));
        $this->output->writeln("aiZhang start!");
        $time = time();
        try {

            $urls = 'https://www.aizhan.com/cha/houxue.com/';
            $get = file_get_contents($urls);
            $pattern = '/t1":\[\d+/';
            $mat = [];
            preg_match_all($pattern, $get, $mat);
            $data_pc['top10'] = substr($mat[0][0], 5);
            $data_m['top10'] = substr($mat[0][1], 5);
            $pattern = '/t5":\[\d+/';
            preg_match_all($pattern, $get, $mat);
            $data_pc['top50'] = substr($mat[0][0], 5);
            $data_m['top50'] = substr($mat[0][1], 5);

            Db::table('url_count_m')->where('create_time', '=', $this->time)->update($data_m);
            Db::table('url_count_pc')->where('create_time', '=', $this->time)->update($data_pc);
        }catch (Exception $e){
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            $this->output->write($e->getMessage().PHP_EOL.$e->getTraceAsString());
        }
        $time = time()-$time;
        $this->output->writeln("all steps end!".(int)($time/60) .':'.$time%60);
        Log::log("aiZhang start end!".(int)($time/60) .':'.$time%60);
    }

}