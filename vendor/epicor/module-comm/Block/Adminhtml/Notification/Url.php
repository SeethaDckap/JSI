<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Notification;


class Url extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->backendHelper = $backendHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function getAdminLogUrl()
    {
        $route = 'adminhtml/epicorcomm_message_log/view';
        $param = array('source' => 'notification');
        return $this->backendHelper->getUrl($route, $param);
    }

}
