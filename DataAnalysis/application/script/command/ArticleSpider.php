<?php
/**
 * 关键字文章爬取
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2018/3/8
 * Time: 11:14
 */

namespace app\script\command;


use app\script\model\ArticleSpiderProcess;
use think\console\Command;
use think\console\input\Argument;
use think\console\Input;
use think\console\Output;

class ArticleSpider extends Command
{
    /**
     * 面板
     */
    protected function configure()
    {
        $this->setName('articleSpider')->setDescription('keyword num -k ,article num -a ');
        $this->addOption('article','a',Argument::OPTIONAL,'one keyword crawl num article',5);
        $this->addOption('keyword','k',Argument::OPTIONAL,'one process handle num keyword',1);
        $this->addOption('process','p',Argument::OPTIONAL,'one process handle num keyword',1);
    }

    /**
     * 入口
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $output->writeln('articleSpider start!process_num:'
        .$input->getOption('process').';keyword_num:'.$input->getOption('keyword')
        .';article_num:'.$input->getOption('article'));
        $model = new ArticleSpiderProcess();
        $model->setQUEUEKEY(ftok(__FILE__,1));
        $model->setPROCESSNUM($input->getOption('process'));
        $model->setKeywordNum($input->getOption('keyword'));
        $model->setArticleNum($input->getOption('article'));
        $model->setFREEQUEUE(true);
        $model->start();
    }
}