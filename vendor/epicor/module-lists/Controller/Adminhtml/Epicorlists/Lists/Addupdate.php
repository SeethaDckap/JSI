<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Addupdate extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;


    /**
     * @var \Epicor\Lists\Model\ListModel\AddressFactory
     */
    protected $listsListModelAddressFactory;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $country;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Directory\Model\Config\Source\Country $country,
        \Epicor\Lists\Model\ListModel\AddressFactory $listsListModelAddressFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->listsListModelFactory = $context->getListsListModelFactory();
        $this->listsListModelAddressFactory = $listsListModelAddressFactory;
        $this->country = $country;
        parent::__construct($context, $backendAuthSession);
    }

    /**
     * Load address restriction form for add/update
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getPost('list_id', null);
        $address_id = $this->getRequest()->getPost('address_id', null);
        $list = $this->listsListModelFactory->create()->load($id);
        /* @var $list Epicor_Lists_Model_ListModel */
        $this->_registry->register('list', $list);
        $address = $this->listsListModelAddressFactory->create()->load($address_id);
        $this->_registry->register('address', $address);
        //M1 > M2 Translation Begin (Rule p2-1)
        //echo $this->getLayout()->createBlock('core/template')->setTemplate('epicor/lists/restrictions/form.phtml')->toHtml();
   /*     echo $this->getLayout()->createBlock(
            'core/template',
            'lists_addupdate',
            [
                'data' => [
                    'country' => $this->country
                ]
            ])->setTemplate('epicor/lists/restrictions/form.phtml')->toHtml();  */
        //M1 > M2 Translation End
        
        echo $this->_view->getLayout()->createBlock('Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Restrictions\Form')->toHtml();
    }

}
