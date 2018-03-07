<?php
namespace app\index\model;
use app\index\controller\Zlog;
use think\Db;
use think\Log;
use think\Request;
use think\Session;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/11
 * Time: 10:18
 */
class Mobserve extends Base
{
    protected $keywordData = [
        'value' => '',
        'pcData' => ['overallScore'=>"",'baiduRank'=>"",'searchVolume'=>"",'rankPage'=>"",'tips'=>"",],
        'mData' => ['overallScore'=>"",'baiduRank'=>"",'searchVolume'=>"",'rankPage'=>"",'tips'=>"",],
        'promotion' => ['score'=>30,'data'=>[['name'=>"学校","value"=>"","state"=>""],['name'=>"课程","value"=>"","state"=>""],['name'=>"新闻","value"=>"","state"=>""],['name'=>"头条","value"=>"","state"=>""],['name'=>"知道","value"=>"","state"=>""],]],
        'include' => ['score'=>40,'data'=>[['name'=>"学校","value"=>"","state"=>""],['name'=>"课程","value"=>"","state"=>""],['name'=>"新闻","value"=>"","state"=>""],['name'=>"头条","value"=>"","state"=>""],['name'=>"知道","value"=>"","state"=>""],]],
        'tableData' => [["id"=>0,"url"=>"www.houxue.com","title"=>"南京雅思学校培训","number"=>10,"density"=>"2.13%","isInclude"=>"是"]],
    ];
    protected $score = ['1'=>"完美推广",'2'=>"一步之遥",'3'=>"初见成效",'4'=>"无效推广",'5'=>"毫无推广"];
    protected $promotionlistarr = ["school"=>"1","course"=>"15","news"=>"30","topnews"=>"30","know"=>"15"];
    public function getkeyWordsInfo()
    {
        $request = Request::instance()->post();
        $comapredKeyword = empty($request['searchData']['comapredKeyword'])?[]:$request['searchData']['comapredKeyword'];
        $type = !empty($request['type'])?$request['type']:"false";
        $changeType = !empty($request['title'])?trim($request['title']):"学校";
        if ($type === true) {
            $keyword = !empty($request['keyword'])?$request['keyword']:"";
        } else {
            $keyword = !empty($request['searchData']['keyword'])?trim($request['searchData']['keyword']):"";
        }
        $arr = array_merge([$keyword],$comapredKeyword);
        $changeKeyword = !empty($request['changeKeyword'])?trim($request['changeKeyword']):$keyword;
        $return_arr = [];
        $result = $this->getkeyWordsFilter()->select();
        if (count($arr) !== count($result)) {
            return ["type"=>false,"info"=>$return_arr];
        }
        if (empty($comapredKeyword) OR $type === true) {
            $geturlInfo = $this->getUrlInfo($changeType,$changeKeyword,"");
            $geturlInfo_new = $this->getUrlInfo("",$changeKeyword,"");
            $includeInfo = $this->getincludeInfo($result[0]);
            $promotionInfo = $this->getpromotionInfo($result[0]);
            $pagename = self::getPageName($result[0]);
            $socre_pc = isset($this->score[$result[0]['score_pc']])?$this->score[$result[0]['score_pc']]:"毫无推广";
            $socre_m = isset($this->score[$result[0]['score_m']])?$this->score[$result[0]['score_m']]:"毫无推广";
            $return_arr['value'] = $result[0]['keyword'];
            $return_arr['update_time'] = $result[0]['update_time'];
            $return_arr['pcData']["overallScore"] = $socre_pc;
            $return_arr['mData']["overallScore"] = $socre_m;
            $return_arr['pcData']["baiduRank"] = $result[0]['baidu_rank_pc'];
            $return_arr['mData']["baiduRank"] = $result[0]['baidu_rank_m'];
            $return_arr['pcData']["searchVolume"] = (int)$result[0]['num_pc'];
            $return_arr['mData']["searchVolume"] = (int)$result[0]['num_m'];
            $return_arr['pcData']["include"] = $includeInfo['pcData'];
            $return_arr['mData']["include"] = $includeInfo['mData'];
            $return_arr['pcData']["promotion"] = $promotionInfo['pc'];
            $return_arr['mData']["promotion"] = $promotionInfo['m'];
            $return_arr['pcData']["tableData"] = $geturlInfo['PC'];
            $return_arr['mData']["tableData"] = $geturlInfo['M'];
            $return_arr['pcData']["rankPage"] = $pagename['pc'];
            $return_arr['mData']["rankPage"] = $pagename['m'];
            $return_arr['pcData']["tips"] = $this->getOptimizationSuggestions($socre_pc,$geturlInfo_new['PC'],$promotionInfo['pc']['type'],$includeInfo['pcData']['type'],$result[0]['is_hightrans_pc'],$result[0]['is_cooperate_pc'],"pc");
            $return_arr['mData']["tips"] = $this->getOptimizationSuggestions($socre_m,$geturlInfo_new['M'],$promotionInfo['m']['type'],$includeInfo['mData']['type'],$result[0]['is_hightrans_m'],$result[0]['is_cooperate_m'],'m');
        } else {
            $num = count($result);
            for ($i=0;$i<$num;$i++) {
                $socre_pc = isset($this->score[$result[$i]['score_pc']])?$this->score[$result[$i]['score_pc']]:"毫无推广";
                $socre_m = isset($this->score[$result[$i]['score_m']])?$this->score[$result[$i]['score_m']]:"毫无推广";
                $return_arr[$i]['value'] = $result[$i]['keyword'];
                $return_arr[$i]['update_time'] = $result[$i]['update_time'];
                $return_arr[$i]['pc']["overallScore"] = $socre_pc;
                $return_arr[$i]['m']["overallScore"] = $socre_m;
                $return_arr[$i]['pc']["baiduRank"] = (int)$result[$i]['baidu_rank_pc'];
                $return_arr[$i]['m']["baiduRank"] = (int)$result[$i]['baidu_rank_m'];
                $return_arr[$i]['pc']["searchVolume"] = (int)$result[$i]['num_pc'];
                $return_arr[$i]['m']["searchVolume"] = (int)$result[$i]['num_m'];
                $return_arr[$i]['pc']["promotionIndex"] = (int)$this->keywordData['promotion']["score"];
                $return_arr[$i]['m']["promotionIndex"] = (int)$this->keywordData['promotion']["score"];
                $return_arr[$i]['pc']["includeIndex"] = (int)$this->keywordData['include']["score"];
                $return_arr[$i]['m']["includeIndex"] = (int)$this->keywordData['include']["score"];
            }
        }
        return ["type"=>true,"info"=>$return_arr];
    }

