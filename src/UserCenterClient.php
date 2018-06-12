<?php
/**
 * Created by PhpStorm.
 * User: wanda
 * Date: 2018/4/27
 * Time: 下午3:40
 */

namespace UserCenter;

use GuzzleHttp\Client;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class UserCenterClient
{
    const USAGE_LOGIN = 1;
    const USAGE_RESET_PASSWORD = 2;
    const USAGE_VERIFY_OLD_PHONE_NUMBER = 3;
    const USAGE_BINDING_PHONE_NUMBER = 4;
    const USAGE_REGISTER = 5;
    const USAGE_CHANGE_PHONE_NUMBER = 6;
    const CONFIG_KEY_UC_API_BASE_URL = "UC_API_BASE_URL";
    const CONFIG_KEY_UC_APP_ID = "UC_APP_ID";
    const CONFIG_KEY_UC_APP_SECRET = "UC_APP_SECRET";
    private static $CLAIM_USER_ID = 'userId';
    private static $CLAIM_PHONE_NUMBER = 'phoneNumber';
    private static $CLAIM_WE_CHAT_UNION_ID = 'weChatUnionId';
    private static $CLAIM_APP_ID = "appId";
    private static $CLAIM_HAS_PASSWORD = "hasPassword";
    private static $CLAIM_COUNTRY_CODE = 'countryCode';


    private $config = [];

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * 通过手机号码获取访问令牌
     * @param string $phoneNumber 手机号码
     * @param string $countryCode 国际区号
     * @return array
     * 成功：{"token":"token"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public function getTokenByPhoneNumber($phoneNumber, $countryCode = "86")
    {
        $signature = $this->buildSignature();
        $client = new Client();
        $response = $client->post($this->config['UC_API_BASE_URL'] . '/users/phoneNumberIs/' . $phoneNumber . '/countryCode/' . $countryCode . '/authenticatedWithSignature/' . $signature . '/tokens', ['http_errors' => false]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 通过微信联合ID获取访问令牌
     * @param string $weChatUnionId 微信联合ID
     * @return array
     * 成功：{"token":"访问令牌"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public function getTokenByWeChatUnionId($weChatUnionId)
    {
        $signature = $this->buildSignature();
        $client = new Client();
        $response = $client->post($this->baseUrl() . '/users/weChatUnionIdIs/' . $weChatUnionId . '/authenticatedWithSignature/' . $signature . '/tokens', ['http_errors' => false]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 通过手机号码和密码登录
     * @param string $phoneNumber
     * @param string $password
     * @param string $countryCode 国际区号
     * @return array
     * 成功：{"token":"访问令牌"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public function loginByPhoneNumberAndPassword($phoneNumber, $password, $countryCode = '86')
    {
        $signature = $this->buildSignature();
        $client = new Client();
        $response = $client->post($this->baseUrl() . '/users/phoneNumberIs/' . $phoneNumber . '/countryCode/' . $countryCode . '/authenticatedWithPassword/' . $signature . '/tokens', array('json' => array('password' => $password), 'http_errors' => false));
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 用户注册
     * @param string $phoneNumber 手机号码
     * @param string $weChatUnionId 微信联合ID
     * @param string $password
     * @param string $countryCode 国际区号
     * @return array
     * 成功：{"user_id":"用户ID","phone_number":"手机号码","we_chat_union_id":"微信联合ID"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public function registerUser($phoneNumber, $weChatUnionId = '', $password = '', $countryCode = '86')
    {
        $signature = $this->buildSignature();
        $client = new Client();
        $response = $client->post($this->baseUrl() . '/apps/authenticatedWithSignature/' . $signature . '/users', array('json' => array('phone_number' => $phoneNumber, 'we_chat_union_id' => $weChatUnionId, 'password' => $password, 'country_code' => $countryCode), 'http_errors' => false));
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 注册或更新用户
     * @param string $phoneNumber 手机号码
     * @param string $weChatUnionId 微信联合ID
     * @param string $password 密码
     * @param string $countryCode 国际区号
     * @return array
     * 成功：{"user_id":"用户ID","phone_number":"手机号码","we_chat_union_id":"微信联合ID","country_code":"国际区号"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public function registerOrUpdateUser($phoneNumber, $weChatUnionId = '', $password = '', $countryCode = '86')
    {
        $signature = $this->buildSignature();
        $client = new Client();
        $response = $client->put($this->baseUrl() . '/apps/authenticatedWithSignature/' . $signature . '/users', array('json' => array('phone_number' => $phoneNumber, 'we_chat_union_id' => $weChatUnionId, 'password' => $password, 'country_code' => $countryCode), 'http_errors' => false));
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 通过短信获取验证码
     * @param string $phoneNumber 手机号码
     * @param $checkCodeUsage 1:验证码登录 3:验证老手机号码 4:绑定手机号码
     * @param string $countryCode 国际区号
     * @return array
     * 成功：{"check_code":"验证码"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public function getCheckCodeByShortMessage($phoneNumber, $checkCodeUsage, $countryCode = "86")
    {
        $signature = $this->buildSignature();
        $client = new Client();
        $response = $client->post($this->baseUrl() . '/apps/authenticatedWithSignature/' . $signature . '/phoneNumber/' . $phoneNumber . '/countryCode/' . $countryCode . '/withUsage/' . $checkCodeUsage . '/checkCodes', ['http_errors' => false]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 通过短信获取验证码
     * @param string $phoneNumber 手机号码
     * @param string $checkCodeUsage 1:验证码登录 3:验证老手机号码 4:绑定手机号码
     * @param string $checkCode 验证码
     * @param string $countryCode 国际区号
     * @return array
     * 成功：{"is_verify":true}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public function verifyShortMessageCheckCode($phoneNumber, $checkCodeUsage, $checkCode, $countryCode = "86")
    {
        $signature = $this->buildSignature();
        $client = new Client();
        $response = $client->get($this->baseUrl() . '/apps/authenticatedWithSignature/' . $signature . '/phoneNumber/' . $phoneNumber . '/countryCode/' . $countryCode . '/withUsage/' . $checkCodeUsage . '/isVerifyCheckCode/' . $checkCode, ['http_errors' => false]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 更改手机号码
     * @param string $token 访问令牌
     * @param string $checkCodeForOldPhoneNumber 用于验证老手机号码的验证码
     * @param string $changedPhoneNumber 新手机号码
     * @param string $checkCodeForChangedPhoneNumber 用于验证新手机号码的验证码
     * @param string $changedCountryCode 国际区号
     * @return array
     * 成功：{"phone_number":"手机号码"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public function changePhoneNumber($token, $checkCodeForOldPhoneNumber, $changedPhoneNumber, $checkCodeForChangedPhoneNumber, $changedCountryCode = '86')
    {
        $client = new Client();
        $response = $client->put($this->baseUrl() . '/users/authenticatedWithToken/' . $token . '/phoneNumber', array('json' => array('changed_phone_number' => $changedPhoneNumber, 'changed_check_code' => $checkCodeForChangedPhoneNumber, 'current_check_code' => $checkCodeForOldPhoneNumber, 'changed_country_code' => $changedCountryCode), 'http_errors' => false));
        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * 重置密码
     * @param string $token 访问令牌
     * @param string $newPassword 新密码
     * @param string $checkCode 用于重置密码的验证码
     * @return array
     * 成功：{"password":"新密码"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public function resetPassword($token, $newPassword, $checkCode)
    {
        $client = new Client();
        $response = $client->put($this->baseUrl() . '/users/authenticatedWithToken/' . $token . '/password', array('json' => array('new_password' => $newPassword, 'check_code' => $checkCode), 'http_errors' => false));
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 获取访问令牌附带的数据
     * @param string $token
     * @return null|TokenPayload
     */
    public function getTokenPayload($token)
    {
        $token = (new Parser())->parse(base64_decode($token));
        $userId = $token->getClaim(static::$CLAIM_USER_ID);
        $phoneNumber = $token->getClaim(static::$CLAIM_PHONE_NUMBER);
        $weChatUnionId = $token->getClaim(static::$CLAIM_WE_CHAT_UNION_ID);
        $hasPassword = $token->getClaim(static::$CLAIM_HAS_PASSWORD);
        $countryCode = $token->getClaim(static::$CLAIM_COUNTRY_CODE);

        if (!$token->verify(new Sha256(), $this->config[UserCenterClient::CONFIG_KEY_UC_APP_SECRET])) {
            return null;
        }

        return new TokenPayload($userId, $phoneNumber, $weChatUnionId, $hasPassword, $countryCode);
    }

    private function baseUrl()
    {
        return $this->config[UserCenterClient::CONFIG_KEY_UC_API_BASE_URL];
    }

    private function buildSignature()
    {
        $signature = (new Builder())
            ->setExpiration(time() + 3600)
            ->set(static::$CLAIM_APP_ID, $this->config[UserCenterClient::CONFIG_KEY_UC_APP_ID])
            ->sign(new Sha256(), $this->config[UserCenterClient::CONFIG_KEY_UC_APP_SECRET])
            ->getToken();
        return base64_encode((string)$signature);
    }

}