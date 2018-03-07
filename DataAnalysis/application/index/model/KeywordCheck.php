<?php

namespace app\index\model;

use app\common\Utility;
use app\index\controller\Zlog;
use think\Db;
use think\Exception;
use think\exception\DbException;
use think\Log;
use think\Model;
use think\Session;
use think\Request;
use app\common\Permission;

class KeywordCheck extends Base
{
    protected  $dailyTime = '';
    public  function getList($p)
    {
        $request = Request::instance()->post();
        $query_a = Db::table('keywords_check')->alias('a')
            ->field("b.keyword,a.*,u.user_name,b.add_time,c.catename as categories")
            ->join('keywords b','a.kw_id=b.kw_id','LEFT')
            ->join('users u','b.user_id = u.user_id','left')
            ->join('categories c','b.c_id = c.c_id','left')
            ->order('b.add_time','desc');
//        if(Session('org_user_id') != 1){
//            $query_a->where('b.user_id','=',(int)Session('org_user_id'));
//        }
        $this->checkRange($query_a,'u.dp_id','u.user_id');
        if ($request['search']['keyword'])
            $query_a->where('b.keyword','like','%'.$request['search']['keyword'].'%');
        if ($request['search']['user_name'])
            $query_a->where('b.user_name','like','%'.$request['search']['user_name'].'%');
        if ($request['search']['status']!=='')
            $query_a->where('a.status',$request['search']['status']);
        if ($request['search']['add_time']){
            $time[0] = $request['search']['add_time'][0];
            $time[1] = date("Y-m-d",strtotime("{$request['search']['add_time'][1]} +1 days"));
            $query_a->where('b.add_time','between',$time);
        }
        $query_b = clone $query_a;
        $pagesize = isset($request["page"]["pageSize"])?trim($request["page"]["pageSize"]):"50";
        $currentPage = isset($request["page"]["currentPage"])?trim($request["page"]["currentPage"]):"1";
        $result = $query_a->page($currentPage,$pagesize)->select();
        $total = $query_b->count();
        //剩余上传数量
//        $all_num = 5;
//        $alreay_num = Db::table('keywords')->where('user_id',Session::get('org_user_id'))->where('to_days(add_time)','to_days(now())')->count();
//        $needKeyword = ($all_num-$alreay_num>0)?$all_num-$alreay_num:0;
        $needKeyword = (new Mlexicon())->getKeywordsNum();
        $needKeyword = $needKeyword>0?$needKeyword:0;
        return [
            "tableData"=>$result,
            "total"=>$total,
            "needKeyword"=>$needKeyword
        ];
    }



    public function editSave()
    {
        $req = Request::instance()->post();
        if (!$req['check_type'])
            return ['type'=>'error','msg'=>'请选择审核结果！'];
        if ($req['check_type']==2 && !$req['memo'])
            return ['type'=>'error','msg'=>'请填写拒绝原因！'];
        try{
            $kw_id = Db::table('keywords_check')->where('id','=',$req['id'])->value('kw_id');
            $uncheckinfo = Db::table("keywords_uncheck")->where("kw_id","=",$kw_id)->find();
            if ($req['check_type'] != 2) {
                switch ($uncheckinfo['type']){
                    case 0:
                        break;
                    case 1:
                        Db::table("keywords")->where("kw_id",$uncheckinfo['kw_id'])->update(['c_id'=>$uncheckinfo['c_id'],"update_time"=>date("Y-m-d H:i:s")]);
                        $keyword_label_result = Db::table("keywords_label")->where("kw_id","=",$uncheckinfo['kw_id'])->select();
                        $tagsarr = unserialize($uncheckinfo['tags']);
                        if (empty($tagsarr)) {
                            if (!empty($keyword_label_result)) {
                                $id = [];
                                foreach ($keyword_label_result as $key=>$value) {
                                    $id[] = $value['id'];
                                }
                                Db::table("keywords_label")->delete($id);
                            }
                        } else {
                            if (!empty($keyword_label_result)) {
                                $id = [];
                                foreach ($keyword_label_result as $key=>$value) {
                                    $id[] = $value['id'];
                                }
                                Db::table("keywords_label")->delete($id);
                            }
                            Db::table("keywords_label")->insertAll($tagsarr);
                        }
                        break;
                    case 3:
                        Db::table("keywords")->where("kw_id",$uncheckinfo['kw_id'])->update(['is_show'=>"1","update_time"=>date("Y-m-d H:i:s")]);
                        break;
                }
            }
            Db::table("keywords_uncheck")->where("kw_id","=",$kw_id)->delete();
            $arr = ['status'=>$req['check_type'],'user_id'=>Session::get('org_user_id'),'check_time'=>date('Y-m-d'),'memo'=>$req['memo']];
            Db::table('keywords_check')->where('id','=',$req['id'])->update($arr);
            Utility::keywordsLog($kw_id,2,'审核：'.$req['memo']);
        } catch (\Exception $e) {
            return ['type'=>'error','msg'=>'数据库操作失败！'];
        }
        return ['type'=>'success','msg'=>'操作成功！'];
    }

