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

use Epicor\Punchout\Model\Request\Validator;
use Epicor\Punchout\Model\ValidatorInterface;
use Epicor\Punchout\Model\ResourceModel\Connections\CollectionFactory;

/**
 * Class for header validation
 */
class HeaderValidator extends Validator implements ValidatorInterface
{

    /**
     * Connection collection.
     *
     * @var CollectionFactory
     */
    private $collectionFactory;


    /**
     * Constructor.
     *
     * @param CollectionFactory $collectionFactory Connection collection factory.
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct(
            $collectionFactory
        );

    }//end __construct()


    /**
     * Validate data
     *
     * @param \SimpleXMLElement $request Request data object.
     *
     * @return array
     */
    public function validate(\SimpleXMLElement $request)
    {
        $id             = null;
        $error          = 1;
        $headerSender   = $request->Header->Sender;
        if (empty($headerSender)) {
            return [
                'connection_id' => $id,
                'error'         => $error,
                'code'          => '400',
            ];
        }
        $identity  = (string) $request->Header->From->Credential->Identity;
        if (!empty((string) $headerSender->Credential->Identity)) {
            $identity = (string) $headerSender->Credential->Identity;
        }
        $sharedSecret   = (string) $headerSender->Credential->SharedSecret;
        $connectionData = $this->getPunchoutConnection($identity, $sharedSecret);
        if (!empty($connectionData)  && !empty($connectionData->getId())) {
            $id    = $connectionData->getId();
            $error = 0;
            return [
                'connection_id' => $id,
                'identity'      => $identity,
                'error'         => $error,
            ];
        }

        return [
            'connection_id' => $id,
            'error'         => $error,
            'code'          => '401',
            'error_message' => 'No valid connection found for given identity and secret key.',
        ];

    }//end validate()


}//end class
