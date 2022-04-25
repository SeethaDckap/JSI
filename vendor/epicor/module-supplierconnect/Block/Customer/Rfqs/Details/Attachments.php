<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Supplierconnect
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Supplierconnect attachments details block.
 */
class Attachments extends \Epicor\Common\Block\Generic\Listing
{
    const FRONTEND_RESOURCE_EDIT = 'Epicor_Supplier::supplier_rfqs_edit';

    /**
     * Registry class.
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Button widget.
     *
     * @var ButtonList
     */
    protected $buttons;

    /**
     * Array of buttons to be skipped.
     *
     * @var array
     */
    protected $skipButtons = [
        'add_cross_reference_part',
        'add_price_break',
    ];


    /**
     * Constructor function.
     *
     * @param Context  $context  Context class.
     * @param Registry $registry Registry class.
     * @param array    $data     Data array.
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data=[]
    ) {
        $this->registry = $registry;
        $this->buttons  = $context->getButtonList();
        parent::__construct(
            $context,
            $data
        );

    }//end __construct()


    /**
     * Grid setup.
     */
    protected function _setupGrid()
    {
        $this->_controller = 'customer_rfqs_details_attachments';
        $this->_blockGroup = 'Epicor_Supplierconnect';
        $this->_headerText = __('Attachments');

        if ($this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT) && $this->registry->registry('rfq_editable')) {
            $this->addButton(
                'add_attachment',
                [
                    'id'    => 'add_attachment',
                    'label' => __('Add'),
                    'class' => 'save',
                ],
                -100
            );
        }

    }//end _setupGrid()


    /**
     * Post setup.
     */
    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();

    }//end _postSetup()


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


}//end class
