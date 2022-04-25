<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer;

/**
 * Country column renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Messagestatus extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Model\Message\LogFactory
     */
    protected $commMessageLogFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory,
        array $data = []
    ) {
        $this->commMessageLogFactory = $commMessageLogFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Render country grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());
        $log = $this->commMessageLogFactory->create();
        $col = $log->getMessageStatuses();
        $index = $data ?: \Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_UNKNOWN;
        $output = $col[$index];
        return $output;
    }

}
