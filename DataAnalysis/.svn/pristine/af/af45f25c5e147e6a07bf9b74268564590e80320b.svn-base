<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2018/3/10
 * Time: 14:01
 */

namespace app\index\model;

use app\common\Utility;
use think\Db;
use think\Exception;
use think\Log;

class TaskDetails extends Base
{

    protected $searchColumn = [
        'keyword' => ['k.keyword', 'like'],
        'userName' => ['u.user_name', 'like'],
        'taskTime' => ['td.task_time', 'between'],
        'status' => ['td.status', '='],
    ];

    public function getList()
    {
        $query = Db::table('task_detail td')
            ->join('users u', 'td.user_id = u.user_id', 'left')
            ->join('keywords k', 'k.kw_id = td.kw_id', 'left')
            ->join('categories c', 'k.c_id = c.c_id', 'left')
            ->join('keywords_label kl', 'k.kw_id = kl.kw_id and kl.type = 1', 'left')
            ->join('labels_school ls', 'ls.s_id = kl.l_id', 'left')
            ->field('td.*,u.user_name,k.keyword,c.catename category,ls.l_name');
        $this->checkSearch($query);
        $this->checkRange($query, 'u.dp_id', 'u.user_id');
        return $this->autoPaginate($query)->toArray();
    }

    public function getInputInfo($kw_id)
    {
        return Db::table('keywords k')
            ->join('keywords_material km', 'k.kw_id = km.kw_id', 'left')
            ->join('categories c', 'k.c_id = c.c_id', 'left')
            ->where('k.kw_id', '=', (int)$kw_id)->where('km.status', '=', 1)
            ->field('k.keyword,c.c_id,c.catename category,count(*) material_num,k.article_num')
            ->find();
    }

