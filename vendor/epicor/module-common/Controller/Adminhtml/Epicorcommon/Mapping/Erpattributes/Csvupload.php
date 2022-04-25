<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erpattributes;

class Csvupload extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erpattributes
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->commHelper = $commHelper;
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Uploads CSV file
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $helper = $this->commHelper;
            /* @var $listsHelper Epicor_Lists_Helper_Data */
            $result = $helper->importAttributeMappingFromCsv($_FILES['csv_file']['tmp_name']);
            
            if (isset($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    $this->messageManager->addErrorMessage($error);
                }
            }
            if (isset($result['success'])) {
                foreach ($result['success'] as $msg) {
                    $this->messageManager->addSuccessMessage($msg);
                }
            }        
            $this->_redirect('*/*/addbycsv');
        } else {
                $this->messageManager->addErrorMessage('InValid Data');
            $this->_redirect('*/*/addbycsv');
        }
    }

}
