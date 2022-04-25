<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Customer\Quotes\Details;


/**
 * Quotes lines grid container
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Lines extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines
{
    const FRONTEND_RESOURCE_CREATE = "Dealer_Connect::dealer_quotes_create";

    const FRONTEND_RESOURCE_EDIT = 'Dealer_Connect::dealer_quotes_edit';

    protected function _setupGrid()
    {
        $this->_controller = 'customer_quotes_details_lines';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_headerText = ''; //Mage::helper('customerconnect')->__('Lines');

        if ($this->registry->registry('rfqs_editable') && $this->_isFormAccessAllowed()) {
            $this->buttons->add(
                'add_line', array(
                'id' => 'add_line',
                'label' => __('Quick Add'),
                'class' => 'add',
            ), -1
            );

            $this->buttons->add(
                'add_search', array(
                'id' => 'add_search',
                'label' => __('Add by Search'),
                'class' => 'show-hide',
            ), -1
            );

//            $this->buttons->add(
//                'newline_button', array(
//                'id' => 'newline_button',
//                'label' => '',
//                'class' => '',
//            ), 0
//            );


            $this->buttons->add(
                'clone_selected', array(
                'id' => 'clone_selected',
                'label' => __('Clone Selected'),
                'class' => 'go',
            ), 1
            );

            $this->buttons->add(
                'delete_selected', array(
                'id' => 'delete_selected',
                'label' => __('Delete Selected'),
                'class' => 'delete',
            ), 1
            );
        }
    }
}
