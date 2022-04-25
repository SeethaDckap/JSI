<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping;

class Change extends \Magento\Framework\App\Action\Action {

    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultJsonFactory;

    /**     * @param \Magento\Framework\App\Action\Context $context      */
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory) {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute() {
        //echo "hello";exit();
        $data = $this->getRequest()->getPost("value");
        $url = $this->_url->getUrl('adminhtml/epicorcommon_mapping_'.$data); //You can give any url, or current page url
        $response = array('error' => false, 'success' => true, 'ajaxExpired' => true, 'ajaxRedirect' => $url);
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);

        return $resultJson;
    }

}
