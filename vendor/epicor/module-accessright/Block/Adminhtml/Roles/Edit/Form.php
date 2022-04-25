<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Block\Adminhtml\Roles\Edit;


/**
 * Role edit form container
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected function _prepareForm()
    {

        $role = $this->getRole();
        //$url = $role->isObjectNew() ? '*/*/create' : '*/*/save';
        $url = '*/*/save';
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
     * Gets the current Role
     *
     * @return \Epicor\AccessRight\Model\RoleModel
     */
    public function getRole()
    {
        if (!isset($this->_role)) {
            $this->_role = $this->_coreRegistry->registry('role');
        }
        return $this->_role;
    }

}
