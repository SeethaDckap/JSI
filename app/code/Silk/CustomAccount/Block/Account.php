<?php

namespace Silk\CustomAccount\Block;

class Account extends \Magento\Framework\View\Element\Template
{
    protected $varFactory;

    protected $customerSession;

    protected $orderCollectionFactory;

    protected $addressCollectionFactory;

    protected $collectionFactory;

    protected $quoteCollectionFactory;

    protected $replaceCollectionFactory;

    protected $request;

    protected $orderRepository;

    protected $orderConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Variable\Model\VariableFactory $varFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Silk\CustomAccount\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Silk\CustomAccount\Model\ResourceModel\Replace\CollectionFactory $replaceCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
    	$this->varFactory = $varFactory;
        $this->customerSession = $customerSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->replaceCollectionFactory = $replaceCollectionFactory;
        $this->request = $request;
        $this->orderConfig = $orderConfig;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    // public function getQuotes(){
    // 	$var = $this->varFactory->create()->loadByCode('quotes');
    // 	if($var){
    // 		return json_decode($var->getPlainValue(), true);
    // 	}
    // }

    public function getQuotes(){
        $customerId = $this->customerSession->getCustomerId();
        $quoteCollection = $this->quoteCollectionFactory->create()->addFieldToFilter('customer_id', ['eq' => $customerId])->setOrder('id','DESC');;

        return $quoteCollection;
    }

    public function getOrders(){
    	if($customerId = $this->customerSession->getCustomerId()){
            return $this->orderCollectionFactory->create($customerId)->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'status',
                ['in' => $this->orderConfig->getVisibleOnFrontStatuses()]
            )->setOrder(
                'created_at',
                'desc'
            );
        }

        return null;
    }

    public function getOrder($id){
        try {
            $order = $this->orderRepository->get($id);
            return $order;
        } catch (\Exception $e) {
            return null;
        }
    }


    // public function getOrders(){
    //     $var = $this->varFactory->create()->loadByCode('orders');
    //     if($var){
    //         return json_decode($var->getPlainValue(), true);
    //     }
    // }

    public function getInvoices(){
        $var = $this->varFactory->create()->loadByCode('invoices');
        if($var){
            return json_decode($var->getPlainValue(), true);
        }
    }

    public function getParam($name){
        return $this->request->getParam($name);
    }

    public function getReplacements(){
    	$customerId = $this->customerSession->getCustomerId();
        $replaceCollection = $this->replaceCollectionFactory->create()->addFieldToFilter('customer_id', ['eq' => $customerId]);

        return $replaceCollection;
    }

    public function getAddresses(){
        $collection = $this->addressCollectionFactory->create();
        $collection->setOrder('entity_id', 'desc');
        $addresses = $collection->setCustomerFilter([$this->customerSession->getCustomerId()]);
        $addressInfo = [];
        if($addresses && !empty($addresses)){
            foreach ($addresses as $address) {
                $addressInfo[] = $address->getDataModel();
            }
        }

        return $addressInfo;
    }

    public function getCustomer(){
        return $this->customerSession->getCustomer();
    }

    public function getProductInfo(){
        $productCollection = $this->collectionFactory->create();
        $productCollection->addAttributeToSelect('*');
        $productInfo = [];
        foreach ($productCollection as $product) {
            $productInfo[$product->getSku()] = number_format($product->getPrice(), 2, '.', '');
        }

        return $productInfo;
    }

}
