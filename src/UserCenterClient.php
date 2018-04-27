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
    private static $CLAIM_USER_ID = 'userId';
    private static $CLAIM_PHONE_NUMBER = 'phoneNumber';
    private static $CLAIM_WE_CHAT_UNION_ID = 'weChatUnionId';
    private static $CLAIM_APP_ID = "appId";

    private $config=[];
    public function __construct($config=[])
    {
        $this->config=$config;
    }

    /**
     * 通过手机号码获取访问令牌
     * @param string $phoneNumber 手机号码
     * @return array
     * 成功：{"token":"token"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public function getTokenByPhoneNumber($phoneNumber)
    {
        $signature = $this->buildSignature();
        $client = new Client();
        $response = $client->post($this->config['UC_API_BASE_URL'] . '/users/phoneNumberIs/' . $phoneNumber . '/authenticatedWithSignature/' . $signature . '/tokens', ['http_errors' => false]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 通过微信联合ID获取访问令牌
     * @param string $weChatUnionId 微信联合ID
     * @return array
     * 成功：{"token":"访问令牌"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public  function getTokenByWeChatUnionId($weChatUnionId)
    {
        $signature = $this->buildSignature();
        $client = new Client();
        $response = $client->post($this->config['UC_API_BASE_URL'] . '/users/weChatUnionIdIs/' . $weChatUnionId . '/authenticatedWithSignature/' . $signature . '/tokens', ['http_errors' => false]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 用户注册
     * @param string $phoneNumber 手机号码
     * @param string $weChatUnionId 微信联合ID
     * @return array
     * 成功：{"user_id":"用户ID","phone_number":"手机号码","we_chat_union_id":"微信联合ID"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public  function registerUser($phoneNumber, $weChatUnionId = '')
    {
        $signature = $this->buildSignature();
        $client = new Client();
        $response = $client->post($this->config['UC_API_BASE_URL'] . '/apps/authenticatedWithSignature/' . $signature . '/users', array('json' => array('phone_number' => $phoneNumber, 'we_chat_union_id' => $weChatUnionId), 'http_errors' => false));
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 注册或更新用户
     * @param string $phoneNumber 手机号码
     * @param string $weChatUnionId 微信联合ID
     * @return array
     * 成功：{"user_id":"用户ID","phone_number":"手机号码","we_chat_union_id":"微信联合ID"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public  function registerOrUpdateUser($phoneNumber, $weChatUnionId = '')
    {
        $signature = $this->buildSignature();
        $client = new Client();
        $response = $client->put($this->config['UC_API_BASE_URL'] . '/apps/authenticatedWithSignature/' . $signature . '/users', array('json' => array('phone_number' => $phoneNumber, 'we_chat_union_id' => $weChatUnionId), 'http_errors' => false));
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 通过短信获取验证码
     * @param string $phoneNumber 手机号码
     * @param $checkCodeUsage 1:验证码登录 3:验证老手机号码 4:绑定手机号码
     * @return array
     * 成功：{"check_code":"验证码"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public  function getCheckCodeByShortMessage($phoneNumber, $checkCodeUsage)
    {
        $signature = $this->buildSignature();
        $client = new Client();
        $response = $client->post($this->config['UC_API_BASE_URL'] . '/apps/authenticatedWithSignature/' . $signature . '/phoneNumber/' . $phoneNumber . '/withUsage/' . $checkCodeUsage . '/checkCodes', ['http_errors' => false]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 通过短信获取验证码
     * @param string $phoneNumber 手机号码
     * @param string $checkCodeUsage 1:验证码登录 3:验证老手机号码 4:绑定手机号码
     * @param string $checkCode 验证码
     * @return array
     * 成功：{"is_verify":true}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public function verifyShortMessageCheckCode($phoneNumber, $checkCodeUsage, $checkCode)
    {
        $signature = $this->buildSignature();
        $client = new Client();
        $response = $client->get($this->config['UC_API_BASE_URL'] . '/apps/authenticatedWithSignature/' . $signature . '/phoneNumber/' . $phoneNumber . '/withUsage/' . $checkCodeUsage . '/isVerifyCheckCode/' . $checkCode, ['http_errors' => false]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 更改手机号码
     * @param string $token 访问令牌
     * @param string $checkCodeForOldPhoneNumber 用于验证老手机号码的验证码
     * @param string $changedPhoneNumber 新手机号码
     * @param string $checkCodeForChangedPhoneNumber 用于验证新手机号码的验证码
     * @return array
     * 成功：{"current_phone_number":"手机号码"}
     * 失败：{"code":错误代码,"message":"失败原因"}
     */
    public  function changePhoneNumber($token, $checkCodeForOldPhoneNumber, $changedPhoneNumber, $checkCodeForChangedPhoneNumber)
    {
        $client = $this-> Client();
        $response = $client->put($this->config['UC_API_BASE_URL'] . '/users/authenticatedWithToken/' . $token . '/phoneNumber', array('json' => array('changed_phone_number' => $changedPhoneNumber, 'changed_check_code' => $checkCodeForChangedPhoneNumber, 'current_check_code' => $checkCodeForOldPhoneNumber), 'http_errors' => false));
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 获取访问令牌附带的数据
     * @param string $token
     * @return null|TokenPayload
     */
    public  function getTokenPayload($token)
    {
        $token = (new Parser())->parse(base64_decode($token));
        $userId = $token->getClaim(static::$CLAIM_USER_ID);
        $phoneNumber = $token->getClaim(static::$CLAIM_PHONE_NUMBER);
        $weChatUnionId = $token->getClaim(static::$CLAIM_WE_CHAT_UNION_ID);

        if (!$token->verify(new Sha256(), $this->config['UC_APP_SECRET'])) {
            return null;
        }

        return new TokenPayload($userId, $phoneNumber, $weChatUnionId);
    }

    private  function buildSignature()
    {
        $signature = (new Builder())
            ->setExpiration(time() + 3600)
            ->set(static::$CLAIM_APP_ID, $this->config['UC_APP_ID'])
            ->sign(new Sha256(),$this->config['UC_APP_SECRET'])
            ->getToken();
        return base64_encode((string)$signature);
    }

}