    public function inputSave($post)
    {
        $data = [
            'kw_id' => $post['kw_id'],
            'url' => '手动录入[' . ($post['material_num'] + 1) . ']' . rand(1, 10000),
            'title' => $post['title'],
            'author' => $post['author'],
            'cover_img' => $post['cover_img'],
            'content' => $post['content'],
            'type' => $post['type'],
            'area_id' => $post['area_id'],
            'points' => $post['points'],
            'add_time' => date('Y-m-d H:i:s'),
            'user_id' => session('org_user_id'),
        ];
        try {
            Db::startTrans();
            if (isset($post['km_id']) && $post['km_id'] > 0) {
                $km_id = $post['km_id'];
                Db::table('keywords_material')->where('km_id', '=', $post['km_id'])
                    ->update($data);
            } else {
                $km_id = Db::table('keywords_material')->insertGetId($data);
                Db::table('keywords')->where('kw_id', '=', $post['kw_id'])->update(['material_num' => ['exp', 'material_num + 1']]);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->errorLog($e)->setMessage('添加素材失败!');
            return false;
        }
        return $km_id;
    }


    public function articleSave($post)
    {
        $data = [
            'kw_id' => $post['kw_id'],
            'td_id' => $post['td_id'],
            'add_user_id' => session('org_user_id'),
            'add_dp_id' => session('org_dp_id'),
            'add_time' => date('Y-m-d H:i:s'),
            'title' => $post['title'],
            'author' => $post['author'],
            'cover_img' => $post['cover_img'],
            'content' => $post['content'],
            'type' => $post['type'],
            'area_id' => $post['area_id'],
            'points' => $post['points'],
        ];
        try {
            Db::startTrans();
            if (isset($post['ka_id']) && $post['ka_id'] > 0) {
                $ka_id = $post['ka_id'];
                Db::table('keywords_article')->where('ka_id', '=', $post['ka_id'])->update($data);
            } else {
                $ka_id = Db::table('keywords_article')->insertGetId($data);
                Db::table('keywords')->where('kw_id', '=', $post['kw_id'])->update(['article_num' => ['exp', 'article_num + 1']]);
                Db::table('task_detail')->where('td_id', '=', $post['td_id'])->update(['complete_num' => ['exp', 'complete_num + 1']]);
            }
            Db::table('keywords_article_log')->insert(['ka_id' => $ka_id, 'edit_time' => date('Y-m-d H:i:s')
                , 'edit_user_id' => session('org_user_id'), 'menu' => '素材录入保持并发布']);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->errorLog($e)->setMessage('保存文章失败');
            return false;
        }
        return $ka_id;
    }

    public function articleRelease($ka_id)
    {
        $url = 'http://api.houxue.com/jsonapi/insertinfo/insert';
        $article = Db::table('keywords_article ka')
            ->join('keywords k', 'k.kw_id = ka.kw_id', 'left')
            ->join('categories c', 'c.c_id = k.c_id', 'left')
            ->join('keywords_label kl', 'k.kw_id = kl.kw_id and kl.type = 1', 'left')
            ->join('labels_school ls', 'kl.l_id = ls.s_id', 'left')
            ->where('ka_id', '=', $ka_id)->field('ka.*,k.keyword,c.catename category,ls.l_name')->find();
        if (!empty($article)) {
            $data = [
                'title' => $article['title'],
                'type' => $article['type'],
                'keyword' => $article['keyword'],
                'imageid' => $article['cover_img'],
                'areaid' => $article['area_id'],
                'category' => $article['category'],
                'content' => $article['content'],
                'token' => $article['type'] == 1 ? $article['l_name'] : '',
                'jifen' => $article['type'] == 3 ? $article['points'] : 0,
            ];
            Log::error($data);

            // todo start ======先提交图片
            // 先提交图片,获取厚学端图片地址
            $cover_img = 'http://127.0.0.7/' . $article['cover_img'];
            // 图片数组
            $imgs_all = [];
            // 匹配img标签的正则，提取img_url为一个数组
//            $preg = '/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i';
            $preg = '/src[=:]\"http:\/\/.*?[(png)(jpg)(jpeg)(gif)(bmp)(GIF)]\"/';
            preg_match_all($preg, $article['content'], $article_imgs);
            // 若正文含图片链接
            if (!empty($article_imgs)) {
                foreach ($article_imgs[0] as $k => $v) {
                    $value = str_replace('"', '', $v);
                    $value = str_replace('src=', '', $value);
                    array_push($imgs_all, $value);
                }
            }
            array_push($imgs_all, $cover_img);
            // 要替换的正文副本
            $content_replace = $article['content'];
            // 厚学图片上传接口地址
            $hx_img_upload = 'http://api.houxue.com/tool/ajaxupload/image';
            foreach ($imgs_all as $k => $v) {
                $file = realpath($v); //要上传的文件
                $info = pathinfo($file);
                // 约定好的post体格式
                $fields = array(
                    'file_ext' => strtolower($info['extension']),
                    'is_persistent' => 'yes',
                    'file' => file_get_contents($file),
                    'imgname' => $info['filename']
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $hx_img_upload);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = json_decode(curl_exec($ch), true);
                if ($response['code'] == 200) {
                    $temp = json_decode($response['data'][0], true);
                    if (end($imgs_all) == $cover_img) {
                        $data['cover_img'] = $temp['imageid'];//传id
                    } else {
                        // todo: 替换第k条url
                        $content_replace = str_replace($imgs_all[$k], $temp['url'], $content_replace);
                    }
                }
            }
            $data['content'] = $content_replace;//传url，全局替换
            // todo end ======

            $result = Utility::httpRequest($url, $data, 'post');
            Log::write($result);
            $result = json_decode($result, 1);
            if (isset($result['code'])) {
                if ($result['code'] == 0) {
                    $this->setMessage('缺少参数');
                } elseif ($result['code'] == 1) {
                    $this->setMessage('关键词密度:' . $result['data']['kwd_density'] . ',文章相似度:' . $result['data']['similar_degree']);
                } else {
                    $this->setMessage('发布成功');
                    return true;
                }
            } else {
                $this->setMessage('接口请求错误');
            }
        }
        return false;
    }
}