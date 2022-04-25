<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Invoices;


/**
 * Customer Orders list
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    protected $buttons;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    ) {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->registry = $registry;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->buttons = $context->getButtonList();
        parent::__construct(
            $context,
            $data
        );
    }
    protected function _setupGrid()
    {
        $this->_controller = 'customer_arpayments_invoices_listing';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('AR Payments');
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

    protected function _prepareLayout()
    {
        // this is needed for frontend grid use to stop search options being retained for future users. 
        // the omission of calling the parent is intentional
        // as all the processing required when calling parent:: should be included
        $this->setChild( 'grid',
            $this->getLayout()->createBlock(
                str_replace(
                    '_',
                    '\\',
                    $this->_blockGroup
                ) . '\\Block\\' . str_replace(
                    ' ',
                    '\\',
                    ucwords(str_replace('_', ' ', $this->_controller))
                ) . '\\Grid',
                $this->_controller . '.grid'
            )->setSaveParametersInSession(false) );

        $this->toolbar->pushButtons($this, $this->buttons);
        return $this;
    }

    public function getButtonsHtml($region = null)
    {
        $out = '';
        $this->buttonList->remove('add-shipping-address');
        foreach ($this->buttons->getItems() as $buttons) {
            /** @var \Magento\Backend\Block\Widget\Button\Item $item */
            foreach ($buttons as $item) {
                if ($region && $region != $item->getRegion()) {
                    continue;
                }
                $out .= $this->getChildHtml($item->getButtonKey());
            }
        }
        return $out;
    }
}
