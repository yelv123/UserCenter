<?php

namespace SDK\Test;

use PHPUnit_Framework_TestCase;
use UserCenter;

class UserCenterClientTests extends PHPUnit_Framework_TestCase
{

    /**
     * @var UserCenter\UserCenterClient
     */
    private $client;

    public function setUp()
    {
        $config = array();
        $config[UserCenter\UserCenterClient::CONFIG_KEY_UC_API_BASE_URL] = "http://127.0.0.1:8000";
        $config[UserCenter\UserCenterClient::CONFIG_KEY_UC_APP_ID] = "B9C2822B-EB82-4948-B424-EEB0F38191AF";
        $config[UserCenter\UserCenterClient::CONFIG_KEY_UC_APP_SECRET] = "NjVlYTQxM2VmNzYzMTMwOTU2Y2NiMWQ4ZTdiMzE1NWQ=";
        $this->client = new UserCenter\UserCenterClient($config);
    }

    public function testRegisterUserSuccess()
    {
        $randomPhoneNumber = '136' . rand(10000000, 99999999);
        $randomWeChatUnionId = 'WX' . rand(10000000, 99999999);
        $result = $this->client->registerUser($randomPhoneNumber, $randomWeChatUnionId);
        self::assertEquals($randomPhoneNumber, $result['phone_number']);
        self::assertEquals($randomWeChatUnionId, $result['we_chat_union_id']);
    }

    public function testLoginByPhoneNumberAndPasswordSuccess()
    {
        $randomPhoneNumber = '136' . rand(10000000, 99999999);
        $randomWeChatUnionId = 'WX' . rand(10000000, 99999999);
        $password = '123456';
        $result = $this->client->registerUser($randomPhoneNumber, $randomWeChatUnionId, $password);
        self::assertEquals($randomPhoneNumber, $result['phone_number']);
        self::assertEquals($randomWeChatUnionId, $result['we_chat_union_id']);

        $result = $this->client->loginByPhoneNumberAndPassword($randomPhoneNumber, $password);
        self::assertNotEmpty($result['token']);
        $tokenPayload = $this->client->getTokenPayload($result['token']);
        self::assertEquals($randomPhoneNumber, $tokenPayload->phoneNumber());
        self::assertEquals($randomWeChatUnionId, $tokenPayload->weChatUnionId());
        self::assertNotEmpty($tokenPayload->userId());
    }

    public function testLoginByPhoneNumberAndPasswordFailureWithEmptyPassword()
    {
        $randomPhoneNumber = '136' . rand(10000000, 99999999);
        $randomWeChatUnionId = 'WX' . rand(10000000, 99999999);
        $result = $this->client->registerUser($randomPhoneNumber, $randomWeChatUnionId);
        self::assertEquals($randomPhoneNumber, $result['phone_number']);
        self::assertEquals($randomWeChatUnionId, $result['we_chat_union_id']);

        $result = $this->client->loginByPhoneNumberAndPassword($randomPhoneNumber, '');
        self::assertEmpty($result['token']);
    }

    public function testRegisterOrUpdateUserSuccess()
    {
        $randomPhoneNumber = '136' . rand(10000000, 99999999);

        $result = $this->client->registerOrUpdateUser($randomPhoneNumber);
        self::assertEquals($randomPhoneNumber, $result['phone_number']);
        self::assertEmpty($result['we_chat_union_id']);

        $password = '123456';
        $randomWeChatUnionId = 'WX' . rand(10000000, 99999999);
        $result = $this->client->registerOrUpdateUser($randomPhoneNumber, $randomWeChatUnionId, $password);
        self::assertEquals($randomPhoneNumber, $result['phone_number']);
        self::assertEquals($randomWeChatUnionId, $result['we_chat_union_id']);

        $result = $this->client->loginByPhoneNumberAndPassword($randomPhoneNumber, $password);
        self::assertNotEmpty($result['token']);
        $tokenPayload = $this->client->getTokenPayload($result['token']);
        self::assertEquals($randomPhoneNumber, $tokenPayload->phoneNumber());
        self::assertEquals($randomWeChatUnionId, $tokenPayload->weChatUnionId());
        self::assertNotEmpty($tokenPayload->userId());
    }