    public function checkMany()
    {
        $req = Request::instance()->post();
        if (!$req['id'])
            return ['type'=>'error','msg'=>'请勾选要批量通过的方框！'];
        try{
            $kw_id = Db::table('keywords_check')->where('id','in',$req['id'])->column('kw_id');
            $arr = ['status'=>1,'user_id'=>Session::get('org_user_id'),'check_time'=>date('Y-m-d'),'memo'=>''];
            Db::table('keywords_check')->where('id','in',$req['id'])->update($arr);
            Utility::keywordsLog($kw_id,2,'批量审核');
            foreach ($kw_id as $list=>$info) {
                $uncheckinfo = Db::table("keywords_uncheck")->where("kw_id","=",$info)->find();
                switch ($uncheckinfo['type']){
                    case 0:
                        break;
                    case 1:
                        Db::table("keywords")->where("kw_id",$uncheckinfo['kw_id'])->update(['c_id'=>$uncheckinfo['c_id'],"update_time"=>date("Y-m-d H:i:s")]);
                        $keyword_label_result = Db::table("keywords_label")->where("kw_id","=",$uncheckinfo['kw_id'])->select();
                        $tagsarr = unserialize($uncheckinfo['tags']);
                        if (empty($tagsarr)) {
                            if (!empty($keyword_label_result)) {
                                $id = [];
                                foreach ($keyword_label_result as $key=>$value) {
                                    $id[] = $value['id'];
                                }
                                Db::table("keywords_label")->delete($id);
                            }
                        } else {
                            if (!empty($keyword_label_result)) {
                                $id = [];
                                foreach ($keyword_label_result as $key=>$value) {
                                    $id[] = $value['id'];
                                }
                                Db::table("keywords_label")->delete($id);
                            }
                            Db::table("keywords_label")->insertAll($tagsarr);
                        }
                        break;
                    case 3:
                        Db::table("keywords")->where("kw_id",$uncheckinfo['kw_id'])->update(['is_show'=>"1","update_time"=>date("Y-m-d H:i:s")]);
                        break;
                }
                Db::table("keywords_uncheck")->where("kw_id","=",$info)->delete();
            }
        } catch (\Exception $e) {
            return ['type'=>'error','msg'=>'数据库操作失败！'];
        }
        return ['type'=>'success','msg'=>'操作成功！'];
    }

    public function exportdaily()
    {
        ini_set('memory_limit', '1024M');
        $this->handle();
        return true;
    }
    private function handle()
    {
//        $csv_header = ['姓名', '部门', '任务数量', '添加数量',];
        $csv_body = [];
        $users = Db::query('SELECT a.user_id,a.user_name,b.dp_name,max(d.KeywordNum) as maxKeyNum from users as a
LEFT JOIN departments as b on a.dp_id=b.dp_id
LEFT JOIN org_userroles as c on a.user_id=c.UserID
LEFT JOIN org_roles as d on c.RoleID=d.ID
GROUP BY user_id');
        foreach ($users as $user){
                $tmp=[];
                $tmp[] = $user['user_name'];
                $tmp[] = $user['dp_name'];
                if ($user['maxKeyNum']==0){
                    $tmp[] = '无限制';
                }else{
                    $tmp[] = $user['maxKeyNum'];
                }
                $num = Db::query("SELECT COUNT(*) from keywords
where user_id=? AND date_format(add_time,'%Y-%m-%d')=?",[$user['user_id'],$this->dailyTime]);
                $tmp[] = $num[0]['COUNT(*)'];
                $csv_body[] = $tmp;
        }
        $title_name = "<tr><th colspan='2'>关键词审核-审核日报</th></tr>
<tr>
<th>姓名</th>
<th>部门</th>
<th>任务数量</th>
<th>添加数量</th>
</tr>";
        $str = "<html xmlns:o='urn:schemas-microsoft-com:office:office'\r\nxmlns:x='urn:schemas-microsoft-com:office:excel'\r\nxmlns='http://www.w3.org/TR/REC-html40'>\r\n<head>\r\n<meta http-equiv=Content-Type content='text/html; charset=utf-8'>\r\n</head>\r\n<body>";
        $str .= "<table border=1><thead>".$title_name."</thead>";
        foreach ($csv_body as $value) {
            $str .= "<tr>";
            foreach ($value as $item) {
                $str .= "<td>".$item."</td>";
            }
            $str .= "</tr>\n";
        }
        $str .= "</table></body></html>";
        header("Content-Type: application/vnd.ms-excel; name='excel'");
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename= keywordCheck_" . $this->dailyTime.".xls");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        exit( $str );
    }


    /**
     * @return string
     */
    public function getDailyTime()
    {
        return $this->dailyTime;
    }

    /**
     * @param string $dailyTime
     */
    public function setDailyTime($dailyTime)
    {
        $this->dailyTime = $dailyTime;
    }

}
