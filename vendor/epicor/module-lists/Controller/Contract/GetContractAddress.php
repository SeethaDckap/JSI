<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Contract;

class GetContractAddress extends \Epicor\Lists\Controller\Contract
{

    /**
     * @var \Epicor\Lists\Block\Customer\Account\Contract\Default
     */
    protected $listsCustomerAccountContractDefault;

    public function __construct(
        \Epicor\Lists\Block\Customer\Account\Contract\DefaultBlock $listsCustomerAccountContractDefault
    ) {
        $this->listsCustomerAccountContractDefault = $listsCustomerAccountContractDefault;
    }
    /**
     * Contract Ajax  page for getting Shipping Informations
     *
     * @return void
     */
    public function execute()
    {
        $result = array(
            'type' => 'error',
            'html' => '',
            'error' => ''
        );
        $data = $this->getRequest()->getPost();
        if ($data) {
            $contractId = $data['id'];
            $result = $this->listsCustomerAccountContractDefault->getCustomerSelectedAddress($contractId);
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode($result));
        }
    }

    }
