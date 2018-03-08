<?php

namespace app\script\command;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use app\index\model;

class ArchiveCheck extends Command
{
    protected $num = 50;// 默认提交百度收录条数
    protected $mode = 1;// 默认提交模式

    /**
     * 面板
     */
    protected function configure()
    {
        $this->setName('baiduArchive')->setDescription('baidu archive submit/check');
        $this->addOption('num', 'N', Argument::OPTIONAL, 'number of urls to submit', $this->num);
        $this->addOption('mode', 'm', Argument::OPTIONAL, 'mode: (1.submit 2.check)', $this->mode);
    }

    /**
     * 入口
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $time = time();
        $this->output->writeln(date('Y-m-d H:i:s'));
        // 模式
        $mode = $input->getOption('mode');
        // 条数
        $num = $input->getOption('num');
        $num = $num > 0 ? $num : $this->num;
        if($mode == 1){
            var_export(model\BaiduArchive::submitBd($num));// 提交百度收录脚本
        }else{
            var_export(model\BaiduArchive::checkBd());// 检查百度收录脚本
        }
        $time = time() - $time;
        $this->output->writeln("all steps end!" . round($time / 60) . ':' . round($time / 60));
    }


}