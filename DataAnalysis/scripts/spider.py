# -*- coding:utf-8 -*-
from __future__ import print_function
import urllib
import urllib2
import requests
from requests.adapters import HTTPAdapter
import json
import re
import pymysql
import sys
import traceback
from bs4 import BeautifulSoup


# 获取百度排名、目标页真实域名
def getPageRank(url, values, page, type=0):
    # ===step 1: 获取html
    # UA参数
    ua_pc = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.82 Safari/537.36'
    ua_m = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0_2 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Mobile/15A421'
    user_agent = ua_pc if type == 0 else ua_m
    headers = {'User-Agent': user_agent}
    data = urllib.urlencode(values)
    # 设置请求url（带参数）
    final_url = url + data
    req = urllib2.Request(final_url)
    # 设置UA
    req.add_header('User-Agent', user_agent)
    # 请求获得解析结果
    res = urllib2.urlopen(req)
    html = res.read().decode('utf-8')
    soup = BeautifulSoup(html, 'lxml')
    # ===step 2: 获取次关键词的百度排名
    result = []
    result_arr = []
    rank = 0
    op_id = 0
    url = ''
    if (type == 0):  # pc
        # 获取每一页非广告的链接，在数组中的顺序即为该页的排名，rank = page * 10 + offset
        for link in soup.find_all(class_="f13"):
            a_click = link.find("a")
            if (a_click != None):
                href = str(a_click.get("href"))
                result_arr.append(href)
        for i, j in enumerate(result_arr):
            # 模拟访问一次，返回的网址即为真实网址
            if 'www.baidu.com/link?url=' in j:
                url = getReq(j, 1)
                if ('houxue.com' in url):
                    rank = page * 10 + (i + 1)
                    global is_self
                    is_self = 1
                    result.append({'rank_pc': rank, 'url_pc': url, 'op_id': 0})
                    break
                for op in opponent:
                    if (op['op_domain'] in url):
                        rank = page * 10 + (i + 1)
                        op_id = op['op_id']
                        result.append({'rank_pc': rank, 'url_pc': url, 'op_id': op_id})
    else:  # m
        # 获取每一页非广告的链接，在数组中的顺序即为该页的排名，rank = page * 10 + offset
        for link in soup.find_all(class_="c-showurl c-line-clamp1"):
            a_click = link.find("a")
            if (a_click != None):
                href = str(a_click.get("href"))
                result_arr.append(href)
        for i, j in enumerate(result_arr):
            # 模拟访问一次，返回的网址即为真实网址
            if 'm.baidu.com/from=0/bd_page_type=1' in j:
                # a = requests.get(j)
                html = getReq(j, 2)  # 从html里匹配m端地址
                pattern = "[a-zA-z]+://[^\s]*"
                items = re.findall(pattern, html)
                url = items[1]  # m端返回的真实地址
                if ('houxue.com' in url):
                    rank = page * 10 + (i + 1)  # todo: 每页不固定
                    is_self = 1
                    result.append({'rank_m': rank, 'url_m': url, 'op_id': 0})
                    break
                for op in opponent:
                    if (op['op_domain'] in url):
                        rank = page * 10 + (i + 1)
                        op_id = op['op_id']
                        result.append({'rank_m': rank, 'url_m': url, 'op_id': op_id})
    return result


