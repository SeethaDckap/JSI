<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Themes
 * @subpackage Setup
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Punchout\Model\Request;

use Magento\Framework\UrlInterface as Url;
use Epicor\Punchout\Model\TokenGenerator\JwtManagement;

/**
 * Class TokenBuilder
 *
 * @package Epicor\Punchout\Model\Request
 */
class TokenBuilder
{

    /**
     * Jwt Management.
     *
     * @var JwtManagement
     */
    private $jwtManagement;

    /**
     * ExpirationTime.
     *
     * @var $_expirationTime
     */
    protected $expirationTime;

    /**
     * Data array
     *
     * @var array
     */
    private $data = [];

    /**
     * TokenBuilder constructor.
     *
     * @param JwtManagement $jwtManagement JwtManagement.
     * @param Url           $url           Url.
     * @param array         $data          Data.
     */
    public function __construct(
        JwtManagement $jwtManagement,
        $expirationTime,
        array $data=[]
    ) {
        $this->data           = $data;
        $this->jwtManagement  = $jwtManagement;
        $this->expirationTime = $expirationTime;

    }//end __construct()


    /**
     * Builds request JWT.
     *
     * @param \SimpleXMLElement $requestObj SimpleXmlElement Request Object.
     * @param array             $id         Array Of required data.
     *
     * @return string
     */
    public function build(\SimpleXMLElement $requestObj, array $id)
    {
        $buyerCookie  = (array) $requestObj->Request->PunchOutSetupRequest->BuyerCookie;
        $cookieToSend = (!empty($buyerCookie)) ? $buyerCookie[0] : '';
        $postUrl      = (array) $requestObj->Request->PunchOutSetupRequest->BrowserFormPost;
        $currentTime  = (new \DateTime())->getTimestamp();
        $token        = [
            'connection_id'   => $id['connection_id'],
            'buyer_cookie'    => $cookieToSend,
            'customer_id'     => $id['shopper_id'],
            'expiration_time' => $currentTime + (60 * $this->expirationTime),
            'post_url'        => $postUrl['URL'],
            'is_punchout'     => 1,
        ];
        return $this->jwtManagement->encode($token, $this->data['config']->getApiKey());

    }//end build()


}//end class
