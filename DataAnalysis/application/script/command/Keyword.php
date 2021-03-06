<?php

namespace app\script\command;

use app\script\model\Spider;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\Db;
use think\Exception;
use think\Log;


class Keyword extends Command
{
    protected $bd = 0;// 是否是厚学统计，0：百度 1：厚学

    /**
     * 面板
     */
    protected function configure()
    {
        $this->setName('keyword')->setDescription('baidu/houxue keyword');
        $this->addOption('baidu', 'b', Argument::OPTIONAL, 'mode: (0.baidu rank 1.houxue_import 2.houxue_stat 3.new rank 4.old rank 5.reset)', $this->bd);
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
        // 是计算百度排名还是厚学统计
        $b = $input->getOption('baidu');
        if ($b == 0) {
            $this->output->writeln('百度排名任务初始化');
            $qid = ftok(__FILE__, 1);
            (new Spider())->setMode(0)->setQUEUEKEY($qid)->start();//百度指数+厚学关键词,供定时脚本用
            $this->output->writeln('百度排名任务结束');
        } elseif ($b == 1) {
            $this->output->writeln('厚学录入任务初始化');
            $qid = ftok(__FILE__, 2);
            (new Spider())->setMode(1)->setQUEUEKEY($qid)->start();//厚学
            $this->output->writeln('厚学录入任务结束');
        } elseif ($b == 2) {
            $this->output->writeln('厚学统计任务初始化');
            $qid = ftok(__FILE__, 3);
            (new Spider())->setMode(2)->setQUEUEKEY($qid)->start();//厚学
            $this->output->writeln('厚学统计任务结束');
        } elseif ($b == 3) {
            $this->output->writeln('厚学每日跑url收录与关键词排名脚本(new)初始化');//new
            $qid = ftok(__FILE__, 4);
            (new Spider())->setMode(3)->setQUEUEKEY($qid)->start();//厚学每日脚本
            //标记为已计算
            try {
                $update_arr = ['cal_status' => 1];
                Db::table('task_detail')->update($update_arr);
            } catch (Exception $e) {
                Log::write($e->getMessage());
            }
            $this->output->writeln('厚学每日脚本结束');
        } elseif ($b == 4) {
            $this->output->writeln('厚学跑url收录与关键词排名脚本(old)初始化');//new
            $qid = ftok(__FILE__, 5);
            (new Spider())->setMode(4)->setQUEUEKEY($qid)->start();//厚学脚本
            //标记为已计算
            try {
                $update_arr = ['cal_status' => 1];
                Db::table('task_detail')->update($update_arr);
            } catch (Exception $e) {
                Log::write($e->getMessage());
            }
            $this->output->writeln('厚学脚本结束');
        } elseif($b == 5){
            //标记为未计算,每日0点重置
            try {
                $update_arr = ['cal_status' => 0];
                Db::table('task_detail')->where('status != 2')->update($update_arr);
            } catch (Exception $e) {
                Log::write($e->getMessage());
            }
        }
        $time = time() - $time;
        $this->output->writeln("all steps end!" . round($time / 60) . ':' . round($time / 60));
    }

}