<?php
/**
 * 百度爬虫类
 * Created by PhpStorm.
 * User: wdy
 * Date: 2018/2/26
 * Time: 16:34
 */

namespace app\script\model;

use app\common\Process;
use app\common\Snoopy;
use app\common\Utility;
use app\index\model\Account;
use app\index\model\BaiduArchive;
use Sunra\PhpSimple\HtmlDomParser;
use think\Db;
use think\Log;
use think\Exception;

class Spider extends Process
{
    private $html = '';
    private $keyword = '';
    private $is_self = 0;
    private $snoopy;
    private $mode = 0;//0：百度排名 1：厚学统计
    const rank_limit = 100;

    public function __construct($keyword = '')
    {
        // 结束后删除已创建的队列
        $this->setFREEQUEUE(true);
        $this->setKeyword($keyword);
        $this->snoopy = new Snoopy();
    }

    public function parse($work)
    {
        Log::write('start to handle: ' . $work . PHP_EOL);
        Log::write('crawler:' . $work . PHP_EOL);
        switch ($this->mode) {
            case 0:// 百度PC/M端搜索排名
                list($kw_id, $keyword) = explode('|', $work);
                $this->updateBd($kw_id, $keyword);
                Log::write($keyword . ' baidu rank processing has finished.');
                break;
            case 1:// 录入厚学url与kw_id绑定关系
                list($kw_id, $keyword) = explode('|', $work);
                Log::write($kw_id . '|' . $keyword);
                $this->process_hx_bind($kw_id, $keyword);
                Log::write($keyword . ' houxue url,kw_id binding has finished.');
                break;
            case 2:// 厚学url统计插入keywords_detail
                $kdu_id_arr = explode('|', $work);
                $this->process_hx_stat($kdu_id_arr);
                Log::write($work . ' keywords_detail_url has finished.');
                break;
            case 3:// 厚学每日脚本(new)
                $this->process_keyword_daily(json_decode($work, true));
                Log::write('single daily script has finished.');
                break;
            case 4:// 厚学脚本(old)
                $this->process_keyword_old(json_decode($work, true));
                Log::write('single daily script has finished.');
                break;
        }

    }

