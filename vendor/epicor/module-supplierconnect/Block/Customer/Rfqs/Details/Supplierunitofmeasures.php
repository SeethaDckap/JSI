<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details;


class Supplierunitofmeasures extends \Epicor\Common\Block\Generic\Listing
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    protected $buttons;

    protected $skipButtons = [
        'add_cross_reference_part',
        'add_price_break',
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
        $this->_controller = 'customer_rfqs_details_supplierunitofmeasures';
        $this->_blockGroup = 'Epicor_Supplierconnect';
        $this->_headerText = __('Supplier Unit Of Measure');

        if ($this->registry->registry('rfq_editable') && $this->registry->registry('allow_conversion_override')) {
            $this->addButton('add_suom', array(
                'id' => 'add_suom',
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
