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

class Identity extends GenericElement
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
        $fieldset   = $form->addFieldset('connection_identity', []);
        $fieldset->addClass('admin__fieldset');

        $fieldset->addType(
            'account_selector',
            'Epicor\Punchout\Block\Adminhtml\Connections\Edit\Form\Element\ErpAccount'
        );

        $fieldset->addField(
            'ecc_erp_account_type',
            'account_selector',
            [
                'label'          => __('Identity'),
                'required'       => 'true',
                'value'          => $connection->getIdentity(),
                'name'           => 'identity',
                'data-form-part' => 'connection_form',
            ]
        );
        $form->addValues($connection->getData());
        $this->setForm($form);

        return parent::_prepareForm();

    }//end _prepareForm()


}//end class



