<?php

namespace UserCenter;


class TokenPayload
{
    /**
     * @var string
     */
    private $userId;
    /**
     * @var string
     */
    private $phoneNumber;
    /**
     * @var string
     */
    private $weChatUnionId;

    /**
     * @var bool
     */
    private $hasPassword;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * TokenPayload constructor.
     * @param string $userId
     * @param string $phoneNumber
     * @param string $weChatUnionId
     * @param bool $hasPassword
     * @param string $countryCode
     */
    public function __construct($userId, $phoneNumber, $weChatUnionId, $hasPassword, $countryCode)
    {
        $this->userId = $userId;
        $this->phoneNumber = $phoneNumber;
        $this->weChatUnionId = $weChatUnionId;
        $this->hasPassword = $hasPassword;
        $this->countryCode = $countryCode;
    }

    /**
     * @return string 用户ID
     */
    public function userId()
    {
        return $this->userId;
    }

    /**
     * @return string 手机号码
     */
    public function phoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @return string 微信联合ID
     */
    public function weChatUnionId()
    {
        return $this->weChatUnionId;
    }

    /**
     * @return bool 是否拥有密码
     */
    public function hasPassword()
    {
        return $this->hasPassword;
    }

    /**
     * @return string
     */
    public function countryCode()
    {
        return $this->countryCode;
    }


}