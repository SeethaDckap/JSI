<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Contract\Select\Grid\Renderer;


/**
 * Column Renderer for Contract Select Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Erpcode extends \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Epicor\Comm\Helper\Messaging $commMessagingHelper
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
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

        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $contractCode = $helper->getUom($row->getData('erp_code'));
        return $contractCode;
    }

}