    public function getkeyWordsFilter()
    {
        $request = Request::instance()->post();
        $comapredKeyword = empty($request['searchData']['comapredKeyword'])?[]:$request['searchData']['comapredKeyword'];
        $type = !empty($request['type'])?$request['type']:"false";
        if ($type === true) {
            $keyword = !empty($request['keyword'])?$request['keyword']:"";
        } else {
            $keyword = empty($request['searchData']['keyword'])?"":$request['searchData']['keyword'];
        }
        if (empty($comapredKeyword) OR $type === true) {
            $query = Db::table("keywords k")->where("k.keyword",$keyword)
                ->field("k.keyword,k.url_pc,k.url_m,k.score_pc,k.score_m,k.is_cooperate_pc,k.is_cooperate_m,k.is_hightrans_pc,k.is_hightrans_m,k.update_time,baidu_rank_pc,baidu_rank_m,baidu_index_pc,baidu_index_m,news_index as news_index_pc,mnews_index as news_index_m,
                school_index as school_index_pc,mschool_index as school_index_m,course_index as course_index_pc,mcourse_index as course_index_m,
                SUM(k.kws_count_pc) AS num_pc,SUM(k.kws_count_m) AS num_m,
                zhidao_index as zhidao_index_pc,mzhidao_index as zhidao_index_m,hotnews_index as hotnews_index_pc,mhotnews_index as hotnews_index_m");
        } else {
            $arr = array_merge([$keyword],$comapredKeyword);
            $query = Db::table("keywords k")
                ->field("k.keyword,k.url_pc,k.url_m,k.score_pc,k.score_m,k.is_cooperate_pc,k.is_cooperate_m,k.is_hightrans_pc,k.is_hightrans_m,k.update_time,baidu_rank_pc,baidu_rank_m,baidu_index_pc,baidu_index_m,news_index as news_index_pc,mnews_index as news_index_m,
                school_index as school_index_pc,mschool_index as school_index_m,course_index as course_index_pc,mcourse_index as course_index_m, 
                SUM(k.kws_count_pc) AS num_pc,SUM(k.kws_count_m) AS num_m,
                zhidao_index as zhidao_index_pc,mzhidao_index as zhidao_index_m,hotnews_index as hotnews_index_pc,mhotnews_index as hotnews_index_m");
            $query = $this->createquery($query,"k.keyword",$arr);
            $query = $query->group("k.kw_id");
        }
//        if(Session('org_user_id') != 1){
//            $query->where('k.user_id','=',Session('org_user_id'));
//        }
        $query->join('users u','k.user_id = u.user_id','left');
        $this->checkRange($query,'u.dp_id','u.user_id');
        return $query;
    }
    /**
     * @获取优化建议
     * @param $overviewName综合评价
     * @param bool $density密度是否达标
     * @param bool $promotion推广是否达标
     * @param bool $included收录是否达标
     * @param bool $moreconversion是否是高转化页面
     * @param bool $cooperation是否是合作相关
     * @return array
     */
    public function getOptimizationSuggestions($overviewName,$density=true,$promotion=true,$included=true,$moreconversion=true,$cooperation=true,$dstype="pc")
    {
        $optimization = "";
        $density_assess = $this->judgmentDensity($density,$dstype);
        $promotion_assess = $this->judgmentPromotion($promotion);
        $included_assess = $this->judgmentIncluded($included);
        if ($overviewName === "完美推广") {
            $optimization[] = "完美推广，请定期查询收录，防止收录吐掉";
        } elseif($overviewName === "一步之遥") {
            $optimization[] = "马上就可以抢占首页，调整内容密度，保持丰富的站内外链接，提高页面的点击量";
            $optimization = array_merge($promotion_assess['optimization'],$included_assess['optimization'],$density_assess['optimization']);
        } elseif ($overviewName === "初见成效") {
            $optimization[] = "马上就可以抢占首页，调整内容密度，保持丰富的站内外链接，提高页面的点击量";
            $optimization = array_merge($promotion_assess['optimization'],$included_assess['optimization'],$density_assess['optimization']);
        } elseif ($overviewName === "无效推广") {
            $optimization[] = "马上就可以抢占首页，调整内容密度，保持丰富的站内外链接，提高页面的点击量";
            if($cooperation == 2) {
                $optimization[] = "目前排名的学校页面已经是非合作学校，请马上调整为合作学校，避免名单丢失";
            }
            if($moreconversion == 2) {
                $optimization[] = "目前排名页面不是高转化的核心页面，请马上调整方向";
            }
            $optimization = array_merge($promotion_assess['optimization'],$included_assess['optimization'],$density_assess['optimization']);
        } elseif ($overviewName === "毫无推广") {
            $optimization[] = "该关键词目前完全没有排名，请抓紧安排你的推广工作";
            $optimization = array_merge($promotion_assess['optimization'],$included_assess['optimization'],$density_assess['optimization']);

        } else{
            $optimization[] = "暂无评价";
        }
        return $optimization;
    }

    public function getpromotionInfo($result)
    {
        $promotionlistarr = ["school"=>"1","course"=>"15","news"=>"30","topnews"=>"30","know"=>"15"];
        $result_arr = [];
        $school_pc = $result['school_index_pc'] >= $promotionlistarr['school']  ? 30 : round($result['school_index_pc'] / $promotionlistarr['school'] * 30);
        $course_pc = $result['course_index_pc'] >= $promotionlistarr['course']  ? 15 : round($result['course_index_pc'] / $promotionlistarr['course'] * 15);
        $news_pc = $result['news_index_pc'] >= $promotionlistarr['news']  ? 15 : round($result['news_index_pc'] / $promotionlistarr['news'] * 15);
        $hotnews_pc = $result['hotnews_index_pc'] >= $promotionlistarr['topnews']  ? 15 : round($result['hotnews_index_pc'] / $promotionlistarr['topnews'] * 15);
        $zhidao_pc = $result['zhidao_index_pc'] >= $promotionlistarr['know'] ? 15 : round($result['zhidao_index_pc'] / $promotionlistarr['know'] * 15);
        $result_arr['pc']['score'] = $school_pc + $course_pc + $news_pc + $hotnews_pc + $zhidao_pc;
        $result_arr['pc']['type'] = ['school'=>$result['school_index_pc'],'course'=>$result['course_index_pc'],'news'=>$result['news_index_pc'],'topnews'=>$result['hotnews_index_pc'],'know'=>$result['zhidao_index_pc'],];
        $result_arr['pc']['data'][] = ["name"=>"学校","value"=>"{$result['school_index_pc']}/{$promotionlistarr['school']}","state"=>$result['school_index_pc'] >= $promotionlistarr['school'] ?"已达标":"未达标"];
        $result_arr['pc']['data'][] = ["name"=>"课程","value"=>"{$result['course_index_pc']}/{$promotionlistarr['course']}","state"=>$result['course_index_pc'] >= $promotionlistarr['course'] ?"已达标":"未达标"];
        $result_arr['pc']['data'][] = ["name"=>"新闻","value"=>"{$result['news_index_pc']}/{$promotionlistarr['news']}","state"=>$result['news_index_pc'] >= $promotionlistarr['news'] ?"已达标":"未达标"];
        $result_arr['pc']['data'][] = ["name"=>"头条","value"=>"{$result['hotnews_index_pc']}/{$promotionlistarr['topnews']}","state"=>$result['hotnews_index_pc'] >= $promotionlistarr['topnews'] ?"已达标":"未达标"];
        $result_arr['pc']['data'][] = ["name"=>"知道","value"=>"{$result['zhidao_index_pc']}/{$promotionlistarr['know']}","state"=>$result['zhidao_index_pc'] >= $promotionlistarr['know'] ?"已达标":"未达标"];
        $school_m = $result['school_index_m'] > $promotionlistarr['school'] ? 30 : $result['school_index_m'] / $promotionlistarr['school'] * 30;
        $course_m = $result['course_index_m'] > $promotionlistarr['course'] ? 15 : $result['course_index_m'] / $promotionlistarr['course'] * 15;
        $news_m = $result['news_index_m'] > $promotionlistarr['news'] ? 15 : $result['news_index_m'] / $promotionlistarr['news'] * 15;
        $hotnews_m = $result['hotnews_index_m'] > $promotionlistarr['topnews'] ? 15 : $result['hotnews_index_m'] / $promotionlistarr['topnews'] * 15;
        $zhidao_m = $result['zhidao_index_m'] > $promotionlistarr['know'] ? 15 : $result['zhidao_index_m'] / $promotionlistarr['know'] * 15;
        $result_arr['m']['score'] = round($school_m + $course_m + $news_m + $hotnews_m + $zhidao_m);
        $result_arr['m']['type'] = ['school'=>$result['school_index_m'],'course'=>$result['course_index_m'],'news'=>$result['news_index_m'],'topnews'=>$result['hotnews_index_m'],'know'=>$result['zhidao_index_m'],];
        $result_arr['m']['data'][] = ["name"=>"学校","value"=>"{$result['school_index_m']}/{$promotionlistarr['school']}","state"=>$result['school_index_m'] >= $promotionlistarr['school'] ?"已达标":"未达标"];
        $result_arr['m']['data'][] = ["name"=>"课程","value"=>"{$result['course_index_m']}/{$promotionlistarr['course']}","state"=>$result['course_index_m'] >= $promotionlistarr['course'] ?"已达标":"未达标"];
        $result_arr['m']['data'][] = ["name"=>"新闻","value"=>"{$result['news_index_m']}/{$promotionlistarr['news']}","state"=>$result['news_index_m'] >= $promotionlistarr['news'] ?"已达标":"未达标"];
        $result_arr['m']['data'][] = ["name"=>"头条","value"=>"{$result['hotnews_index_m']}/{$promotionlistarr['topnews']}","state"=>$result['hotnews_index_m'] >= $promotionlistarr['topnews'] ?"已达标":"未达标"];
        $result_arr['m']['data'][] = ["name"=>"知道","value"=>"{$result['zhidao_index_m']}/{$promotionlistarr['know']}","state"=>$result['zhidao_index_m'] >= $promotionlistarr['know'] ?"已达标":"未达标"];

        return $result_arr;
    }

    public function getincludeInfo($result)
    {
        $new_result = [];
        $xuexiao_pc = $ask_pc = $kecheng_pc = $news_pc = $topnews_pc = $xuexiao_m = $ask_m = $kecheng_m = $news_m = $topnews_m = 0;
        $includelistarr = ["school"=>"1","course"=>"15","news"=>"30","topnews"=>"30","know"=>"15"];
        $data = Db::table("keywords_detail kd") ->join("keywords k","kd.kw_id  = k.kw_id",'LEFT')
            ->field("CASE WHEN kd.dstype = 1 THEN 'PC' ELSE 'M' END AS dstype,kd.type,kd.title,kd.url,keyword,news_index as news_index_pc,mnews_index as news_index_m,
                school_index as school_index_pc,mschool_index as school_index_m,course_index as course_index_pc,mcourse_index as course_index_m,
                zhidao_index as zhidao_index_pc,mzhidao_index as zhidao_index_m,hotnews_index as hotnews_index_pc,mhotnews_index as hotnews_index_m")
        ->where("k.keyword",$result['keyword'])->where("kd.is_alive","1")->select();
        foreach ($data as $key=>$value) {
            if(stristr($value['url'],"://www.houxue.com")){
                if (stristr($value['url'],"/xuexiao")) {
                    $xuexiao_pc += 1;
                } elseif (stristr($value['url'],"/ask")) {
                    $ask_pc += 1;
                } elseif (stristr($value['url'],"/kecheng")) {
                    $kecheng_pc += 1;
                }elseif (stristr($value['url'],"/news")) {
                    $topnews_pc += 1;
                }elseif (stristr($value['url'],"/news") && stristr($value['url'],".html")) {
                    $news_pc += 1;
                }
            } elseif (stristr($value['url'],"://m.houxue.com")) {
                if (stristr($value['url'],"/xuexiao")) {
                    $xuexiao_m += 1;
                } elseif (stristr($value['url'],"/ask")) {
                    $ask_m += 1;
                } elseif (stristr($value['url'],"/kecheng")) {
                    $kecheng_m += 1;
                }elseif (stristr($value['url'],"/news")) {
                    $topnews_m += 1;
                }elseif (stristr($value['url'],"/news") && stristr($value['url'],".html")) {
                    $news_m += 1;
                }
            } else{
                continue;
            }
        }
        $promotion = $this->getpromotionInfo($result);
        $promotion_pc = $promotion['pc'];
        $promotion_m = $promotion['m'];
        $new_result['pcData']['score'] = $promotion_pc['score'] == 0 ? 0 :round(($news_pc+$topnews_pc+$ask_pc+$xuexiao_pc+$kecheng_pc) / $promotion_pc['score']);
        $new_result['mData']['score'] = $promotion_m['score'] == 0 ? 0 :round(($news_m+$topnews_m+$ask_m+$xuexiao_m+$kecheng_m) / $promotion_m['score']);
        $new_result['pcData']['type'] = ["school"=>$xuexiao_pc,"course"=>$kecheng_pc,"news"=>$news_pc,"topnews"=>$topnews_pc,"know"=>$ask_pc,];
        $new_result['mData']['type'] = ["school"=>$xuexiao_m,"course"=>$kecheng_m,"news"=>$news_m,"topnews"=>$topnews_m,"know"=>$ask_m,];
        $new_result['pcData']['data'][] = ["name"=>"学校","value"=>"{$xuexiao_pc}/{$includelistarr['school']}","state"=>$xuexiao_pc >= $includelistarr['school'] ?"已达标":"未达标"];
        $new_result['pcData']['data'][] = ["name"=>"课程","value"=>"{$kecheng_pc}/{$includelistarr['course']}","state"=>$kecheng_pc >= $includelistarr['course'] ?"已达标":"未达标"];
        $new_result['pcData']['data'][] = ["name"=>"新闻","value"=>"{$news_pc}/{$includelistarr['news']}","state"=>$news_pc >= $includelistarr['news'] ?"已达标":"未达标"];
        $new_result['pcData']['data'][] = ["name"=>"头条","value"=>"{$topnews_pc}/{$includelistarr['topnews']}","state"=>$topnews_pc >= $includelistarr['topnews'] ?"已达标":"未达标"];
        $new_result['pcData']['data'][] = ["name"=>"知道","value"=>"{$ask_pc}/{$includelistarr['know']}","state"=>$ask_pc >= $includelistarr['know'] ?"已达标":"未达标"];
        $new_result['mData']['data'][] = ["name"=>"学校","value"=>"{$xuexiao_m}/{$includelistarr['school']}","state"=>$xuexiao_m >= $includelistarr['school'] ?"已达标":"未达标"];
        $new_result['mData']['data'][] = ["name"=>"课程","value"=>"{$kecheng_m}/{$includelistarr['course']}","state"=>$kecheng_m >= $includelistarr['course'] ?"已达标":"未达标"];
        $new_result['mData']['data'][] = ["name"=>"新闻","value"=>"{$news_m}/{$includelistarr['news']}","state"=>$news_m >= $includelistarr['news'] ?"已达标":"未达标"];
        $new_result['mData']['data'][] = ["name"=>"头条","value"=>"{$topnews_m}/{$includelistarr['topnews']}","state"=>$topnews_m >= $includelistarr['topnews'] ?"已达标":"未达标"];
        $new_result['mData']['data'][] = ["name"=>"知道","value"=>"{$ask_m}/{$includelistarr['know']}","state"=>$ask_m >= $includelistarr['know'] ?"已达标":"未达标"];
        return $new_result;
    }

    public function getUrlInfo($type,$keyword,$dstype="")
    {
        $new_result = [];
        $typearr = ['学校'=>1,'知道'=>5,'头条'=>4,'新闻'=>3,'课程'=>2,];
        $dstypearr = ['pc'=>1,'m'=>2];
        $query = Db::table("keywords_detail kd") ->join("keywords k","kd.kw_id  = k.kw_id",'LEFT')
            ->field("k.keyword,kd.url,kd.type,kd.id,kd.title,k.is_cooperate_pc,k.is_cooperate_m,kd.kw_num as number,CONCAT(FLOOR((kd.density *100)),'%')  AS density,CASE WHEN kd.is_alive = 1 THEN '已收录' WHEN kd.is_alive = 2 THEN '已提交' ELSE '未收录' END AS isInclude,CASE WHEN kd.dstype = 1 THEN 'PC' ELSE 'M' END AS dstype")
            ->where("k.keyword",$keyword);
        if (isset($typearr[$type])) {
            $query = $query->where("type",$typearr[$type]);
        }
        if ($dstype != "") {
            $result = $query->where("kd.dstype",$dstypearr[$dstype])->select();
        } else {
            $result = $query->select();
        }
        foreach ($result as $key=>$value) {
            $new_result[$value['dstype']][] = $value;
        }
        if (empty($new_result)) {
            return ["PC"=>[],"M"=>[]];
        } else {
            return $new_result;
        }
    }


    /**
     * @判断根据推广是否达标获取评价
     * @param $promotion
     * @return array
     */
    public function judgmentPromotion($promotion)
    {
        $optimization = [];
        if (!empty($promotion)) {
            if ($promotion['school'] < $this->promotionlistarr['school']) {
                $optimization[] = "学校页面推广内容过少，未达到目标量，请抓紧完善";
            }
            if ($promotion['course'] < $this->promotionlistarr['course']) {
                $optimization[] = "课程页面推广内容过少，未达到目标量，请抓紧完善";
            }
            if ($promotion['news'] < $this->promotionlistarr['news']) {
                $optimization[] = "新闻页面推广内容过少，未达到目标量，请抓紧完善";
            }
            if ($promotion['topnews'] < $this->promotionlistarr['topnews']) {
                $optimization[] = "头条页面推广内容过少，未达到目标量，请抓紧完善";
            }
            if ($promotion['know'] < $this->promotionlistarr['know']) {
                $optimization[] = "知道页面推广内容过少，未达到目标量，请抓紧完善";
            }
            return ['type'=>false,'optimization'=>$optimization];
        } else {
            return ['type'=>true,'optimization'=>$optimization];
        }
    }

    /**
     * @判断根据收录是否达标获取评价
     * @param $promotion
     * @return array
     */
    public function judgmentIncluded($included)
    {
        $optimization = [];
        if (!empty($included)) {
            if ($included['school'] < $this->promotionlistarr['school']) {
                $optimization[] = "学校页面收录页面太差，未达到全收录，请不断提交";
            }
            if ($included['course'] < $this->promotionlistarr['course']) {
                $optimization[] = "课程页面收录页面太差，未达到全收录，请不断提交";
            }
            if ($included['news'] < $this->promotionlistarr['news']) {
                $optimization[] = "新闻页面收录页面太差，未达到全收录，请不断提交";
            }
            if ($included['topnews'] < $this->promotionlistarr['topnews']) {
                $optimization[] = "头条页面收录页面太差，未达到全收录，请不断提交";
            }
            if ($included['know'] < $this->promotionlistarr['know']) {
                $optimization[] = "知道页面收录页面太差，未达到全收录，请不断提交";
            }
            return ['type'=>false,'optimization'=>$optimization];
        } else {
            return ['type'=>true,'optimization'=>$optimization];
        }
    }
    /**
     * @判断根据密度是否达标获取评价
     * @param $promotion
     * @return array
     */
    public function judgmentDensity($density,$dstype)
    {
        $type = ['1'=>"学校",'2'=>"课程",'3'=>"新闻",'4'=>"头条",'5'=>"知道"];
        $typearr = [];
        $optimization = [];
        if (!empty($density)) {
            foreach ($density as $key=>$value) {
                if ((int)$value['density'] *100 < 200){
                    $typearr[] = $value['type'];
                }
                if ($dstype == "pc") {
                    if ((int)$value['is_cooperate_pc'] == 2){
                        $typearr[] = $value['type'];
                    }
                } else {
                    if ((int)$value['is_cooperate_m'] == 2){
                        $typearr[] = $value['type'];
                    }
                }
            }
            $typearr = array_unique($typearr);
            foreach ($typearr as $key=>$value) {
                $optimization[] = "{$type[$value]}页面存在密度不达标现象，请调整密度至2%至-4%";
            }
            return ['type'=>false,'optimization'=>$optimization];
        } else {
            return ['type'=>true,'optimization'=>$optimization];
        }
    }

    public static function getPageName($result)
    {
        $pagename['pc'] = $pagename['m'] = "";
        $pagename['pc'] = self::changeUrlname($result['url_pc']);
        $pagename['m'] = self::changeUrlname($result['url_m']);
        if ($result['is_cooperate_pc'] == 1) {
            $pagename['pc'] .= "--合作";
        } else {
            $pagename['pc'] .= "--非合作";
        }
        if ($result['is_cooperate_m'] == 1) {
            $pagename['m'] .= "--合作";
        } else {
            $pagename['m'] .= "--非合作";
        }
        return $pagename;
    }

    public static function changeUrlname($url)
    {
        if(stristr($url,"://www.houxue.com")) {
            if (stristr($url, "/xuexiao")) {
                $pagename = "学校";
            } elseif (stristr($url, "/ask")) {
                $pagename = "知道";
            } elseif (stristr($url, "/kecheng")) {
                $pagename = "课程";
            } elseif (stristr($url, "/news")) {
                $pagename = "头条";
            } elseif (stristr($url, "/news") && stristr($url, ".html")) {
                $pagename = "新闻";
            } else {
                $pagename = "未知页面";
            }
        } else {
            $pagename = "未知页面";
        }
        return $pagename;
    }

    public function included()
    {
        $request = Request::instance()->post();
        $userid = Session::get("org_user_id");
        $id = !empty($request['id'])?$request['id']:0;
        $url = !empty($request['url'])?$request['url']:"";
        if ($id === 0 OR $url === "") {
            return ['type'=>false,"info"=>"未获取到需要收录的关键词"];
        }
        Db::startTrans();
        try{
            Db::table("keywords_detail")->where("id","=",$id)->update(['is_alive'=>2]);
            Db::table("keywords_apply")->insert(['kd_id'=>$id,'url'=>$url,"apply_user"=>$userid,"add_time"=>date("Y-m-d H:i:s")]);
            Db::commit();
            return ['type'=>true,"info"=>"收录成功！"];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
    }
}