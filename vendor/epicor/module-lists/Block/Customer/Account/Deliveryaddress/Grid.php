<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account\Deliveryaddress;


/**
 * Customer delivery address Grid
 * 
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    protected $listsFrontendRestrictedHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Epicor\Comm\Block\Customer\Address\Renderer\StreetFactory
     */
    protected $commCustomerAddressRendererStreetFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSessionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Epicor\Comm\Block\Customer\Address\Renderer\StreetFactory $commCustomerAddressRendererStreetFactory,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        array $data = []
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commCustomerAddressRendererStreetFactory = $commCustomerAddressRendererStreetFactory;
        $this->listsFrontendRestrictedHelper = $listsFrontendRestrictedHelper;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->eventManager = $context->getEventManager();
        $this->commonHelper = $commonHelper;
        $this->customerSessionFactory = $customerSessionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setId('selectgrid');
        $this->setDefaultSort('firstname');
        $this->setDefaultDir('DESC');

        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('epicor_common');
        $this->setIdColumn('id');

        $this->setFilterVisibility(true);
        $this->setPagerVisibility(true);
        $this->setCacheDisabled(true);
        $this->setUseAjax(true);
        $this->setSkipGenerateContent(true);

        $this->setCustomData($this->_getCustomData());
    }


    /**
     * Custom Columns of Grid
     *
     * @return array
     */
    protected function _getColumns()
    {
        $columns = [
            'firstname'  => [
                'header'    => __('Firstname'),
                'index'     => 'firstname',
                'width'     => '150',
                'type'      => 'text',
                'condition' => 'LIKE',
            ],
            'street'     => [
                'header'    => __('Address'),
                'index'     => 'street',
                'type'      => 'text',
                'condition' => 'LIKE',
                'renderer'  => 'Epicor\Comm\Block\Customer\Address\Renderer\Street',
            ],
            'city'       => [
                'header'    => __('City'),
                'index'     => 'city',
                'type'      => 'text',
                'condition' => 'LIKE',
            ],
            'region'     => [
                'header'    => __('Region'),
                'index'     => 'region',
                'type'      => 'text',
                'condition' => 'LIKE',
            ],
            'country_id' => [
                'header'    => __('Country'),
                'index'     => 'country_id',
                'type'      => 'text',
                'condition' => 'LIKE',
            ],
            'postcode'   => [
                'header'    => __('Postcode'),
                'index'     => 'postcode',
                'type'      => 'text',
                'condition' => 'LIKE',
            ],
            'select'     => [
                'header'    => __('Select'),
                'align'     => 'center',
                'index'     => 'entity_id',
                'width'     => '280',
                'renderer'  => 'Epicor\Lists\Block\Customer\Account\Deliveryaddress\Grid\Renderer\Select',
                'links'     => 'true',
                // 'getter' => 'getId',
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
                'actions'   => [
                    [
                        'caption' => __('Select'),
                        'url'     => '',
                        'id'      => 'link',
                        'onclick' => 'changeShippingAdresss(this); return false;',
                    ],
                ],
            ],
        ];


        return $columns;

    }//end _getColumns()


    protected function _getCustomData()
    {
        $addresses = $this->getAddresses();

        $helper = $this->listsFrontendRestrictedHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Restricted */

        $address = $helper->getRestrictionAddress();
        // if the address that is selected is a branch - do not show in choose address page. 
        $branchLocationCode = $helper->getBranchPickupAddress();
        $branchValid = true;
        if (($branchLocationCode) && ($branchLocationCode == $address->getEccErpAddressCode())) {
            $branchValid = false;
        }
        if (
            $address instanceof \Magento\Quote\Model\Quote\Address &&
            ($address->getCustomerAddressId() == false && $address->getEccErpAddressCode() == false && $branchValid)
        ) {
            $addresses[] = $address;
        }

        return $addresses;
    }

    public function getAddresses()
    {
        $customer = $this->customerSessionFactory->create()->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */
        $transportObject = $this->dataObjectFactory->create();
        $transportObject->setAddresses(array());
        $transportObject->setLoadAddresses(true);
        $dispatchParams = array(
            'quote' => $this->checkoutSession->getQuote(),
            'type' => 'delivery',
            'restrict_by_type' => $this->restrictAddressTypes(),
            'addresses' => $transportObject
        );
        $this->eventManager->dispatch('epicor_comm_onepage_get_checkout_addresses', $dispatchParams);
        $addresses = $transportObject->getAddresses();
        $loadAddresses = $transportObject->getLoadAddresses();

        if ($loadAddresses) {
            $addresses = ($this->restrictAddressTypes()) ? $customer->getAddressesByType('delivery') : $customer->getCustomAddresses();
        }

        return $addresses;
    }

    /**
     * Checks whether addresses should be restricted
     * 
     * @return boolean
     */
    public function restrictAddressTypes()
    {
        $helper = $this->commonHelper;
        /* @var $helper Epicor_Common_Helper_Data */

        return $helper->restrictAddressTypes();
    }


    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/deliveryaddressgrid', array(
                '_current' => true
        ));
    }

    public function getRowUrl($item)
    {
        return false;
    }


}
