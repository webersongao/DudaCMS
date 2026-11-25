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

use Pay\Exceptions\Exception;

class IpHelper
{
    private static $checkedIps = [];

    public static function is_ip($str)
    {
        $ip = explode('.', $str);
        for ($i = 0; $i < count($ip); $i++) {
            if ($ip[$i] > 255) {
                return false;
            }
        }
        return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $str);
    }

    public static function  ip()
    {
        // 客户端可伪造 X-Forwarded-For / Client-IP，直接以连接来源为准。
        $remote = (string) (request()->server('REMOTE_ADDR', ''));
        return filter_var($remote, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) ? $remote : '未知IP';
    }

    /**
     * 获取真实ip地址
     * @param bool $int
     * @return array|false|float|int|mixed|string
     */
    public static function getRealIp(bool $int = false)
    {
        $remote = (string) request()->server('REMOTE_ADDR', '');
        $ip = $remote;
        // 仅当直连来源是内网/代理地址时才采信转发头，且只取第一个合法地址。
        if (self::isPrivateIp($remote)) {
            $forwarded = (string) request()->server('HTTP_X_FORWARDED_FOR', request()->server('HTTP_X_REAL_IP', ''));
            if ($forwarded !== '') {
                foreach (array_map('trim', explode(',', $forwarded)) as $candidate) {
                    if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
                        $ip = $candidate;
                        break;
                    }
                }
            }
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) === false) {
            $ip = '0.0.0.0';
        }

        return $int && $ip ? inet_pton($ip) : $ip;
    }

    private static function isPrivateIp(string $ip): bool
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)
            || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /**
     * 获取IP所属归属地
     * @param $ip
     * @param string $type
     * @return mixed|string
     */
    public static function queryIpLocalInfo($ip, $type = 'text')
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) === false) {
            return '';
        }
        if (!function_exists('curl_init')) {
            return '';
        }
        try {
            $url = 'https://ipwho.is/' . rawurlencode($ip) . '?lang=zh-CN';
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_USERAGENT => 'DudaCMS/1.0',
            ]);
            $body = curl_exec($ch);
            curl_close($ch);
            if (!is_string($body)) {
                return '';
            }
            $result = json_decode($body, true);
            if (!is_array($result) || empty($result['success'])) {
                return '内部网络';
            }
            if ($type !== 'text') {
                return $result;
            }
            return (string) ($result['country'] ?? '') . (string) ($result['region'] ?? '') . (string) ($result['city'] ?? '');
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * 验证 IP 地址是否为内网 IP
     * @param string
     * @return bool
     */
    public static function validInternalIp($ip): bool
    {
        if (!self::is_ip($ip)) {
            return false;
        }
        $ip_address = explode('.', $ip);
        if ($ip_address[0] == 10) {
            return true;
        }
        if ($ip_address[0] == 172 and $ip_address[1] > 15 and $ip_address[1] < 32) {
            return true;
        }
        if ($ip_address[0] == 192 and $ip_address[1] == 168) {
            return true;
        }
        return false;
    }

    public static function checkIp(?string $requestIp, $ips): bool
    {
        if (null === $requestIp) {
            return false;
        }

        if (!\is_array($ips)) {
            $ips = [$ips];
        }

        $method = substr_count($requestIp, ':') > 1 ? 'checkIp6' : 'checkIp4';

        foreach ($ips as $ip) {
            if (self::$method($requestIp, $ip)) {
                return true;
            }
        }

        return false;
    }

    public static function checkIp4(?string $requestIp, string $ip)
    {
        $cacheKey = $requestIp . '-' . $ip;
        if (isset(self::$checkedIps[$cacheKey])) {
            return self::$checkedIps[$cacheKey];
        }

        if (!filter_var($requestIp, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            return self::$checkedIps[$cacheKey] = false;
        }

        if (str_contains($ip, '/')) {
            [$address, $netmask] = explode('/', $ip, 2);

            if ('0' === $netmask) {
                return self::$checkedIps[$cacheKey] = filter_var($address, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4);
            }

            if ($netmask < 0 || $netmask > 32) {
                return self::$checkedIps[$cacheKey] = false;
            }
        } else {
            $address = $ip;
            $netmask = 32;
        }

        if (false === inet_pton($address)) {
            return self::$checkedIps[$cacheKey] = false;
        }

        return self::$checkedIps[$cacheKey] = 0 === substr_compare(sprintf('%032b', inet_pton($requestIp)), sprintf('%032b', inet_pton($address)), 0, $netmask);
    }

    public static function checkIp6(?string $requestIp, string $ip)
    {
        $cacheKey = $requestIp . '-' . $ip;
        if (isset(self::$checkedIps[$cacheKey])) {
            return self::$checkedIps[$cacheKey];
        }

        if (!((\extension_loaded('sockets') && \defined('AF_INET6')) || @inet_pton('::1'))) {
            throw new \RuntimeException('Unable to check Ipv6. Check that PHP was not compiled with option "disable-ipv6".');
        }

        if (str_contains($ip, '/')) {
            [$address, $netmask] = explode('/', $ip, 2);

            if ('0' === $netmask) {
                return (bool) unpack('n*', @inet_pton($address));
            }

            if ($netmask < 1 || $netmask > 128) {
                return self::$checkedIps[$cacheKey] = false;
            }
        } else {
            $address = $ip;
            $netmask = 128;
        }

        $bytesAddr = unpack('n*', @inet_pton($address));
        $bytesTest = unpack('n*', @inet_pton($requestIp));

        if (!$bytesAddr || !$bytesTest) {
            return self::$checkedIps[$cacheKey] = false;
        }

        for ($i = 1, $ceil = ceil($netmask / 16); $i <= $ceil; ++$i) {
            $left = $netmask - 16 * ($i - 1);
            $left = ($left <= 16) ? $left : 16;
            $mask = ~(0xffff >> $left) & 0xffff;
            if (($bytesAddr[$i] & $mask) != ($bytesTest[$i] & $mask)) {
                return self::$checkedIps[$cacheKey] = false;
            }
        }

        return self::$checkedIps[$cacheKey] = true;
    }
}
