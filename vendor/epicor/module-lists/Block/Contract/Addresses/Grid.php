<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Contract\Addresses;


/**
 * List Addresses Serialized Grid Frontend
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    private $_selected = array();

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory
     */
    protected $listsResourceListModelAddressCollectionFactory;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Epicor\Lists\Block\Contract\Renderer\AddressFactory
     */
    protected $listsContractRendererAddressFactory;

    /**
     * @var \Epicor\Lists\Block\Contract\Renderer\AddressactiveFactory
     */
    protected $listsContractRendererAddressactiveFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper, 
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory $listsResourceListModelAddressCollectionFactory,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Lists\Block\Contract\Renderer\AddressFactory $listsContractRendererAddressFactory,
        \Epicor\Lists\Block\Contract\Renderer\AddressactiveFactory $listsContractRendererAddressactiveFactory,
        array $data = []
    )
    {
        $this->listsContractRendererAddressFactory = $listsContractRendererAddressFactory;
        $this->listsContractRendererAddressactiveFactory = $listsContractRendererAddressactiveFactory;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->checkoutCart = $checkoutCart;
        $this->listsResourceListModelAddressCollectionFactory = $listsResourceListModelAddressCollectionFactory;
        $this->listsHelper = $listsHelper;
        parent::__construct(
                $context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $data
        );
        $this->setId('addressesGrid');
        $this->setDefaultSort('type');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('epicor_common');
        $this->setIdColumn('id');

        $this->setFilterVisibility(true);
        $this->setPagerVisibility(true);
        $this->setCacheDisabled(true);
        $this->setUseAjax(true);
        $this->setTemplate('Epicor_Common::widget/grid/extended.phtml');
    }

    /**
     * Gets the List for this tab
     *
     * @return boolean
     */
    public function getList()
    {
        $this->list = $this->listsListModelFactory->create()->load($this->getRequest()->getParam('contract'));
        return $this->list;
    }

    protected function _prepareLayout()
    {
        $this->setChild('add_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(array(
                    'label' => __('Close'),
                    'onclick' => 'addressSelector.closepopup()',
                    'class' => 'task'
                ))
        );


        $urlRedirect = $this->getUrl('*/*/selectcontract', array('_current' => true, 'contract' => $this->getRequest()->getParam('contract')));
        $onClick = 'location.href=\'' . $urlRedirect . '\';';
        $quote = $this->checkoutCart->getQuote();
        /* @var $quote Epicor_Comm_Model_Quote */
        if ($quote->hasItems()) {
            $message = __('Changing Contract may remove items from the cart that are not valid for the selected Contract. Do you wish to continue?');
            $onClick = 'if(confirm(\'' . $message . '\')) { ' . $onClick . ' }';
        }


        $this->setChild('select_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(array(
                    'label' => __('Select List'),
                    'onclick' => $onClick,
                    'class' => 'task'
                ))
        );

        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getSelectButtonHtml()
    {
        return $this->getChildHtml('select_button');
    }

    public function getMainButtonsHtml()
    {
        $html = $this->getSelectButtonHtml();
        $html .= $this->getAddButtonHtml();
        $html .= parent::getMainButtonsHtml();
        return $html;
    }

    /**
     * Build data for List Addresses
     *
     * 
     */
    protected function _prepareCollection()
    {
        $collection = $this->listsResourceListModelAddressCollectionFactory->create();
        /* @var $collection Epicor_Lists_Model_Resource_List_Address_Collection */
        $collection->addFieldToFilter('list_id', $this->getList()->getId());

        $this->setCollection($collection);
        return \Magento\Backend\Block\Widget\Grid\Extended::_prepareCollection();
    }

    /**
     * Build columns for List Addresses
     *
     * 
     */
    protected function _prepareColumns()
    {
        $helper = $this->listsHelper;
        /* @var $helper Epicor_Lists_Helper_Data */

        $this->addColumn(
            'address_code', array(
            'header' => __('Address Code'),
            'index' => 'address_code',
            'type' => 'text'
            )
        );

        $this->addColumn(
            'purchase_order_number', array(
            'header' => __('Purchase Order Number'),
            'index' => 'purchase_order_number',
            'type' => 'text'
            )
        );

        $this->addColumn(
            'address_name', array(
            'header' => __('Name'),
            'index' => 'name',
            'type' => 'text'
            )
        );

        $this->addColumn(
            'flatt_address', array(
            'header' => __('Address'),
            'index' => 'address1',
            'type' => 'text',
            'renderer' => '\Epicor\Lists\Block\Contract\Renderer\Address',
            'filter_condition_callback' => array($this, '_addressFilter'),
            )
        );

        $this->addColumn(
            'address_email_address', array(
            'header' => __('Email'),
            'index' => 'email_address',
            'type' => 'text'
            )
        );

        $this->addColumn(
            'status', array(
            'header' => __('Status'),
            'type' => 'options',
            'options' => $this->getStatusName(),
            'renderer' => '\Epicor\Lists\Block\Contract\Renderer\Addressactive',
            'filter_condition_callback' => array($this, 'statusFilter')
            )
        );


        return parent::_prepareColumns();
    }

    protected function _addressFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $clone = clone $collection;

        $filterIds = array();
        foreach ($clone->getItems() as $item) {
            /* @var $item Epicor_Lists_Model_ListModel */
            if (stripos($item->getFlattenedAddress(), $value) !== false) {
                $filterIds[] = $item->getId();
            }
        }

        $collection->addFieldToFilter('id', array('in' => $filterIds));
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/addressesgrid', array('_current' => true));
    }
    
    /**
     * Row Click URL
     * 
     * @return null
     */
    public function getRowUrl($row) {
        return null;
    }

    public function isSectionEditable()
    {
        return $this->getList()->getTypeInstance()->isSectionEditable('addresses');
    }

    public function getStatusName()
    {

        $statusName = array('Active' => 'Active', 'Inactive' => 'Inactive', 'Expired' => 'Expired');
        return $statusName;
    }

    public function statusFilter($collection, $column)
    {

        if (!$value = $column->getFilter()->getValue()) {   // if unable to get a value of the column don't attempt filter  
            return $this;
        }
        switch ($value) {

            case 'Inactive':
                $collection->addFieldToFilter('activation_date', array(
                    'neq' => null
                ));
                $collection->addFieldToFilter('activation_date', array(
                    'gteq' => now()
                ));

                break;

            case 'Expired':
                $collection->addFieldToFilter('expiry_date', array(
                    'neq' => null
                ));
                $collection->addFieldToFilter('expiry_date', array(
                    'lt' => now()
                ));

                break;

            case 'Active':
                $collection->addFieldToFilter('expiry_date', array(
                    'null' => true
                ));
                $collection->addFieldToFilter('activation_date', array(
                    'null' => true
                ));
                break;

            default:
                break;
        }
        return $this;
    }

}
