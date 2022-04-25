<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Account\Masqueradesearch;


/**
 * Customer Orders list
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{

    protected function _setupGrid()
    {
        $this->_controller = 'customer_account_masqueradesearch_listing';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Masquerade Account Search');
        $this->addButton('close', array(
            'id' => 'close-button',
            'label' => __('Close'),
            'onclick' => "masqueradeSearchClosePopup()",
            'class' => 'close',
            ), -100);
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

}
