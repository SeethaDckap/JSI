<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Crqs\Details;


/**
 * RFQ details - non-editable info block
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Info extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Info
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\ErpquotestatusFactory
     */
    protected $customerconnectErpMappingErpquotestatusFactory;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,     
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Epicor\Customerconnect\Model\Erp\Mapping\ErpquotestatusFactory $customerconnectErpMappingErpquotestatusFactory,
           array $data = []   
    ) {
        $this->registry = $registry;
        $this->storeManager = $context->getStoreManager();
        $this->customerconnectErpMappingErpquotestatusFactory = $customerconnectErpMappingErpquotestatusFactory;
        parent::__construct(
            $context,
            $registry,
            $customerconnectHelper, 
            $customerconnectMessagingHelper,    
            $data
        );
    }
    public function _construct()
    {
        parent::_construct();
        
        $this->setTemplate('Epicor_SalesRep::epicor/salesrep/crqs/details/info.phtml');
        $this->setTitle(__('Information'));
    }

    public function getQuoteStatusHtmlSelect()
    {

        $rfq = $this->registry->registry('customer_connect_rfq_details');

        if ($this->registry->registry('rfqs_editable')) {
            $quoteStatusCode = $rfq->getQuoteStatus();

            $store_id = $this->storeManager->getStore()->getStoreId();

            /* @var $model Epicor_Customerconnect_Model_Erp_Mapping_Erpquotestatus */
            $model = $this->customerconnectErpMappingErpquotestatusFactory->create();

            $quoteStatusArray = $model->getCollection()->addFieldToFilter('store_id', $store_id)->getItems();
            if ($store_id != 0 && empty($quoteStatusArray)) {
                $quoteStatusArray = $model->getCollection()->addFieldToFilter('store_id', 0)->getItems();
            }

            $options = array();
            foreach ($quoteStatusArray as $quoteStatus) {
                $options[] = array(
                    'value' => $quoteStatus->getCode(),
                    'label' => __($quoteStatus->getStatus())
                );
            }

            $select = $this->getLayout()->createBlock('\Magento\Framework\View\Element\Html\Select')
                ->setName('quote_status')
                ->setId('quote-status-select')
                ->setClass('quote-status-select')
                ->setValue($quoteStatusCode)
                ->setOptions($options);
            $html = $select->getHtml();
        } else {
            $html = $this->getQuoteStatus() . '<input type="hidden" id="quote_status" name="quote_status" value="' . $rfq->getQuoteStatus() . '"/>';
        }

        return $html;
    }

    //M1 > M2 Translation Begin (Rule p2-8)
    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->registry->registry($key);
    }

    /**
     * @param $key
     * @param $value
     * @param bool $graceful
     */
    public function register($key, $value, $graceful = false)
    {
        $this->registry->register($key, $value, $graceful);
    }

    /**
     * @param $key
     */
    public function unregister($key)
    {
        $this->registry->unregister($key);
    }
    //M1 > M2 Translation End
}
