<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Customer\Account\Contracts\parts\Listing\Renderer;

/**
 * RFQ line attachments column renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    public function __construct(
    \Magento\Backend\Block\Context $context, \Epicor\Lists\Helper\Data $listsHelper, array $data = []
    ) {
        $this->listsHelper = $listsHelper;
        parent::__construct(
                $context, $data
        );
    }

    public function render(\Magento\Framework\DataObject $row) {
        return $this->listsHelper->getStatus($row->getLineStatus());
    }

}
