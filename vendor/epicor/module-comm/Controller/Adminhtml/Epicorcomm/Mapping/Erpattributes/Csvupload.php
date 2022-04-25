<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes;

class Csvupload extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper
    ) {
        $this->commHelper = $commHelper;
    }
    /**
     * Uploads CSV file
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $helper = $this->commHelper;
            /* @var $listsHelper Epicor_Lists_Helper_Data */
            $result = $helper->importAttributeMappingFromCsv($_FILES['csv_file']['tmp_name']);
            $this->_redirect('*/*/addbycsv');
        } else {
            $this->_redirect('*/*/addbycsv');
        }
    }

}
