<?php
// +----------------------------------------------------------------------
// | DudaCMS 内置短信服务
// +----------------------------------------------------------------------
// | 由原 plugins/sms 迁移而来：不再依赖插件框架与 hook 事件，
// | 配置统一存放于 dask_config 的「短信配置」分组（group 14）。
// +----------------------------------------------------------------------
namespace app\common\service;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use app\common\library\helper\DataHelper;
use app\common\library\helper\IpHelper;
use Darabonba\OpenApi\Models\Config;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Sms\V20190711\Models\SendSmsRequest as TencentSendSmsRequest;
use TencentCloud\Sms\V20190711\SmsClient;
use think\facade\Cache;

final class SmsService
{
    /** 验证码有效期与同号码发送间隔（秒）。 */
    private const CODE_TTL = 300;

    /** 验证码校验失败锁定：10 分钟内最多 5 次。 */
    private const MAX_VERIFY_FAILS = 5;
    private const FAIL_WINDOW = 600;

    public static function isEnabled(): bool
    {
        return self::driver() !== 'N';
    }

    public static function driver(): string
    {
        $driver = strtolower(trim((string) get_setting('sms_enable', 'N')));
        return in_array($driver, ['ali', 'tencent'], true) ? $driver : 'N';
    }

    /**
     * 发送短信验证码。
     * 返回结构与原 hook('sms') 的 JSON 结果一致，调用方不再需要 json_decode。
     */
    public static function send($mobile): array
    {
        $mobile = (string) $mobile;
        $driver = self::driver();
        if ($driver === 'N') {
            return self::response(0, L('短信功能未启用'));
        }
        if (preg_match('/^1[3-9]\d{9}$/', $mobile) !== 1) {
            return self::response(0, '手机号格式不正确');
        }

        $cacheKey = self::cacheKey($mobile);
        if (Cache::get($cacheKey)) {
            return self::response(0, L('验证码5分钟内有效，请使用收到的验证码进行填写'));
        }

        // 部分 SDK 在 PHP 8.2+ 会触发 deprecated 提示，这里局部屏蔽。
        $oldLevel = error_reporting();
        error_reporting($oldLevel & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        try {
            $code = random_int(100000, 999999);
            return $driver === 'ali' ? self::sendAli($mobile, $code, $cacheKey) : self::sendTencent($mobile, $code, $cacheKey);
        } catch (\Throwable $e) {
            return self::response(0, '短信发送失败：' . $e->getMessage());
        } finally {
            error_reporting($oldLevel);
        }
    }

    /** 校验验证码：一次性使用，并对同一手机号+IP 的失败次数限流。 */
    public static function verify($mobile, $code): bool
    {
        $mobile = (string) $mobile;
        $cacheKey = self::cacheKey($mobile);
        $failKey = 'sms_fail_' . md5($mobile . '|' . IpHelper::getRealIp());
        $fails = (int) Cache::get($failKey, 0);
        if ($fails >= self::MAX_VERIFY_FAILS) {
            return false;
        }

        $cached = Cache::get($cacheKey);
        $ok = $cached !== null && $cached !== '' && $code !== null && $code !== ''
            && hash_equals((string) $cached, (string) $code);

        if ($ok) {
            Cache::delete($cacheKey);
            Cache::delete($failKey);
            return true;
        }

        Cache::set($failKey, $fails + 1, self::FAIL_WINDOW);
        return false;
    }

    public static function cacheKey(string $mobile): string
    {
        return 'sms_' . $mobile;
    }

    private static function sendAli(string $mobile, int $code, string $cacheKey): array
    {
        $accessKeyId = (string) get_setting('sms_ali_access_key_id', '');
        $accessKeySecret = (string) get_setting('sms_ali_access_key_secret', '');
        $signName = (string) get_setting('sms_ali_sign_name', '');
        $templateCode = (string) get_setting('sms_ali_template_code', '');
        if ($accessKeyId === '' || $accessKeySecret === '' || $signName === '' || $templateCode === '') {
            return self::response(0, '阿里云短信配置不完整');
        }

        $config = new Config([
            'accessKeyId'     => $accessKeyId,
            'accessKeySecret' => $accessKeySecret,
        ]);
        // 仅允许官方节点域名，避免配置成任意地址的 SSRF 风险。
        $endpoint = (string) get_setting('sms_ali_endpoint', 'dysmsapi.aliyuncs.com');
        $config->endpoint = preg_match('/^[a-z0-9.-]+\.aliyuncs\.com$/i', $endpoint)
            ? $endpoint
            : 'dysmsapi.aliyuncs.com';

        $client = new Dysmsapi($config);
        $request = new SendSmsRequest([
            'phoneNumbers'  => $mobile,
            'signName'      => $signName,
            'templateCode'  => $templateCode,
            'templateParam' => json_encode(['code' => (string) $code], JSON_UNESCAPED_UNICODE),
        ]);
        $result = DataHelper::objToArray($client->sendSms($request)->body);
        if (($result['code'] ?? '') !== 'OK') {
            return self::response(0, (string) ($result['message'] ?? '短信发送失败'));
        }

        Cache::set($cacheKey, $code, self::CODE_TTL);
        self::writeLog($mobile, '阿里云短信', $templateCode, $code);
        return self::response(1, L('验证码发送成功'), ['mobile' => $mobile]);
    }

    private static function sendTencent(string $mobile, int $code, string $cacheKey): array
    {
        $appId = (string) get_setting('sms_tencent_app_id', '');
        $secretId = (string) get_setting('sms_tencent_secret_id', '');
        $secretKey = (string) get_setting('sms_tencent_access_key_secret', '');
        $signName = (string) get_setting('sms_tencent_sign_name', '');
        $templateCode = (string) get_setting('sms_tencent_template_code', '');
        if ($appId === '' || $secretId === '' || $secretKey === '' || $signName === '' || $templateCode === '') {
            return self::response(0, '腾讯云短信配置不完整');
        }

        $cred = new Credential($secretId, $secretKey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint('sms.tencentcloudapi.com');
        $clientProfile = new ClientProfile();
        $clientProfile->setSignMethod('HmacSHA256');
        $clientProfile->setHttpProfile($httpProfile);
        $client = new SmsClient($cred, '', $clientProfile);

        $request = new TencentSendSmsRequest();
        $request->SmsSdkAppid = $appId;
        $request->Sign = $signName;
        $request->ExtendCode = '0';
        $request->PhoneNumberSet = ['+86' . $mobile];
        $request->SenderId = '';
        $request->SessionContext = '';
        $request->TemplateID = $templateCode;
        $request->TemplateParamSet = [(string) $code, '5'];

        $response = json_decode($client->SendSms($request)->toJsonString(), true);
        $status = $response['SendStatusSet'][0]['Code'] ?? '';
        if ($status !== 'Ok') {
            return self::response(0, L('短信发送失败'));
        }

        Cache::set($cacheKey, $code, self::CODE_TTL);
        self::writeLog($mobile, '腾讯云短信', $templateCode, $code);
        return self::response(1, L('短信发送成功'), ['mobile' => $mobile]);
    }

    /** 日志只保留脱敏内容，不落明文验证码。 */
    private static function writeLog(string $mobile, string $type, string $templateCode, int $code): void
    {
        db('sms_log')->insert([
            'mobile'        => $mobile,
            'send_type'     => $type,
            'template_code' => $templateCode,
            'content'       => substr((string) $code, 0, 2) . '****',
            'ip'            => IpHelper::getRealIp(),
            'create_time'   => time(),
        ]);
    }

    private static function response(int $code, string $msg, array $data = []): array
    {
        $result = ['code' => $code, 'msg' => $msg];
        if ($data) {
            $result['data'] = $data;
        }
        return $result;
    }
}
