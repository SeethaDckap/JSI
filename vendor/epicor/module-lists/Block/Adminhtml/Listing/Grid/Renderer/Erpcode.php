<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Grid\Renderer;


/**
 * List ERP Code renderer
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Erpcode extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = []
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * render erp code
     * @param \Epicor\Lists\Model\ListModel $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        /* @var $row Epicor_Lists_Model_ListModel */
        // needed so that contract ERP Codes render without delimiter

        if ($row->getType() == 'Co') {
            $helper = $this->commMessagingHelper;
            /* @var $helper Epicor_Comm_Helper_Messaging */
            $accountCode = $helper->getSku($row->getData('erp_code'));
            $contractCode = $helper->getUom($row->getData('erp_code'));
            return $contractCode . ' - ' . $accountCode;
        } else {
            return $row->getErpCode();
        }
    }

}
