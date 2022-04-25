<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Lists;

class Save extends \Epicor\Lists\Controller\Lists
{

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory
     */
    protected $listProductCollectionFactory;
    /**
     * @var \Epicor\Lists\Model\ListModel\Product
     */
    private $listProduct;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /**
     * /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutCartHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory $listProductCollectionFactory,
        \Epicor\Lists\Model\ListModel\Product $listProduct,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Helper\Cart $checkoutCartHelper

    )
    {

        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $backendJsHelper,
            $commHelper,
            $listsListModelFactory,
            $generic,
            $listsHelper,
            $listsFrontendRestrictedHelper,
            $timezone
        );

        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->listProductCollectionFactory = $listProductCollectionFactory;
        $this->listProduct = $listProduct;
        $this->registry = $commHelper->getRegistry();
        $this->listsHelper = $listsHelper;
        $this->orderFactory = $orderFactory;
        $this->checkoutCartHelper = $checkoutCartHelper;
    }

    public function execute()
    {
        $customer = $this->customerSession->getCustomer();

        if ($data = $this->getRequest()->getPost()) {
            $saveNewCartToListArray = [];
            if (isset($data['saveNewCartToList'])) {
                $saveNewCartToListArray = json_decode(base64_decode($data['saveNewCartToList']), true);
            }
            $list = $this->loadEntity();
            $listData = $list->getData();
            if (empty($listData)) {
                $list = $this->listsListModelFactory->create()->load($this->getRequest()->getPost('id'));
                // if supplied set list code (will only be supplied when list doesn't exist during save new cart to list advanced option
                if (isset($saveNewCartToListArray['listCode'])) {
                    $data['erp_code'] = $saveNewCartToListArray['listCode'];
                }
            }
            $list->setErpAccountsExclusion();
            $list->setErpAccountLinkType('E');
            $this->processDetailsSave($list, $data);
            $valid = $list->validate(true);
            $session = $this->generic;

            if ($valid === true) {
                $customer = $this->customerCustomerFactory->create()->load($this->customerSession->getId());
                $erpAccount = $this->commHelper->getErpAccountInfo();
                $customerAccountType = $customer->getEccErpAccountType();
                if ($customerAccountType == "guest") {
                    $list->setErpAccountLinkType('N');
                } else {
                    $list->addErpAccounts(array(
                        $erpAccount->getid()
                    ));
                }

                $new = false;
                if ($list->isObjectNew()) {
                    $new = true;
                    $list->setOwnerId($this->customerSession->getId());
                    $list->setSource('customer');
                }
                $list->save();
                $list_id = array(
                    $list->getId()
                );

                // When a master shopper creates or edits a list he should not be assigned to the list automatically. 
                //It is up to the master shopper to decide whether he wants to assign himself. 
                if ($customer->getEccMasterShopper() == false) {
                    $list->addCustomers(array($customer->getId()));
                }

                $this->processCustomersSave($list, $data);

                $this->processProductsSave($list, $data);
                if ($data['productsQty'] == '[]' && $data['product_info']) {
                    $data = $this->proccessProducttoLists($data, $list);
                } else {
                    $this->proccessProductsQtytoLists($data, $list);
                }

                if (isset($data['saveNewCartToList'])) {
                    $this->saveNewCartToListProducts($list, $saveNewCartToListArray, $data);
                }
                $list->save();
                $this->processProductsQtySave($list, $data);

                $this->messageManager->addSuccess(__('List Saved Successfully'));
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array(
                        'id' => base64_encode($list->getId())
                    ));
                } else {
                    $this->_redirect('*/*/');
                }
            } else {
                $this->messageManager->addError(__('The Following Error(s) occurred on Save:'));
                foreach ($valid as $error) {
                    $this->messageManager->addError($error);
                }
                $session->setFormData($data);
                if ($list->getId()) {
                    $this->_redirect('*/*/edit', array(
                        'id' => base64_encode($list->getId())
                    ));
                } else {
                    $this->_redirect('*/*/new');
                }
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save product qty if changed
     */
    protected function processProductsQtySave($list, $data)
    {
        $productQty = [];
        $collection = $this->listProductCollectionFactory->create();
        $collection->addFieldToFilter('list_id', $list->getId());
        //if count > 0 determine if some are group products
        //delete all existing products on list product table for supplied products relating to list
        $existingListProductSkus = [];
        $groupParentSku = [];
        if (count($collection) > 0) {
            foreach ($collection as $listProduct) {
                $existingListProductSkus[$listProduct->getSku() . '_' . $listProduct->getLocationCode()] = $listProduct;
            }
            $savedGroupProducts = $list->saveGroupedProducts($existingListProductSkus, [], true, true);
            $groupParentSku = !empty($savedGroupProducts) ? array_flip($savedGroupProducts) : [];

        }
        /* @var $collection Epicor_Lists_Model_Resource_List_Collection */
        if (isset($data['productsQty'])) {
            $decodedProductsQty = json_decode($data['productsQty'], true);
            foreach ($decodedProductsQty as $key => $prodQty) {
                if (isset($prodQty['uom'])) {
                    //if the group parent product exists on list product table without the uom add uom to sku, else don't add it
                    if (array_key_exists($prodQty['sku'], $groupParentSku)) {
                        $sku = $prodQty['sku'] . $this->listsHelper->getCommMessagingHelper()->getUOMSeparator() . $prodQty['uom'];
                    } else {
                        $sku = $prodQty['sku'];
                    }
                    $productQty[$key]['sku'] = $sku;
                    $productQty[$key]['qty'] = $prodQty['val'];
                    if (isset($prodQty['location_code'])) {
                        $productQty[$key]['loc'] = $prodQty['location_code'];
                    }
                } else {
                    $productQty[$key]['sku'] = $prodQty['sku'];
                    $productQty[$key]['qty'] = $prodQty['val'];
                    if (isset($prodQty['location_code'])) {
                        $productQty[$key]['loc'] = $prodQty['location_code'];
                    }
                }
            }
        } else {
            if (isset($data['selected_products'])) {
                foreach ($data['selected_products'] as $key => $prod) {
                    $productQty[$key]['sku'] = $prod->getSku();
                    $productQty[$key]['qty'] = $prod->getQty();
                    $productQty[$key]['loc'] = $prod->getEccLocationCode();
                }
            }
        }
        //insert all data
        $nonFaTrack = [];
        if (isset($data['saveNewCartToList']) || isset($data['saveCartToExistingList']) || isset($data['saveCartToAdvancedList'])) {
            foreach ($productQty as $key => $listProd) {
                //if list type is not Fa set qty to 0
                $listProd['qty'] = $list->getType() == 'Fa' ? $listProd['qty'] : 0;
                $listProd['loc'] = $list->getType() == 'Fa' ? (isset($listProd['loc']) ? $listProd['loc'] : null) : null;
                //if product already exists, update it
                if (array_key_exists(preg_replace('~(?>&nbsp;)+$~', '', $listProd['sku'] . '_' . $listProd['loc']), $existingListProductSkus)) {
                    $listProduct = $existingListProductSkus[preg_replace('~(?>&nbsp;)+$~', '', $listProd['sku'] . '_' . $listProd['loc'])];
                    $listProduct->setQty($listProd['qty']);
                    $listProduct->setLocationCode($listProd['loc'] == '&nbsp;' ? null : $listProd['loc']);
                    $listProduct->save();
                } else {
                    if ($list->getType() == 'Fa' || !in_array($listProd['sku'], $nonFaTrack)) {
                        //if product doesn't already exist, create it
                        $newListProduct = $this->listProduct;
                        $newListProduct->setSku($listProd['sku']);
                        $newListProduct->setListId($list->getId());
                        $newListProduct->setLocationCode($listProd['loc'] == '&nbsp;' ? null : $listProd['loc']);
                        $newListProduct->setQty($listProd['qty']);
                        $newListProduct->save();
                        $newListProduct->unsetData();
                    }

                }
                $nonFaTrack[] = $listProd['sku'];

            }
        } else {
            foreach ($productQty as $key => $listProd) {
                //if list type is not Fa set qty to 0
                $listProd['qty'] = $list->getType() == 'Fa' ? $listProd['qty'] : 0;
                $listProd['loc'] = $list->getType() == 'Fa' ? (isset($listProd['loc']) ? $listProd['loc'] : null) : null;
                //if product already exists, update it
                if (array_key_exists(preg_replace('~(?>&nbsp;)+$~', '', $listProd['sku'] . '_' . $listProd['loc']), $existingListProductSkus)) {

                    $listProduct = $existingListProductSkus[preg_replace('~(?>&nbsp;)+$~', '', $listProd['sku'] . '_' . $listProd['loc'])];
                    $listProduct->setQty($listProd['qty']);
                    $listProduct->setLocationCode($listProd['loc'] == '&nbsp;' ? null : $listProd['loc']);
                    $listProduct->save();
                }
                $nonFaTrack[] = $listProd['sku'];
            }
        }

    }

    /*
    * Save products when list crated as part of saveCartToList advanced
    */
    protected function saveNewCartToListProducts($list, $saveNewCartToListArray, $data)
    {
        if (isset($saveNewCartToListArray['listCode'])) {
            unset($saveNewCartToListArray['listCode']);
        }
        $saveNewCartToListArray = $this->getItems($saveNewCartToListArray);
        $productsArray = [];
        foreach ($saveNewCartToListArray as $key => $val) {
            if (is_array($val)) {
                $productsArray[$key] = ['sku' => $val['sku'], 'val' => $val['qty'], 'uom' => $val['uom'], 'location_code' => $val['location_code']];
            } else {
                $productsArray[$key] = ['sku' => $val['sku'], 'val' => $val, 'uom' => null, 'location_code' => null];
            }
        }
        $data['productsQty'] = json_encode($productsArray);
        return $data;
    }

    /*
    * Save products when list crated as part of add to lists button
    */
    protected function proccessProductsQtytoLists($data, $list)
    {
        if (isset($data['productsQty']) && isset($data['product_info'])) {
            $decodedProductsQty = json_decode($data['productsQty'], true);
            foreach ($decodedProductsQty as $prodQty) {
                $newListProduct = $this->listProduct;
                $newListProduct->setSku($prodQty['sku']);
                $newListProduct->setListId($list->getId());
                $newListProduct->setLocationCode($prodQty['location_code'] == '&nbsp;' ? null : $prodQty['location_code']);
                $newListProduct->setQty($prodQty['val']);
                $newListProduct->save();
                $newListProduct->unsetData();
            }
        }
    }

    /*
    * Save products when list crated as part of add to lists button
    */
    protected function proccessProducttoLists($data, $list)
    {
        $product_info = json_decode(base64_decode(strtr($this->getRequest()->getParam('product_info'), '-_', '+/')), true);
        $productsArray = [];
        foreach ($product_info as $key => $val) {
            if (is_array($val)) {
                $productsArray[$key] = ['sku' => $val['sku'], 'val' => $val['qty'], 'uom' => $val['uom'], 'location_code' => $val['location_code']];
            } else {
                $productsArray[$key] = ['sku' => $val['sku'], 'val' => $val, 'uom' => null, 'location_code' => null];
            }
        }
        foreach ($productsArray as $listProd) {
            $newListProduct = $this->listProduct;
            $newListProduct->setSku($listProd['sku']);
            $newListProduct->setListId($list->getId());
            $newListProduct->setLocationCode($listProd['location_code'] == '&nbsp;' ? null : $listProd['location_code']);
            $newListProduct->setQty($listProd['val']);
            $newListProduct->save();
            $newListProduct->unsetData();
        }
        $data['productsQty'] = json_encode($productsArray);
        return $data;
    }


    /**
     * get Items From Request
     *
     * @param array $request
     * @param array $data
     *
     * @return void
     */
    public function getItems($request)
    {
        $items = [];
        $cartItemsQty = [];
        if (isset($request['order'])) {
            $order = $this->orderFactory->create()->load($request['order']);
            $items = $order->getItems();
        } else if (isset($request['quote'])) {
            $cartHelper = $this->checkoutCartHelper;
            $items = $cartHelper->getCart()->getItems();
        } else if (is_array($request)) {
            $cartItemsQty = $request;
        }
        foreach ($items as $key => $item) {
            if ($item->getQtyOrdered()) {
                $item->setQty($item->getQtyOrdered());
            }
            $cartItemsQty[$key] = ['sku' => $item->getSku(), 'qty' => $item->getQty(), 'uom' => $item->getUom(), 'location_code' => $item->getEccLocationCode()];
        }
        return $cartItemsQty;
    }

    /**
     * Loads List
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    protected function loadEntity()
    {
        $id = $this->getRequest()->getParam('id', null);
        $list = $this->listsListModelFactory->create()->load($id);
        return $list;
    }


}
