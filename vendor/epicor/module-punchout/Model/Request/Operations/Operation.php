<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Request\Operations;

/**
 * Class for header validation
 */
class Operation
{

    const CREATE_OPERATION = 'create';
    const EDIT_OPERATION   = 'edit';

    /**
     * CreateRequest
     *
     * @var \Epicor\Punchout\Model\Request\Operations\Epicor\Punchout\Model\Request\Operations\Create
     */
    private $createRequest;


    /**
     * Operation constructor.
     *
     * @param \Epicor\Punchout\Model\Request\Operations\Create $request Request.
     */
    public function __construct(
        Create $request
    ) {
        $this->createRequest = $request;

    }//end __construct()


    /**
     * @param /SimpleXMLElement $requestData RequestData.
     * @param int               $customerId  Customer Id.
     * @param string            $identity    Connection Identity.
     *
     * @return array
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function processOperation($requestData, $customerId, $identity)
    {
        $punchoutCart   = [];
        $punchoutCartId = null;
        $error          = 0;
        $errorMessage   = '';
        $itemOut        = $requestData->xpath('ItemOut');
        $operation      = !empty((string) $requestData->attributes()) ? (string) $requestData->attributes() : '';
        if (empty($itemOut) && $operation === self::EDIT_OPERATION) {
            return [
                'error_message' => 'Item Data is required for '.$operation.' operation',
                'error'         => 1,
                'punchoutCart'  => $punchoutCartId,
            ];
        }

        if ($operation === self::CREATE_OPERATION || $operation === self::EDIT_OPERATION) {
            $punchoutCart = $this->createRequest->createCart($itemOut, $customerId, $identity);
        } else {
            $error        = 1;
            $errorMessage = 'Requested operation is incorrect!';
        }
        return [
            'error_message' => $errorMessage,
            'error'         => $error,
            'notAddedProd'  => (!empty($punchoutCart['notAddedProd'])) ? $punchoutCart['notAddedProd'] : '',
            'punchoutCart'  => $punchoutCart['cartId'],
        ];

    }//end processOperation()


}//end class
