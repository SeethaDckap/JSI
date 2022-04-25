<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class State extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{


    /**
     * @var \Epicor\Lists\Model\ListModel\AddressFactory
     */
    protected $listsListModelAddressFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $directoryResourceModelRegionCollectionFactory;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Lists\Model\ListModel\AddressFactory $listsListModelAddressFactory,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $directoryResourceModelRegionCollectionFactory
    )
    {
        $this->listsListModelAddressFactory = $listsListModelAddressFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->directoryResourceModelRegionCollectionFactory = $directoryResourceModelRegionCollectionFactory;

        parent::__construct($context, $backendAuthSession);
    }

    /**
     * Load counties for selected country
     *
     * @return html
     */
    public function execute()
    {
        $countryCode = $this->getRequest()->getPost('country');
        $addressId = $this->getRequest()->getPost('address_id');
        $this->_registry->register('country_code', $countryCode);
        //$address = $this->listsListModelAddressFactory->create()->load($addressId);
        $county = '';
        if ($countryCode != '') {
            $statearray = $this->directoryRegionFactory->create()->getResourceCollection()->addCountryFilter($countryCode)->load();

            $statearray = $this->directoryResourceModelRegionCollectionFactory->create()
                ->addCountryFilter($countryCode)
                ->load()
                ->toOptionArray();
            // print_r($statearray);die;
            // if ($statearray->count() > 0) {
            if (count($statearray) > 0) {
                foreach ($statearray as $_state) {
                    // $county[$_state->getCode()] = $_state->getDefaultName();
                    $county[$_state['label']] = $_state['label'];
                }
            }

            $this->getResponse()->setBody(json_encode($county));
        }
    }

    }
