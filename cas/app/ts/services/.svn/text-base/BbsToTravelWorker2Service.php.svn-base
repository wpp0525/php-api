<?php

use Lvmama\Cas\Component\DaemonServiceInterface,
    Lvmama\Common\Components\ApiClient,
    Lvmama\Common\Utils\UCommon;

class BbsToTravelWorker2Service implements DaemonServiceInterface
{

    private $traveldatasvc;
    private $bbsdatasvc;
    private $flag_id;
    private $client;
    private $travel_id;
    private $travel_content_id;
    private $redis;

    private $redis_cache_key = 'pp_bbs_to_travel2';

    public function __construct($di)
    {
        $this->traveldatasvc = $di->get('cas')->get('travel_data_service');
        $this->traveldatasvc->setReconnect(true);

        $this->bbsdatasvc = $di->get('cas')->get('bbs-data-service');
        $this->bbsdatasvc->setReconnect(true);

        $this->client = new ApiClient('http://ca.lvmama.com');
        $this->redis = $di->get('cas')->getRedis();

    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null)
    {
        $this->flag_id = $flag;
        $this->insertData();
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null)
    {
        // nothing to do
    }

    /**
     * 数据插入
     * @param int $start
     */
    private function insertData()
    {
        $tid_data = $this->getAllowTid();

        // 获取游标
        $last_id = $this->getLastId();

        foreach ($this->getRows($tid_data) as $key=>$row) {
            if($key == count($tid_data)-1){
                //重置游标
                $this->setLastId(0);
                $this->stopFlag('已结束');
            }
            if($key > $last_id) {
                echo '循环开始：tid = ', $row['tid'], ',authorid = ', $row['authorid'], "\n";
                $this->travel_id = $this->travel_content_id = 0;
                $content_data = $this->getContents($row);

                if (!$content_data)
                    continue;
                $content = $username = '';
                foreach ($this->getRows($content_data) as $item) {
                    $content .= $item['message'];
                    $username = $item['author'];
                }

                $params = array(
                    'username' => $username . 'bbs',
                    'publish_time' => $content_data['0']['dateline'],
                    'title' => $content_data['0']['subject'],
                );
                $this->travel_id = $this->getTravelId($params);
                if (!$this->travel_id)
                    continue;

                $this->travel_content_id = $this->getTravelContentId();

                $parse_content = $this->parseTags($content);

                $content_params = array(
                    'table' => 'travel_content',
                    'where' => "id = '{$this->travel_content_id}'",
                    'data' => array(
                        'title' => '',
                        'content' => $this->my_nl2br(strip_tags($parse_content, '<p><a><img><strong>')),
                    ),
                );
                $this->traveldatasvc->update($content_params);


                $this->setLastId($key);
                echo 'end', "\n";
                sleep(3);
            }
        }



        die('done');
    }

