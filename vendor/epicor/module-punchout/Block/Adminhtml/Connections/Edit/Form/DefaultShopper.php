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

use Magento\Framework\Exception\LocalizedException;

class DefaultShopper extends GenericElement
{


    /**
     * Prepare form.
     *
     * @return Generic
     * @throws LocalizedException Exception.
     */
    protected function _prepareForm()
    {
        $connection = $this->connectionRepository->loadEntity();
        $form       = $this->_formFactory->create();
        $fieldset   = $form->addFieldset('connection_shopper', []);
        $fieldset->addClass('admin__fieldset');
        if (!$connection->getId()) {
            $fieldset->addClass('no-display');
        }

        $fieldset->addType(
            'customer_selector',
            'Epicor\Punchout\Block\Adminhtml\Connections\Edit\Form\Element\Customer'
        );

        $fieldset->addField(
            'customer_name',
            'customer_selector',
            [
                'label'          => __('Default Shopper'),
                'required'       => 'true',
                'value'          => $connection->getDefaultShopper(),
                'name'           => 'default_shopper',
                'data-form-part' => 'connection_form',
            ]
        );
        $form->addValues($connection->getData());
        $this->setForm($form);

        return parent::_prepareForm();

    }//end _prepareForm()


}//end class

