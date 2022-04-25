<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Context;

class Lines extends \Epicor\Common\Block\Generic\Listing
{
    /**
     * Button widget.
     *
     * @var ButtonList
     */
    private $buttons;

    /**
     * Array of buttons to be skipped.
     *
     * @var array
     */
    private $skipButtons = [
        'add_attachment',
    ];

    /**
     * Constructor function.
     *
     * @param Context  $context  Context class.
     * @param array    $data     Data array.
     */
    public function __construct(
        Context $context,
        array $data=[]
    ) {
        $this->buttons  = $context->getButtonList();
        parent::__construct(
            $context,
            $data
        );

    }//end __construct()

    /**
     * Setting of Grid Block details
     */
    protected function _setupGrid()
    {
        $this->_controller = 'customer_orders_details_lines';
        $this->_blockGroup = 'Epicor_Supplierconnect';
        $this->_headerText = __('Lines');
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

    /**
     * Gets button html
     *
     * @param mixed $region Region.
     *
     * @return string
     */
    public function getButtonsHtml($region=null)
    {
        $out = '';
        foreach ($this->buttons->getItems() as $buttons) {
            foreach ($buttons as $item) {
                $id = $item->getId();
                if (in_array($id, $this->skipButtons) || ($region && $region != $item->getRegion())) {
                    continue;
                }
                $out .= $this->getChildHtml($item->getButtonKey());
            }
        }
        return $out;
    }//end getButtonsHtml()

}
