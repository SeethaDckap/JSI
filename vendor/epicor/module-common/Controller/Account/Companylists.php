<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Companylists extends \Magento\Customer\Controller\Account\Index
{

    protected $_commHelper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Epicor\Comm\Helper\Data $commHelper
    ) {
        $this->_commHelper = $commHelper;
        parent::__construct(
            $context,
            $resultPageFactory
        );
    }

    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
       if (!$this->_commHelper->isMasquerading()) {
           $resultPage->getLayout()->getUpdate()->addHandle('epicor_account_companylists_empty');
       }
        return $resultPage;
    }

}
