<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\History;

/**
 * @category   Epicor
 * @package    Epicor_Arpayments
 * @author     Epicor Websales Team
 */
class Grid extends  \Epicor\Common\Block\Generic\Listing\Grid
{

    const FRONTEND_RESOURCE_DETAIL = "Epicor_Customer::my_account_ar_payment_received_details";
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;
    
     protected $customerSession;    
    
    /*
     * @var \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\Collection
     */
    protected $arpaymentOrderCollectionFactory;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\CollectionFactory $arpaymentOrderCollectionFactory,
        array $data = []
    )
    {
        $this->commHelper = $commHelper;
        $this->arpaymentOrderCollectionFactory = $arpaymentOrderCollectionFactory;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );
         
        $this->setId('arpayment_history');
        $this->setSaveParametersInSession(true);
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setFilterVisibility(false);
        $this->setCacheDisabled(true);
        //$this->setDefaultSort('id');
       // $this->setDefaultDir('asc');
       // $this->setPagerVisibility(false);
        // $this->setIdColumn('number');
    }
    
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        
        $this->addColumn('increment_id', array(
            'header' => __('AR PAYMENT REFERENCE'),
            'index' => 'increment_id',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Arpayments\History\Renderer\ArpaymentReference',
        ));

      $this->addColumn('created_at', array(
            'header' => __('Date'),
            'index' => 'created_at',
            'type'      => 'date',
            'filter_index' => 'created_at'
        ));
      
      $this->addColumn('grand_total', array(
            'header' => __('Payment Total'),
            'index' => 'grand_total',
            'filter_index' => 'grand_total',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Arpayments\History\Renderer\GrandTotal',
        ));

      if ($this->isRowUrlAllowed()) {
          $this->addColumn('action', array(
              'header' => __('Action'),
              'width' => '100',
              'type' => 'action',
              'getter' => 'getId',
              'actions' => array(
                  array(
                      'caption' => __('View Payment'),
                      'url' => array('base' => '*/*/view'),
                      'field' => 'order_id'
                  ),
              ),
              'filter' => false,
              'sortable' => false,
              'index' => 'id',
              'is_system' => true,
          ));
      }
      
        return $this;
    }
    
    protected function _prepareCollection()
    {  
        \Magento\Backend\Block\Widget\Grid\Extended::_prepareCollection();
        
        
        $customer_id = $this->customerSession->getCustomer()->getId();
        $collection =  $this->arpaymentOrderCollectionFactory->create($customer_id);
        $collection->setOrder('created_at', 'desc');
        $this->setCollection($collection);
    }
    
    public function getRowUrl($row)
    {
        return null;
    }
}
