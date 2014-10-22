<?php

/*
 * 微信公众平台自定义文件 
 * 朙囧月
 * 20131008
 */

/**
 * TOKEN验证
 */
define("TOKEN", "FangYaFei"); //将token值设置为你所需要的值，token可由开发者任意填写，用作生成签名。
//require_once (dirname(__FILE__) . "/../include/common.inc.php");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();


//$wechatObj->valid();

class wechatCallbackapiTest {
    public function responseMsg() {
        //get post data, May be due to the different environments
        global $dsql;
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];       
        
        //extract post data
        if (!empty($postStr)) {

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $RX_TYPE = trim($postObj->MsgType);
            $time = time();

            switch ($RX_TYPE) {
                case "text":
                    $resultStr = $this->handleText($postObj);
                    break;
                case "event":
                    $resultStr = $this->handleEvent($postObj);
                    break;
                default:
                    $resultStr = "Unknow msg type: " . $RX_TYPE;
                    break;
            }        
            echo $resultStr;
        } else {
            echo "ERROR";
            exit;
        }
    }

    

    public function responseText($object, $content, $flag = 0) {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }

    private function checkSignature() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    public function handleNews($postObj) {//最近活动
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $time = time();
        $textTpl = "<xml>
 					<ToUserName><![CDATA[%s]]></ToUserName>
 					<FromUserName><![CDATA[%s]]></FromUserName>
 					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
 					<ArticleCount>3</ArticleCount>
 					<Articles>
 						<item>
 							<Title><![CDATA[%s]]></Title> 
 							<Description><![CDATA[%s]]></Description>
 							<PicUrl><![CDATA[%s]]></PicUrl>
 							<Url><![CDATA[%s]]></Url>
 						</item>
 						<item>
 							<Title><![CDATA[%s]]></Title>
 							<Description><![CDATA[%s]]></Description>
 							<PicUrl><![CDATA[%s]]></PicUrl>
 							<Url><![CDATA[%s]]></Url>
 						</item>
						<item>
 							<Title><![CDATA[%s]]></Title>
 							<Description><![CDATA[%s]]></Description>
 							<PicUrl><![CDATA[%s]]></PicUrl>
 							<Url><![CDATA[%s]]></Url>
 						</item>
 					</Articles>
 					<FuncFlag>1</FuncFlag>
 				</xml>";
        $title1 = "IAP 雄起，传统服务App 已死？！";
        $Description1 = "1";
        $PicUrl1 = "http://tankr.net/s/medium/44Y9.jpg";
        $Url1 = "http://jandan.net/2013/10/09/paid-apps-arent-dead.html";

        $title2 = "米尔格伦的「服从权威实验」的真相";
        $Description2 = "2";
        $PicUrl2 = "http://tankr.net/s/medium/RRP3.jpg";
        $Url2 = "http://jandan.net/2013/10/09/truth-of-the-milgram.html";

        $title3 = "为什么说压力会让你吃得更多";
        $Description3 = "3";
        $PicUrl3 = "http://tankr.net/s/medium/QBQM.jpg";
        $Url3 = "http://jandan.net/2013/10/09/does-stress-make-you-hungry.html";

        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $title1, $Description1, $PicUrl1, $Url1, $title2, $Description2, $PicUrl2, $Url2, $title3, $Description3, $PicUrl3, $Url3);
        echo $resultStr;
    }

    public function handleHotNews($postObj) {//热门推荐
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $time = time();
        $textTpl = "<xml>
 					<ToUserName><![CDATA[%s]]></ToUserName>
 					<FromUserName><![CDATA[%s]]></FromUserName>
 					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
 					<ArticleCount>3</ArticleCount>
 					<Articles>
 						<item>
 							<Title><![CDATA[%s]]></Title> 
 							<Description><![CDATA[%s]]></Description>
 							<PicUrl><![CDATA[%s]]></PicUrl>
 							<Url><![CDATA[%s]]></Url>
 						</item>
 						<item>
 							<Title><![CDATA[%s]]></Title>
 							<Description><![CDATA[%s]]></Description>
 							<PicUrl><![CDATA[%s]]></PicUrl>
 							<Url><![CDATA[%s]]></Url>
 						</item>
						<item>
 							<Title><![CDATA[%s]]></Title>
 							<Description><![CDATA[%s]]></Description>
 							<PicUrl><![CDATA[%s]]></PicUrl>
 							<Url><![CDATA[%s]]></Url>
 						</item>
 					</Articles>
 					<FuncFlag>1</FuncFlag>
 				</xml>";
        $title1 = "<span color='red'>超级英雄为儿童医院刷墙</span>";
        $Description1 = "1";
        $PicUrl1 = "http://tankr.net/s/medium/YSFI.jpg";
        $Url1 = "http://jandan.net/2013/10/08/superhero-window-washers.html";

        $title2 = "罪犯乐园：委内瑞拉圣安东尼监狱";
        $Description2 = "2";
        $PicUrl2 = "http://tankr.net/s/medium/SYYY.jpg";
        $Url2 = "http://jandan.net/2013/10/08/venezuelas-paradise.html";

        $title3 = "女摄影师的孕期自拍";
        $Description3 = "3";
        $PicUrl3 = "http://ww1.sinaimg.cn/mw600/6d050af1gw1e9dmlbze8fj20go0p0q68.jpg";
        $Url3 = "http://jandan.net/2013/10/08/sophie-starzenski.html";

        return $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $title1, $Description1, $PicUrl1, $Url1, $title2, $Description2, $PicUrl2, $Url2, $title3, $Description3, $PicUrl3, $Url3);
    }
    
    
    public function handleText($postObj) {//根据关键词回复
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $Location_X = $postObj->Location_X;
        $time = time();
        $city = array('北京', '朝阳', '顺义', '怀柔', '通州', '昌平', '延庆', '丰台', '石景山', '大兴', '房山', '密云', '门头沟', '平谷', '八达岭', '佛爷顶', '汤河口', '密云上甸子', '斋堂', '霞云岭', '北京城区', '海淀', '天津', '宝坻', '东丽', '西青', '北辰', '蓟县', '汉沽', '静海', '津南', '塘沽', '大港', '武清', '宁河', '上海', '宝山', '嘉定', '南汇', '浦东', '青浦', '松江', '奉贤', '崇明', '徐家汇', '闵行', '金山', '石家庄', '张家口', '承德', '唐山', '秦皇岛', '沧州', '衡水', '邢台', '邯郸', '保定', '廊坊', '郑州', '新乡', '许昌', '平顶山', '信阳', '南阳', '开封', '洛阳', '商丘', '焦作', '鹤壁', '濮阳', '周口', '漯河', '驻马店', '三门峡', '济源', '安阳', '合肥', '芜湖', '淮南', '马鞍山', '安庆', '宿州', '阜阳', '亳州', '黄山', '滁州', '淮北', '铜陵', '宣城', '六安', '巢湖', '池州', '蚌埠', '杭州', '舟山', '湖州', '嘉兴', '金华', '绍兴', '台州', '温州', '丽水', '衢州', '宁波', '重庆', '合川', '南川', '江津', '万盛', '渝北', '北碚', '巴南', '长寿', '黔江', '万州天城', '万州龙宝', '涪陵', '开县', '城口', '云阳', '巫溪', '奉节', '巫山', '潼南', '垫江', '梁平', '忠县', '石柱', '大足', '荣昌', '铜梁', '璧山', '丰都', '武隆', '彭水', '綦江', '酉阳', '秀山', '沙坪坝', '永川', '福州', '泉州', '漳州', '龙岩', '晋江', '南平', '厦门', '宁德', '莆田', '三明', '兰州', '平凉', '庆阳', '武威', '金昌', '嘉峪关', '酒泉', '天水', '武都', '临夏', '合作', '白银', '定西', '张掖', '广州', '惠州', '梅州', '汕头', '深圳', '珠海', '佛山', '肇庆', '湛江', '江门', '河源', '清远', '云浮', '潮州', '东莞', '中山', '阳江', '揭阳', '茂名', '汕尾', '韶关', '南宁', '柳州', '来宾', '桂林', '梧州', '防城港', '贵港', '玉林', '百色', '钦州', '河池', '北海', '崇左', '贺州', '贵阳', '安顺', '都匀', '兴义', '铜仁', '毕节', '六盘水', '遵义', '凯里', '昆明', '红河', '文山', '玉溪', '楚雄', '普洱', '昭通', '临沧', '怒江', '香格里拉', '丽江', '德宏', '景洪', '大理', '曲靖', '保山', '呼和浩特', '乌海', '集宁', '通辽', '阿拉善左旗', '鄂尔多斯', '临河', '锡林浩特', '呼伦贝尔', '乌兰浩特', '包头', '赤峰', '南昌', '上饶', '抚州', '宜春', '鹰潭', '赣州', '景德镇', '萍乡', '新余', '九江', '吉安', '武汉', '黄冈', '荆州', '宜昌', '恩施', '十堰', '神农架', '随州', '荆门', '天门', '仙桃', '潜江', '襄樊', '鄂州', '孝感', '黄石', '咸宁', '成都', '自贡', '绵阳', '南充', '达州', '遂宁', '广安', '巴中', '泸州', '宜宾', '内江', '资阳', '乐山', '眉山', '凉山', '雅安', '甘孜', '阿坝', '德阳', '广元', '攀枝花', '银川', '中卫', '固原', '石嘴山', '吴忠', '西宁', '黄南', '海北', '果洛', '玉树', '海西', '海东', '济南', '潍坊', '临沂', '菏泽', '滨州', '东营', '威海', '枣庄', '日照', '莱芜', '聊城', '青岛', '淄博', '德州', '烟台', '济宁', '泰安', '西安', '延安', '榆林', '铜川', '商洛', '安康', '汉中', '宝鸡', '咸阳', '渭南', '太原', '临汾', '运城', '朔州', '忻州', '长治', '大同', '阳泉', '晋中', '晋城', '吕梁', '乌鲁木齐', '石河子', '昌吉', '吐鲁番', '库尔勒', '阿拉尔', '阿克苏', '喀什', '伊宁', '塔城', '哈密', '和田', '阿勒泰', '阿图什', '博乐', '克拉玛依', '拉萨', '山南', '阿里', '昌都', '那曲', '日喀则', '林芝', '台北县', '高雄', '台中', '海口', '三亚', '东方', '临高', '澄迈', '儋州', '昌江', '白沙', '琼中', '定安', '屯昌', '琼海', '文昌', '保亭', '万宁', '陵水', '西沙', '南沙岛', '乐东', '五指山', '琼山', '长沙', '株洲', '衡阳', '郴州', '常德', '益阳', '娄底', '邵阳', '岳阳', '张家界', '怀化', '黔阳', '永州', '吉首', '湘潭', '南京', '镇江', '苏州', '南通', '扬州', '宿迁', '徐州', '淮安', '连云港', '常州', '泰州', '无锡', '盐城', '哈尔滨', '牡丹江', '佳木斯', '绥化', '黑河', '双鸭山', '伊春', '大庆', '七台河', '鸡西', '鹤岗', '齐齐哈尔', '大兴安岭', '长春', '延吉', '四平', '白山', '白城', '辽源', '松原', '吉林', '通化', '沈阳', '鞍山', '抚顺', '本溪', '丹东', '葫芦岛', '营口', '阜新', '辽阳', '铁岭', '朝阳', '盘锦', '大连', '锦州');
        if (in_array($keyword, $city)) {
                $urlstr = file_get_contents("http://sou.qq.com/online/get_weather.php?callback=Weather&city=" . $keyword);
                $strgsh = "[" . substr($urlstr, 8, -2) . "]";
                $arrayjson = json_decode($strgsh, true);
                if ($arrayjson[0]['future']['forecast'][3]['BWEA'] == $arrayjson[0]['future']['forecast'][3]['EWEA']) {
                    $hwea = $arrayjson[0]['future']['forecast'][3]['BWEA'];
                } else {
                    $hwea = $arrayjson[0]['future']['forecast'][3]['BWEA'] . "转" . $arrayjson[0]['future']['forecast'][3]['EWEA'];
                }
                if ($arrayjson[0]['future']['forecast'][4]['BWEA'] == $arrayjson[0]['future']['forecast'][4]['EWEA']) {
                    $dhwea = $arrayjson[0]['future']['forecast'][4]['BWEA'];
                } else {
                    $dhwea = $arrayjson[0]['future']['forecast'][4]['BWEA'] . "转" . $arrayjson[0]['future']['forecast'][4]['EWEA'];
                }
                $huifu = "城市：" . $arrayjson[0]['future']['name'] . "。
现在气温是" . $arrayjson[0]['real']['temperature'] . "℃。
今日天气：" . $arrayjson[0]['future']['wea_0'] . "。
温度范围：" . $arrayjson[0]['future']['forecast'][0]['TMAX'] . "~" . $arrayjson[0]['future']['forecast'][0]['TMIN'] . "℃
明天天气：" . $arrayjson[0]['future']['wea_1'] . "
后天天气：" . $arrayjson[0]['future']['wea_2'] . "
大后天的：" . $hwea . "
大大后天：" . $dhwea;
            } else if ($keyword == 6) {
                $this->handleBangDing($postObj);
            } else if ($keyword == 1) {
                $this->handleNaXiang($postObj);
            } else if ($keyword == 2) {
                $this->handleYuLin($postObj);
            } else if ($keyword == 3) {
                $this->handleANa($postObj);
            } else if ($keyword == 4) {
                $this->handleJianJie($postObj);
            } else if ($keyword == 5) {
                $this->handleLianXi($postObj);
            } else if (strpos($keyword, "妈的") !== false) {
                $huifu = "你说脏话，我叫警察叔叔来抓你！ ";
            } else if (strpos($keyword, "操") !== false) {
                $huifu = "你说脏话，我叫警察叔叔来抓你！ 哼~ ";
            } /*else if (strpos($keyword, "帮助") !== false) {
                $huifu = "输入城市名称查天气就好啦! 比如输入”北京“";
            } else if (strpos($keyword, "是谁") !== false) {
                $huifu = "我是微天气， 你可以叫我小微，可以叫我小天，但不要叫我小气~";
            } else if (strpos($keyword, "小气") !== false) {
                $huifu = "你才小气呐！";
            }  else if ($keyword == "h" || $keyword == "H") {
                $huifu = "欢迎使用微天气，
输入城市名字（如：'北京'）并发送即可查询天气情况。
输入'H'或者'h'显示本帮助内容。
输入'冷笑话'即可查看冷笑话一枚。
输入'a'查看程序相关。
输入v查看版本号。";
            } */ else if ($keyword == 'a') {
                $huifu = "本程序是由李亮 创建。 
欢迎提出改进意见。:)";
            } else if ($keyword == "v") {
                $huifu = "版本号:1.0";
            } else {
                $huifu = "回复数字查看详细信息，

1:查看那香山介绍，
2:查看雨林谷介绍，
3:查看阿那亚介绍，
4:公司简介，
5:联系我们，
6:查看我的订单。";
            }
            $time = time();
            $textTpl = "<xml>
                                    <ToUserName><![CDATA[" . $fromUsername . "]]></ToUserName>
                                    <FromUserName><![CDATA[" . $toUsername . "]]></FromUserName>
                                    <CreateTime>" . $time . "</CreateTime>
                                    <MsgType><![CDATA[text]]></MsgType>
                                    <Content><![CDATA[" . $huifu . "]]></Content>
                                    <FuncFlag>0</FuncFlag>
                                    </xml>";
            if (!empty($keyword)) {
                $msgType = "text";
                $contentStr = "Welcome to wechat world!";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $textTpl;
            } else {
                $msgType = "text";
                $contentStr = "Welcome to wechat world!";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $textTpl;
            }
    }

    public function offersQuery($object) {//优惠查询
        $fromUsername = $object->FromUserName;
        $toUsername = $object->ToUserName;
        $keyword = trim($object->Content);
        $time = time();
        $textTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        
                    </xml>";
        if (!empty($keyword)) {
            $contentStr = "感谢您的使用！";
        } else {
            $contentStr = "中文请回复1，英文请回复2，日语请回复3，感谢您的关注！...";
        }
        $msgType = "text";
        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
        echo $resultStr;
    }

    public function stores($object) {//多图文显示
        $fromUsername = $object->FromUserName;
        $toUsername = $object->ToUserName;
        $time = time();
        $content = "";
        $textTpl = "<xml>
        <ToUserName><![CDATA[" . $fromUsername . "]]></ToUserName>
        <FromUserName><![CDATA[" . $toUsername . "]]></FromUserName>
        <CreateTime>" . $time . "</CreateTime>
        <MsgType><![CDATA[text]]></MsgType>
        <Content><![CDATA[" . $toUsername . "]]></Content>
        <FuncFlag>0</FuncFlag>
        </xml>";
    }

    
    public function subscription($object) {//单图文显示
        $fromUsername = $object->FromUserName;
        $toUsername = $object->ToUserName;
        $time = time();
        $ArticleCount = "1";

        $textTpl = "<xml>
 					<ToUserName><![CDATA[%s]]></ToUserName>
 					<FromUserName><![CDATA[%s]]></FromUserName>
 					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
 					<ArticleCount>%s</ArticleCount>
 					<Articles>
 						<item>
 							<Title><![CDATA[%s]]></Title> 
 							<Description><![CDATA[%s]]></Description>
 							<PicUrl><![CDATA[%s]]></PicUrl>
 						</item>
 					</Articles>
 					<FuncFlag>1</FuncFlag>
 				</xml>";

        $title = "暂未开通,敬请期待！";
        $Description = "全球最低,圆您土豪梦,没有998,只有997,坐拥小三不是梦,大U惠帮您来升华.";
        $PicUrl = "http://fangyafei.com/weixin/image/test.jpg";
        //$Url = "";



        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $ArticleCount, $title, $Description, $PicUrl, $Url);
        echo $resultStr;
    }
    
    public function subscription1($object) {//单图文显示
    	$fromUsername = $object->FromUserName;
    	$toUsername = $object->ToUserName;
    	$time = time();
    	
    
    	$textTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        
                    </xml>";
    
    	$contentStr="欢迎您关注清晏九洲微信服务平台";
    	//$Url = "";
    
    
    	$msgType = "text";
    	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
    	echo $resultStr;
    }

    
    
    public function handleJianJie($postObj) {//简介
    	$fromUsername = $postObj->FromUserName;
    	$toUsername = $postObj->ToUserName;
    	$time = time();
    	$content = "       清晏九洲成立于2010年，是由晏财智集团全资投资，专注于为全球高端客户提供世界级顶豪、私属度假方式的旅游地产投资集团。
			清晏九洲集团在中国一线度假胜地拥有三亚清晏那香山，清晏雨林谷，清晏陶然湾，千岛湖清晏悠然山，北戴河清晏阿那亚，四川峨眉山清晏山屿湖等二十二个白金七星级别墅度假区，为全球高端客户开创了一种跨时代的模式。
			追随中国皇家别院的顶豪生活，这是中国行宫文化的当代演绎，让每个客户都能“一生私享”顶豪别墅度假人生，春夏秋冬全部能在景致和环境最怡人的无处不在的“云端行宫”，私享极致生活；无论是温泉、高尔夫、阳光沙滩、游艇，还是峨眉山的金顶，青城的青城山，哪怕是置身于雨林中的天然氧吧，清晏九洲为每个客户的身心提供了惊叹于世界的完美享受。
			作为中国第一个提出“移动的家”的概念的创想者，首家“全区域，全功能，全天候”私享奢华度假生活的先行者，清晏九洲遵循世界顶级富豪生活方式为标准，将以其始终如一的热情，为客户提供一种无争的生活方式。";
    	$textTpl = "<xml>
    	<ToUserName><![CDATA[" . $fromUsername . "]]></ToUserName>
    	<FromUserName><![CDATA[" . $toUsername . "]]></FromUserName>
    	<CreateTime>" . $time . "</CreateTime>
    	<MsgType><![CDATA[text]]></MsgType>
    	<Content><![CDATA[" . $content . "]]></Content>
    	<FuncFlag>0</FuncFlag>
    	</xml>";
    	echo $textTpl;
    }
    
    
	
    public function handleLianXi($postObj) {//联系我们
    	$fromUsername = $postObj->FromUserName;
    	$toUsername = $postObj->ToUserName;
    	$time = time();
    	$content = "客服电话:
4000-180-140
官方网站:
www.fangyafei.com
北京接待中心:北京市朝阳区盈科中心A座7层";
    	$textTpl = "<xml>
    	<ToUserName><![CDATA[" . $fromUsername . "]]></ToUserName>
    	<FromUserName><![CDATA[" . $toUsername . "]]></FromUserName>
    	<CreateTime>" . $time . "</CreateTime>
    	<MsgType><![CDATA[text]]></MsgType>
    	<Content><![CDATA[" . $content . "]]></Content>
    	<FuncFlag>0</FuncFlag>
    	</xml>";
    	echo $textTpl;
    }
    
    

    public function handleYuLin($postObj) {//雨林谷
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $time = time();
        $textTpl = "<xml>
 					<ToUserName><![CDATA[%s]]></ToUserName>
 					<FromUserName><![CDATA[%s]]></FromUserName>
 					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
 					<ArticleCount>3</ArticleCount>
 					<Articles>
 						<item>
 							<Title><![CDATA[%s]]></Title> 
 							<Description><![CDATA[%s]]></Description>
 							<PicUrl><![CDATA[%s]]></PicUrl>
 							<Url><![CDATA[%s]]></Url>
 						</item>
 						<item>
 							<Title><![CDATA[%s]]></Title>
 							<Description><![CDATA[%s]]></Description>
 							<PicUrl><![CDATA[%s]]></PicUrl>
 							<Url><![CDATA[%s]]></Url>
 						</item>
						<item>
 							<Title><![CDATA[%s]]></Title>
 							<Description><![CDATA[%s]]></Description>
 							<PicUrl><![CDATA[%s]]></PicUrl>
 							<Url><![CDATA[%s]]></Url>
 						</item>
 					</Articles>
 					<FuncFlag>1</FuncFlag>
 				</xml>";
        $title1 = "龙湾·雨林谷，生产于雨林的建筑";
        $Description1 = "1";
        $PicUrl1 = "http://www.fangyafei.com/weixin/image/yulin1.jpg";
        $Url1 = "http://www.fangyafei.com/weixin/html/yulin1.html";

        $title2 = "温泉雨林天籁养生别墅";
        $Description2 = "2";
        $PicUrl2 = "http://www.fangyafei.com/weixin/image/yulin2.jpg";
        $Url2 = "http://www.fangyafei.com/weixin/html/yulin2.html";

        $title3 = "多节点园林景观,热带浪漫气息无处不在";
        $Description3 = "3";
        $PicUrl3 = "http://www.fangyafei.com/weixin/image/yulin3.jpg";
        $Url3 = "http://www.fangyafei.com/weixin/html/yulin3.html";

         $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $title1, $Description1, $PicUrl1, $Url1, $title2, $Description2, $PicUrl2, $Url2, $title3, $Description3, $PicUrl3, $Url3);
         echo $resultStr;
    }
    
    public function handleNaXiang($postObj) {//那香山
    	$fromUsername = $postObj->FromUserName;
    	$toUsername = $postObj->ToUserName;
    	$time = time();
    	$textTpl = "<xml>
    	<ToUserName><![CDATA[%s]]></ToUserName>
    	<FromUserName><![CDATA[%s]]></FromUserName>
    	<CreateTime>%s</CreateTime>
    	<MsgType><![CDATA[news]]></MsgType>
    	<ArticleCount>3</ArticleCount>
    	<Articles>
    	<item>
    	<Title><![CDATA[%s]]></Title>
    	<Description><![CDATA[%s]]></Description>
    	<PicUrl><![CDATA[%s]]></PicUrl>
    	<Url><![CDATA[%s]]></Url>
    	</item>
    	<item>
    	<Title><![CDATA[%s]]></Title>
    	<Description><![CDATA[%s]]></Description>
    	<PicUrl><![CDATA[%s]]></PicUrl>
    	<Url><![CDATA[%s]]></Url>
    	</item>
    	<item>
    	<Title><![CDATA[%s]]></Title>
    	<Description><![CDATA[%s]]></Description>
    	<PicUrl><![CDATA[%s]]></PicUrl>
    	<Url><![CDATA[%s]]></Url>
    	</item>
    	</Articles>
    	<FuncFlag>1</FuncFlag>
    	</xml>";
    	$title1 = "清晏九洲那香山度假别墅";
    	$Description1 = "1";
    	$PicUrl1 = "http://www.fangyafei.com/weixin/image/naxiang1.jpg";
    	$Url1 = "http://www.fangyafei.com/weixin/html/naxiang1.html";
    
    	$title2 = "极致用心创造 世界级名宅典范";
    	$Description2 = "2";
    	$PicUrl2 = "http://www.fangyafei.com/weixin/image/naxiang2.jpg";
    	$Url2 = "http://www.fangyafei.com/weixin/html/naxiang2.html";
    
    	$title3 = "那香山度假别墅户型赏析";
    	$Description3 = "3";
    	$PicUrl3 = "http://www.fangyafei.com/weixin/image/naxiang3.jpg";
    	$Url3 = "http://www.fangyafei.com/weixin/html/naxiang3.html";
    
    	 $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $title1, $Description1, $PicUrl1, $Url1, $title2, $Description2, $PicUrl2, $Url2, $title3, $Description3, $PicUrl3, $Url3);
    	 echo $resultStr;
    }
    
    
    public function handleANa($postObj) {//阿那亚
    	$fromUsername = $postObj->FromUserName;
    	$toUsername = $postObj->ToUserName;
    	$time = time();
    	$textTpl = "<xml>
    	<ToUserName><![CDATA[%s]]></ToUserName>
    	<FromUserName><![CDATA[%s]]></FromUserName>
    	<CreateTime>%s</CreateTime>
    	<MsgType><![CDATA[news]]></MsgType>
    	<ArticleCount>3</ArticleCount>
    	<Articles>
    	<item>
    	<Title><![CDATA[%s]]></Title>
    	<Description><![CDATA[%s]]></Description>
    	<PicUrl><![CDATA[%s]]></PicUrl>
    	<Url><![CDATA[%s]]></Url>
    	</item>
    	<item>
    	<Title><![CDATA[%s]]></Title>
    	<Description><![CDATA[%s]]></Description>
    	<PicUrl><![CDATA[%s]]></PicUrl>
    	<Url><![CDATA[%s]]></Url>
    	</item>
    	<item>
    	<Title><![CDATA[%s]]></Title>
    	<Description><![CDATA[%s]]></Description>
    	<PicUrl><![CDATA[%s]]></PicUrl>
    	<Url><![CDATA[%s]]></Url>
    	</item>
    	</Articles>
    	<FuncFlag>1</FuncFlag>
    	</xml>";
    	$title1 = "Aranya阿那亚全球高端度假天堂";
    	$Description1 = "1";
    	$PicUrl1 = "http://www.fangyafei.com/weixin/image/ana1.jpg";
    	$Url1 = "http://www.fangyafei.com/weixin/html/ana1.html";
    
    	$title2 = "中国度假享乐方式领导者";
    	$Description2 = "2";
    	$PicUrl2 = "http://www.fangyafei.com/weixin/image/ana2.jpg";
    	$Url2 = "http://www.fangyafei.com/weixin/html/ana2.html";
    
    	$title3 = "世界唯一  加西亚签名设计的links高尔夫球场";
    	$Description3 = "3";
    	$PicUrl3 = "http://www.fangyafei.com/weixin/image/ana3.jpg";
    	$Url3 = "http://www.fangyafei.com/weixin/html/ana3.html";
    
    	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $title1, $Description1, $PicUrl1, $Url1, $title2, $Description2, $PicUrl2, $Url2, $title3, $Description3, $PicUrl3, $Url3);
    	echo $resultStr;
    }
    
    
    
    public function handleBangDing($postObj) {// 
    	global $dsql;
    	$fromUsername = $postObj->FromUserName;
    	$toUsername = $postObj->ToUserName;
    	$time = time();
    	
    	//判断是否已经绑定
    	$sql = "SELECT * FROM #@__member WHERE weixinid='$fromUsername'";
    	$row = $dsql->GetOne($sql);
    	//file_put_contents("sql.txt", print_r($row));
    	 if(is_array($row)) {
    		$content = "该微信号已经和卡号".$row['card']."绑定,<a href=\"http://www.fangyafei.com/member/my_spa.php?fromusername=$fromUsername\">点击查看我的订单</a>";
    	} else {
    		$content = "尚未与您的卡号绑定,<a href=\"http://www.fangyafei.com/member/login1.php?fromusername=$fromUsername\">立即绑定</a>";
    	} 
    	$textTpl = "<xml>
    	<ToUserName><![CDATA[" . $fromUsername . "]]></ToUserName>
    	<FromUserName><![CDATA[" . $toUsername . "]]></FromUserName>
    	<CreateTime>" . $time . "</CreateTime>
    	<MsgType><![CDATA[text]]></MsgType>
    	<Content><![CDATA[" . $content . "]]></Content>
    	<FuncFlag>0</FuncFlag>
    	</xml>";
    	echo $textTpl;
    }
    
    public function handleDingDan($postObj) {//
    	global $dsql;
    	$fromUsername = $postObj->FromUserName;
    	$toUsername = $postObj->ToUserName;
    	$time = time();
    	 
    	//判断是否已经绑定
    	$sql = "SELECT * FROM #@__member WHERE weixinid='$fromUsername'";
    	$row = $dsql->GetOne($sql);
    	//file_put_contents("sql.txt", print_r($row));
    	if(is_array($row)) {
    		$content = "该微信号已经和卡号".$row['card']."绑定,<a href=\"http://www.fangyafei.com/member/my_spa.php?fromusername=$fromUsername\">点击查看我的订单</a>";
    	} else {
    		$content = "尚未与您的卡号绑定,<a href=\"http://www.fangyafei.com/member/login1.php?fromusername=$fromUsername\">立即绑定</a>";
    	}
    	$textTpl = "<xml>
    	<ToUserName><![CDATA[" . $fromUsername . "]]></ToUserName>
    	<FromUserName><![CDATA[" . $toUsername . "]]></FromUserName>
    	<CreateTime>" . $time . "</CreateTime>
    	<MsgType><![CDATA[text]]></MsgType>
    	<Content><![CDATA[" . $content . "]]></Content>
    	<FuncFlag>0</FuncFlag>
    	</xml>";
    	echo $textTpl;
    }
    
    
    public function handleEvent($object) {//菜单判断
        $contentStr = "";
        $key = $object->EventKey;

        if (!empty($key)) {
            if ($key == "jianjie") {
                $resultStr = $this->handleJianJie($object);
            } else if ($key == "lianxiwomen") {
                $resultStr = $this->handleLianXi($object);
            } else if ($key == "woyaobangding") {
                $resultStr = $this->handleBangDing($object);
            } else if ($key == "yulingu") {
                $resultStr = $this->handleYuLin($object);
            } else if ($key == "wodedingdan") {
                $resultStr = $this->handleDingDan($object);
            } else if ($key == "V1001_03_01") {
                $resultStr = $this->subscription($object);
            } else if ($key == "naxiangshan") {
            	$resultStr = $this->handleNaXiang($object);
            } else if ($key == "anaya") {
            	$resultStr = $this->handleANa($object);
            }else {
                // $contentStr = "感谢您关注【大U惠顾】111111111111";
                $resultStr = $this->subscription1($object);
            }
        } else {
            $contentStr = "购物新时尚";
            $resultStr = $this->responseText($object, $contentStr);
        }
        return $resultStr;
    }

}

//验证结束
include 'qmenu.php'; //引入菜单页面
?>

