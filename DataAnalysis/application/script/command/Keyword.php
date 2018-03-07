<?php
namespace app\script\command;

use app\common\Spider;
use app\common\Utility;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Log;
use think\Db;
use app\index\model;

class Keyword extends Command
{
    /**
     * 面板
     */
    protected function configure()
    {
        $this->setName('keyword')->setDescription('baidu index keyword');
    }

    /**
     * 入口
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        ini_set('max_execution_time', '0');//无超时
        $time = time();
        $this->output->writeln(date('Y-m-d H:i:s'));
//        var_export(model\Keyword::updateBdHx());// 百度指数+厚学关键词,供定时脚本用
        (new Spider())->start();//百度指数+厚学关键词,供定时脚本用
        $time = time()-$time;

        $this->output->writeln("all steps end!".round($time/60).':'.round($time/60));
    }



}