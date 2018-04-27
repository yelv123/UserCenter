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
     * TokenPayload constructor.
     * @param string $userId
     * @param string $phoneNumber
     * @param string $weChatUnionId
     */
    public function __construct($userId, $phoneNumber, $weChatUnionId)
    {
        $this->userId = $userId;
        $this->phoneNumber = $phoneNumber;
        $this->weChatUnionId = $weChatUnionId;
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


}