<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Mapping\Warranty\Renderer;


/**
 * Customer Account Type Grid Renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Common\Helper\Account\Selector
     */
    protected $commonAccountSelectorHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Common\Helper\Account\Selector $commonAccountSelectorHelper,
        array $data = []
    ) {
        $this->commonAccountSelectorHelper = $commonAccountSelectorHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Render country grid column
     *
     * @param   \Epicor\Comm\Model\Location\Product $row
     * 
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $status = $row->getStatus();
        if($status =="yes") {
            $statusCode = "Active";
        } else {
            $statusCode = "Inactive";
        }
        return $statusCode;
    }

}
