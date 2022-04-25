<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Punchout\Block\Adminhtml\Connections\Edit\Form\Customer;

use Exception;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\System\Store;
use Epicor\Comm\Helper\Data as CommHelper;


/**
 * Customer grid for ERP account selector
 */
class Grid extends Extended
{

    /**
     * Customer collection factory
     *
     * @var CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * Comm helper.
     *
     * @var CommHelper
     */
    private $commHelper;

    /**
     * Store model.
     *
     * @var Store
     */
    private $storeSystemStore;


    /**
     * Grid constructor.
     *
     * @param Context           $context                   Context.
     * @param Data              $backendHelper             Backend helper.
     * @param CollectionFactory $customerCollectionFactory Customer collection factory.
     * @param CommHelper        $commHelper                Comm Helper.
     * @param Store             $storeSystemStore          Store model.
     * @param array             $data                      Data array.
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $customerCollectionFactory,
        CommHelper $commHelper,
        Store $storeSystemStore,
        array $data=[]
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->commHelper = $commHelper;
        $this->storeSystemStore = $storeSystemStore;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('customer_grid');
        $this->setDefaultSort('email');
        $this->setDefaultDir('ASC');
        $this->setRowClickCallback('accountSelector.selectCustomer.bind(accountSelector)');
        $this->setRowInitCallback('accountSelector.updateWrapper.bind(accountSelector)');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);

    }//end __construct()


    /**
     * Prepare collection.
     *
     * @return Extended
     * @throws LocalizedException Exception.
     */
    protected function _prepareCollection()
    {
        $collection = $this->customerCollectionFactory->create();
        $data       = $this->getRequest()->getParams();

        if (isset($data['scope_id']) && isset($data['selected_erpaccount'])) {
            // scope_id = website_id:store_id
            $tokens    = explode(':', $data['scope_id']);
            $websiteId = $tokens[0];

            //ERP Account ID
            $erpAccount = $this->commHelper->getErpAccountByAccountNumber($data['selected_erpaccount'], 'customer', true);
            $erpAccountId = $erpAccount->getId();

            $collection->addAttributeToFilter('ecc_erpaccount_id', $erpAccountId);

            if ($websiteId != 0) {
                $collection->addFieldToFilter('website_id', $websiteId);
            }

            //Remove customers against multiple CUCO's
        }
        $this->setCollection($collection);

        return parent::_prepareCollection();

    }//end _prepareCollection()


    /**
     * Prepare columns.
     *
     * @return $this|Extended
     *
     * @throws Exception Exception.
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'rowdata',
            [
                'header'           => __(''),
                'align'            => 'left',
                'width'            => '1',
                'name'             => 'rowdata',
                'filter'           => false,
                'sortable'         => false,
                'renderer'         => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Rowdata',
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display',
            ]
        );

        $this->addColumn(
            'email',
            [
                'header'       => __('Email'),
                'index'        => 'email',
                'width'        => '20px',
                'filter_index' => 'email',
            ]
        );

        $this->addColumn(
            'firstname',
            [
                'header'       => __('Firstname'),
                'index'        => 'firstname',
                'width'        => '20px',
                'filter_index' => 'firstname',
            ]
        );

        $this->addColumn(
            'lastname',
            [
                'header'       => __('Lastname'),
                'index'        => 'lastname',
                'width'        => '20px',
                'filter_index' => 'lastname',
            ]
        );

        $this->addColumn(
            'website_id',
            [
                'header'  => __('Website'),
                'align'   => 'left',
                'width'   => '160px',
                'type'    => 'options',
                'options' => $this->storeSystemStore->getWebsiteOptionHash(false),
                'index'   => 'website_id',
            ]
        );

        return $this;

    }//end _prepareColumns()


    /**
     * Get row Url.
     *
     * @param Product|DataObject $row Row object.
     *
     * @return integer|string
     */
    public function getRowUrl($row)
    {
        return $row->getId();

    }//end getRowUrl()


    /**
     * Get grid Url.
     *
     * @return string
     */
    public function getGridUrl()
    {
        $data = $this->getRequest()->getParams();
        return $this->getUrl(
            '*/*/*',
            [
                'grid'                => true,
                'field_id'            => $data['field_id'],
                'selected_erpaccount' => $data['selected_erpaccount'],
                'scope_id'            => $data['scope_id'],
            ]
        );

    }//end getGridUrl()


}//end class


