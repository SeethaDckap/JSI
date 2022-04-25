<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Punchout\Block\Adminhtml\Connections\Edit\Form;

use Magento\Backend\Block\Widget\Grid\Container;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Customer
 */
class Customer extends Container
{


    /**
     * COnstructor function.
     */
    protected function _construct()
    {
        $this->_controller = 'epicor_punchout/connections/listCustomerGrid';
        $this->_blockGroup = 'Epicor_Punchout';
        $this->_headerText = __('Customer');

        $this->addButton(
            20,
            [
                'label'   => 'Cancel',
                'onclick' => 'accountSelector.closepopup()',
            ],
            1
        );

        parent::_construct();
        $this->removeButton('add');

    }//end _construct()


    /**
     * Prepare layout.
     *
     * @return $this
     * @throws LocalizedException Exception.
     */
    protected function _prepareLayout()
    {
        if (false === $this->getChildBlock('grid')) {
            $this->setChild(
                'grid',
                $this->getLayout()->createBlock(
                    'Epicor\Punchout\Block\Adminhtml\Connections\Edit\Form\Customer\Grid'
                )->setSaveParametersInSession(
                    true
                )
            );
        }

        $this->toolbar->pushButtons($this, $this->buttonList);

        return $this;

    }//end _prepareLayout()


}//end class
