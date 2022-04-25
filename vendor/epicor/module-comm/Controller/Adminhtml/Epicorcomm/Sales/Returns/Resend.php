<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Returns;

class Resend extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Returns {

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModelFactory
     */
    protected $commCustomerReturnModelFactory;

    public function __construct(
      \Epicor\Comm\Controller\Adminhtml\Context $context,
      \Magento\Backend\Model\Auth\Session $backendAuthSession,
      \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory
    ) {
        $this->commCustomerReturnModelFactory = $commCustomerReturnModelFactory;
        parent::__construct($context, $backendAuthSession);
       }

    public function execute() {
        $content = __('Return Not Found');

        if ($data = $this->getRequest()->getPost()) {

            $return = $this->commCustomerReturnModelFactory->create()->load($this->getRequest()->get('return_id'));
            /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
            if ($return && !$return->isObjectNew()) {
                $return->setErpSyncStatus('N');
                $return->save();
                $content =  __('Return Will Be Re-Sent Shortly');
            }
        }

        $this->getResponse()->setBody($content);
    }

}
