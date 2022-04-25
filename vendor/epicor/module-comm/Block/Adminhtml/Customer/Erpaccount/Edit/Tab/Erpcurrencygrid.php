<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


use Epicor\Comm\Model\MinOrderAmountFlag;

class Erpcurrencygrid extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_erpCustomer;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    private $minOrderAmountFlag;

    public function __construct(
        MinOrderAmountFlag $minOrderAmountFlag,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('currencyGrid');
        $this->setSaveParametersInSession(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->minOrderAmountFlag = $minOrderAmountFlag;
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Currency Grid';
    }

    public function getTabTitle()
    {
        return 'Currency Grid';
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();

        $allowedCurrencies = $this->dataObjectFactory->create(['data' => $this->registry->registry('customer_erp_account')->getAllCurrencyData()]);
        foreach ($allowedCurrencies->getData() as $currency) {
            $data = $this->dataObjectFactory->create(['data' => $currency->getData()]);
            $collection->addItem($data);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('Currency Code', array(
            'header' => __('Currency Code'),
            'width' => '50',
            'index' => 'currency_code',
            'sortable' => false,
        ));

        $this->addColumn('credit_limit', array(
            'header' => __('Credit Limit'),
            'width' => '50',
            'index' => 'credit_limit',
            'sortable' => false,
        ));

        $this->addColumn('balance', array(
            'header' => __('Current Balance'),
            'width' => '50',
            'index' => 'balance',
            'sortable' => false,
        ));
        $this->addColumn('onstop', array(
            'header' => __('On Stop'),
            'width' => '50',
            'index' => 'onstop',
            'sortable' => false,
        ));

        $this->addColumn('min_order_amount', array(
            'header' => __('Min Order Amount'),
            'width' => '50',
            'index' => 'min_order_amount',
            'sortable' => false
        ));
        $id = $this->getRequest()->getParam('id');
        if ($this->minOrderAmountFlag->isErpCurrencyGridEditable()) {
            $this->addColumn('action', array(
                'header' => __('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => __('Edit'),
                        'url' => array(
                            'base' => 'currency/epicorcomm_customer_erpaccount_currency/edit',
                            'params' => array(
                                'erpaccount' => $id,
                            )
                        ),
                        'field' => 'id'
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'id',
                'is_system' => true,
            ));
        }

        return parent::_prepareColumns();
    }
}
