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

use Magento\Framework\Intl\DateTimeFactory;
use Epicor\Punchout\Model\TokenGenerator\JwtManagement;

/**
 * Class JwtPayloadValidator
 *
 * @package Epicor\Punchout\Model\Request
 */
class JwtPayloadValidator implements JwtPayloadValidatorInterface
{

    /**
     * DateTimeFactory
     *
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * JwtManagement.
     *
     * @var \Epicor\Punchout\Model\TokenGenerator\JwtManagement
     */
    private $jwtManagement;


    /**
     * JwtPayloadValidator constructor.
     *
     * @param \Magento\Framework\Intl\DateTimeFactory             $dateTimeFactory
     * @param \Epicor\Punchout\Model\TokenGenerator\JwtManagement $jwtManagement
     */
    public function __construct(
        DateTimeFactory $dateTimeFactory,
        JwtManagement $jwtManagement
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->jwtManagement   = $jwtManagement;

    }//end __construct()


    /**
     * Validate Token.
     *
     * @param array $jwtPayload JwtPayload.
     *
     * @return bool
     */
    public function validate(array $jwtPayload): bool
    {
        $expTimestamp = $jwtPayload['expiration_time'] ?? 0;
        return $this->isTokenExpired($expTimestamp);

    }//end validate()


    /**
     * Check token Expired.
     *
     * @param int $expTimestamp ExpTimestamp.
     *
     * @return bool
     */
    public function isTokenExpired($expTimestamp)
    {
        $currentTimestamp = (new \DateTime())->getTimestamp();
        if ($expTimestamp >= $currentTimestamp) {
            return false;
        }

        return true;

    }//end isTokenExpired()


}//end class
