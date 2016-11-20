<?php
/*************************************************
 * 文件名：MaterialModel.class.php
 * 功能：     素材管理模型
 * 日期：     2015.9.1
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

class MaterialModel extends WeixinModel
{

    protected $tableName = 'imgtxt_manage';

    /**
     * 获取素材列表
     * 
     * @param string $cm_id
     *            企业ID
     * @param string $accessToken
     *            access token令牌
     * @param string $type
     *            素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
     * @param string $offset
     *            从全部素材的该偏移位置开始返回，0表示从第一个素材 返回
     * @param string $count
     *            返回素材的数量，取值在1到20之间
     * @return total_count 该类型的素材的总数
     *         item_count 本次调用获取的素材的数量
     *         title 图文消息的标题
     *         thumb_media_id 图文消息的封面图片素材id（必须是永久mediaID）
     *         show_cover_pic 是否显示封面，0为false，即不显示，1为true，即显示
     *         author 作者
     *         digest 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空
     *         content 图文页的URL，或者，当获取的列表是图片素材列表时，该字段是图片的URL
     *         content_source_url 图文消息的原文地址，即点击“阅读原文”后的URL
     *         update_time 这篇图文消息素材的最后更新时间
     *         name 文件名称
     */
    public function batchget_material($cm_id, $access_token, $type, $offset = 0, $count = 10, $category = '', $keywords = '')
    {
        // 连接redis
        $redis = $this->connectRedis();
        // 读取缓存
        $keys = "batchget_material:{$cm_id}:{$type}:{$offset}:{$count}";
        
        // 有搜索条件时
        if ($category || $keywords) {
            // 直接读取单条素材的缓存
            $keys = "get_one_material:{$cm_id}:{$type}:*";
            $keyResult = $redis->keys($keys);
            // 重新组装列表数组
            foreach ($keyResult as $kk => $kv) {
                $temp = json_decode($redis->get($kv), true);
                $cache['list'][$kk]['media_id'] = $temp['media_id'];
                $cache['list'][$kk]['content']['news_item'] = $temp['news_item'];
                $cache['list'][$kk]['update_time'] = $temp['update_time'];
                $cache['list'][$kk]['category_id'] = $temp['category_id'];
                $cache['list'][$kk]['category_name'] = $temp['category_name'];
            }
            $cache['total'] = ceil(count($keyResult) / 10);
            $cache['news_total'] = count($keyResult);
            return $cache;
        }
        
        // 无搜索条件时
        $cache = $redis->get($keys);
        if ($cache) {
            // TODO 缓存存在，直接读取
            // 断开连接redis
            $this->disConnectRedis();
            $list = json_decode($cache, true);
            return $list;
        }

        // TODO 缓存不存在，调用接口
        // 请求url
        $url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=' . $access_token;
        
        // 传递参数
        $parameter = '{
            "type":"' . $type . '",
            "offset":"' . $offset . '",
            "count":"' . $count . '"
        }';
        
        // POST请求接口
        $result = $this->http_post($url, $parameter);
        $result = json_decode($result);
        if ($result->errcode) {
            // TODO 有错误码返回
            return false;
        } else {
            // TODO 图文信息处理
            if ($type == 'news') {
                // TODO 接口调用成功，判断图文信息所属分类是否存在
                // 查询该企业下图文信息的默认分类（自定义分类1）
                $defaultCateResult = $this->table('fx_sys_category')
                    ->where(array(
                    'cm_id' => $cm_id,
                    'type' => 1
                ))
                    ->getField('id,name', true);
                $defaultCateId = array_keys($defaultCateResult)[0];
                $defaultCateName = array_shift($defaultCateResult);
                // 获取所有media_id
                foreach ($result->item as $iv) {
                    $media_ids[] = $iv->media_id;
                    // 设置默认分类ID
                    $iv->category_id = $defaultCateId;
                    $iv->category_name = $defaultCateName;
                }
                
                // 查询所属的所有分类ID
                $mediaMap = array(
                    'i.media_id' => array(
                        'in',
                        $media_ids
                    ),
                    'i.category_id=c.id'
                );
                $category_ids = $this->table(array(
                    'fx_sys_category' => 'c',
                    'fx_imgtxt_manage' => 'i'
                ))
                    ->field('i.id,i.media_id,i.category_id,c.name')
                    ->where($mediaMap)
                    ->select();
                // 组装没有分类的分类数组
                foreach ($media_ids as $mk => $mv) {
                    foreach ($category_ids as $ck => $cv) {
                        if ($cv['media_id'] != $mv) {
                            $noCategory_ids[$mv] = $defaultCateId;
                        }
                    }
                }
                
                // 如果该图文信息有所属分类，覆盖默认分类
                foreach ($result->item as $ik => $iv) {
                    foreach ($category_ids as $ck => $cv) {
                        if ($cv['media_id'] == $iv->media_id) {
                            $result->item[$ik]->category_id = $cv['category_id'];
                            $result->item[$ik]->category_name = $cv['name'];
                            continue;
                        }
                    }
                }
                
                // 分类不存在，将图文信息添加到自定义分组1;
                if ($noCategory_ids) {
                    $values = "";
                    foreach ($noCategory_ids as $nk => $nv) {
                        if ($nk == 0) {
                            $values .= "('{$nk}',{$defaultCateId})";
                        } else {
                            $values .= ",('{$nk}',{$defaultCateId})";
                        }
                    }
                    $values = substr($values, 1);
                    // 3.2.2无批量添加实在蛋疼→_→
                    $sql = "INSERT INTO `fx_imgtxt_manage` (`media_id`,`category_id`) VALUES{$values}";
                    $this->startTrans();
                    $addResult = $this->execute($sql);
                    if ($addResult) {
                        $this->commit();
                    } else {
                        $this->rollback();
                    }
                }
            }
            
            // TODO 图片库处理
            if ($type == 'image') {
                foreach ($result->item as $ik => $iv) {
                    // 重组图片名称
                    $result->item[$ik]->name = end(explode('/', $iv->name));
                }
            }
            
            $list = json_encode(array(
                'list' => $result->item,
                'total' => ceil($result->total_count / 10),
                'news_total' => $result->total_count
            ));
            // 写入素材信息列表redis
            $key = "batchget_material:{$cm_id}:{$type}:{$offset}:{$count}";
            $redis->set($key, $list);
            // 写入单条素材信息redis
            foreach (json_decode($list, true)['list'] as $lk => $lv) {
                $oneKey = "get_one_material:{$cm_id}:{$type}:{$lv['media_id']}";
                if ($type == 'news') {
                    $one_material = json_encode(array(
                        'media_id' => $lv['media_id'],
                        'news_item' => $lv['content']['news_item'],
                        'update_time' => $lv['update_time'],
                        'category_id' => $lv['category_id'],
                        'category_name' => $lv['category_name']
                    ));
                }
                if ($type == 'image') {
                    $one_material = json_encode($lv);
                }
                $redis->set($oneKey, $one_material);
            }
            
            // 断开连接redis
            $this->disConnectRedis();
            return json_decode($list, true);
        }
    }

    /**
     * 获取永久素材
     * 
     * @param string $access_token
     *            公众号access_token
     * @param string $media_id
     *            素材的media_id
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function get_material($access_token, $media_id)
    {
        // 请求url
        $url = 'https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=' . $access_token;
        
        // 传递参数
        $parameter = '{"media_id":"' . $media_id . '"}';
        
        // POST请求接口
        $result = $this->http_post($url, $parameter);
        if ($result) {
            if ($result->errcode)
                return false;
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 处理单个永久素材
     * 
     * @param string $access_token
     *            公众号access_token
     * @param string $media_id
     *            素材的media_id
     * @param string $cm_id
     *            企业ID
     * @param string $type
     *            素材的类型 news-图文信息 images-图片 video-视频
     * @return boolean|multitype:NULL
     */
    public function get_one_material($access_token, $media_id, $cm_id, $type)
    {
        // 判断是哪种素材类型
        if ($type == 'images') {
            // 判断redis是否有相关信息
            $redis = $this->connectRedis();
            $cache = $redis->get("weixinImages:{$cm_id}:{$media_id}");
            if ($cache) {
                // redis存在信息，直接读取redis
                return $cache;
            } else {
                // TODO 调用接口获取素材
                $year = date('Y');
                $month = date('m');
                $day = date('d');
                
                $result = $this->get_material($access_token, $media_id);
                // 图片类型，指定静态资源文件夹和文件名
                $dir = './Public/weixinImages/' . $cm_id . '/' . $year . '/' . $month . '/' . $day;
                $file = $media_id . '.png';
                // 判断目录是否存在，不存在则建立相应的目录
                if (! is_dir($dir))
                    mkdir($dir, 0755, true);
                    // 将素材内容写入文件
                $putResult = file_put_contents("{$dir}/{$file}", $result);
                if ($putResult) {
                    // TODO 写入成功，将media_id和路径写入redis
                    $redis->set("weixinImages:{$cm_id}:{$media_id}", "{$dir}/{$file}");
                    $this->disConnectRedis();
                    return $dir . '/' . $file;
                } else {
                    // TODO 写入失败，删除文件
                    $this->disConnectRedis();
                    @unlink("{$dir}/{$file}");
                    return null;
                }
            }
        } else {
            // 连接redis
            $redis = $this->connectRedis();
            // 读取缓存
            $key = "get_one_material:{$cm_id}:{$type}:{$media_id}";
            $cache = $redis->get($key);
            if ($cache) {
                // TODO 存在缓存，直接读取
                $result = json_decode($cache, true);
                // 断开redis
                $this->disConnectRedis();
                return $result;
            }
            
            // TODO 缓存不存在，调用接口获取素材
            $result = $this->get_material($access_token, $media_id);
            if ($result->errcode) {
                // 有错误码返回
                return false;
            } else {
                if ($type == 'news') {
                    // 图文信息
                    // 写入缓存
                    $redis->set($key, $result);
                    // 断开redis
                    $this->disConnectRedis();
                    return json_decode($result, true);
                } elseif ($type == 'video') {
                    // 视频信息
                }
            }
        }
    }

    /**
     * 新建图文信息
     * 
     * @param string $cm_id
     *            企业ID
     * @param string $access_token
     *            公众号access_token
     * @param string $category_id
     *            分类ID
     * @param string $title
     *            标题
     * @param string $thumb_media_id
     *            封面media_id
     * @param string $author
     *            作者
     * @param string $digest
     *            摘要
     * @param string $content
     *            正文
     * @param string $content_source_url
     *            原文链接
     * @param number $show_cover_pic
     *            是否显示封面，默认0
     * @return string 图文信息的media_id
     */
    public function add_news($cm_id, $access_token, $category_id, $title, $thumb_media_id, $author, $digest, $content, $content_source_url, $show_cover_pic = 0)
    {
        // 请求url
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=' . $access_token;
        
        $content = str_replace('"', '\"', $content);
        // 传递参数
        $parameter = '{
          "articles": [{
               "title": "' . $title . '",
               "thumb_media_id": "' . $thumb_media_id . '",
               "author": "' . $author . '",
               "digest": "' . $digest . '",
               "show_cover_pic": "' . $show_cover_pic . '",
               "content": "' . $content . '",
               "content_source_url": "' . $content_source_url . '"
            },
         ]
        }';
        
        // POST请求接口
        $result = $this->http_post($url, $parameter);
        $result = json_decode($result);
        if ($result->errcode) {
            return false;
        } else {
            // 刷新图文信息redis缓存
            // 连接redis
            $redis = $this->connectRedis();
            $keys = "batchget_material:{$cm_id}:news:*:*";
            $keysResult = $redis->keys($keys);
            foreach ($keysResult as $kk => $vv) {
                // 清除缓存
                $redis->set($vv, null);
            }
            // 刷新图文信息封面缓存
            $this->get_one_material($access_token, $thumb_media_id, $cm_id, 'image');
            // 断开redis
            $this->disConnectRedis();
            
            $media_id = $result->media_id;
            // 将图文信息添加到该企业的默认分组
            $data = array(
                'media_id' => $media_id,
                'category_id' => $category_id
            );
            $this->startTrans();
            $addResult = $this->add($data);
            if ($addResult) {
                $this->commit();
            } else {
                $this->rollback();
            }
            return $media_id;
        }
    }

    /**
     * 更新图文信息
     * 
     * @param string $cm_id
     *            企业ID
     * @param string $access_token
     *            公众号access_token
     * @param string $category_id
     *            分类ID
     * @param string $media_id
     *            素材的media_id
     * @param string $title
     *            标题
     * @param string $thumb_media_id
     *            封面的media_id
     * @param string $author
     *            作者
     * @param string $digest
     *            摘要
     * @param string $content
     *            正文
     * @param string $content_source_url
     *            原文链接
     * @param number $index
     *            要更新的文章在图文消息中的位置（多图文消息时，此字段才有意义），第一篇为0
     * @param number $show_cover_pic
     *            是否显示封面，0为false，即不显示，1为true，即显示
     * @return boolean
     */
    public function update_news($cm_id, $access_token, $category_id, $media_id, $title, $thumb_media_id, $author, $digest, $content, $content_source_url, $index = 0, $show_cover_pic = 0)
    {
        // 请求url
        $url = 'https://api.weixin.qq.com/cgi-bin/material/update_news?access_token=' . $access_token;
        
        $content = str_replace('"', '\"', $content);
        // 传递参数
        $parameter = '{
            "media_id":"' . $media_id . '",
            "index":"' . $index . '",
            "articles":{
                "title":"' . $title . '",
                "thumb_media_id":"' . $thumb_media_id . '",
                "author":"' . $author . '",
                "digest":"' . $digest . '",
                "show_cover_pic":"' . $show_cover_pic . '",
                "content":"' . $content . '",
                "content_source_url":"' . $content_source_url . '"
            }
        }';
        
        // POST请求接口
        $result = $this->http_post($url, $parameter);
        $result = json_decode($result);
        if ($result->errcode == 0) {
            // TODO 修改图文信息成功，刷新图文信息缓存
            // 连接redis
            $redis = $this->connectRedis();
            // 读取缓存
            $keys1 = "batchget_material:{$cm_id}:news:*:*";
            $keysResult1 = $redis->keys($keys1);
            foreach ($keysResult1 as $vv1) {
                // 清除缓存
                $redis->set($vv1, null);
            }
            // 清除该图文信息缓存
            $keys2 = "get_one_material:{$cm_id}:news:*";
            $keysResult2 = $redis->keys($keys2);
            foreach ($keysResult2 as $vv2) {
                $redis->set($keys2, null);
            }
            // 刷新图文信息封面缓存
            $this->get_one_material($access_token, $thumb_media_id, $cm_id, 'image');
            // 断开redis
            $this->disConnectRedis();
            
            // 设置图文信息分组
            if ($category_id) {
                // 查询该图文信息是否有分组
                $newsCateInfo = $this->get_news_category($media_id, $category_id);
                if (! $newsCateInfo) {
                    // 该图文信息无分组，添加分组
                    $data = array(
                        'media_id' => $media_id,
                        'category_id' => $category_id
                    );
                    // 添加分组，开启事务
                    $this->startTrans();
                    $cateResult = $this->add($data);
                    if ($cateResult) {
                        // TODO 添加成功，提交事务
                        $this->commit();
                        return true;
                    } else {
                        // TODO 添加失败，回滚事务
                        $this->rollback();
                        return false;
                    }
                } else {
                    $data = array(
                        'category_id' => $category_id,
                        'modify_time' => date('Y-m-d H:i:s')
                    );
                    // 修改分组，开启事务
                    $this->startTrans();
                    $map = array(
                        'media_id' => $media_id
                    );
                    $cateResult = $this->where($map)->save($data);
                    if ($cateResult) {
                        // TODO 修改成功，提交事务
                        $this->commit();
                        return true;
                    } else {
                        // TODO 修改失败，回滚事务
                        $this->rollback();
                        return false;
                    }
                }
            }
            return true;
        } else {
            // TODO 修改图文信息失败
            return false;
        }
    }

    /**
     * 删除永久素材
     * 
     * @param string $cm_id
     *            企业ID
     * @param string $access_token
     *            公众号access_token
     * @param string $media_id
     *            素材的media_id
     * @return boolean
     */
    public function del_material($cm_id, $access_token, $media_id, $type)
    {
        // 请求url
        $url = 'https://api.weixin.qq.com/cgi-bin/material/del_material?access_token=' . $access_token;
        
        // 传递参数
        $parameter = '{
            "media_id":"' . $media_id . '"
        }';
        
        // POST请求接口
        $result = $this->http_post($url, $parameter);
        $result = json_decode($result);
        if ($result->errcode == 0) {
            // TODO 删除成功，刷新redis缓存
            // 连接redis
            $redis = $this->connectRedis();
            // 读取缓存
            $key = "get_one_material:{$cm_id}:{$type}:{$media_id}";
            // 清除单条素材的缓存
            $redis->del($key);
            // 刷新素材列表的缓存
            $keys = "batchget_material:{$cm_id}:{$type}:*:*";
            $keysResult = $redis->keys($keys);
            $redis->del($keysResult);
            
            // 删除表fx_imgtxt_manage中的相关记录
            $map = array(
                'media_id' => $media_id
            );
            $this->startTrans();
            $delResult = $this->where($map)->delete();
            if ($delResult)
                $this->commit();
            else
                $this->rollback();
            return true;
        }
        return false;
    }

    /**
     * 新增其他类型永久素材
     * 
     * @param string $cm_id
     *            企业ID
     * @param string $access_token
     *            公众号access_token
     * @param string $type
     *            媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     * @param string $filepath
     *            文件路径
     * @return boolean|mixed
     */
    public function add_material($cm_id, $access_token, $type, $filepath)
    {
        // 请求url
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=' . $access_token;
        
        // 传递参数
        $parameter = array(
            'type' => $type,
            'media' => '@' . urldecode(str_replace("http://" . $_SERVER['HTTP_HOST'], C('UPLOADIMG_PATH'), $filepath))
        )
        // 'media'=>'@'.urldecode(str_replace("http://localhost/wg/", 'F:/wamp/www'.__ROOT__.'/', $filepath)),
        ;
        
        // POST请求接口
        $result = $this->http_post($url, $parameter);
        $result = json_decode($result);
        if ($result->errcode) {
            return false;
        }
        // 连接redis
        $redis = $this->connectRedis();
        // 刷新缓存
        $keys = "batchget_material:{$cm_id}:{$type}:*:*";
        $keysResult = $redis->keys($keys);
        foreach ($keysResult as $vv) {
            // 清空缓存
            $redis->set($vv, null);
        }
        return $result;
    }

    /**
     * 图文信息内容图片上传
     * 
     * @param string $access_token
     *            公众号access_token
     * @param string $filepath
     *            文件路径
     * @return boolean
     */
    public function uploadimg($access_token, $filepath)
    {
        // 请求url
        $url = 'https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=' . $access_token;
        
        // 传递参数
        $parameter = array(
            'media' => '@' . urldecode(C('UE_UPLOADIMG_PATH') . $filepath)
        )
        // 'media'=>'@'.urldecode(str_replace("http://localhost/wg/", 'F:/wamp/www'.__ROOT__.'/', $filepath)),
        ;
        
        // POST请求接口
        $result = $this->http_post($url, $parameter);
        $result = json_decode($result);
        if ($result->errcode) {
            return false;
        } else {
            return $result->url;
        }
    }

    /**
     * 查询图文信息所属分组
     * 
     * @param string $media_id
     *            媒体ID
     * @param string $category_id
     *            分组ID
     * @return \Think\mixed
     */
    public function get_news_category($media_id, $category_id)
    {
        $map = array(
            'media_id' => $media_id
        );
        $result = $this->field('id,media_id,category_id')
            ->where($map)
            ->find();
        return $result;
    }
}


