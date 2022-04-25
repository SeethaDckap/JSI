<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit;


/**
 * List edit form container
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected function _prepareForm()
    {

        $list = $this->getList();

        $url = $list->isObjectNew() ? '*/*/create' : '*/*/save';

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
     * Gets the current List
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getList()
    {
        if (!isset($this->_list)) {
            $this->_list = $this->_coreRegistry->registry('list');
        }
        return $this->_list;
    }

}