    public function input()
    {
        echo 'start processing keywords, select 5 once a time.' . PHP_EOL;
        $no_date = '0000-00-00 00:00:00';
        $limit = 5;
        switch ($this->mode) {
            case 0:
                $keywords = Db::table('keywords k')
                    ->join('keywords_check kc', 'k.kw_id = kc.kw_id', 'left')
                    ->where('k.status', '=', 0)
                    ->where('kc.status', '=', 1)
                    ->limit($limit)
                    ->field('k.kw_id kw_id,k.keyword keyword')->select();
                if (!empty($keywords)) {
                    foreach ($keywords as $item) {
                        echo '=====>Assigned keyword : ' . $item['keyword'];
                        $this->push($item['kw_id'] . '|' . $item['keyword']);
                    }
                } else {
                    echo 'All ' . $limit . ' keywords baidu rank calc finished.';
                }
                break;
            case 1:
                $keywords = Db::table('keywords k')
                    ->join('keywords_check kc', 'k.kw_id = kc.kw_id', 'left')
                    ->where('k.status', '=', 2)
                    ->where('kc.status', '=', 1)
                    ->where('k.status_hx', '=', 0)
                    ->limit($limit)
                    ->field('k.kw_id kw_id,k.keyword keyword')->select();
                if (!empty($keywords)) {
                    foreach ($keywords as $item) {
                        $this->push($item['kw_id'] . '|' . $item['keyword']);
                    }
                } else {
                    echo 'All ' . $limit . ' keywords houxue statistics calc finished.';
                }
                break;
            case 2:
                $num = 20;
                $kdu_id_arr = Db::table('keywords_detail_url')
                    ->where('status', '=', 0)
                    ->limit($limit * $num)
                    ->column('kdu_id');
                if (!empty($kdu_id_arr)) {
                    Db::table('keywords_detail_url')->where('kdu_id', 'in', $kdu_id_arr)->update(['status' => 2]);
                    $kdu_id_arr_t = array_chunk($kdu_id_arr, $num);
                    foreach ($kdu_id_arr_t as $v) {
                        $this->push(implode('|', $v));
                    }
                }
                break;
            case 3:
                // 需要跑的数据(new)
                $td = Db::table('task_detail')
                    ->where('status != 2')
                    ->where('cal_status = 0')
                    ->limit($limit * 20)
                    ->column('td_id,kw_id,new_rank_pc,new_rank_m');
                $td_arr = [];
                if (!empty($td)) {
                    foreach ($td as $item) {
                        $this->push(json_encode($item));
                        $td_arr[] = $item['td_id'];
                    }
                    Db::table('task_detail')->where('td_id', 'in', $td_arr)->update(['cal_status' => 1]);
                } else {
                    echo 'All ' . $limit . 'amount of daily script calc has finished.';
                }
                break;
            case 4:
                // 需要跑的数据(old)
                $td = Db::table('task_detail')
                    ->where('status != 2')
                    ->where('cal_status = 0')
                    ->limit($limit * 20)
                    ->column('td_id,kw_id,new_rank_pc,new_rank_m');
                $td_arr = [];
                if (!empty($td)) {
                    foreach ($td as $item) {
                        $this->push(json_encode($item));
                        $td_arr[] = $item['td_id'];
                    }
                    Db::table('task_detail')->where('td_id', 'in', $td_arr)->update(['cal_status' => 1]);
                } else {
                    echo 'All ' . $limit . 'amount of daily script calc has finished.';
                }
                break;
        }

    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param string $keyword
     * @return $this
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param mixed $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * 查百度总入口
     */
    public function getResults()
    {
        $keyword = $this->keyword;
        $info_pc = $this->requestBaidu($keyword, 0, 0);//pc
        $this->is_self = 0;
        $info_m = $this->requestBaidu($keyword, 1, 0);//m
        return ['info_pc' => isset($info_pc) ? $info_pc : [], 'info_m' => isset($info_m) ? $info_m : []];
    }

    /*
     * 请求百度PC/M端排名
     */
    public function requestBaidu($keyword, $type, $page, $max_page = 10, $opp = '')
    {
        # 最多尝试10页
        $rank = 0;
        $info = [];
        $init_page = $page;
        while ($init_page < $max_page) {
            $url = $type == 0 ? 'https://www.baidu.com/s?' : 'https://m.baidu.com/s?';
            $values = ['wd' => $keyword, 'pn' => $init_page * 10];
            $temp = $this->getPageRank($url, $values, $init_page, $type, $opp);
            if (!empty($temp)) {
                $info = array_merge($info, $temp);
            }
            if ($this->is_self > 0) {
                $init_page = 10;
            } else {
                $init_page += 1;
            }
        }
        return $info;
    }

    /**
     * 获取百度PC/M端排名计算
     * @param $url
     * @param $values
     * @param $page
     * @param $type
     * @return array
     */
    public function getPageRank($url, $values, $page, $type, $opp = '')
    {

        $ua_pc = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.82 Safari/537.36';
        $ua_m = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) GSA/34.1.167176684 Mobile/15A372 Safari/602.1';
        $this->snoopy->agent = $type == 0 ? $ua_pc : $ua_m;
        $param = http_build_query($values);
        $final_url = $url . $param;
//        // 对手及厚学自己
//        try {
//            $op = Db::table('opponent')->field('op_id,op_domain')->select();
//            if (empty($op)) {
//                $op = [0 => ['op_id' => 0, 'op_domain' => 'houxue.com']];
//            } else {
//                $op[] = ['op_id' => 0, 'op_domain' => 'houxue.com'];
//            }
//        } catch (Exception $e) {}

        // 厚学自己
        $op = [0 => ['op_id' => 0, 'op_domain' => 'houxue.com']];
        if (!empty($opp)) {
            $op = [0 => ['op_id' => 0, 'op_domain' => 'houxue.com']];
        }
        $op_domain = Utility::arrayToSingleByIndex($op, 'op_domain');

        // 初始化
        $result = [];
        $rank = 0;
        $op_id = 0;

        // 获得需要解析的页面html
        $html = $this->snoopy->fetch($final_url)->getResults();
        try {
            $dom = HtmlDomParser::str_get_html($html);
            if ($type == 0) {
                // (1) PC:
                $list = $dom->find('.f13');// 百度搜索结果列表
                $links = [];
                $single_rank = 0;
                // 只抽取自己及竞争对手的url
                foreach ($list as $k => $v) {
                    $a = $v->find('.c-showurl');
                    if (empty($a)) {
                        continue;
                    }
                    $link = $a[0]->getAttribute('href');
                    $domain_part = $a[0]->text();// todo 直接从链接文字里取域名
                    echo $domain_part,PHP_EOL;
//                    foreach ($op_domain as $k1 => $v1) {
//                        if (strpos($domain_part, $v1)) {
//                            $real_link = get_redirect_url($link);// 真实网址
//                            $links[] = $real_link;
//                            $single_rank = $k + 1;
//                            break;
//                        } else {
//                            continue;
//                        }
//                    }
                    if (strpos($domain_part, $op_domain[0])) {
                        $real_link = get_redirect_url($link);// 真实网址
                        $links[] = $real_link;
                        $single_rank = $k + 1;
                        break;
                    }
                }
                // 统计排名
                foreach ($links as $k => $v) {
                    if (strpos($v, 'houxue.com')) {
                        $rank = $page * 10 + $single_rank;
                        $rank = $rank == 0 ? self::rank_limit : $rank;
                        $this->is_self = 1;
                        array_push($result, ['rank_pc' => $rank, 'url_pc' => $v, 'op_id' => 0]);
                        break;
                    } else {
                        foreach ($op as $k1 => $v1) {
                            if (strpos($v, $v1['op_domain'])) {
                                $rank = $page * 10 + $single_rank;
                                $rank = $rank == 0 ? self::rank_limit : $rank;
                                $op_id = $v1['op_id'];
                                array_push($result, ['rank_pc' => $rank, 'url_pc' => $v, 'op_id' => $op_id]);
                            }
                        }
                    }
                }
            } else {
                // (2) M:
                $list = $dom->find('div[class=c-showurl c-line-clamp1]');// 百度搜索结果列表
                $links = [];
                $single_rank = 0;
                foreach ($list as $k => $v) {
                    $t = $v->find('a');
                    $domain_part_m = $t[0]->text();// todo 直接从链接文字里取域名
                    $link = $t[0]->getAttribute('href');
                    // fix: 批量替换';'为'&'
                    $link = str_replace(';', '&', $link);
//                    foreach ($op_domain as $k1 => $v1) {
//                        if (strpos($domain_part_m, $v1)) {
//                            $real_link = $this->get_redir_m_url($link);// 真实网址
//                            $links[] = $real_link;
//                            $single_rank = $k+1;
//                            break;
//                        } else {
//                            continue;
//                        }
//                    }
                    if (strpos($domain_part_m, $op_domain[0])) {
                        $real_link = $this->get_redir_m_url($link);// 真实网址
                        $links[] = $real_link;
                        $single_rank = $k + 1;
                        break;
                    } else {
                        continue;
                    }
                }
                // 统计排名
                foreach ($links as $k => $v) {
                    if (strpos($v, 'houxue.com')) {
                        $rank = $page * 10 + $single_rank;
                        $rank = $rank == 0 ? self::rank_limit : $rank;
                        $this->is_self = 1;
                        array_push($result, ['rank_m' => $rank, 'url_m' => $v, 'op_id' => 0]);
                        break;
                    } else {
                        foreach ($op as $k1 => $v1) {
                            if (strpos($v, $v1['op_domain'])) {
                                $rank = $page * 10 + $single_rank;
                                $rank = $rank == 0 ? self::rank_limit : $rank;
                                $op_id = $v1['op_id'];
                                array_push($result, ['rank_m' => $rank, 'url_m' => $v, 'op_id' => $op_id]);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::write($e->getMessage());
        }
        return $result;
    }

    /**
     * 更新百度
     * @param $kw_id_in
     * @param $keyword_in
     * @return array|bool
     */
    public function updateBd($kw_id_in, $keyword_in)
    {
//        echo $keyword_in . '百度排名计算开始' . PHP_EOL;
        $time_now = date('Y-m-d H:i:s', time());
        // 标记这条为计算中
        try {
            Db::table('keywords')->where('kw_id', '=', $kw_id_in)->update(['status' => 1]);
        } catch (Exception $e) {
            return ['type' => 'error', 'msg' => $e->getLine() . '数据库操作失败!' . $e->getMessage() . $e->getTraceAsString()];
        }
        // 处理关键词，去掉潜在的中英文逗号
        $temp = str_replace('，', '', $keyword_in);
        $temp = str_replace(',', '', $temp);
        $keyword_in = $temp;

        //===step 1:获取百度指数统计量（pc/m) + 获取百度排名、域名
        $mdl = new Account();
        $bd_rank = $mdl->getBaiduRank($keyword_in);//百度排名
        Log::write($bd_rank);
//            $bd_index = [0 => 3, 1 => 4];//todo
        $bd_index = $mdl->getBaiduIndex($keyword_in);//百度指数
        Log::write('1111111');
        Log::write($bd_index);

        // 百度排名
        $baidu_rank_pc = isset($bd_rank['info_pc']) ? $bd_rank['info_pc'] : [];//数组，含本身及其竞争对手(pc)
        $baidu_rank_m = isset($bd_rank['info_m']) ? $bd_rank['info_m'] : [];//数组，含本身及其竞争对手(m)
        // 找出自身的百度排名
        $own_rank_pc = [];
        $own_rank_m = [];
        foreach ($bd_rank['info_pc'] as $k => $v) {
            if ($v['op_id'] == 0) {
                $own_rank_pc = $v;
            }
        }
        foreach ($bd_rank['info_m'] as $k => $v) {
            if ($v['op_id'] == 0) {
                $own_rank_m = $v;
            }
        }
        // 更新自身
        $result_bd = [
            'baidu_index_pc' => $bd_index[0],//百度指数pc
            'baidu_index_m' => $bd_index[1],//百度指数m
            'update_time' => $time_now,//更新时间
            'is_archive' => ($bd_index[0] + $bd_index[1] == 0) ? 0 : 1,//是否被收录
            'baidu_rank_pc' => isset($own_rank_pc['rank_pc']) ? $own_rank_pc['rank_pc'] : self::rank_limit,
            'baidu_rank_m' => isset($own_rank_m['rank_m']) ? $own_rank_m['rank_m'] : self::rank_limit
        ];
//        Log::write('-----1.百度指数、百度排名-------');

        //===step 3: 更新step1/2查到的数据
        // 3.1:百度指数、排名
        if (!empty($result_bd)) {
            try {
                // 更新keywords表
                Db::table('keywords')->where('kw_id', '=', $kw_id_in)->update($result_bd);

                // 处理数据，插入keywords_crawl_log表
                $temp_pc = [];
                $temp_m = [];
                //pc竞争对手按op_id去重
                foreach ($baidu_rank_pc as $k1 => $v1) {
                    $temp_pc[$v1['op_id']] = $v1;
                }
                //m竞争对手按op_id去重
                foreach ($baidu_rank_m as $k1 => $v1) {
                    $temp_m[$v1['op_id']] = $v1;
                }
                // 合并pc/m数组
                $temp_final = $temp_pc;
                foreach ($temp_m as $k1 => $v1) {
                    if (!isset($temp_final[$k1])) {
                        $temp_final[$k1] = $temp_m[$k1];
                        $temp_final[$k1]['url_pc'] = '';
                        $temp_final[$k1]['rank_pc'] = self::rank_limit;
                    } else {
                        $temp_final[$k1] = array_merge($temp_final[$k1], $temp_m[$k1]);
                    }
                }
                foreach ($temp_pc as $k1 => $v1) {
                    if (!isset($temp_m[$k1])) {
                        $temp_final[$k1]['url_m'] = '';
                        $temp_final[$k1]['rank_m'] = self::rank_limit;
                    }
                }
                // 合并完成
                foreach ($temp_final as $k1 => $v1) {
                    // 记录每一次更新的日志
                    $kcl = [
                        'create_time' => $result_bd['update_time'],
                        'kw_id' => $kw_id_in,
                        'index' => $result_bd['baidu_index_pc'],
                        'index_m' => $result_bd['baidu_index_m'],
                        'op_id' => $k1,
                        'url' => $v1['url_pc'],
                        'url_m' => $v1['url_m'],
                        'rank' => $v1['rank_pc'] > 0 ? $v1['rank_pc'] : self::rank_limit,
                        'rank_m' => $v1['rank_m'] > 0 ? $v1['rank_m'] : self::rank_limit
                    ];
                    Db::table('keywords_crawl_log')->insert($kcl);
                }
            } catch (\Exception $e) {
                return ['type' => 'error', 'msg' => $e->getLine() . '数据库操作失败!' . $e->getMessage() . $e->getTraceAsString()];
            }
        }
        //===step 4: 更新统计量
        // 搜索词模糊匹配
//            $kws_id_range = Utility::arrayToSingleByIndex(Db::query('SELECT kws_id FROM keywords_search WHERE keyword LIKE \'%' . $v . '%\''), 'kws_id');
        $kws_id_range = Db::table('keywords_search')->where('keyword', 'like', '%' . $keyword_in . '%')->column('kws_id');
        if ($kws_id_range) {
            $kws_id_range = '(' . implode(',', $kws_id_range) . ')';
            // 查该关键词对应的pc/m搜索量
//                $num_pc = Db::table('keyword_statistics')->where('dstype', '=', 1)->where('kws_id', 'EXP', 'IN' . $kws_id_range)->field('SUM(num) as num')->value('num', 0);
            $num_pc = Db::table('keyword_statistics')->where('dstype', '=', 1)->where('kws_id', 'IN', $kws_id_range)->sum('num');
//                $num_m = Db::table('keyword_statistics')->where('dstype', '=', 2)->where('kws_id', 'EXP', 'IN' . $kws_id_range)->field('SUM(num) as num')->value('num', 0);
            $num_m = Db::table('keyword_statistics')->where('dstype', '=', 2)->where('kws_id', 'IN', $kws_id_range)->sum('num');               // 更新
            try {
                $update_arr = ['kws_count_pc' => $num_pc, 'kws_count_m' => $num_m];
                Log::write('-----4.统计量-------');
                Log::log($update_arr);
                Db::table('keywords')->where('kw_id', '=', $kw_id_in)->update($update_arr);
            } catch
            (\Exception $e) {
                return ['type' => 'error', 'msg' => $e->getLine() . '数据库操作失败!' . $e->getMessage() . $e->getTraceAsString()];
            }
        }
        //===step 5: 计算综合评分
        try {
            $param = Db::table('keywords')->where('kw_id', '=', $kw_id_in)
                ->field('url_pc,url_m,baidu_rank_pc,baidu_rank_m')->find();
            // pc端
            $is_hightrans_pc = Utility::isTransform($param['url_pc'], true);//是否是高转化，网址正则判断
            $is_cooperate_pc = self::getCooperate($param['url_pc']);//是否为合作,1是2否
            $score_pc = self::getScore($param['baidu_rank_pc'], $is_hightrans_pc, $is_cooperate_pc);//综合评分

            // m端
            $is_hightrans_m = Utility::isTransform($param['url_m'], false);//是否是高转化，网址正则判断
            $is_cooperate_m = self::getCooperate($param['url_m']);//是否为合作,1是2否
            $score_m = self::getScore($param['baidu_rank_m'], $is_hightrans_m, $is_cooperate_m);//综合评分

            $update_arr = [
                "is_hightrans_pc" => $is_hightrans_pc,
                "is_cooperate_pc" => $is_cooperate_pc,
                "score_pc" => $score_pc,
                "is_hightrans_m" => $is_hightrans_m,
                "is_cooperate_m" => $is_cooperate_m,
                "score_m" => $score_m
            ];
            Log::write('-----5.综合评分-------');
            Log::log($update_arr);
            Db::table('keywords')->where('kw_id', '=', $kw_id_in)->update($update_arr);
        } catch (Exception $e) {
            return ['type' => 'error', 'msg' => $e->getMessage() . $e->getTraceAsString()];
        }

        // 全部结束
        // 标记这5条为计算完成
        try {
            Db::table('keywords')->where('kw_id', '=', $kw_id_in)->update(['status' => 2]);
        } catch (Exception $e) {
            return ['type' => 'error', 'msg' => $e->getLine() . '数据库操作失败!' . $e->getMessage() . $e->getTraceAsString()];
        }
        Log::write($keyword_in . ' 关键词处理完成');
        // 返回
        return true;
    }

    /**
     * 根据url获取html网址，返回统计的关键词出现次数、频度、是否收录
     * @param string $url
     * @param string $keyword
     * @return array|bool
     */
    public function getWordDetail($url = '', $keyword = '')
    {
        if (!$url || !$keyword) {
            return false;
        }
        // step 1: 该网页中，该关键词出现次数、频度
        $kw_num = $density = 0;
        $snoopy = $this->snoopy;
        $snoopy->fetchtext($url);
        if (($snoopy->status) == "200") {
            $res = $snoopy->results;
            $length = mb_strlen($res, "utf-8");// 正文总长度
            if ($length < 1) {
                return false;// 无内容
            }
            if ($length > strlen($keyword)) {// 一定是真包含
                $kw_num = substr_count($res, $keyword);// 关键词出现次数
            }
            $density = round(($kw_num * strlen($keyword) / $length), 2);// 关键词频度
        }

        return ['kw_num' => $kw_num, 'density' => $density];
    }

    /**
     * 计算综合评分
     * @param $rank
     * @param $is_hightrans
     * @param $is_cooperate
     * @return int
     */
    public static function getScore($rank, $is_hightrans, $is_cooperate)
    {
        $score = 5;//默认毫无推广
        //评分等级：1:完美推广 2:一步之遥 3:初见成效 4:无效推广 5:毫无推广
        if ($rank == 1 && $is_hightrans == 1 && $is_cooperate == 1) {
            $score = 1;
        } elseif (($rank > 1 && $rank < 3) && $is_hightrans == 1 && $is_cooperate == 1) {
            $score = 2;
        } elseif (($rank > 3 && $rank < 11) && $is_hightrans == 1 && $is_cooperate == 1) {
            $score = 3;
        } elseif (($rank > 0 && $rank < 11) && $is_hightrans == 0 && $is_cooperate == 0) {
            $score = 4;
        }
        return $score;
    }

    /**
     * 根据url查询是否为合作链接
     * @param $url
     * @return int
     */
    public static function getCooperate($url)
    {
        $post = ['url' => json_encode([$url])];
        $res = curl_request('http://api.houxue.com/jsonapi/keywords/iscooperate', $post, 0, 0);
        $result = json_decode($res, true);

        if ($result['code'] == 200) {
            if ($result['data'][0] == 1) {
                return 1;//合作
            } else {
                return 2;//非合作
            }
        } else {
            return 2;
        }
    }

    /**
     * 获取百度m站跳转链接
     * @param $url
     * @return string
     */
    public function get_redir_m_url($url)
    {
//        $res = $this->snoopy->fetch($url)->results;// why returning empty?
        $res = (new Snoopy)->fetch($url)->results;
//        $result = preg_match_all("/(http|ftp|https):[\/]{2}[a-z]+[.]{1}[a-z\d\-]+[.]{1}[a-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*/", $res, $matches);
        $result = preg_match_all("/(http|ftp|https):\/\/([\w\d\-_]+[\.\w\d\-_]+)[:\d+]?([\/]?[\w\/\.\?=&;%@#\+,]+)/i", $res, $matches);
        if ($result) {
            return isset($matches[0][0]) ? $matches[0][0] : '';
        } else {
            return '';
        }
    }

    /**
     * 请求厚学接口，更新keyword,录入keyword_detail,keyword_detail_url
     */
    public function process_hx_bind($kw_id_in, $keyword_in)
    {
        Log::write('---厚学关键词拉取url绑定kw_id开始---' . PHP_EOL);
        $keyword_arr = [$keyword_in];
        $post = ['kws' => json_encode($keyword_arr)];
        $res = curl_request('http://api.houxue.com/jsonapi/keywords/list', $post, 0, 0);
        $res = json_decode($res, true);
        $result_hx = $res['data'][0];
        $time_now = date('Y-m-d H:i:s', time());
        // step 1: 先录入keywords_detail_url,去重
        // type 映射
        foreach ($result_hx['school_data'] as $k => $v) {
            $result_hx['school_data'][$k]['type'] = 1;
        }
        foreach ($result_hx['mschool_data'] as $k => $v) {
            $result_hx['mschool_data'][$k]['type'] = 1;
        }
        foreach ($result_hx['course_data'] as $k => $v) {
            $result_hx['course_data'][$k]['type'] = 2;
        }
        foreach ($result_hx['mcourse_data'] as $k => $v) {
            $result_hx['mcourse_data'][$k]['type'] = 2;
        }
        foreach ($result_hx['news_data'] as $k => $v) {
            $result_hx['news_data'][$k]['type'] = 3;
        }
        foreach ($result_hx['mnews_data'] as $k => $v) {
            $result_hx['mnews_data'][$k]['type'] = 3;
        }
        foreach ($result_hx['hotnews_data'] as $k => $v) {
            $result_hx['hotnews_data'][$k]['type'] = 4;
        }
        foreach ($result_hx['mhotnews_data'] as $k => $v) {
            $result_hx['mhotnews_data'][$k]['type'] = 4;
        }
        foreach ($result_hx['zhidao_data'] as $k => $v) {
            $result_hx['zhidao_data'][$k]['type'] = 5;
        }
        foreach ($result_hx['mzhidao_data'] as $k => $v) {
            $result_hx['mzhidao_data'][$k]['type'] = 5;
        }
        // pc
        $pc = array_merge(array_values($result_hx['school_data']), array_values($result_hx['course_data']),
            array_values($result_hx['news_data']), array_values($result_hx['hotnews_data']),
            array_values($result_hx['zhidao_data']));
        $m = array_merge(array_values($result_hx['mschool_data']), array_values($result_hx['mcourse_data']),
            array_values($result_hx['mnews_data']), array_values($result_hx['mhotnews_data']),
            array_values($result_hx['mzhidao_data']));
        foreach ($pc as $k => $v) {
            $pc[$k]['dstype'] = 1;
            $pc[$k]['update_time'] = $time_now;
        }
        foreach ($m as $k => $v) {
            $m[$k]['dstype'] = 2;
            $m[$k]['update_time'] = $time_now;
        }
        $insert_arrs = array_merge($pc, $m);
        //url为空的去掉
        foreach ($insert_arrs as $k => $v) {
            if ($v['url'] == '') {
                unset($insert_arrs[$k]);
            }
        }
        //keywords_detail_url里已存在url过滤掉
        $urls_existed = Db::table('keywords_detail_url')->column('url');
        foreach ($insert_arrs as $k => $v) {
            if (in_array($v['url'], $urls_existed)) {
                unset($insert_arrs[$k]);
            }
        }
        // 建立关系，url唯一索引
        foreach ($insert_arrs as $k => $v) {
            try {
                // step 1: keyword更新完毕
                $kw_arr = [
                    'school_index' => $result_hx['school_index'],
                    'course_index' => $result_hx['course_index'],
                    'news_index' => $result_hx['news_index'],
                    'hotnews_index' => $result_hx['hotnews_index'],
                    'zhidao_index' => $result_hx['zhidao_index'],
                    'mschool_index' => $result_hx['mschool_index'],
                    'mcourse_index' => $result_hx['mcourse_index'],
                    'mnews_index' => $result_hx['mnews_index'],
                    'mhotnews_index' => $result_hx['mhotnews_index'],
                    'mzhidao_index' => $result_hx['mzhidao_index'],
                    'update_time' => $time_now,
                ];
                Db::table('keywords')->where('kw_id', '=', $kw_id_in)->update($kw_arr);
                // step 2: 数据入库keyword_detail_url
                $kdu_id = Db::table('keywords_detail_url')->insertGetId($v);
                // step 3: 数据入库keywords_detail
                // 关键词数量、密度
                $append_arr = $this->getWordDetail($v['url'], $keyword_in);//kw_num,density
                $kd_arr = ['kw_id' => $kw_id_in, 'kdu_id' => $kdu_id, 'add_time' => $time_now];
                $kd_arr = array_merge($kd_arr, $append_arr);
                Db::table('keywords_detail')->insert($kd_arr);
            } catch (Exception $e) {
                Log::write($e->getMessage());
            }
        }
        Log::write('keyword更新、录入厚学url、绑定kw_id完成');
        return true;
    }

    /**
     *  厚学关键词统计计算
     */
    public function process_hx_stat($kdu_id_arr)
    {
        $url_arr = Db::table('keywords_detail_url')
            ->field('kdu_id,url,dstype')
            ->where('kdu_id', 'in', $kdu_id_arr)
            ->select();
        $time_now = date('Y-m-d H:i:s', time());
        foreach ($url_arr as $v) {
            // 该网址是否被百度收录
            $bd_url = "https://www.baidu.com/s?wd=" . $v['url'];
            $res = Utility::curlRequest($bd_url);
            $flag = "<div class=\"c-abstract\">";
            if (strpos($res, $flag) == true) {
                $is_alive = 1;// 已收录
            } else {
                $is_alive = 0;// 未收录
            }
            Db::table('keywords_detail_url')->where('kdu_id', '=', $v['kdu_id'])
                ->update(['is_alive' => $is_alive, 'status' => 1, 'update_time' => $time_now]);
        }
    }

    /**
     * 关键词排名 + url是否收录, 不考核工作量
     * @param $td
     */
    public function process_keyword_old($td)
    {
        // 跑排名
        $keyword = Db::table('keywords')->where('kw_id', '=', $td['kw_id'])->value('keyword');
        Log::write($keyword);
        $rank_pc_res = $this->requestBaidu($keyword, 0, 0, 2, 'houxue');
        $rank_pc = isset($rank_pc_res[0]['rank_pc']) ? $rank_pc_res[0]['rank_pc'] : 100;
        Log::write($rank_pc);
        $rank_m_res = $this->requestBaidu($keyword, 1, 0, 2, 'houxue');
        $rank_m = isset($rank_m_res [0]['rank_m']) ? $rank_m_res [0]['rank_m'] : 100;
        Log::write($rank_m);
        // 更新task_detail
        $update_arr = [
            'old_rank_pc' => ($rank_pc == 21) ? 20 : $rank_pc,
            'old_rank_m' => ($rank_m == 21) ? 20 : $rank_m,
            'cal_status' => 1//计算完成
        ];
        try {
            Db::table('task_detail')->where('td_id', '=', $td['td_id'])->update($update_arr);
            Log::error($td['td_id'] . '已经抓取');
        } catch (Exception $e) {
            Log::write($e->getMessage());
        }
    }

    /**
     * 当天跑完：关键词排名 + url是否收录
     * @param $td
     */
    public function process_keyword_daily($td)
    {
        // 跑排名
        $keyword = Db::table('keywords')->where('kw_id', '=', $td['kw_id'])->value('keyword');
        Log::write($keyword);
        $rank_pc_res = $this->requestBaidu($keyword, 0, 0, 2, 'houxue');
        $rank_pc = isset($rank_pc_res[0]['rank_pc']) ? $rank_pc_res[0]['rank_pc'] : 100;
        $rank_pc_url = isset($rank_pc_res[0]['url_pc']) ? $rank_pc_res[0]['url_pc'] : '';
        Log::write($rank_pc);
        $rank_m_res = $this->requestBaidu($keyword, 1, 0, 2, 'houxue');
        $rank_m = isset($rank_m_res [0]['rank_m']) ? $rank_m_res [0]['rank_m'] : 100;
        $rank_m_url = isset($rank_m_res[0]['url_m']) ? $rank_m_res[0]['url_m'] : '';
        Log::write($rank_m);

        // 文章含的厚学url
        $urls = Db::table('keywords_article')->where('td_id', '=', $td['td_id'])->field('url_pc,url_m')->select();
        $urls_pc = Utility::arrayToSingleByIndex($urls, 'url_pc');
        $urls_m = Utility::arrayToSingleByIndex($urls, 'url_m');
        $rank_pc_new = 100;
        $rank_m_new = 100;
        if (in_array($rank_pc_url, $urls_pc)) {
            $rank_pc_new = $rank_pc;
        }
        if (in_array($rank_m_url, $urls_m)) {
            $rank_m_new = $rank_m;
        }
        // url收录
        $is_archive_pc = 0;
        $is_archive_m = 0;
        foreach ($urls_pc as $k1 => $v1) {
            if (BaiduArchive::check($v1) == 1) {
                $is_archive_pc = 1;
                break;
            } else {
                $is_archive_pc = 100;
            }
        }
        foreach ($urls_m as $k1 => $v1) {
            if (BaiduArchive::check_m($v1) == 1) {
                $is_archive_m = 1;
                break;
            } else {
                $is_archive_m = 100;
            }
        }
        // 更新task_detail
        $update_arr = [
            'new_rank_pc' => ($rank_pc_new == 21) ? 20 : $rank_pc_new,
            'new_rank_m' => ($rank_m_new == 21) ? 20 : $rank_m_new,
            'is_archive_pc' => $is_archive_pc,
            'is_archive_m' => $is_archive_m,
            'cal_status' => 1//计算完成
        ];
        // 插入关键词排名日志
        $insert_arr = [
            'kw_id' => $td['kw_id'],
            'keyword' => $keyword,
            'rank_pc' => ($rank_pc == 21) ? 20 : $rank_pc,
            'rank_m' => ($rank_m == 21) ? 20 : $rank_m,
            'create_time' => date('Y-m-d H:i:s', time())
        ];
        try {
            // 更新task_detail
            Db::table('task_detail')->where('td_id', '=', $td['td_id'])->update($update_arr);
            // 记录关键词排名日志
            Db::table('keywords_rank')->insert($insert_arr);
            Log::error($td['td_id'] . '已经抓取');
        } catch (Exception $e) {
            Log::write($e->getMessage());
        }
    }
}