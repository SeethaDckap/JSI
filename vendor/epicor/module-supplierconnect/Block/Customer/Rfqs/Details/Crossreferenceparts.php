<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details;


class Crossreferenceparts extends \Epicor\Common\Block\Generic\Listing
{
    const FRONTEND_RESOURCE_EDIT = 'Epicor_Supplier::supplier_rfqs_edit';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    protected $buttons;

    protected $skipButtons = [
        'add_price_break',
        'add_suom',
        'add_attachment'
    ];

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->buttons = $context->getButtonList();
        parent::__construct(
            $context,
            $data
        );
    }

    protected function _setupGrid()
    {
        $this->_controller = 'customer_rfqs_details_crossreferenceparts';
        $this->_blockGroup = 'Epicor_Supplierconnect';
        $this->_headerText = __('Cross Reference Part Information');

        if ($this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT) && $this->registry->registry('rfq_editable')) {
            $this->addButton('add_cross_reference_part', array(
                'id' => 'add_cross_reference_part',
                'label' => __('Add'),
                'class' => 'save',
                ), -100);
        }
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

    public function getButtonsHtml($region = null)
    {
        $out = '';
        foreach ($this->buttons->getItems() as $buttons) {
            /** @var \Magento\Backend\Block\Widget\Button\Item $item */
            foreach ($buttons as $item) {
                $id = $item->getId();
                if (in_array($id, $this->skipButtons) || ($region && $region != $item->getRegion())) {
                    continue;
                }
                $out .= $this->getChildHtml($item->getButtonKey());
            }
        }
        return $out;
    }
}
