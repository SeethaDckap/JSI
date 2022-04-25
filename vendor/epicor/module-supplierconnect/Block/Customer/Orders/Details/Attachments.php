<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Supplierconnect
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Customer\Orders\Details;

/**
 * Supplierconnect attachments details block.
 */
class Attachments extends \Epicor\Common\Block\Generic\Listing
{
    const FRONTEND_RESOURCE_EDIT = 'Epicor_Supplier::supplier_orders_edit';

    /**
     * Grid setup.
     */
    protected function _setupGrid()
    {
        $this->_controller = 'customer_orders_details_attachments';
        $this->_blockGroup = 'Epicor_Supplierconnect';
        $this->_headerText = __('Attachments');

        if ($this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT)) {
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

}//end class
