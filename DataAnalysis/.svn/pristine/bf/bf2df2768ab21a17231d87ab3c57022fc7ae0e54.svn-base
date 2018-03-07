<?php
namespace app\script\test;

use app\common\Utility;
use app\index\controller\Zlog;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Log;
use think\Db;

class GuHao extends Command
{
    /**
     * 面板
     */
    protected function configure()
    {
        $this->setName('guhao')->setDescription('guhao test only');
    }

    /**
     * 入口
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
       $data = Db::table('source_domain_sort')->page(2,2)->select();
       Zlog::$logfile='gh.txt';
       Zlog::write($data);
    }

}