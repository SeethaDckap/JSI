<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Punchout\Model\Request\Validators;

use Epicor\Punchout\Model\ValidatorInterface;

/**
 * Class RequestValidator
 *
 * @package Epicor\Punchout\Model\Request\Validators
 */
class RequestValidator implements ValidatorInterface
{

    const VALIDATION_PARAMS = ['header', 'shopper'];

    /**
     * Expiration time in minutes
     */
    const EXPIRATION_TIME = '5';

    /**
     * Validators
     *
     * @var ValidatorInterface[]
     */
    protected $validators;


    /**
     * Constructor
     *
     * @param ValidatorInterface[] $validators Validator object array.
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;

    }//end __construct()


    /**
     * Validate data
     *
     * @param \SimpleXMLElement $request        Request Object.
     * @param bool              $emptyCartCheck Empty Cart Check.
     * @return array
     */
    public function validate(\SimpleXMLElement $request, $emptyCartCheck = false)
    {
        $shopperValidation = [];
        $headerValidation  = [];
        $params            = self::VALIDATION_PARAMS;

        if ($emptyCartCheck) {
            $params[] ='emptyCart';
        }
        foreach ($params as $param) {
            $validation = $this->validators[$param]->validate($request);
            if ($validation['error'] == 1) {
                return $validation;
            } else {
                if ($param === 'shopper') {
                    $shopperValidation = $validation;
                } else if ($param === 'header') {
                    $headerValidation = $validation;
                }
            }
        }

        return [
            'identity'      => $headerValidation['identity'],
            'shopper_id'    => $shopperValidation['shopper_id'],
            'website_id'    => $shopperValidation['website_id'],
            'website_url'   => $shopperValidation['website_url'],
            'store_id'      => $shopperValidation['store_id'],
            'connection_id' => $headerValidation['connection_id'],
        ];

    }//end validate()


}//end class
