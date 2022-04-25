<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Adminnotification;


class Inbox extends \Magento\AdminNotification\Model\Inbox
{

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->backendHelper = $backendHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function getUrl($route = null, $grid = true)
    {
//         if($route && $route != 'adminhtml/notification'){
        if ($route) {
            if ($route == 'adminhtml/notification' & $grid) {
                return;
            }
            if ((0 === strpos($route, 'http://')) || (0 === strpos($route, 'https://'))) {
                return $route;
            } else {
                return $this->backendHelper->getUrl($route);
            }
        } else {
            return;
        }
    }

}
