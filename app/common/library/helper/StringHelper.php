<?php
// +----------------------------------------------------------------------
// | DudaCMS
// +----------------------------------------------------------------------
// | Copyright (c) 2026 https://ask.btbk.org
// +----------------------------------------------------------------------
// | DudaCMS团队一款基于TP8开发的社交化知识付费问答系统、企业内部知识库系统，打造私有社交化问答、内部知识存储
// +----------------------------------------------------------------------
// | Author: DudaCMS团队 <ask@btbk.org>
// +----------------------------------------------------------------------

namespace app\common\library\helper;

use Exception;
use Ramsey\Uuid\Uuid;

class StringHelper
{
    /**
     * 生成Uuid
     * @param string $type 类型 默认时间 time/md5/random/sha1/uniqid 其中uniqid不需要特别开启php函数
     * @param string $name 加密名
     * @return string
     * @throws Exception
     */
    public static function uuid(string $type = 'time', string $name = 'aws'): string
    {
        switch ($type) {
            // 生成版本1（基于时间的）UUID对象
            case 'time':
                $uuid = Uuid::uuid1();
                return $uuid->toString();
                break;
            // 生成第三个版本（基于名称的和散列的MD5）UUID对象
            case 'md5':
                $uuid = Uuid::uuid3(Uuid::NAMESPACE_DNS, $name);
                return $uuid->toString();
                break;
            // 生成版本4（随机）UUID对象
            case 'random':
                $uuid = Uuid::uuid4();
                return $uuid->toString();
                break;
            // 产生一个版本5（基于名称和散列的SHA1）UUID对象
            case 'sha1':
                $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, $name);
                return $uuid->toString();
                break;
            // php自带的唯一id
            case  'uniqid':
                return md5(uniqid(md5(microtime(true) . self::randomNum(8)), true));
                break;
        }
    }

    /**
     * 字符串截取
     * @param $str
     * @param int $start
     * @param int $length
     * @param string $charset
     * @param bool $suffix
     * @return false|string
     */
    public static function m_substr($str, int $start = 0, int $length = 255, string $charset = "utf-8", bool $suffix = true)
    {
        // 过滤html代码
        $str = strip_tags($str);
        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
            $strLen = mb_strlen($str, $charset);
        } elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);
            $strLen = iconv_strlen($str, $charset);
        } else {
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
            $strLen = count($match[0]);
        }
        if ($suffix && $strLen > $length) $slice .= '...';
        return $slice;
    }

    /**
     * @param $content
     * @return string
     * 字符串替换
     */
    public static function htmlReplace($content): string
    {
        $content = str_replace("&lt;", "<", $content);
        $content = str_replace("&gt;", ">", $content);
        $content = str_replace("&namp;", "&", $content);
        return str_replace("&quot;", "\"", $content);
    }

    /**
     * 随机字符
     * @param int $length 长度
     * @param string $type 类型
     * @param int $convert 转换大小写 1 大写 0 小写
     * @return string
     */
    public static function randomNum(int $length = 10, string $type = 'all', int $convert = 0): string
    {
        $config = array(
            'number' => '1234567890',
            'letter' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'string' => 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
            'all' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
        );

        if (!isset($config[$type])) $type = 'letter';
        $string = $config[$type];

        $code = '';
        $strLen = strlen($string) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= $string[mt_rand(0, $strLen)];
        }
        if (!empty($convert)) {
            $code = ($convert > 0) ? strtoupper($code) : strtolower($code);
        }
        return $code;
    }

    /**
     * PHP格式化字节大小
     * @param number $size 字节数
     * @param string $delimiter 数字和单位分隔符
     * @return string            格式化后的带单位的大小
     */
    public static function formatBytes($size, string $delimiter = ''): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
        return round($size, 2) . $delimiter . $units[$i];
    }

    /**
     * 将$name中的下划线转换成类名   全如  aa_aa   变成 AaAa
     * @param $name
     * @return string
     */
    public static function formatClass($name): string
    {
        $temp_array = array();
        $arr = explode("_", $name);
        foreach ($arr as $key => $value) {
            $temp_array[] = ucfirst($value);
        }
        return implode('', $temp_array);
    }

    /**
     * @param string $str
     * @return string
     */
    public static function getOrderSn(string $str = ""): string
    {
        return $str . date("YmdHis", time()) . sprintf('%06s', rand(0, 999999));
    }

    /**
     * 过滤字符串中的一些内容
     * @param string $value
     * @return string
     */
    public static function paramFilter(string $value = ""): string
    {
        $value = preg_replace("/<script[\s\S]*?<\/script>/im", "", $value);
        return preg_replace("/<script>|<\/script>/im", "", $value);
    }

    /**
     * @param string|null $str
     * @return array|string|string[]|null
     */
    public static function stripTagsClear(?string $str = null)
    {
        $str = preg_replace('/<[^>]+>/', '', preg_replace("/[\r\n\t ]{1,}/", ' ', self::delNbsp(strip_tags($str))));
        return preg_replace('/&(\w{4});/i', '', $str);
    }

    /**
     * 删除空白字段
     * @param $str
     * @return string
     */
    public static function delNbsp($str): string
    {
        $str = str_replace("　", ' ', str_replace("", ' ', $str));
        $str = preg_replace("/[\r\n\t ]{1,}/", ' ', $str);
        return trim($str);
    }

    /**
     * 移除微信昵称中的emoji字符
     * @param string $nickname
     * @return string
     */
    public static function removeEmoji(string $nickname): string
    {
        $clean_text = "";
        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $nickname);
        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);
        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);
        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);
        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);
        return trim($clean_text);
    }

    /**
     * @param int $num
     * @param int $length
     * @param string $prefix
     * @return array 生成卡密
     */
    public static function getCardId(int $num = 1, int $length = 10, string $prefix = 'HX_'): array
    {
        //输出数组
        $card = array();
        //填补字符串
        $pad = '';
        //日期
        $temp = time();
        $Y = date('Y', $temp);
        $M = date('m', $temp);
        $D = date('d', $temp);
        $TD = date('YmdHis', $temp);
        //长度
        $LY = strlen((string)$Y);
        $LM = strlen((string)$M);
        $LD = strlen((string)$D);
        $LTD = strlen((string)$TD);
        //流水号长度
        $W = 5;
        //根据长度生成填补字串
        if ($length <= 12) {
            $pad = $prefix . self::randomNum($length - $W);
        } else if ($length <= 16) {
            $pad = $prefix . (string)$Y . self::randomNum($length - ($LY + $W));
        } else if ($length <= 20) {
            $pad = $prefix . (string)$Y . (string)$M . self::randomNum($length - ($LY + $LM + $W));
        } else {
            $pad = $prefix . (string)$TD . self::randomNum($length - ($LTD + $W));
        }
        //生成X位流水号
        for ($i = 0; $i < $num; $i++) {
            $STR = $pad . str_pad((string)($i + 1), $W, '0', STR_PAD_LEFT);
            $card[$i] = $STR;
        }
        return $card;
    }

    //生成密码
    public static function getCardPwd($num = 1): array
    {
        $pwd = array();
        for ($i = 0; $i < $num; $i++) {
            //生成基本随机数
            $chard = substr(MD5(uniqid(mt_rand(), true)), 8, 16) . self::randomNum(4, '2');
            $pwd[$i] = strtoupper($chard);
        }
        return $pwd;
    }

    //获取加密token
    public static function getToken($data): string
    {
        $arr = '';
        if (is_array($data)) {
            $arr = implode('', $data);
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
            $arr = implode('', $data);
        } elseif (is_string($data)) {
            $arr = $data;
        }
        return md5($arr);
    }

    public static function urlStringToArray($url_param): array
    {
        $arr = explode('&', $url_param); //转成数组
        $result = array();
        foreach ($arr as $k => $v) {
            $arr = explode('=', $v);
            $result[$arr[0]] = $arr[1];
        }

        return $result;
    }

    /**
     * url参数字符串转换为数组
     * @param $query
     * @return array
     */
    public static function convertUrlQuery($query): array
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    /**
     * 敏感词替换
     * @param $content
     * @param string $replace
     * @return array|mixed
     */
    public static function sensitive_words($content, string $replace = '*')
    {
        if (!$content or !get_setting('sensitive_words')) {
            return $content;
        }

        if (is_array($content)) {
            foreach ($content as $key => $val) {
                $content[$key] = self::sensitive_words($val, $replace);
            }
            return $content;
        }

        $sensitive_words = str_replace(array("/r/n", "/r", "/n"), '\n', get_setting('sensitive_words'));
        $sensitive_words = explode("\n", $sensitive_words);

        foreach ($sensitive_words as $word) {
            $word = trim($word);
            if (!$word) {
                continue;
            }

            if (substr($word, 0, 1) == '{' and substr($word, -1, 1) == '}') {
                $regex[] = substr($word, 1, -1);
            } else {
                $word_length = mb_strlen($word);
                $replace_str = str_repeat($replace, $word_length);
                $content = str_replace($word, $replace_str, $content);
            }
        }

        if (isset($regex)) {
            preg_replace($regex, '***', $content);
        }

        return $content;
    }

    /**
     * 是否包含敏感词
     * @param $content
     * @return bool
     */
    public static function sensitive_word_exists($content): bool
    {
        if (!$content or !get_setting('sensitive_words')) {
            return false;
        }

        if (is_array($content)) {
            foreach ($content as $key => $val) {
                if (self::sensitive_word_exists($val)) {
                    return true;
                }
            }
            return false;
        }

        $sensitive_words = explode("\n", get_setting('sensitive_words'));

        foreach ($sensitive_words as $word) {
            $word = trim($word);
            if (!$word) {
                continue;
            }
            if (substr($word, 0, 1) == '{' and substr($word, -1, 1) == '}') {
                if (preg_match(substr($word, 1, -1), $content)) {
                    return true;
                }
            } else {
                if (strstr($content, $word)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 是否有特殊字符
     * @param $str
     * @return bool
     */
    public static function hasSpecialChar($str): bool
    {
        $len   = mb_strlen($str);
        $array = [];
        for ($i = 0; $i < $len; $i++) {
            $array[] = mb_substr($str, $i, 1, 'utf-8');
            if (strlen($array[$i]) >= 4) {
                return true;
            }
        }
        return false;
    }

    /**
     * 搜索关键词标准化：只保留可读文本，限制长度，阻断 SQL 探测串。
     * 返回空字符串表示该关键词不应写入 search_log / 热门搜索。
     */
    public static function sanitizeSearchKeyword($value, int $maxLength = 60): string
    {
        $keyword = is_scalar($value) ? (string) $value : '';
        $keyword = htmlspecialchars_decode($keyword, ENT_QUOTES);
        $keyword = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $keyword) ?: '';
        $keyword = preg_replace('/\s+/u', ' ', $keyword) ?: '';
        $keyword = trim($keyword, " \t\r\n\0\x0B/\\'\"#<>{}[]()");

        if ($keyword === '') {
            return '';
        }

        // 扫描器/注入样本特征：不进入日志，也不进入热门搜索
        $patterns = [
            '/\b(?:union\s+select|select\s+.{0,80}\bfrom\b|insert\s+into|delete\s+from|drop\s+table|update\s+\w+\s+set)\b/i',
            '/\border\s+by\s+\d+\b/i',
            '/\b(?:and|or)\s+\d+\s*=\s*\d+\b/i',
            '/\bnull(?:\s*null){1,}\b/i',
            '/\b(?:all|any|some)\s+null\b/i',
            '/\bcase\s+when\b/i',
            '/\binformation_schema\b/i',
            '/\b(?:concat|elt|floor|extractvalue|updatexml)\s*\(/i',
            '/0x[0-9a-f]{6,}/i',
            '/quot/i',
            '/(?:--|#)\s*$|\/\*.*?\*\//',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $keyword) === 1) {
                return '';
            }
        }

        if (mb_strlen($keyword) > $maxLength) {
            $keyword = mb_substr($keyword, 0, $maxLength);
        }

        return $keyword;
    }
}
