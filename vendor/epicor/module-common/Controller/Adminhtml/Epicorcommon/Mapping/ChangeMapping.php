<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping;

class ChangeMapping extends \Magento\Framework\App\Action\Action {

    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultJsonFactory;

    protected $globalConfig;

    protected $registry;

    /**     * @param \Magento\Framework\App\Action\Context $context      */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->globalConfig = $globalConfig;
        $this->registry = $registry;
        parent::__construct($context);
    }

    public function execute() {
        $dataPost = $this->getRequest()->getPost("value");
        $data = array();
        $attribute_data = array();
        $preselected = $dataPost;
        $request = (array)$this->globalConfig->get('mapping_xml_request/messages');
        $_upload = array();
        if ($preselected) {
            foreach ($request as $key => $request) {
                unset($request['title']);
                if ($preselected == strtoupper($key)) {
                    $arrayKeys = array_keys($request);
                    if (in_array('grid_config', $arrayKeys)) {
                        $_upload[] = array('key'=>'grid_config','value'=>'Grid Config');
                    }
                    if (in_array('address_section', $arrayKeys)) {
                        $_upload[] = array('key'=>'address_section','value'=>'Address Section');
                    }
                    if (in_array('information_section', $arrayKeys)) {
                        $_upload[] = array('key'=>'information_section','value'=>'Information Section');
                    }
                    if(in_array('lineinformation_section',$arrayKeys)) {
                        $_upload[] = array('key'=>'lineinformation_section','value'=>'Line Information Section');
                    }
                    if (in_array('replacement_grid_config', $arrayKeys)) {
                        $_upload[] = array('key'=>'replacement_grid_config','value'=>'Replacements Grid Setup');
                    }
                    if (in_array('newpogrid_config', $arrayKeys)) {
                        $_upload[] = array('key'=>'newpogrid_config','value'=>'New PO Grid Setup');
                    }
                }
            }
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($_upload);
        return $resultJson;
    }

}