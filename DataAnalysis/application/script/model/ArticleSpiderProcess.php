<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2018/3/8
 * Time: 11:48
 */

namespace app\script\model;

use think\Db;
use app\common\Process;

class ArticleSpiderProcess extends Process
{
    private $article_num = 10;
    private $keyword_num = 10;


    public function input()
    {
        echo 'keyword_num:'.$this->keyword_num.';article_num:'.$this->article_num.PHP_EOL;
        $keywords=Db::table('keywords')->where('material_num','<',$this->article_num)
            ->order('add_time','asc')->limit($this->keyword_num)
            ->field('keyword,material_num')->select();
        if(!empty($keywords)){
            foreach ($keywords as $item){
                $this->push($item['keyword'].'|'.($this->article_num - $item['material_num']));
            }
        }else{
            echo 'not find keyword need crawl from keywords table!';
        }
    }

    public function parse($work)
    {
        $data = explode('|',$work);
        $this->crawler($data[0],$data[1]);
    }

    public function crawler($keyword,$num){
        echo 'crawler:'.$keyword.'|'.$num.PHP_EOL;
    }

    /**
     * @param int $article_num
     * @return $this
     */
    public function setArticleNum($article_num)
    {
        $this->article_num = $article_num;
        return $this;
    }

    /**
     * @param int $keyword_num
     * @return $this
     */
    public function setKeywordNum($keyword_num)
    {
        $this->keyword_num = $keyword_num;
        return $this;
    }
}