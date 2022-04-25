<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock;


class Form extends \Magento\Backend\Block\Widget\Form
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    protected function includeStoreIdElement($data)
    {
        if (!isset($data['store_id'])) {
            $data['store_id'] = (int) $this->getRequest()->getParam('store', 0);
        }
        $this->getForm()->getElement('mapping_form')->addField('store_id', 'hidden', array('name' => 'store_id', 'required' => true));

        return $data;
    }

}