# 获取百度竞争对手
def getPageOp(url, values, page, type=0):
    # ===step 1: 获取html
    # UA参数
    ua_pc = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.82 Safari/537.36'
    ua_m = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0_2 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Mobile/15A421'
    user_agent = ua_pc if type == 0 else ua_m
    headers = {'User-Agent': user_agent}
    data = urllib.urlencode(values)
    # 设置请求url（带参数）
    final_url = url + data
    req = urllib2.Request(final_url)
    # 设置UA
    req.add_header('User-Agent', user_agent)
    # 请求获得解析结果
    res = urllib2.urlopen(req)
    html = res.read().decode('utf-8')
    soup = BeautifulSoup(html, 'lxml')
    # ===step 2: 获取次关键词的百度排名
    result_arr = []
    rank = 0
    url = ''
    if (type == 0):  # pc
        # 获取每一页非广告的链接，在数组中的顺序即为该页的排名，rank = page * 10 + offset
        for link in soup.find_all(class_="f13"):
            a_click = link.find("a")
            if (a_click != None):
                href = str(a_click.get("href"))
                result_arr.append(href)
        for i, j in enumerate(result_arr):
            # 模拟访问一次，返回的网址即为真实网址
            if 'www.baidu.com/link?url=' in j:
                # a = requests.get(j, headers=headers)
                url = getReq(j, 1)
                if ('houxue.com' in url):
                    rank = page * 10 + (i + 1)
                    break
    else:  # m
        # 获取每一页非广告的链接，在数组中的顺序即为该页的排名，rank = page * 10 + offset
        test = soup.find_all(class_="c-showurl c-line-clamp1")
        for link in soup.find_all(class_="c-showurl c-line-clamp1"):
            a_click = link.find("a")
            if (a_click != None):
                href = str(a_click.get("href"))
                result_arr.append(href)
        for i, j in enumerate(result_arr):
            # 模拟访问一次，返回的网址即为真实网址
            if 'm.baidu.com/from=0/bd_page_type=1' in j:
                # a = requests.get(j)
                html = getReq(j, 2)  # 从html里匹配m端地址
                pattern = "[a-zA-z]+://[^\s]*"
                items = re.findall(pattern, html)
                url = items[1]  # m端返回的真实地址
                if ('houxue.com' in url):
                    rank = page * 10 + (i + 1)  # todo: 每页不固定
                    break
    result = {'rank': rank, 'url': url}
    return result


# 获取百度PC/M端排名
def requestBaidu(keyword, type=0, page=0):
    # 最大尝试10页
    rank = 0
    info = []
    init_page = page
    while init_page < 10:
        url = 'https://www.baidu.com/s?' if type == 0 else 'https://m.baidu.com/s?'
        values = {'wd': keyword, 'pn': init_page * 10}
        temp = getPageRank(url, values, init_page, type)
        info.extend(temp)
        if (is_self > 0):
            init_page = 10
        else:
            init_page = init_page + 1
    return info


# 自定义带超时重试的requests请求
# type: 1:pc--2:m
def getReq(url, type=1):
    s = requests.Session()
    s.mount('http://', HTTPAdapter(max_retries=3))
    s.mount('https://', HTTPAdapter(max_retries=3))
    try:
        res = s.get(url, timeout=1)
        if (res.status_code == 200):
            if (type == 1):
                return res.url
            if (type == 2):
                return res.text
        else:
            return ''
    except:
        return ''


def getOpInfo():
    # 数据库连接配置
    # config = {
    #     'host': '192.168.8.218',
    #     'port': 3306,
    #     'user': 'c1_logbak',
    #     'password': 'qvcyA_BL3U',
    #     'db': 'c1_hxtongji',
    #     'charset': 'utf8',
    #     'cursorclass': pymysql.cursors.DictCursor,
    # }
    config = {
        'host': '192.168.8.219',
        'port': 3306,
        'user': 'c1_sjtj',
        'password': 'Lsel0#3I',
        'db': 'c1_sjtj',
        'charset': 'utf8',
        'cursorclass': pymysql.cursors.DictCursor,
    }
    # 打开数据库连接
    db = pymysql.connect(**config)
    # 使用cursor()方法获取操作游标
    cursor = db.cursor()
    # 查询竞争对手
    sql = "select op_id,op_domain from opponent"
    try:
        # 执行SQL语句
        cursor.execute(sql)
        # 获取所有记录列表
        results = cursor.fetchall()
        return results
    except:  # 方法二：采用traceback模块查看异常
        # 输出异常信息
        traceback.print_exc()
        # 如果发生异常，则回滚
        db.rollback()
    finally:  # 最终关闭数据库连接
        db.close()


# ===开始===
# keyword = 'php培训'
keyword = urllib.unquote(sys.argv[1])

# 竞争对手表数据
opponent = getOpInfo()
op_arr = []
is_self = 0
# pc/m请求百度
info_pc = requestBaidu(keyword, 0, 0)
is_self = 0
info_m = requestBaidu(keyword, 1, 0)
result = {'info_pc': info_pc, 'info_m': info_m}
print(json.dumps(result), end='')
