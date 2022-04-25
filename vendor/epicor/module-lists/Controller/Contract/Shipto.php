<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Contract;

class Shipto extends \Epicor\Lists\Controller\Contract
{
    /**
     * Contract Select Page
     *
     * @return void
     */
    public function execute()
    {
        $contractHelper = $this->listsFrontendContractHelper;
        if ($contractHelper->contractsDisabled()) {
            return $this->_redirect($this->_url->getUrl('customer/account'));
        }
        $result = $this->resultPageFactory->create();

        return $result;
    }

    }
