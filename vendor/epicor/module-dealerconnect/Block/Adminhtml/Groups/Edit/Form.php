<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Groups\Edit;


/**
 * Dealer Group edit form container
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected function _prepareForm()
    {

        $dealerGrp = $this->getDealerGrp();

        $url = $dealerGrp->isObjectNew() ? '*/*/create' : '*/*/save';

        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl($url, array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ]
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Gets the current Dealer Group
     *
     * @return \Epicor\Dealerconnect\Model\Dealergroups
     */
    public function getDealerGrp()
    {
        if (!isset($this->_dealerGrp)) {
            $this->_dealerGrp = $this->_coreRegistry->registry('dealergrp');
        }
        return $this->_dealerGrp;
    }

}
