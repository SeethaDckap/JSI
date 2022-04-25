<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Customer;

class FetchAddress extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Customer
{

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    public function __construct(
        \Epicor\Common\Helper\Data $commonHelper
    ) {
        $this->commonHelper = $commonHelper;
    }
    public function execute()
    {
        $result = array(
            'type' => 'error',
            'html' => '',
            'error' => ''
        );
        $data = $this->getRequest()->getPost();
        if ($data) {
            $addressId = $data['id'];
            $customerId = $data['customerId'];
            $result = $this->commonHelper->customerSelectedAddressById($addressId, $customerId);
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode($result));
        }
    }

}