    /**
     * 获取所有符合迁移条件的数据ID
     * 迁移条件：
     * 用户:"chenjianchao","翼云kiki","野菊花swq","wb1961","msk1956","luobo1012","天达-馨宜","老仇","卡洛斯","达达迪达","金罗毛哥","远方的心","kimi_pan"
     * 版块：光影记录
     * 图章：精华、热帖、美图、优秀、置顶、推荐、原创、版主推荐、爆料、编辑采用
     * 将BBS全部数据中没有图章的文章，迁移至游记数据库中。
     * @return array
     */
    private function getAllowTid()
    {
        $params = array(
            'table' => 'forum_thread',
            'select' => 'tid,authorid',
            'where' => array('fid' => array('EQ', "4"), 'stamp' => array('EQ', '-1'), 'authorid' => array('NEQ', '1'),'author' => array('IN', '("chenjianchao","翼云kiki","野菊花swq","wb1961","msk1956","luobo1012","天达-馨宜","老仇","卡洛斯","达达迪达","金罗毛哥","远方的心","kimi_pan"
)')),
        );
        if ($this->flag_id)
            $params['limit'] = $this->flag_id;

        $res = $this->bbsdatasvc->select($params);
        if (!$res['list']) {
            echo '查询所有符合迁移条件的数据ID失败!', "\n";
            return array();
        }
        return $res['list'];
    }

    /**
     * 获取发帖人的所有楼层数据
     * @param $row
     * @return array
     */
    private function getContents($row)
    {
        $params = array(
            'table' => 'forum_post',
            'select' => 'tid,subject,dateline,author,authorid,message',
            'where' => array('tid' => $row['tid'], 'authorid' => $row['authorid']),
            'order' => 'dateline'
        );
        $res = $this->bbsdatasvc->select($params);
        if (!$res['list']) {
            echo '查询发帖人的所有楼层数据失败', "\n", 'end', "\n";
            return array();
        }
        return $res['list'];
    }

    /**
     *  解析标签
     * @param $content
     * @return mixed
     */
    private function parseTags($content)
    {
        $content = str_replace(array(
            '[/color]', '[/backcolor]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]', '[s]', '[/s]', '[hr]', '[/p]',
            '[i=s]', '[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
            '[list=A]', "\r\n[*]", '[*]', '[/list]', '[indent]', '[/indent]', '[/float]', '[tr]', '[/tr]', '[td]', '[/td]'
        ), array(
            '</font>', '</font>', '</font>', '</font>', '</div>', '<strong>', '</strong>', '<strike>', '</strike>', '<hr class="l" />', '</p>', '<i class="pstatus">', '<i>',
            '</i>', '<u>', '</u>', '<ul>', '<ul type="1" class="litype_1">', '<ul type="a" class="litype_2">',
            '<ul type="A" class="litype_3">', '<li>', '<li>', '</ul>', '<blockquote>', '</blockquote>', '</span>', '<tr>', '</tr>', '<td>', '</td>'
        ), preg_replace(array(
            "/\[color=([#\w]+?)\]/i",
            "/\[color=(rgb\([\d\s,]+?\))\]/i",
            "/\[backcolor=([#\w]+?)\]/i",
            "/\[backcolor=(rgb\([\d\s,]+?\))\]/i",
            "/\[size=(\d{1,2}?)\]/i",
            "/\[size=(\d{1,2}(\.\d{1,2}+)?(px|pt)+?)\]/i",
            "/\[font=([^\[\<]+?)\]/i",
            "/\[align=(left|center|right)\]/i",
            "/\[p=(\d{1,2}|null), (\d{1,2}|null), (left|center|right)\]/i",
            "/\[float=left\]/i",
            "/\[float=right\]/i",
            "/\[url=(https?){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i",
            "/\[url=(https?){1}:\/\/([^\[\"']+?)\]\[\/url\]/i",
            "/\[url=(.+?)\](.+?)\[\/url\]/i",
            "/\[url=(.?)\](.+?)\[\/url\]/i",
            "/\[url(.?)\]\[\/url\]/i",
            "/\[url\](.+?)\[\/url\]/i",
            "/\[quote]([\s\S]*?)\[\/quote\]/i",
            "/\[table(.+?)\]/i",
            "/\[table\]/i",
            "/\[td=(.+?)\]/i"
        ), array(
            "<font color=\"$1\">",
            "<font style=\"color:$1\">",
            "<font style=\"background-color:$1\">",
            "<font style=\"background-color:$1\">",
            "<font size=\"$1\">",
            "<font style=\"font-size:$1\">",
            "<font face=\"$1\">",
            "<div align=\"$1\">",
            "<p style=\"line-height:$1px;text-indent:$2em;text-align:$3\">",
            "<span style=\"float:left;margin-right:5px\">",
            "<span style=\"float:right;margin-left:5px\">",
            "<a href=\"$1://$2\" target=\"_blank\">$3</a>",
            "",
            "",
            "",
            "",
            "<a href=\"$3\" target=\"_blank\">$3</a>",
            "<div class=\"quote\"><blockquote>$1</blockquote></div>\n",
            "<table>",
            "<table>",
            "<td>"
        ), $content));
        $content = $this->parseAttachTag($content);
        $parse_content = $this->parseImgTag($content);
        $parse_content = str_replace(
            array('[/table]', '[img]', '[/img]', '[/quote]', '[/url]'),
            '', $parse_content);
        return $parse_content;
    }

    /**
     * 解析图片标签
     * @param $content
     * @return mixed
     */
    private function parseImgTag($content)
    {
        $parser_1 = preg_replace_callback('/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/i', 'self::saveAndReplace', $content);
        $parser_2 = preg_replace_callback('/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/i', 'self::saveAndReplace', $parser_1);
        return $parser_2;
    }

    /**
     * 解析附件标签
     * @param $content
     * @return mixed
     */
    private function parseAttachTag($content)
    {
        preg_match_all('/\[attach\](\d+)\[\/attach\]/', $content, $res);
        if ($res['1']) {
            $finds = $res['0'];
            $replaces = array();
            foreach ($this->getRows($res['1']) as $attach_id) {
                $replaces[$attach_id] = '';
                $table_id_data = $this->bbsdatasvc->select(array(
                    'table' => 'forum_attachment',
                    'select' => 'tableid',
                    'where' => array('aid' => $attach_id),
                    'limit' => '1'
                ));
                if (!$table_id_data['list'])
                    continue;
                $table_id = $table_id_data['list']['0']['tableid'];
                $attach_data = $this->bbsdatasvc->select(array(
                    'table' => 'forum_attachment_' . $table_id,
                    'select' => 'attachment',
                    'where' => array('aid' => $attach_id),
                    'limit' => '1'
                ));
                if (!$attach_data['list'])
                    continue;
                //TODO 此处需要将图片上传，图片url为: "http://lvbbs.lvmama.com/data/attachment/forum/" . $attach_data['list']['0']['attachment']

                $image_url = 'http://lvbbs.lvmama.com/data/attachment/forum/' . $attach_data['list']['0']['attachment'];
                $upload_image_url = $this->saveImage($image_url);

                if ($upload_image_url)
                    $replaces[$attach_id] = '<img src = "http://pic.lvmama.com' . $upload_image_url . '" />';
                else
                    $replaces[$attach_id] = '';
            }
            $content = str_replace($finds, $replaces, $content);
        }
        return $content;
    }

    /**
     * 保存图片
     * @param $image_url
     * @param $travel_id
     */
    private function saveImage($image_url, $num = 1)
    {
        if (!$image_url)
            return false;
        if ($num == 6) {
            echo "{$image_url}上传失败 \n";
            return false;
        }
        $params = array(
            'image_url' => $image_url,
            'cr' => '162',
            'type' => 'guide',
            'id' => $this->travel_id,
        );
        echo "{$image_url}开始上传\n";
        $res = $this->client->exec('travel/saveImage', $params, array(), 'post');
        if ($res['code'] == '200') {
            $image_url = $res['data']['url'];
            echo "{$image_url}上传成功！", json_encode($res), "\n";
            $curr_time = time();
            $image_params = array(
                'table' => 'image',
                'data' => array(
                    'width' => $res['data']['width'],
                    'dest_id' => '0',
                    'url' => $image_url,
                    'create_time' => $curr_time,
                    'update_time' => $curr_time,
                )
            );
            $image_result = $this->traveldatasvc->insert($image_params);

            if ($image_result['result']) {
                $image_rel_params = array(
                    'table' => 'travel_image_rel',
                    'data' => array(
                        'travel_id' => $this->travel_id,
                        'travel_content_id' => $this->travel_content_id,
                        'image_id' => $image_result['result'],
                        'create_time' => $curr_time,
                        'update_time' => $curr_time,
                    ),
                );
                $this->traveldatasvc->insert($image_rel_params);
            }
            return $image_url;
        } else {
            echo "第{$num}次传图失败，重试... \n";
            $num++;
            sleep(1);
            $this->saveImage($image_url, $num);
        }
    }

    /**
     * 获取游记ID
     * @param $username
     * @return mixed
     */
    private function getTravelId($params)
    {
        $uid = $this->getUidByUsername($params['username']);

        if (!$uid) return false;

        $curr_time = time();
        $travel_params = array(
            'table' => 'travel',
            'data' => array(
                'username' => $params['username'],
                'publish_time' => $params['publish_time'],
                'title' => $params['title'],
                'uid' => $uid,
                'order_num' => '1',
                'create_time' => $curr_time,
                'update_time' => $curr_time,
            ),
        );
        $result = $this->traveldatasvc->insert($travel_params);

        $travel_ext_params = array(
            'table' => 'travel_ext',
            'data' => array(
                'travel_id' => $result['result'],
                'main_status' => '1',
                'create_time' => $curr_time,
                'update_time' => $curr_time,
            ),
        );
        $this->traveldatasvc->insert($travel_ext_params);
        return $result['result'];
    }

    /**
     * 获取章节ID
     * @param $travel_id
     * @return mixed
     */
    private function getTravelContentId()
    {
        $curr_time = time();
        $insert_params = array(
            'table' => 'travel_content',
            'data' => array(
                'travel_id' => $this->travel_id,
                'content'=>'',
                'create_time' => $curr_time,
                'update_time' => $curr_time,
            ),
        );
        $result = $this->traveldatasvc->insert($insert_params);

        return $result['result'];
    }

    /**
     * 将匹配到的图片上传保存并替换
     * @param $matches
     * @return string
     */
    private function saveAndReplace($matches)
    {
        $attr_str = '';
        if (count($matches) > 2) {
            $patt_url = $matches['3'];
            $attr_str = "width='{$matches[1]}' height='{$matches[2]}'";
        } else
            $patt_url = $matches['1'];
        $image_url = '';
        if (strpos($patt_url, 'http://') === false) {
            $image_url = 'http://lvbbs.lvmama.com/data/attachment/' . $patt_url;
        } else {
            $image_url = str_replace('bbs.lvmama.com', 'lvbbs.lvmama.com', $patt_url);
        }

        $upload_image_url = $this->saveImage($image_url);
        if (!$upload_image_url)
            return '';
        return "<img src='http://pic.lvmama.com{$upload_image_url}' {$attr_str} />";
    }

    private function my_nl2br($s)
    {
        return str_replace("&nbsp;", ' ', str_replace("\n", '<br>', str_replace("\r", '<br>', str_replace("\r\n", '<br>', $s))));
    }

    /**
     * 返回用户名对应的uid
     * @param $username
     * @return mixed|string
     */
    private function getUidByUsername($username)
    {
        $user_info = array(
            '2c9486e65cef761a015d11d152e35e67' => '*浅墨*1749177bbs',
            '2c9486e45cef7726015d11d153555f2c' => '125zaq1063684bbs',
            '2c9486e65cef761a015d11d153ca5e68' => '1381693380bbs',
            '2c9486ee5cef7d31015d11d154215e1c' => '198921shaohebbs',
            '2c9486ef5cef78c6015d11d154845d5b' => '236005bbs',
            '2c9486ef5cef78c6015d11d154e35d5c' => '2个小豆豆bbs',
            '2c9486e65cef761a015d11d1555c5e69' => '4444444bbs',
            '2c9486e65cef761a015d11d155c35e6a' => '5201314189bbs',
            '2c9486e65cef761a015d11d156275e6b' => '57godajiao123bbs',
            '2c9486ef5cef78c6015d11d1566e5d5d' => 'abbs',
            '2c9486ef5cef78c6015d11d156d45d5e' => 'a0813bbs',
            '2c9486e45cef7726015d11d157575f2d' => 'agndbbs',
            '2c9486ef5cef78c6015d11d157ab5d5f' => 'ailuyou1bbs',
            '2c9486e55cef76f3015d11d158235d69' => 'ailvyou131bbs',
            '2c9486ee5cef7d31015d11d158815e1d' => 'ailvyou2012bbs',
            '2c9486e65cef761a015d11d158f95e6c' => 'allen满天bbs',
            '2c9486e55cef76f3015d11d159675d6a' => 'alvin_xiebbs',
            '2c9486e55cef76f3015d11d159cd5d6b' => 'annalbbbs',
            '2c9486e55cef76f3015d11d15a355d6c' => 'anyihtbbs',
            '2c9486ef5cef78c6015d11d15a915d60' => 'astray01bbs',
            '2c9486e65cef761a015d11d15afa5e6d' => 'axiumimibbs',
            '2c9486ef5cef78c6015d11d15b4b5d61' => 'bb88bbs',
            '2c9486ef5cef78c6015d11d15bae5d62' => 'bbs416bbs',
            '2c9486ef5cef78c6015d11d15c1d5d63' => 'binbinjiqimaobbs',
            '2c9486e65cef761a015d11d15c9e5e6e' => 'bobyccbbs',
            '2c9486ef5cef78c6015d11d15cf45d64' => 'bohanshibbs',
            '2c9486e55cef76f3015d11d15d555d6d' => 'bokudesubbs',
            '2c9486e45cef7726015d11d15dd15f2e' => 'cam1209bbs',
            '2c9486e45cef7726015d11d15e245f2f' => 'cc0320bbs',
            '2c9486e65cef761a015d11d15e865e6f' => 'chenjianchaobbs',
            '2c9486e65cef761a015d11d15efa5e70' => 'cococat307bbs',
            '2c9486e55cef76f3015d11d160275d6e' => 'cxqcindybbs',
            '2c9486e45cef7726015d11d160935f30' => 'daha2000bbs',
            '2c9486ef5cef78c6015d11d160eb5d65' => 'define_007bbs',
            '2c9486ef5cef78c6015d11d1615e5d66' => 'earthabbs',
            '2c9486e55cef76f3015d11d161bf5d6f' => 'f1法拉利60bbs',
            '2c9486ee5cef7d31015d11d1621a5e1e' => 'fanny0519bbs',
            '2c9486ef5cef78c6015d11d162965d67' => 'freerskybbs',
            '2c9486e65cef761a015d11d163275e72' => 'fromsinaweibobbs',
            '2c9486e45cef7726015d11d163915f31' => 'from+sina+weibobbs',
            '2c9486e45cef7726015d11d163f15f32' => 'gorgeous小珂bbs',
            '2c9486ee5cef7d31015d11d1643f5e1f' => 'gulilibbs',
            '2c9486e55cef76f3015d11d164b15d70' => 'guomabbs',
            '2c9486ef5cef78c6015d11d165075d68' => 'happysuki065bbs',
            '2c9486e45cef7726015d11d1656c5f33' => 'hua2024bbs',
            '2c9486ef5cef78c6015d11d165b35d69' => 'hxqdreambbs',
            '2c9486ef5cef78c6015d11d166195d6a' => 'hyde666hidebbs',
            '2c9486e65cef761a015d11d1667d5e73' => 'jameshu1978bbs',
            '2c9486e45cef7726015d11d166ff5f34' => 'jeansewarmbbs',
            '2c9486e45cef7726015d11d167695f35' => 'jennynuibbs',
            '2c9486ee5cef7d31015d11d167b65e20' => 'jfyadnaoibbs',
            '2c9486e65cef761a015d11d168415e74' => 'jialusixbbs',
            '2c9486ee5cef7d31015d11d1689a5e21' => 'jinmilaoshubbs',
            '2c9486e55cef76f3015d11d169205d71' => 'journey134bbs',
            '2c9486e45cef7726015d11d1698c5f36' => 'kanssuubbs',
            '2c9486e45cef7726015d11d169eb5f37' => 'katey316bbs',
            '2c9486ef5cef78c6015d11d16a3e5d6b' => 'kimi_panbbs',
            '2c9486ef5cef78c6015d11d16aaf5d6c' => 'lancexrbbs',
            '2c9486e55cef76f3015d11d16b215d72' => 'lanxilin1bbs',
            '2c9486e55cef76f3015d11d16b815d73' => 'lch2015bbs',
            '2c9486e65cef761a015d11d16be95e75' => 'li9xubbs',
            '2c9486e65cef761a015d11d16c4b5e76' => 'linglingyixingbbs',
            '2c9486e55cef76f3015d11d16cb05d74' => 'liufeijian1bbs',
            '2c9486e65cef761a015d11d16d035e77' => 'losingyoubbs',
            '2c9486ef5cef78c6015d11d16d5a5d6d' => 'lucky90bbs',
            '2c9486ef5cef78c6015d11d16dbd5d6e' => 'luobo1012bbs',
            '2c9486e65cef761a015d11d16e225e78' => 'lv13621764333bbs',
            '2c9486ee5cef7d31015d11d16e7d5e22' => 'lv1386334cfkjbbs',
            '2c9486ee5cef7d31015d11d16ee25e23' => 'lv1550116iohlbbs',
            '2c9486ef5cef78c6015d11d16f585d6f' => 'lv1760744qzqqbbs',
            '2c9486ef5cef78c6015d11d16fca5d70' => 'lv18019216558bbs',
            '2c9486e65cef761a015d11d170445e79' => 'lv1811979qz6nbbs',
            '2c9486e65cef761a015d11d170b25e7a' => 'lvatmbbs',
            '2c9486e55cef76f3015d11d1711c5d75' => 'manzitracybbs',
            '2c9486ef5cef78c6015d11d171745d71' => 'mcclanebbs',
            '2c9486ef5cef78c6015d11d171d55d72' => 'mlxingbbs',
            '2c9486e55cef76f3015d11d1724c5d76' => 'mo2117416564bbs',
            '2c9486ee5cef7d31015d11d172b25e24' => 'msk1956bbs',
            '2c9486ef5cef78c6015d11d173255d73' => 'nbvghxbbs',
            '2c9486e55cef76f3015d11d173995d77' => 'nickel1992bbs',
            '2c9486e45cef7726015d11d174045f38' => 'nicky8885bbs',
            '2c9486e65cef761a015d11d1746e5e7b' => 'ninihuibbs',
            '2c9486e45cef7726015d11d174d65f39' => 'outs1derbbs',
            '2c9486e55cef76f3015d11d1753c5d78' => 'pfptxbbs',
            '2c9486ee5cef7d31015d11d175805e25' => 'qazmiaomiaobbs',
            '2c9486ef5cef78c6015d11d175ef5d74' => 'qdmxdbbs',
            '2c9486e55cef76f3015d11d176525d79' => 'qianlike1bbs',
            '2c9486e65cef761a015d11d176b25e7c' => 'qincc9292bbs',
            '2c9486ef5cef78c6015d11d177035d75' => 'qinxbbs',
            '2c9486e55cef76f3015d11d177665d7a' => 'qiqi121bbs',
            '2c9486ee5cef7d31015d11d177a75e26' => 'qq348883578bbs',
            '2c9486e55cef76f3015d11d178265d7b' => 'remember乀bbs',
            '2c9486e55cef76f3015d11d1788d5d7c' => 'setsuna00bbs',
            '2c9486e55cef76f3015d11d178ed5d7d' => 'seven_nanakobbs',
            '2c9486e65cef761a015d11d1794a5e7d' => 'seven青瓜bbs',
            '2c9486e55cef76f3015d11d179be5d7e' => 'shluyebbs',
            '2c9486e45cef7726015d11d17a295f3a' => 'smile淡忘bbs',
            '2c9486e45cef7726015d11d17a8c5f3b' => 'steelzhangbbs',
            '2c9486ee5cef7d31015d11d17ae25e27' => 'summershantibbs',
            '2c9486e45cef7726015d11d17b505f3c' => 'sunqqqxxbbs',
            '2c9486ee5cef7d31015d11d17b915e28' => 'tank144993bbs',
            '2c9486e65cef761a015d11d17c085e7e' => 'ultraman0bbs',
            '2c9486ef5cef78c6015d11d17c5c5d76' => 'user16035947bbs',
            '2c9486e65cef761a015d11d17cf25e7f' => 'user22533455bbs',
            '2c9486e65cef761a015d11d17d5d5e80' => 'user499871bbs',
            '2c9486ee5cef7d31015d11d17da95e29' => 'user69457752bbs',
            '2c9486e45cef7726015d11d17e215f3d' => 'user77581441bbs',
            '2c9486ef5cef78c6015d11d17e7a5d77' => 'user95405bbs',
            '2c9486e65cef761a015d11d17ee45e82' => 'vincentsjtubbs',
            '2c9486e55cef76f3015d11d17f555d7f' => 'viphenshihuibbs',
            '2c9486ee5cef7d31015d11d17fb05e2a' => 'waidbbs',
            '2c9486e45cef7726015d11d180315f3e' => 'wangzhiqiang102bbs',
            '2c9486ee5cef7d31015d11d180715e2b' => 'wb1961bbs',
            '2c9486e55cef76f3015d11d180d55d80' => 'wllkbbs',
            '2c9486e65cef761a015d11d181465e83' => 'xfhzc100910bbs',
            '2c9486ee5cef7d31015d11d1511d5e1b' => 'xiangyue168bbs',
            '2c9486e65cef761a015d11d151945e65' => 'xiaottaobbs',
            '2c9486ef5cef78c6015d11d151ec5d5a' => 'xueji001bbs',
            '2c9486e55cef76f3015d11d152775d68' => 'xxyylljjbbs',
            '2c9486e65cef761a015d11d0fb9d5e2f' => 'yangjie1985bbs',
            '2c9486e55cef76f3015d11d0fc605d3b' => 'yimingdubbs',
            '2c9486ee5cef7d31015d11d0fd285df8' => 'yingtiaobbs',
            '2c9486e65cef761a015d11d0fe0d5e33' => 'yinnan1180bbs',
            '2c9486e65cef761a015d11d0fec35e34' => 'ylwsq827bbs',
            '2c9486e45cef7726015d11d0ffa75f01' => 'youduobbs',
            '2c9486e65cef761a015d11d100685e35' => 'youwanjibbs',
            '2c9486ef5cef78c6015d11d1012e5d32' => 'yo旅游bbs',
            '2c9486e65cef761a015d11d1020f5e37' => 'zhaoyunminbbs',
            '2c9486e55cef76f3015d11d102ce5d3d' => 'zhengdubbs',
            '2c9486e65cef761a015d11d1039d5e38' => 'zj7961bbs',
            '2c9486e65cef761a015d11d104425e3a' => 'zjjnhylybbs',
            '2c9486e65cef761a015d11d104fe5e3b' => 'zmd520bbs',
            '2c9486e65cef761a015d11d105a95e3d' => 'zzzcybbs',
            '2c9486ef5cef78c6015d11d106685d34' => '_蚂_蚁_bbs',
            '2c9486e45cef7726015d11d107315f05' => 'ヤ恛メ憶ヤbbs',
            '2c9486e45cef7726015d11d107eb5f06' => '一介农夫bbs',
            '2c9486e65cef761a015d11d108a75e3e' => '一只跑鱼bbs',
            '2c9486ef5cef78c6015d11d1096c5d38' => '一种人bbs',
            '2c9486ef5cef78c6015d11d10a425d39' => '一路向西超越bbs',
            '2c9486e55cef76f3015d11d10b125d41' => '一辉在涠洲岛bbs',
            '2c9486ee5cef7d31015d11d10bd05dfc' => '一颗葡萄bbs',
            '2c9486ef5cef78c6015d11d10c9c5d3c' => '不愛，請閃開！bbs',
            '2c9486e45cef7726015d11d10d505f07' => '不拉是我的bbs',
            '2c9486e55cef76f3015d11d10e185d42' => '个人体会删除bbs',
            '2c9486e45cef7726015d11d10ec85f08' => '为你变乖了bbs',
            '2c9486e45cef7726015d11d10f8e5f09' => '丽江诗语bbs',
            '2c9486ef5cef78c6015d11d110405d3e' => '丿灬叶子bbs',
            '2c9486ee5cef7d31015d11d111175dff' => '久闻jowenbbs',
            '2c9486e55cef76f3015d11d111dc5d44' => '乖乖米老鼠bbs',
            '2c9486e45cef7726015d11d112a95f0b' => '九国的骆驼bbs',
            '2c9486e65cef761a015d11d113765e43' => '五丈原bbs',
            '2c9486e65cef761a015d11d1141d5e45' => '享清福bbs',
            '2c9486e55cef76f3015d11d115235d47' => '亲亲一吻bbs',
            '2c9486ef5cef78c6015d11d115e65d40' => '人在旅途ebbs',
            '2c9486ef5cef78c6015d11d116b15d41' => '人文泰州的人民教师bbs',
            '2c9486ee5cef7d31015d11d117805e00' => '伊丽bbbbs',
            '2c9486e55cef76f3015d11d119365d49' => '伊靓法拉利bbs',
            '2c9486e65cef761a015d11d11a025e49' => '优美时尚5922287bbs',
            '2c9486e55cef76f3015d11d11ac95d4b' => '伯利亚bbs',
            '2c9486e65cef761a015d11d11ba45e4a' => '似水年华0119bbs',
            '2c9486e65cef761a015d11d11c675e4c' => '低头浅笑bbs',
            '2c9486ef5cef78c6015d11d11d2c5d44' => '低调的华丽12bbs',
            '2c9486e65cef761a015d11d11e155e4d' => '你瞒我瞒bbs',
            '2c9486e55cef76f3015d11d11edc5d4d' => '依兰哈刺bbs',
            '2c9486ee5cef7d31015d11d11fc65e01' => '光头沈平bbs',
            '2c9486e65cef761a015d11d120995e4f' => '光影点缀bbs',
            '2c9486e45cef7726015d11d121965f0e' => '兔兔007bbs',
            '2c9486ee5cef7d31015d11d1226b5e02' => '公主柔风bbs',
            '2c9486e45cef7726015d11d123505f0f' => '六粮液3748bbs',
            '2c9486e45cef7726015d11d1241d5f10' => '兰雨馨bbs',
            '2c9486ef5cef78c6015d11d124ce5d48' => '冬天的幸福bbs',
            '2c9486e65cef761a015d11d125a05e51' => '冬日恋曲bbs',
            '2c9486ee5cef7d31015d11d126505e04' => '冰雨bbs',
            '2c9486e55cef76f3015d11d127265d52' => '凉离bbs',
            '2c9486e55cef76f3015d11d1281f5d53' => '凤凰古城旅游bbs',
            '2c9486e55cef76f3015d11d128e15d54' => '凤凰花开的路口但看岁月静好bbs',
            '2c9486e55cef76f3015d11d129d35d55' => '刘海砍樵88bbs',
            '2c9486e55cef76f3015d11d12a875d57' => '十年一刻bbs',
            '2c9486ef5cef78c6015d11d12b4e5d4a' => '半支烟灰bbs',
            '2c9486ef5cef78c6015d11d12c035d4c' => '南方西施bbs',
            '2c9486ef5cef78c6015d11d12cc95d4d' => '卡塞蒂发bbs',
            '2c9486e45cef7726015d11d12da15f16' => '卡洛斯bbs',
            '2c9486e65cef761a015d11d12f2d5e53' => '口吐白沫bbs',
            '2c9486ee5cef7d31015d11d12fd95e05' => '叶、小沫bbs',
            '2c9486ee5cef7d31015d11d130af5e06' => '叶开kiiiibbs',
            '2c9486e65cef761a015d11d131745e55' => '吉诃德啦bbs',
            '2c9486e65cef761a015d11d1325b5e56' => '向往不如前往bbs',
            '2c9486ee5cef7d31015d11d133305e07' => '向着户外旅行bbs',
            '2c9486ef5cef78c6015d11d134015d4f' => '吕慧晶晶bbs',
            '2c9486e55cef76f3015d11d134dc5d5b' => '吾爱驴友bbs',
            '2c9486e45cef7726015d11d135955f1b' => '呼伦贝尔自助bbs',
            '2c9486e45cef7726015d11d136605f1c' => '和田子玉bbs',
            '2c9486e65cef761a015d11d1372d5e57' => '哈哈山bbs',
            '2c9486e45cef7726015d11d137f35f1e' => '喜欢狗、877522bbs',
            '2c9486e55cef76f3015d11d138ab5d5d' => '喵的春天bbs',
            '2c9486e55cef76f3015d11d139715d5e' => '嘻嘻长不大bbs',
            '2c9486e45cef7726015d11d13a2f5f1f' => '噗通扑通bbs',
            '2c9486e45cef7726015d11d13ae25f21' => '团龙格格bbs',
            '2c9486e65cef761a015d11d13ba15e58' => '土豆肉丝bbs',
            '2c9486e45cef7726015d11d13c4a5f23' => '多梦卡修bbs',
            '2c9486ee5cef7d31015d11d13ce75e0d' => '夜心万万bbs',
            '2c9486ee5cef7d31015d11d13dc15e0e' => '大概哇塞bbs',
            '2c9486e65cef761a015d11d13ea05e5b' => '天达-白雪铺路bbs',
            '2c9486ee5cef7d31015d11d13f615e0f' => '天达-静远bbs',
            '2c9486ee5cef7d31015d11d1401c5e10' => '天达-馨宜bbs',
            '2c9486e55cef76f3015d11d140f05d61' => '天达乐悠悠bbs',
            '2c9486ef5cef78c6015d11d141a75d52' => '天达大吴bbs',
            '2c9486e65cef761a015d11d142985e5e' => '天达迪文bbs',
            '2c9486e55cef76f3015d11d142fe5d62' => '天达黄山松bbs',
            '2c9486e55cef76f3015d11d1436c5d63' => '奔跑的香蕉bbs',
            '2c9486e65cef761a015d11d143d85e5f' => '女马甲号bbs',
            '2c9486ee5cef7d31015d11d144175e11' => '好摄之徒--丫丫bbs',
            '2c9486ef5cef78c6015d11d144835d54' => '如此人生1310894bbs',
            '2c9486ef5cef78c6015d11d144eb5d55' => '如水819bbs',
            '2c9486ef5cef78c6015d11d145515d56' => '妈妈的炫彩之旅bbs',
            '2c9486ee5cef7d31015d11d145b85e12' => '妮子0726bbs',
            '2c9486e65cef761a015d11d1463a5e60' => '娟子2006bbs',
            '2c9486ef5cef78c6015d11d1468f5d57' => '孤独的叶子bbs',
            '2c9486e65cef761a015d11d146ff5e61' => '宝宝儿儿bbs',
            '2c9486ee5cef7d31015d11d147645e13' => '宝贝不哭哭bbs',
            '2c9486ee5cef7d31015d11d147cb5e14' => '害虫漫步bbs',
            '2c9486e45cef7726015d11d148405f25' => '寻千里bbs',
            '2c9486e45cef7726015d11d148a75f26' => '寻觅那一抹温柔bbs',
            '2c9486ee5cef7d31015d11d148f75e15' => '封神clubbbs',
            '2c9486ef5cef78c6015d11d1496d5d58' => '小三的二bbs',
            '2c9486e45cef7726015d11d149e35f27' => '小五爱旅游bbs',
            '2c9486ee5cef7d31015d11d14a425e16' => '小保姆bbs',
            '2c9486e45cef7726015d11d14aa85f28' => '小兵长bbs',
            '2c9486ee5cef7d31015d11d14af95e17' => '小夏叶子bbs',
            '2c9486e55cef76f3015d11d14b7a5d64' => '小大老虎bbs',
            '2c9486ee5cef7d31015d11d14bec5e18' => '小小的骡子bbs',
            '2c9486e65cef761a015d11d14c5b5e62' => '小志777bbs',
            '2c9486ee5cef7d31015d11d14c995e19' => '小敏的小龟bbs',
            '2c9486ef5cef78c6015d11d14d025d59' => '小猪看世界bbs',
            '2c9486e65cef761a015d11d14d7b5e63' => '小白喵bbs',
            '2c9486e45cef7726015d11d14de55f29' => '小胡子的大叔bbs',
            '2c9486e55cef76f3015d11d14e4b5d65' => '小花乎乎bbs',
            '2c9486ee5cef7d31015d11d14e8a5e1a' => '少旅时代bbs',
            '2c9486e55cef76f3015d11d14f235d66' => '山水乐bbs',
            '2c9486e55cef76f3015d11d14f8b5d67' => '山水浙*bbs',
            '2c9486e45cef7726015d11d14ff35f2a' => '山野幽居bbs',
            '2c9486e45cef7726015d11d150595f2b' => '川页广隶bbs',
            '2c9486e65cef761a015d11d150c75e64' => '左手叶儿bbs',
            '2c9486ee5cef7d31015d11d0f9bb5df6' => '已有一天bbs',
            '2c9486ee5cef7d31015d11d0fa365df7' => '帕劳中国bbs',
            '2c9486e45cef7726015d11d0fac35f00' => '帕瓦7弟bbs',
            '2c9486ef5cef78c6015d11d0fb205d2f' => '平起平坐bbs',
            '2c9486e65cef761a015d11d0fbfe5e30' => '开心小帅bbs',
            '2c9486e65cef761a015d11d0fcc55e31' => '开心阳光bbs',
            '2c9486e65cef761a015d11d0fdb05e32' => '心灵彼岸bbs',
            '2c9486ef5cef78c6015d11d0fe515d30' => '快乐请柬bbs',
            '2c9486e55cef76f3015d11d0ff3c5d3c' => '怡然自得bbs',
            '2c9486ef5cef78c6015d11d100005d31' => '慈母手中线bbs',
            '2c9486e65cef761a015d11d100d95e36' => '慢游人吴晖bbs',
            '2c9486e45cef7726015d11d101a75f02' => '我乐毅bbs',
            '2c9486e45cef7726015d11d102745f03' => '我叫毛豆豆bbs',
            '2c9486e55cef76f3015d11d1033a5d3e' => '我是90后bbs',
            '2c9486e65cef761a015d11d103f15e39' => '我是曹平bbs',
            '2c9486e45cef7726015d11d104ab5f04' => '我是阿发bbs',
            '2c9486e65cef761a015d11d105545e3c' => '我是青春bbs',
            '2c9486ef5cef78c6015d11d106015d33' => '我爱我家11bbs',
            '2c9486ef5cef78c6015d11d106ca5d35' => '我的变速器bbs',
            '2c9486e55cef76f3015d11d1077e5d40' => '拉丁伦巴bbs',
            '2c9486ee5cef7d31015d11d108335df9' => '按时打算单位bbs',
            '2c9486ef5cef78c6015d11d109065d37' => '据斯蒂芬bbs',
            '2c9486ee5cef7d31015d11d109cd5dfa' => '探索者宁bbs',
            '2c9486ef5cef78c6015d11d10aa05d3a' => '敲章达人bbs',
            '2c9486ee5cef7d31015d11d10b6a5dfb' => '旅程天下bbs',
            '2c9486ef5cef78c6015d11d10c375d3b' => '旅行蛙bbs',
            '2c9486ee5cef7d31015d11d10cea5dfd' => '春天白玉兰bbs',
            '2c9486ee5cef7d31015d11d10da35dfe' => '晚秋伤心bbs',
            '2c9486e65cef761a015d11d10e785e3f' => '暹罗大猫bbs',
            '2c9486e65cef761a015d11d10f395e40' => '月亮是寂寞的眼bbs',
            '2c9486ef5cef78c6015d11d10fe55d3d' => '李薰明bbs',
            '2c9486e65cef761a015d11d110c45e41' => '来自腾讯微博的清清苹果心(xibbs',
            '2c9486e55cef76f3015d11d111795d43' => '板桥霜bbs',
            '2c9486e45cef7726015d11d112485f0a' => '极限冰地bbs',
            '2c9486e55cef76f3015d11d1130d5d45' => '桉树2009bbs',
            '2c9486e65cef761a015d11d113c55e44' => '梦幻天空1bbs',
            '2c9486ef5cef78c6015d11d114795d3f' => '梦秋bbs',
            '2c9486e65cef761a015d11d1159b5e46' => '樱丶桃bbs',
            '2c9486e55cef76f3015d11d1164e5d48' => '橘话丶bbs',
            '2c9486ef5cef78c6015d11d1171c5d42' => '欣欣向荣2011bbs',
            '2c9486e65cef761a015d11d117f35e47' => '毛毛大蝦bbs',
            '2c9486e65cef761a015d11d1199e5e48' => '水调歌头bbs',
            '2c9486e55cef76f3015d11d11a675d4a' => '永远的麦格雷迪bbs',
            '2c9486e45cef7726015d11d11b325f0c' => '汤汤126bbs',
            '2c9486e65cef761a015d11d11c0a5e4b' => '沉默的麻雀bbs',
            '2c9486e55cef76f3015d11d11cc95d4c' => '没有终点bbs',
            '2c9486ef5cef78c6015d11d11d9e5d45' => '泡沫葒茶bbs',
            '2c9486ef5cef78c6015d11d11e665d46' => '泼皮牛二bbs',
            '2c9486e65cef761a015d11d11f485e4e' => '浅笑依然bbs',
            '2c9486e45cef7726015d11d120445f0d' => '海上蓝雪bbs',
            '2c9486e55cef76f3015d11d120fe5d4e' => '海上随风bbs',
            '2c9486e55cef76f3015d11d122165d4f' => '海鲜的故事bbs',
            '2c9486ef5cef78c6015d11d122d25d47' => '消失的今天bbs',
            '2c9486e65cef761a015d11d123ba5e50' => '淡星映画廊bbs',
            '2c9486e45cef7726015d11d124865f11' => '淡茶色bbs',
            '2c9486e55cef76f3015d11d1254b5d50' => '清尘若惜bbs',
            '2c9486ee5cef7d31015d11d125e25e03' => '温泉李bbs',
            '2c9486e55cef76f3015d11d126cb5d51' => '溜呗溜bbs',
            '2c9486e45cef7726015d11d127ba5f12' => '滾一邊去bbs',
            '2c9486e45cef7726015d11d128735f13' => '熊小猫猫bbs',
            '2c9486ef5cef78c6015d11d1295f5d49' => '燕*嘟嘟bbs',
            '2c9486e55cef76f3015d11d12a265d56' => '爱你爱旅游bbs',
            '2c9486e65cef761a015d11d12af75e52' => '爱得到了吗bbs',
            '2c9486ef5cef78c6015d11d12ba15d4b' => '牛牛_jackbbs',
            '2c9486e45cef7726015d11d12c645f15' => '牵手去旅行mmbbs',
            '2c9486e55cef76f3015d11d12d395d58' => '猪猪侠51bbs',
            '2c9486e45cef7726015d11d12df55f17' => '玩嗨嗨bbs',
            '2c9486e65cef761a015d11d12f895e54' => '瑞嘉宝宝bbs',
            '2c9486e45cef7726015d11d1305a5f18' => '田园风光美哉bbs',
            '2c9486e55cef76f3015d11d131165d59' => '田小kibbs',
            '2c9486e45cef7726015d11d131e05f19' => '番茄洋葱bbs',
            '2c9486ef5cef78c6015d11d132b75d4e' => '疯颠颠的孩子丶bbs',
            '2c9486e55cef76f3015d11d133aa5d5a' => '瘦人bbs',
            '2c9486e45cef7726015d11d134705f1a' => '白天就梦游bbs',
            '2c9486ee5cef7d31015d11d1352f5e08' => '白眼龙bbs',
            '2c9486e55cef76f3015d11d135fb5d5c' => '相互原谅bbs',
            '2c9486ef5cef78c6015d11d136bb5d50' => '真不激动bbs',
            '2c9486e45cef7726015d11d1378b5f1d' => '神瑛侍者1313bbs',
            '2c9486ee5cef7d31015d11d138355e09' => '空间健康bbs',
            '2c9486ee5cef7d31015d11d138ed5e0a' => '笛苑sjpbbs',
            '2c9486ee5cef7d31015d11d139bd5e0b' => '粟欣儿bbs',
            '2c9486e45cef7726015d11d13a835f20' => '精装白粉笔bbs',
            '2c9486e45cef7726015d11d13b3f5f22' => '糖糖223bbs',
            '2c9486ee5cef7d31015d11d13bea5e0c' => '紫晶恋bbs',
            '2c9486e55cef76f3015d11d13c985d5f' => '紫陌zimobbs',
            '2c9486e65cef761a015d11d13d5f5e5a' => '紫飞语bbs',
            '2c9486e45cef7726015d11d13e405f24' => '结伴之旅bbs',
            '2c9486e55cef76f3015d11d13f065d60' => '给咖啡加点糖bbs',
            '2c9486e65cef761a015d11d13fda5e5c' => '绿色风玄bbs',
            '2c9486e65cef761a015d11d140895e5d' => '美丽宝典bbs',
            '2c9486ef5cef78c6015d11d141485d51' => '美丽芯bbs',
            '2c9486ef5cef78c6015d11d1420e5d53' => '翼云kikibbs',
            '2c9486ef5cef78c6015d11d0eb025d26' => '老仇bbs',
            '2c9486e55cef76f3015d11d0eb685d35' => '老闯bbs',
            '2c9486e45cef7726015d11d0ebcb5efd' => '考拉在树上bbs',
            '2c9486e65cef761a015d11d0ec325e24' => '耍酷玩失踪二bbs',
            '2c9486ef5cef78c6015d11d0ec8e5d27' => '胖嘟嘟妈bbs',
            '2c9486ee5cef7d31015d11d0ed005def' => '胖纸有人疼bbs',
            '2c9486ee5cef7d31015d11d0ed585df0' => '自由and飞翔bbs',
            '2c9486e65cef761a015d11d0edb55e25' => '良言之写意bbs',
            '2c9486e65cef761a015d11d0ee095e26' => '艺游天下bbs',
            '2c9486ee5cef7d31015d11d0ee4b5df1' => '若青琴bbs',
            '2c9486e65cef761a015d11d0eec95e27' => '英雄联盟001bbs',
            '2c9486e65cef761a015d11d0ef285e28' => '茉莉花暗纹bbs',
            '2c9486e55cef76f3015d11d0efb55d36' => '莺歌舞bbs',
            '2c9486e65cef761a015d11d0f0175e29' => '菜豆腐bbs',
            '2c9486e65cef761a015d11d0f0735e2a' => '蜡笔狼bbs',
            '2c9486e65cef761a015d11d0f0ca5e2b' => '行天下者也bbs',
            '2c9486ef5cef78c6015d11d0f11f5d28' => '行者dazhibbs',
            '2c9486ee5cef7d31015d11d0f1835df2' => '行走天境bbs',
            '2c9486e55cef76f3015d11d0f2005d37' => '西安康辉旅行bbs',
            '2c9486ef5cef78c6015d11d0f25b5d29' => '西望会好bbs',
            '2c9486e45cef7726015d11d0f2eb5efe' => '西行驿站bbs',
            '2c9486e55cef76f3015d11d0f3605d38' => '西部川藏行8bbs',
            '2c9486e55cef76f3015d11d0f4a35d39' => '记忆载未来bbs',
            '2c9486e65cef761a015d11d0f4f65e2c' => '识途小马哥bbs',
            '2c9486ee5cef7d31015d11d0f54f5df3' => '话剧人生bbs',
            '2c9486ef5cef78c6015d11d0f5b65d2a' => '赏遍天下bbs',
            '2c9486ef5cef78c6015d11d0f6075d2b' => '走西北bbs',
            '2c9486ef5cef78c6015d11d0f6605d2c' => '跋山涉水的人bbs',
            '2c9486ee5cef7d31015d11d0f6ad5df4' => '路上看到的bbs',
            '2c9486ee5cef7d31015d11d0f7005df5' => '路客熊bbs',
            '2c9486e65cef761a015d11d0f7815e2d' => '蹦跶的小驴bbs',
            '2c9486ef5cef78c6015d11d0f7bf5d2d' => '达达迪达bbs',
            '2c9486e45cef7726015d11d0f8315eff' => '过了就算bbs',
            '2c9486e55cef76f3015d11d0f89c5d3a' => '运斤生风bbs',
            '2c9486e65cef761a015d11d0f9125e2e' => '远方的心bbs',
            '2c9486ef5cef78c6015d11d0f95b5d2e' => '遥望北戴河bbs',
            '2c9486e65cef761a015d11d0de1e5e1c' => '那个冬季bbs',
            '2c9486e55cef76f3015d11d0de785d2d' => '那时候心无旁鹫bbs',
            '2c9486e55cef76f3015d11d0decb5d2e' => '郑小蕾豆腐干bbs',
            '2c9486e55cef76f3015d11d0df325d2f' => '酷酷的你bbs',
            '2c9486ef5cef78c6015d11d0dfb25d1f' => '野菊花swqbbs',
            '2c9486e65cef761a015d11d0e02d5e1d' => '金罗毛哥bbs',
            '2c9486e45cef7726015d11d0e08c5ef5' => '铁冠道人bbs',
            '2c9486e55cef76f3015d11d0e0fb5d30' => '铁寒bbs',
            '2c9486e65cef761a015d11d0e15e5e1e' => '阿盯bbs',
            '2c9486e65cef761a015d11d0e1b45e1f' => '陆璐寒bbs',
            '2c9486e45cef7726015d11d0e2145ef6' => '陽洸囡陔bbs',
            '2c9486e65cef761a015d11d0e2795e20' => '随便什么吧18bbs',
            '2c9486e45cef7726015d11d0e2f35ef7' => '随心旅游bbs',
            '2c9486e65cef761a015d11d0e3445e21' => '雪月风花bbs',
            '2c9486e45cef7726015d11d0e3a05ef8' => '零下273摄氏度bbs',
            '2c9486ef5cef78c6015d11d0e3e15d20' => '零胆酒bbs',
            '2c9486e55cef76f3015d11d0e45d5d31' => '霖霖roycebbs',
            '2c9486e45cef7726015d11d0e4c65ef9' => '露露3bbs',
            '2c9486ef5cef78c6015d11d0e5055d21' => '青春河边巢bbs',
            '2c9486ef5cef78c6015d11d0e5655d22' => '青椒肉丝bbs',
            '2c9486e55cef76f3015d11d0e5e25d32' => '青青水边草bbs',
            '2c9486ef5cef78c6015d11d0e6345d23' => '飘飘荡荡bbs',
            '2c9486ef5cef78c6015d11d0e6b25d24' => '飘飘非凡bbs',
            '2c9486ee5cef7d31015d11d0e71a5dec' => '香蕉牛奶okbbs',
            '2c9486e45cef7726015d11d0e7975efa' => '香香啊bbs',
            '2c9486ee5cef7d31015d11d0e7e55ded' => '驴友行走天涯bbs',
            '2c9486e55cef76f3015d11d0e8605d33' => '驴妈妈车友会沈劼bbs',
            '2c9486e55cef76f3015d11d0e8ca5d34' => '驴蠢是的念过倒bbs',
            '2c9486e45cef7726015d11d0e9275efb' => '骑马逛bbs',
            '2c9486e65cef761a015d11d0e9a95e22' => '高三朝bbs',
            '2c9486ef5cef78c6015d11d0e9e85d25' => '鸡丝豆腐饼干bbs',
            '2c9486e65cef761a015d11d0ea4b5e23' => '鹿城游侠bbs',
            '2c9486e45cef7726015d11d0eaae5efc' => '麦兜橘bbs',
        );

        $uid = array_search($username, $user_info);

        if (!$uid) {
            echo '未查询到对应的uid', "\n", 'end', "\n";
            return false;
        }
        return $uid;
    }

    /**
     * 生成器
     * @param array $data
     * @return Generator
     */
    private function getRows(array $data)
    {
        foreach ($data as $item) {
            yield $item;
        }
    }

    /**
     * 记录日志
     * @param $route
     * @param array $params
     * @param array $result
     * @param string $message
     * @param string $log_level
     */
    private function addDebugLog($route, $params = array(), $result = array(), $message = '', $log_level = 'debug')
    {
        if (!$message)
            $message = "调用 {$route} 出错.";
        $data = array(
            'route' => $route,
            'params' => $params,
            'result' => $result,
        );
        $this->client->exec2('/filelogger/add-log', array('message' => $message . "相关参数 " . json_encode($data), $log_level), 'post');
    }

    /**
     * 设置游标
     * @param $id
     * @return mixed
     */
    public function setLastId($id)
    {
        $result = $this->redis->set($this->redis_cache_key,$id,3600);
        return $result;
    }

    /**
     * 获取游标
     * @return mixed
     */
    public function getLastId()
    {
        $result = $this->redis->get($this->redis_cache_key);

        if ( empty($result) ) {
            $this->redis->set($this->redis_cache_key,0,3600);
            $result = $this->redis->get($this->redis_cache_key);
        }

        return $result;


    }

    private function stopFlag($content)
    {
        exit($content);
    }
}