    public function testResetPasswordSuccess()
    {
        $randomPhoneNumber = '136' . rand(10000000, 99999999);
        $randomWeChatUnionId = 'WX' . rand(10000000, 99999999);
        $this->client->registerUser($randomPhoneNumber, $randomWeChatUnionId);
        $token = $this->client->getTokenByPhoneNumber($randomPhoneNumber);

        $checkCode = $this->client->getCheckCodeByShortMessage($randomPhoneNumber, UserCenter\UserCenterClient::USAGE_RESET_PASSWORD);
        self::assertNotEmpty($checkCode['check_code']);


        $newPassword = '123456';
        $result = $this->client->resetPassword($token['token'], $newPassword, $checkCode['check_code']);
        self::assertEquals($newPassword, $result['password']);
    }

//    public function testRegisterUserFailure()
//    {
//        $randomPhoneNumber = '15180105621';
//        $randomWeChatUnionId = 'oOzsSwuZT99dk25g3aWZ1JYNAWdA1';
//        $result = $this->client->registerUser($randomPhoneNumber, $randomWeChatUnionId);
//        self::assertEquals(400, $result['code']);
//        self::assertEquals('手机号码已存在', $result['message']);
//    }
//
//    public function testRegisterUserFailure()
//    {
//        $randomPhoneNumber = '15180105621';
//        $randomWeChatUnionId = 'oOzsSwuZT99dk25g3aWZ1JYNAWdA';
//        $result = $this->client->registerUser($randomPhoneNumber, $randomWeChatUnionId);
//        self::assertEquals(409, $result['code']);
//        self::assertEquals('手机号码已存在', $result['message']);
//    }

//    public function testGetTokenByPhoneNumberSuccess()
//    {
//        $result = LuxuryMoreClient::getTokenByPhoneNumber($this->FIXTURE_PHONE_NUMBER);
//        self::assertNotEmpty($result['token']);
//        $tokenPayload = LuxuryMoreClient::getTokenPayload($result['token']);
//        self::assertEquals($this->FIXTURE_PHONE_NUMBER, $tokenPayload->phoneNumber());
//        self::assertEquals($this->FIXTURE_WE_CHAT_UNION_ID, $tokenPayload->weChatUnionId());
//        self::assertNotEmpty($tokenPayload->userId());
//    }
//
//    public function testGetTokenByWeChatUnionIdSuccess()
//    {
//        $result = LuxuryMoreClient::getTokenByWeChatUnionId($this->FIXTURE_WE_CHAT_UNION_ID);
//        self::assertNotEmpty($result['token']);
//        $tokenPayload = LuxuryMoreClient::getTokenPayload($result['token']);
//        self::assertEquals($this->FIXTURE_PHONE_NUMBER, $tokenPayload->phoneNumber());
//        self::assertEquals($this->FIXTURE_WE_CHAT_UNION_ID, $tokenPayload->weChatUnionId());
//        self::assertNotEmpty($tokenPayload->userId());
//    }
//
//    public function testGetTokenPayloadSuccess()
//    {
//        $result = LuxuryMoreClient::getTokenByWeChatUnionId($this->FIXTURE_WE_CHAT_UNION_ID);
//        self::assertNotEmpty($result['token']);
//        $tokenPayload = LuxuryMoreClient::getTokenPayload($result['token']);
//        self::assertEquals($this->FIXTURE_PHONE_NUMBER, $tokenPayload->phoneNumber());
//        self::assertEquals($this->FIXTURE_WE_CHAT_UNION_ID, $tokenPayload->weChatUnionId());
//        self::assertNotEmpty($tokenPayload->userId());
//    }

//    public function testGetCheckCodeByShortMessageSuccess()
//    {
//        $result = LuxuryMoreClient::getCheckCodeByShortMessage($this->FIXTURE_PHONE_NUMBER, 1);
//        self::assertNotEmpty($result['check_code']);
//    }

//    public function testVerifyCheckCodeSuccess()
//    {
//        $result = LuxuryMoreClient::getCheckCodeByShortMessage($this->FIXTURE_PHONE_NUMBER, 4);
//        $result = LuxuryMoreClient::verifyShortMessageCheckCode($this->FIXTURE_PHONE_NUMBER, 4, $result['check_code']);
//        self::assertTrue($result['is_verify']);
//    }


}