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

/**
 * Class SecretKey
 */
class SecretKey extends GenericElement
{


    /**
     * Prepare form.
     *
     * @return GenericElement
     * @throws LocalizedException Exception.
     */
    protected function _prepareForm()
    {
        $connection = $this->connectionRepository->loadEntity();
        if ($connection->getId()) {
            $form = $this->_formFactory->create();
            $this->setForm($form);

            $fieldset = $form->addFieldset('connection_key', []);
            $fieldset->addClass('admin__fieldset');

            $fieldset->addField(
                'shared_secret',
                'Epicor\Punchout\Block\Adminhtml\Connections\Edit\Form\Element\Text',
                [
                    'label'          => __('Shared Secret'),
                    'name'           => 'shared_secret',
                    'data-form-part' => 'connection_form',
                    'disabled'       => true,
                ]
            )->setAfterElementHtml($this->getAdditionalHtml());
            $form->addValues($connection->getData());
            $form->setUseContainer(false);
            $this->setForm($form);
        }//end if

        return parent::_prepareForm();

    }//end _prepareForm()


    /**
     * Get additional HTML.
     *
     * @return string
     */
    public function getAdditionalHtml()
    {
        return '<input id="key_regenerate" name="key_regenerate" type="checkbox"
                    value="0" class="checkbox config-inherit" >
                    <label for="key_regenerate" class="inherit">Regenerate Key</label>';

    }//end getAdditionalHtml()


}//end class