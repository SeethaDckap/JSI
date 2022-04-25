<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


class Websiterender extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $storeWebsiteFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\WebsiteFactory $storeWebsiteFactory,
        array $data = []
    ) {
        $this->storeWebsiteFactory = $storeWebsiteFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        if ($value) {
            $collection = $this->storeWebsiteFactory->create()->load($value, 'website_id');
            $val = $collection->getName();
            return $val;
        }
    }

}
