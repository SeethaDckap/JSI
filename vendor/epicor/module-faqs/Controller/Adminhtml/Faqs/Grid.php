<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Controller\Adminhtml\Faqs;

class Grid extends \Epicor\Faqs\Controller\Adminhtml\Faqs
{

   public function __construct(
       \Epicor\Comm\Controller\Adminhtml\Context $context,
       \Magento\Backend\Model\Auth\Session $backendAuthSession)
   {
       parent::__construct($context, $backendAuthSession);
   }

    /**
     * Grid ajax action
     */
    public function execute()
    {
        $resultPage = $this->_resultLayoutFactory->create();
        return $resultPage;

    }

}
