<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Skus\Renderer;

use Epicor\Customerconnect\Model\Skus\CpnuManagement;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Description
 * @package Epicor\Customerconnect\Block\Customer\Skus\Renderer
 */
class Description extends AbstractRenderer
{
    /**
     * @var CpnuManagement
     */
    private $cpnuManagement;

    /**
     * Description constructor.
     * @param Context $context
     * @param CpnuManagement $cpnuManagement
     * @param array $data
     */
    public function __construct(
        Context $context,
        CpnuManagement $cpnuManagement,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->cpnuManagement = $cpnuManagement;
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row): string
    {
        $index = $this->getColumn()->getIndex();
        $value = $row->getData($index);
        if (is_null($value)) {
            $value = '';
        }

        if ($this->cpnuManagement->isEditable() &&
            $this->cpnuManagement->isAccessAllowed(CpnuManagement::FRONTEND_RESOURCE_ACCOUNT_SKU_EDIT) &&
            $this->cpnuManagement->erpUpdateAllow()) {
            $html = '<input type="text" name="products[' . $row->getData('entity_id') . '][' . $index . ']" value="' . $value . '" class="desc_' . $index . '"/>';
        } else {
            $html = $value;
        }

        return $html;
    }
}
