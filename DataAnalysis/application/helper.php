<?php

use think\Db;
use think\session;

function getAllPermissions($user_id)
{
    $roles = Db::table('org_userroles')->where('UserID', $user_id)->column('RoleID');
    $permissions = Db::table('org_rolepermissions')->distinct(true)->field('PermissionID')->where('RoleID', 'in', $roles)->column('PermissionID');
    return $permissions;
}

function check($rule, $permissions)
{
    $p_id = Db::table('org_permissions')->where('Title', $rule)->value('ID');
    if ($p_id > 0) {
        return in_array($p_id, $permissions);
    } else {
        return true;
    }
}

function buttonDisable($permissions, $buttonRoutes)
{
    $user_id = Session('org_user_id');
    $res = [];
    foreach ($buttonRoutes as $buttonRoute) {
        $b = false;
        if ($user_id == 1) {
            $b = true;
        } else {
            $route_id = Db::table('org_permissions')->where('Title', $buttonRoute)->value('ID');
            if (in_array($route_id, $permissions)) {
                $b = true;
            }
        }
        $buttonRoute = str_replace('/', '', $buttonRoute);
        $res[$buttonRoute] = $b;
    }
    return $res;
}

//参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：是否为HTTPS，参数4：提交的$cookies,参数5：是否返回$cookies
function curl_request($url, $post = '', $https = false, $cookie = '', $returnCookie = 0)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
    // https请求 不验证证书和hosts
    if ($https) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    if ($post) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    if ($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
    curl_setopt($curl, CURLOPT_TIMEOUT, 600);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        return curl_error($curl);
    }
    curl_close($curl);
    if ($returnCookie) {
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie'] = substr($matches[1][0], 1);
        $info['content'] = $body;
        return $info;
    } else {
        return $data;
    }
}

/**
 * 获取单次重定向网址
 * @param $url
 * @return string
 */
function get_redirect_url($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $a = curl_exec($ch);
    $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return $url;
}