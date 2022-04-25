<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper;


use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Generic grid helper
 * 
 * used for processing rows displayed by the generic grid for the various message types
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Returns extends \Epicor\Comm\Helper\Messaging
{

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModelFactory
     */
    protected $commCustomerReturnModelFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\CollectionFactory
     */
    protected $commResourceCustomerReturnModelCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModel\LineFactory
     */
    protected $commCustomerReturnLineFactory;

    /*
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;


    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    protected $customerSession;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    public function __construct(
        \Epicor\Comm\Helper\Messaging\Context $context,
        \Magento\Framework\View\LayoutInterface $layout,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\CollectionFactory $commResourceCustomerReturnModelCollectionFactory,
        \Epicor\Comm\Model\Customer\ReturnModel\LineFactory $commCustomerReturnLineFactory,
        \Epicor\Common\Helper\File $commonFileHelper,
        CustomerFactory $customerFactory = null,
        StoreManagerInterface $storeManager = null
    ) {
        $this->layout = $layout;
        $this->commCustomerReturnModelFactory = $commCustomerReturnModelFactory;
        $this->commResourceCustomerReturnModelCollectionFactory = $commResourceCustomerReturnModelCollectionFactory;
        $this->salesOrderFactory = $context->getOrderFactory();
        $this->commCustomerReturnLineFactory = $commCustomerReturnLineFactory;
        $this->commonFileHelper = $commonFileHelper;
        parent::__construct($context);
        $this->customerFactory = $customerFactory ?: ObjectManager::getInstance()->get(CustomerFactory::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }
    /**
     * Encode return object
     * 
     * 
     * @param \Epicor\Comm\Model\Customer\ReturnModel $return
     * @return string
     */
    public function encodeReturn($return)
    {
        return base64_encode(serialize($return));
    }

    /**
     * Decode encoded return object
     * 
     * @param string $str
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function decodeReturn($str)
    {
        return unserialize(base64_decode($str));
    }

    /**
     * get next returns step
     * 
     * @param string $step
     * @return array
     */
    public function getNextReturnsStep($step)
    {
        $block = $this->layout->createBlock('\Epicor\Comm\Block\Customer\Returns');
        /* @var $block Epicor_Comm_Block_Customer_Returns */
        $steps = $block->getSteps();

        $nextkey = false;
        $result = array();

        $currentStep = isset($steps[$step]) ? $steps[$step] : '';

        if (!empty($currentStep)) {
            $result['refresh_section'] = array(
                'name' => $step,
                'html' => $this->layout->createBlock($currentStep['block'])->toHtml()
            );

            if (isset($currentStep['remove_section'])) {
                $enabled = $this->checkConfigFlag('return_' . $currentStep['remove_section']);
                if (!$enabled) {
                    $result['remove_section'] = $currentStep['remove_section'];
                }
            }
        }

        foreach ($steps as $key => $stepData) {
            if ($nextkey == false) {
                if ($key == $step) {
                    $nextkey = true;
                }
                continue;
            }

            if ($key == 'attachments') {
                $enabled = $this->checkConfigFlag('return_attachments');
                if (!$enabled) {
                    continue;
                }
            }

            $result['goto_section'] = $key;
            $result['update_section'] = array(
                'name' => $key,
                'html' => $this->layout->createBlock($stepData['block'])->toHtml()
            );

            break;
        }

        return $result;
    }

    /**
     * Finds a Case by it's case number (sends a CCMS)
     * 
     * reutrns whether it's a valid case number, and whether there is a return for it or not
     * 
     * @param string $caseNumber
     * 
     * @return array
     */
    public function findCase($caseNumber)
    {
        $validCase = false;
        $return = false;

        $searches = array(
            'case_number' => array(
                'EQ' => $caseNumber
            )
        );

        $search = $this->sendErpMessage('epicor_comm', 'ccms', array(), $searches);
        $erpReturn = '';

        if ($search['success']) {
            $message = $search['message'];
            $results = $message->getResults();
            if (!empty($results)) {
                $returnData = array_pop($results);
                $validCase = true;
                $erpReturn = $returnData->getErpReturnsNumber();
            }
        }

        return array(
            'valid' => $validCase,
            'erp_return_number' => $erpReturn
        );
    }

    /**
     * Loads an ERP return by CRRD
     * 
     * @param string $erpReturnsNumber
     * @param int $erpAccountId
     * @param boolean $saveReturn
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function loadErpReturn($erpReturnsNumber, $erpAccountId = null, $saveReturn = false)
    {
        $data = array(
            'erp_returns_number' => $erpReturnsNumber,
            //M1 > M2 Translation Begin (Rule p2-6.4)
            //'language_code' => $this->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()),
            'language_code' => $this->getLanguageMapping($this->scopeConfig->getValue(
                        'general/locale/code',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $this->storeManager->getStore()->getStoreId()
                    )
                ),
            //M1 > M2 Translation End
            'account_number' => $this->getErpAccountNumber($erpAccountId),
        );

        $return = $this->commCustomerReturnModelFactory->create()->load($erpReturnsNumber, 'erp_returns_number');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        if (!$return->getId() || $return->getErpSyncAction() == '') {
            $erpMessage = $this->sendErpMessage('epicor_comm', 'crrd', $data);

            $return = false;

            if ($erpMessage['success']) {
                $message = $erpMessage['message'];
                $return = $message->getReturn();

                if ($saveReturn) {
                    $return->save();
                }
            }
        }

        return $return;
    }

    /**
     * Finds a return by type
     * 
     * @param string $findType
     * @param string $findValue
     * @param boolean $createOnFind
     * @param boolean $sendMessage
     * @param \Epicor\Comm\Model\Customer $customer
     * 
     * @return array
     */
    public function findReturn($findType, $findValue, $createOnFind = false, $sendMessage = true, $customer = null)
    {
        $errors = array();
        $found = false;
        $source = 'local';

        switch ($findType) {
            case 'return':
                $key = 'erp_returns_number';
                break;
            case 'customer_ref':
                $key = 'customer_reference';
                break;
            case 'case_no':
                $key = 'rma_case_number';
                break;
        }

        $searches = array(
            $key => array(
                'EQ' => $findValue
            )
        );

        $returnSearch = $this->searchLocalReturns($searches, $customer, true);
        $return = $returnSearch->getFirstItem();
        $userType = $this->getReturnUserType();

        if ($return->isObjectNew()) {
            if ($sendMessage && $userType == 'b2b') {

                $session = $this->customerSessionFactory->create();
                /* @var $session Mage_Customer_Model_Session */

                if (!$session->isLoggedIn()) {
                    $searches['email_address'] = array(
                        'EQ' => $session->getReturnGuestEmail()
                    );
                    $searches['customer_name'] = array(
                        'EQ' => $session->getReturnGuestName()
                    );
                }

                $returns = $this->searchErpReturns($searches);

                if (!empty($returns)) {
                    $returnData = array_pop($returns);

                    if (!$returnData->getErpReturnsNumber()) {
                        $errors[] = __('No return can be found with the specified data');
                    } else {

                        $source = 'message';
                        $found = true;

                        if ($createOnFind) {
                            if (is_null($customer)) {
                                $customer = $session->getCustomer();
                            }

                            $commHelper = $this;
                            /* @var $commHelper Epicor_Comm_Helper_Data */
                            $erpAccount = $commHelper->getErpAccountInfo();
                            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

                            $return = $this->loadErpReturn(
                                $returnData->getErpReturnsNumber(), $erpAccount->getId()
                            );

                            if (!$return->canBeAccessedByCustomer()) {
                                $found = false;
                                $errors[] = __('No return can be found with the specified data');
                            } else {
                                $return->save();
                            }
                        } else {
                            $return = $this->commCustomerReturnModelFactory->create();
                            /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
                            $return->setErpReturnsNumber($returnData->getErpReturnsNumber());
                            $return->setWebReturnsNumber($returnData->getWebReturnsNumber());
                            $return->setRmaDate($returnData->getRmaDate());
                            $return->setReturnsStatus($returnData->getReturnsStatus());
                            $return->setCustomerReference($returnData->getCustomerReference());
                            $return->setAddressCode($returnData->getCustomerCode());
                            $return->setCustomerName($returnData->getCustomerName());
                            $return->setCreditInvoiceNumber($returnData->getCreditInvoiceNumber());
                            $return->setRmaCaseNumber($returnData->getRmaCaseNumber());
                            $return->setRmaContact($returnData->getRmaContact());
                        }
                    }
                }
            }
        } else {
            $found = true;
        }

        if (!$found) {
            $errors[] = __('No return can be found with the specified data');
        }

        return array(
            'found' => $found,
            'return' => $return,
            'errors' => $errors,
            'source' => $source
        );
    }

    /**
     * Searches Local DB for returns matching conditions
     * 
     * @param array $searches - search conditions
     * @param \Epicor\Comm\Model\Customer $customer
     * @param boolean $byGuest
     * 
     * 
     * @return \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Collection
     * @return \Epicor\Comm\Model\ResourceModel\Customer\Return\Collection
     */
    public function searchLocalReturns($searches, $customer = null, $byGuest = false)
    {
        $returnSearch = $this->commResourceCustomerReturnModelCollectionFactory->create();
        $returnSearch = $this->commResourceCustomerReturnModelCollectionFactory->create();
        /* @var $returnSearch Epicor_Comm_Model_Resource_Customer_ReturnModel_Collection */

        foreach ($searches as $key => $conditions) {
            foreach ($conditions as $condition => $value) {
                if ($condition == 'EQ') {
                    $returnSearch->addFieldToFilter($key, $value);
                }
            }
        }

        if ($customer === null) {
            $session = $this->customerSessionFactory->create();
            /* @var $session Mage_Customer_Model_Session */
            if ($session->isLoggedIn()) {
                $customer = $session->getCustomer();
                /* @var $customer Epicor_Comm_Model_Customer */
                $returnSearch->filterByCustomer($customer);
            } else {
                if ($byGuest) {
                    $returnSearch->filterByGuest($session->getReturnGuestName(), $session->getReturnGuestEmail());
                }
            }
        } else if ($customer !== false) {
            if (is_array($customer)) {
                if ($byGuest) {
                    $returnSearch->filterByGuest($customer['name'], $customer['email']);
                }
            } else {
                $returnSearch->filterByCustomer($customer);
            }
        }

        return $returnSearch;
    }

    /**
     * Sends a CRRS to find erp returns
     * 
     * @param array $searches
     * 
     * @return array
     */
    public function searchErpReturns($searches)
    {
        $returns = array();

        $search = $this->sendErpMessage('epicor_comm', 'crrs', array(), $searches);

        if ($search['success']) {
            $message = $search['message'];
            $returns = $message->getResults();
        }

        return $returns;
    }

    public function findLocalOrder($orderNum)
    {
        $order = $this->salesOrderFactory->create()->load($orderNum, 'ecc_erp_order_number');

        if ($order->isObjectNew()) {
            $order = $this->salesOrderFactory->create()->load($orderNum, 'increment_id');
        }

        if (!$order->isObjectNew()) {

            $userType = $this->getReturnUserType();

            $session = $this->customerSessionFactory->create();
            /* @var $session Mage_Customer_Model_Session */

            if ($userType == 'guests') {
                $email = $session->getReturnGuestEmail();
            } else {
                $email = $session->getCustomer()->getEmail();
            }

            $orderEmail = $order->getCustomerEmail();

            if (strtolower($email) != strtolower($orderEmail)) {
                $order = false;
            }
        }

        return $order;
    }

    /**
     * Finds products by the relevant message type
     * 
     * Types:
     * 
     * order - Checks local orders, then sends CUOD
     * invoice - Sends CUID
     * shipment - Sends CUSS to the the order number, then CUSD
     * serial_number - Sends CSNS
     * 
     * @param string $findType - type to search for
     * @param string $findValue - value to search for
     * @param array $extraData - any extra data for the search, can bypass CUSS if order_number provided
     * 
     * @return array - array with 2 keys "products" - products found & "errors" - any errors in searching
     */
    public function findProductsByMessage($findType, $findValue, $extraData = array())
    {
        $products = array();
        $errors = array();
        $found = false;

        $data = array(
            $findType . '_number' => $findValue
        );

        $messageTypes = array();

        if ($findType == 'order') {
            $messageTypes[] = array(
                'type' => 'cuod',
                'data' => $data
            );
        } else if ($findType == 'invoice') {
            if (isset($extraData['type'])) {
                $data['type'] = $extraData['type'];
            }
            $messageTypes[] = array(
                'type' => 'cuid',
                'data' => $data
            );
        } else if ($findType == 'shipment') {
            if (!isset($extraData['order_number'])) {

                $search = $this->sendErpMessage(
                    'customerconnect', 'cuss', array(), array('packingSlip' => array(
                        'EQ' => $findValue,
                    ))
                );

                if ($search['success']) {
                    $message = $search['message'];
                    $results = $message->getResults();

                    if (!empty($results)) {
                        foreach ($results as $result) {
                            $messageTypes[] = array(
                                'type' => 'cusd',
                                'data' => array(
                                    'shipment_number' => $findValue,
                                    'order_number' => $result->getOrderNumber(),
                                )
                            );
                        }
                    }
                }
            } else {
                $messageTypes[] = array(
                    'type' => 'cusd',
                    'data' => array(
                        'shipment_number' => $findValue,
                        'order_number' => $extraData['order_number'],
                    )
                );
            }
        } else if ($findType == 'serial') {
            $searches = array(
                'serial_number' => array(
                    'EQ' => $findValue
                ),
            );

            $userType = $this->getReturnUserType();
            $session = $this->customerSessionFactory->create();
            /* @var $session Mage_Customer_Model_Session */

            if ($userType == 'guests') {
                $email = $session->getReturnGuestEmail();
            } else {
                $email = $session->getCustomer()->getEmail();
            }

            if ($userType != 'b2b') {
                $searches['ship_to_email'] = array(
                    'EQ' => $email
                );
            }

            $messageTypes['csns'] = array(
                'type' => 'csns',
                'base' => 'epicor_comm',
                'data' => array(),
                'searches' => $searches,
                'source' => '',
                'lines_ref' => 'products',
                'line_ref' => 'product',
            );
        }

        if (!empty($messageTypes)) {
            foreach ($messageTypes as $info) {
                $base = isset($info['base']) ? $info['base'] : 'customerconnect';
                $msgData = isset($info['data']) ? $info['data'] : $data;
                $searchData = isset($info['searches']) ? $info['searches'] : array();
                $dataSource = isset($info['source']) ? $info['source'] : $findType;
                $linesRef = isset($info['lines_ref']) ? $info['lines_ref'] : 'lines';
                $lineRef = isset($info['line_ref']) ? $info['line_ref'] : 'line';

                $search = $this->sendErpMessage($base, $info['type'], $msgData, $searchData);

                if ($search['success']) {
                    $message = $search['message'];

                    $response = $message->getResponse();
                    $source = !empty($dataSource) ? $response->getData($dataSource) : $response;

                    if ($source) {
                        $getLines = 'get' . ucfirst($linesRef);
                        $getLine = 'getasarray' . ucfirst($lineRef);
                        $linesGroup = $source->$getLines();
                        $lines = ($linesGroup) ? $linesGroup->$getLine() : array();

                        $products = $this->_buildProductsArrayFromMessage($findType, $findValue, $lines, $products);
                        if (!empty($products)) {
                            $found = true;
                        }
                    }
                }
            }
        }

        if (!$found) {
            //M1 > M2 Translation Begin (Rule 55)
            //$errors[] = $this->__('Could not find valid %s Number', ucfirst($findType));
            $errors[] = __('Could not find valid %1 Number', ucfirst($findType));
            //M1 > M2 Translation End
        }

        return array('products' => $products, 'errors' => $errors);
    }

    private function _buildProductsArrayFromMessage($sourceType, $sourceValue, $lines, $products = array())
    {
        $sourceRef = array();
        foreach ($lines as $x => $line) {

            $qty = $line->getQuantity() ?: $line->getQuantities();

            $sourceData = array(
                $sourceType . '_number' => $sourceValue,
            );

            $sourceData['order_number'] = !isset($sourceData['order_number']) ? $line->getOrderNumber() : $sourceData['order_number'];
            $sourceData['invoice_number'] = !isset($sourceData['invoice_number']) ? $line->getInvoiceNumber() : $sourceData['invoice_number'];

            $sourceData['order_line'] = $line->getOrderLine();
            $sourceData['order_release'] = $line->getOrderRelease();
            $sourceData['invoice_line'] = $line->getInvoiceLine();

            $num = $x;
            if ($line->getData('_attributes')) {
                $num = $line->getData('_attributes')->getNum();
                $sourceData[$sourceType . '_line'] = $num;
            }

            $product = array(
                'sku' => $line->getProductCode(),
                'uom' => $line->getUnitOfMeasureCode() ?: $line->getUnitOfMeasureDescription(),
                'qty_returned' => $qty ? $qty->getDelivered() * 1 : 1,
                'qty_ordered' => $qty ? $qty->getOrdered() * 1 : 1,
                'source' => $sourceType,
                'source_label' => ucfirst($sourceType) . ' #' . $sourceValue,
                'source_data' => $this->encodeReturn($sourceData),
                'source_data_raw' => $sourceData,
            );

            if ($this->getReturnUserType() != 'b2b' && $sourceType == 'order') {
                $order = !isset($sourceRef[$sourceValue]) ? $this->findLocalOrder($sourceValue) : $sourceRef[$sourceValue];
                if ($order && !$order->getIsObjectNew()) {
                    $product['source_label'] = ucfirst($sourceType) . ' #' . $order->getIncrementId();
                }
                $sourceRef[$sourceValue] = $order;
            }

            $existingIndex = $this->_isProductBeingAdded($product, $products);

            if ($existingIndex === false) {
                $products[] = $product;
            } else {
                $product['qty_returned'] += $products[$existingIndex]['qty_returned'];
                $products[$existingIndex] = $product;
            }
        }
        return $products;
    }

    private function _isProductBeingAdded($productToAdd, $products)
    {
        $productX = false;

        foreach ($products as $x => $product) {
            if ($productToAdd['sku'] == $product['sku'] && $productToAdd['uom'] == $product['uom'] && $productToAdd['source_data_raw']['order_number'] == $product['source_data_raw']['order_number'] && $productToAdd['source_data_raw']['order_line'] == $product['source_data_raw']['order_line']
            ) {
                $productX = $x;
                break;
            }
        }

        return $productX;
    }

    /**
     * Processes posted lines
     * 
     * 
     * @param \Epicor\Comm\Model\Customer\ReturnModel $return
     * @param array $linesPost
     * 
     */
    public function processPostedLines(&$return, $linesPost)
    {
        $lines = array();
        $lines = array();
        $lineNotesRequired = $this->scopeConfig->getValue('epicor_comm_returns/notes/line_notes_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $tabLength = $this->scopeConfig->getValue('epicor_comm_returns/notes/line_notes_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($linesPost) {
            foreach ($linesPost as $ref => $lineData) {
            if (isset($lineData['old_data'])) {
                $old = unserialize(base64_decode($lineData['old_data']));
                $line = $return->getLine($old['id']);
            } else {
                $line = $this->commCustomerReturnLineFactory->create();
                /* @var $line Epicor_Comm_Model_Customer_ReturnModel_Line */

                $line->setProductCode($lineData['sku']);
                $line->setUnitOfMeasureCode($lineData['uom']);
                $line->setQtyOrdered($lineData['quantity_ordered']);
                $line->setReturnId($return->getId());
                $line->setActions('All');

                if (!empty($lineData['source_data'])) {
                    $sourceData = unserialize(base64_decode($lineData['source_data']));
                }

                if (isset($sourceData['order_number'])) {
                    $line->setOrderNumber($sourceData['order_number']);
                }

                if (isset($sourceData['order_line'])) {
                    $line->setOrderLine($sourceData['order_line']);
                }

                if (isset($sourceData['order_release'])) {
                    $line->setOrderRelease($sourceData['order_release']);
                }

                if (isset($sourceData['invoice_number'])) {
                    $line->setInvoiceNumber($sourceData['invoice_number']);
                }

                if (isset($sourceData['invoice_line'])) {
                    $line->setInvoiceLine($sourceData['invoice_line']);
                }

                if (isset($sourceData['shipment_number'])) {
                    $line->setShipmentNumber($sourceData['shipment_number']);
                }

                if (isset($sourceData['serial_number'])) {
                    $line->setSerialNumber($sourceData['serial_number']);
                }
            }

            if ($line) {
                if (isset($lineData['delete'])) {
                    $return->deleteLine($line->getId());
                } else {
                    if (isset($lineData['quantity_returned'])) {
                        $line->setQtyReturned($lineData['quantity_returned']);
                    }

                    if (isset($lineData['return_code'])) {
                        $line->setReasonCode($lineData['return_code']);
                    }

                    if (isset($lineData['note_text'])) {
                    if (isset($lineData['note_text'])) {
                        if ($lineNotesRequired && $tabLength) {
                            if ($tabLength < strlen($lineData['note_text'])) {
                                $lines['length_incorrect'] = "The line note length is greater than the {$tabLength} characters allowed in config";
                            } else {
                                $line->setNoteText($lineData['note_text']);
                            }
                        } else {
                            $line->setNoteText($lineData['note_text']);
                        }
                    }
                    }
                    //don't save if line text is wrong
                    if (!isset($lines['length_incorrect'])) {
                        $line->setToBeDeleted('N');
                        $line->save();
                        $return->addLine($line);
                        $lines[$ref] = $line->getId();
                    }
                }
            }
        }
    }
        $return->save();
        $return->reloadChildren();
        return $lines;
    }

    /**
     * 
     * 
     * @param \Epicor\Comm\Model\Customer\ReturnModel $return
     * @param array $attachmentsPost
     * @param string $key
     * 
     */
    public function processPostedAttachments($return, $attachmentsPost, $key, $lines = array())
    {
        $fileHelper = $this->commonFileHelper;
        /* @var $fileHelper Epicor_Common_Helper_File */

        $data = array($key => $attachmentsPost);

        $files = $fileHelper->processPageFiles($key, $data);

        foreach ($files as $fileData) {
            if (isset($fileData['old_data']) && !empty($fileData['old_data'])) {
                $oldData = unserialize(base64_decode($fileData['old_data']));
                if (isset($fileData['delete'])) {
                    if (!empty($oldData['line_id'])) {
                        $line = $return->getLine($oldData['line_id']);
                        $line->deleteAttachment($oldData['attachment_id']);
                        $return->addLine($line);
                    } else {
                        $return->deleteAttachment($oldData['attachment_id']);
                    }
                } else {
                    if (!empty($oldData['line_id'])) {
                        $line = $return->getLine($oldData['line_id']);
                        $line->addAttachment($fileData['file_model']);
                        $return->addLine($line);
                    } else {
                        $return->addAttachment($fileData['file_model']);
                    }
                }
            } else {
                if (isset($fileData['line_number'])) {
                    if (isset($lines[$fileData['line_number']])) {
                        $line = $return->getLine($lines[$fileData['line_number']]);
                        $line->addAttachment($fileData['file_model']);
                        $return->addLine($line);
                    }
                } else {
                    $return->addAttachment($fileData['file_model']);
                }
            }
        }

        $return->save();
        $return->reloadChildren();
    }

    /**
     * Creates a return from a document
     * 
     * @param string $type - type of document being created from
     * @param string $data - encoded date for the document
     */
    public function createReturnFromDocument($type, $data)
    {
        $type = $this->decodeReturn($type);
        $data = $this->decodeReturn($data);

        $return = $this->commCustomerReturnModelFactory->create();
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        // set customer info here
        $customer = $this->customerSessionFactory->create()->getCustomer();

        $commHelper = $this;
        $erpAccount = $commHelper->getErpAccountInfo();
        $erpAccountId = $erpAccount->getId();

        $session = $this->customerSessionFactory->create();
        if (!$session->isLoggedIn()) {
            $session->unsReturnGuestName();
            $session->unsReturnGuestEmail();
            $session->unsReturnAccountNumber();
        }

        if ($customer->getId() > 0) {
            $customerName = $customer->getName();
            $customerContactCode = $customer->getContactCode();
            $customerId = $customer->getId();
            $customerEmail = $customer->getEmail();
            $shipTo = $customer->getDefaultShippingAddress();
            $addressCode = $shipTo->getEccErpAddressCode();
        } else {
            $order = $this->salesOrderFactory->create()->load($data['order_number'], 'ecc_erp_order_number');
            $customerEmail = $order->getCustomerEmail();
            $customerObject = $this->customerFactory->create();
            $customerObject->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
            $customerModel = $customerObject->loadByEmail($customerEmail);
            if ($customerModel->getId() > 0) {
                $customer = $customerModel;
                $customerName = $customer->getName();
                $customerContactCode = $customer->getContactCode();
                $customerId = $customer->getId();
                $shipTo = $customer->getDefaultShippingAddress();
                $addressCode = $shipTo->getEccErpAddressCode();
                $erpAccountId = $customer->getEccErpaccountId();
                $erpAccountNumber = $commHelper->getErpAccountInfo($erpAccountId)->getAccountNumber();
                $session->setReturnAccountNumber($erpAccountNumber);
            } else {
                $firstname = $order->getCustomerFirstname();
                $midname = $order->getCustomerMiddlename();
                $lastname = $order->getCustomerLastname();
                $prefix = $order->getCustomerPrefix();
                $suffix = $order->getCustomerSuffix();
                $customerName = '';
                if ($prefix) {
                    $customerName = $customerName . $prefix . ' ';
                }
                if ($firstname) {
                    $customerName = $customerName . $firstname;
                }
                if ($midname) {
                    $customerName = $customerName . ' ' . $midname;
                }
                if ($lastname) {
                    $customerName = $customerName . ' ' . $lastname;
                }
                if ($suffix) {
                    $customerName = $customerName . ' ' . $suffix;
                }
                $customerContactCode = '';
                $customerId = '';
                $addressCode = null;
            }

            $session->setReturnGuestName($customerName);
            $session->setReturnGuestEmail($customerEmail);

        }

        $return->setErpAccountId($erpAccountId);
        $return->setIsGlobal(0);
        $return->setCustomerName($customerName);
        $return->setRmaContact($customerContactCode);
        $return->setCustomerId($customerId);
        $return->setEmailAddress($customerEmail);
        $return->setActions('All');
        //M1 > M2 Translation Begin (Rule 25)
        //$return->setRmaDate(now());
        $return->setRmaDate(date('Y-m-d H:i:s'));
        //M1 > M2 Translation End
        $return->setStoreId($this->storeManager->getStore()->getId());

        $return->setAddressCode($addressCode);

        switch ($type) {
            case 'order':
                $identifier = $data['order_number'];
                break;
            case'shipment':
                $identifier = $data['packing_slip'];
                break;
            case 'invoice':
                $identifier = $data['invoice_number'];
                break;
        }

        $lines = $this->findProductsByMessage($type, $identifier, $data);

        if (!empty($lines['products'])) {
            foreach ($lines['products'] as $lineData) {


                $line = $this->commCustomerReturnLineFactory->create();
                /* @var $line Epicor_Comm_Model_Customer_ReturnModel_Line */

                $line->setProductCode($lineData['sku']);
                $line->setUnitOfMeasureCode($lineData['uom']);
                $line->setQtyOrdered($lineData['qty_ordered']);
                $line->setQtyReturned($lineData['qty_returned']);
                $line->setActions('All');

                $sourceData = $this->decodeReturn($lineData['source_data']);

                switch ($type) {
                    case 'order':
                        $line->setOrderNumber($sourceData['order_number']);
                        $line->setOrderLine($sourceData['order_line']);
                        $line->setOrderRelease($sourceData['order_release']);
                        break;
                    case'shipment':
                        $line->setShipmentNumber($sourceData['shipment_number']);
                        $line->setOrderNumber($sourceData['order_number']);
                        $line->setOrderLine($sourceData['order_line']);
                        $line->setInvoiceNumber($sourceData['invoice_number']);
                        $line->setInvoiceLine($sourceData['invoice_line']);
                        break;
                    case 'invoice':
                        $line->setInvoiceNumber($sourceData['invoice_number']);
                        $line->setInvoiceLine($sourceData['invoice_line']);
                        break;
                }

                $return->addLine($line);
            }

            $return->save();
        }

        return $return;
    }

    /**
     * Returns an invoice return url
     *
     * @param \Epicor\Comm\Model\Xmlvarien $invoice
     * 
     * @return string 
     */
    public function getInvoiceReturnUrl($invoice)
    {
        $url = $this->getCreateReturnUrl(
            'invoice', array(
            'invoice_number' => $invoice->getInvoiceNumber(),
            'type' => $invoice->get_attributesType() ?: '',
            )
        );

        return $url;
    }

    /**
     * Returns a shipment return url
     *
     * @param \Epicor\Comm\Model\Xmlvarien $shipment
     * 
     * @return string 
     */
    public function getShipmentReturnUrl($shipment)
    {
        $url = $this->getCreateReturnUrl(
            'shipment', array(
            'packing_slip' => $shipment->getPackingSlip(),
            'order_number' => $shipment->getOrderNumber()
            )
        );

        return $url;
    }

    /**
     * Returns an order return url
     *
     * @param \Epicor\Comm\Model\Xmlvarien $order
     * 
     * @return string 
     */
    public function getOrderReturnUrl($order)
    {
        $url = $this->getCreateReturnUrl(
            'order', array(
            'order_number' => $order->getOrderNumber()
            )
        );

        return $url;
    }

    /**
     * Returns an order reorder URL fro the invoice object provided,
     *
     * Also optional to change the return url
     * 
     * @param \Epicor\Comm\Model\Xmlvarien $orderObj
     * @param string $return
     * @return type
     */
    public function getCreateReturnUrl($type, $data)
    {
        $params = array(
            'type' => base64_encode(serialize($type)),
            'data' => base64_encode(serialize($data)),
            'return' => $this->urlEncoder->encode($this->_urlBuilder->getCurrentUrl())
        );


       // return Mage::getUrl('epicor_comm/Returns/createReturnFromDocument', $params);
        //M1 > M2 Translation Begin (Rule p2-4)
        //return Mage::getUrl('epicor_comm/returns/createReturnFromDocument', $params);
        return $this->_getUrl('epicor_comm/returns/createReturnFromDocument', $params);
        //M1 > M2 Translation End
    }

    /**
     * returns the current user type to determin what retirn config to listen to 
     * 
     * @return string
     */
    public function getReturnUserType()
    {
        $type = '';
        $session = $this->customerSessionFactory->create();

        if ($session->isLoggedIn()) {
            $customer = $session->getCustomer();
            /* @var $customer \Epicor\Comm\Model\Customer */
            if ($customer->isGuest()) {
                $type = 'b2c';
            } else if ($customer->isCustomer()) {
                $type = 'b2b';
            } else if ($customer->isSupplier()) {
                $type = 'supplier';
            }
        } else {
            $name = $session->getReturnGuestName();
            $email = $session->getReturnGuestEmail();

            if (!empty($name) || !empty($email)) {
                $type = 'guests';
            }
        }

        return $type;
    }

    /**
     * Checks to see if returns are enabled 
     * 
     * @return boolean
     */
    public function isReturnsEnabled()
    {
        $type = $this->getReturnUserType();

        $enabled = false;

        if (empty($type)) {
            $guests = $this->checkConfigFlag('enabled', 'guests');
            $b2c = $this->checkConfigFlag('enabled', 'b2c');
            $b2b = $this->checkConfigFlag('enabled', 'b2b');

            if ($guests || $b2c || $b2b) {
                $enabled = true;
            }
        } else if ($type == 'supplier') {
            $enabled = false;
        } else {
            $enabled = $this->checkConfigFlag('enabled', $type);
        }

        return $enabled;
    }

    /**
     * Checks a return config flag
     * 
     * @return boolean
     */
    public function checkConfigFlag($path, $type = null, $store = null)
    {
        if (empty($type)) {
            $type = $this->getReturnUserType();
        }

        return $this->scopeConfig->isSetFlag('epicor_comm_returns/' . $type . '/' . $path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Checks a return config value is present
     * 
     * @return boolean
     */
    public function configHasValue($path, $value, $type = null)
    {
        if (empty($type)) {
            $type = $this->getReturnUserType();
        }

        $configValue = $this->scopeConfig->getValue('epicor_comm_returns/' . $type . '/' . $path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (strpos($configValue, ',') !== false) {
            $configValue = explode(',', $configValue);
        }

        $checkSuccess = false;

        if (is_array($value)) {
            foreach ($value as $v) {
                if (is_array($configValue)) {
                    $checkSuccess = in_array($v, $configValue);
                } else {
                    $checkSuccess = ($configValue == $v);
                }

                if ($checkSuccess) {
                    break;
                }
            }
        } else if (is_array($configValue)) {
            $checkSuccess = in_array($value, $configValue);
        } else {
            $checkSuccess = ($configValue == $value);
        }

        return $checkSuccess;
    }

    /**
     * Validates whether the find product search is valid
     * 
     * @param string $findType
     * @param string $findValue
     * 
     * @return array()
     */
    public function validateNewFindBy($findType, $findValue)
    {
        $errors = array();
        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        $linesData = array();
        if ($return) {
            $linesData = $return->getLines() ?: array();

            foreach ($linesData as $line) {
                /* @var $line Epicor_Comm_Model_Customer_ReturnModel_Line */
                $source = $line->getSourceType();
                if ($source == $findType) {
                    if ($line->getData($source . '_number') != $findValue) {
                        $errors[$source] = __(
                        //M1 > M2 Translation Begin (Rule 55)
                            //'You can only add further lines from %s %s', ucfirst($findType), $findValue
                            'You can only add further lines from %1 %2', ucfirst($findType), $findValue
                        //M1 > M2 Translation End
                        );
                    }
                } else {
                    $errors[$source] = $errors[$source] = __(
                    //M1 > M2 Translation Begin (Rule 55)
                        //'You cannot add lines of type %s to this return, because %s line(s) are already present', ucfirst($findType), ucfirst($source)
                        'You cannot add lines of type %1 to this return, because %2 line(s) are already present', ucfirst($findType), ucfirst($source)
                    //M1 > M2 Translation End
                    );
                }
            }
        }

        return $errors;
    }

    public function canAccessB2cReturns()
    {
        $session = $this->customerSessionFactory->create();
        /* @var $session Mage_Customer_Model_Session */

        $access = true;

        if ($session->isLoggedIn()) {
            $customer = $session->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */
            if ($customer->isCustomer()) {
                $access = false;
            }
        } else {
            $access = false;
        }

        return $access;
    }
	
	/**
     * @return bool
     */
    public function returnsEnabled()
    {
        if ($this->scopeConfig->getValue('epicor_comm_returns/guests/enabled')) {
            return true;
        }
        return false;
    }

    /**
     * @param $order
     * @return type
     */
    public function getOrderGuestReturnUrl($order)
    {
        $url = $this->getCreateReturnUrl(
            'order', array(
                'order_number' => $order->getEccErpOrderNumber()
            )
        );

        return $url;
    }
}
