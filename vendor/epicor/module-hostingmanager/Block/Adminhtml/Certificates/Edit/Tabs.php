<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml\Certificates\Edit;


/**
 * Certificates edit tabs block
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    public function _construct()
    {
        parent::_construct();
        $this->setId('certificate_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Certificate'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('details', array(
            'label' => 'Certificate Details',
            'title' => 'Certificate Details',
            'content' => $this->getLayout()->createBlock('\Epicor\HostingManager\Block\Adminhtml\Certificates\Edit\Tab\Details')->initForm()->toHtml(),
        ));

        $this->addTab('csr', array(
            'label' => 'Generate CSR',
            'title' => 'Generate CSR',
            'content' => $this->getLayout()->createBlock('\Epicor\HostingManager\Block\Adminhtml\Certificates\Edit\Tab\Csr')->initForm()->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}
