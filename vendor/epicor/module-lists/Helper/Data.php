<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Helper;


class Data extends \Epicor\Comm\Helper\Data
{

    private $linkTypes = array(
        'B' => 'B2B',
        'C' => 'B2C',
        'E' => 'Chosen ERP',
        'N' => 'No Specific Link',
    );
    protected $_settings = array();
    protected $_noAccountsSupplied;
    protected $_exclude = array();
    protected $_accountLinkTypeFound;

    /**
     * @var \Epicor\Lists\Model\ListModel\TypeFactory
     */
    protected $listsListModelTypeFactory;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;
    
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;
 /**
     * @var \Epicor\Common\Helper\Locale\Format\Currency
     */
    protected $commonLocaleFormatCurrencyHelper;
 /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;
 /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
    protected $listsResourceListModelProductCollection;

    public function __construct(
        \Epicor\Lists\Helper\Context $context

    ) {
        $this->listsListModelTypeFactory = $context->getListsListModelTypeFactory();
        $this->commProductHelper = $context->getCommProductHelper();
        $this->resourceConnection = $context->getResourceConnection();
        $this->commonLocaleFormatCurrencyHelper = $context->getEccLocaleFormatCurrencyHelper(); 
        $this->catalogProductFactory = $context->getProductFactory();
        $this->commHelper = $context->getCommHelperForList();
        $this->listsResourceListModelProductCollection = $context->getListsListProductCollCsv();

        parent::__construct($context);
    }
    
    public function importListFromCsv($file)
    {
        $list = $this->listsListModelFactory->create();
        /* @var $list Epicor_Lists_Model_ListModel */
        $list->setType('Pg');


        $next = null;

        $fileContents = fopen($file, "rb");

        if (!$fileContents) {
            $errors[] = __('Could not process file properly, please try again.');
        }

        do {
            $row = fgets($fileContents);
            $data = explode(',', $row);

            $property = preg_replace('/\s+/', '', strtolower($data[0]));
            if (in_array($property, array('header', 'accounts', 'products', 'restrictions'))) {
                $next = $property;
            }
        } while (!feof($fileContents) && !$next);
        while ($next) {
            switch ($next) {
                case 'header':
                    $next = $this->_importListHeader($list, $fileContents, $errors);
                    break;
                case 'accounts':
                    $next = $this->_importListAccounts($list, $fileContents, $errors);
                    break;
                case 'products':
                    $next = $this->_importListProducts($list, $fileContents, $errors);
                    break;
                case 'restrictions':
                    $next = $this->_importListRestrictions($list, $fileContents, $errors);
                    break;
                case 'addresses':
                    $next = $this->_importListAddresses($list, $fileContents, $errors);
                    break;
                case 'countries':
                    $next = $this->_importListCountries($list, $fileContents, $errors);
                    break;
                case 'counties':
                    $next = $this->_importListCounties($list, $fileContents, $errors);
                    break;
                case 'postcodes':
                    $next = $this->_importListPostcodes($list, $fileContents, $errors);
                    break;
                default:
                    break 2;
            }
        }
        $this->setExcludeValue($list, isset($this->_exclude['accounts']) ? $this->_exclude['accounts'] : '', $errors, 'Accounts');
        $this->setExcludeValue($list, isset($this->_exclude['products']) ? $this->_exclude['products'] : '', $errors, 'Products');
        $this->checkLinkType($list, $errors);
        $list->setSettings($this->_settings);
        if (!$this->_accountLinkTypeFound) {
            $list->setErpAccountLinkType('N');
            $errors['warnings'][] = __('No ERP Account Link Type Specified. Defaulted to: No Specific Link');
        }
        fclose($fileContents);

        if (!$list->getErpCode() && !$list->getTitle()) {
            $errors['errors'][] = __('List Code and Title are required.');
        } else if (!$list->getErpCode()) {
            $errors['errors'][] = __('List Code is required.');
        } else if (!$list->getTitle()) {
            $errors['errors'][] = __('Title is required.');
        }

        if ($erpCode = $list->getErpCode()) {
            $duplicatedList = $this->listsListModelFactory->create()->load($erpCode, 'erp_code');
            if (!$duplicatedList->isObjectNew()) {
                //M1 > M2 Translation Begin (Rule 55)
                //$errors['errors'][] = $this->__('List Code %s already exists, change Code to create a new List.', $erpCode);
                $errors['errors'][] = __('List Code %1 already exists, change Code to create a new List.', $erpCode);
                //M1 > M2 Translation End
            }
        }

        $typeOptions = $this->listsListModelTypeFactory->create()->toOptionArray();
        foreach ($typeOptions as $data) {

            $typeData[] = $data['value'];
        }
        if (!in_array($list->getType(), $typeData)) {

            //M1 > M2 Translation Begin (Rule 55)
            //$errors['errors'][] = $this->__('Type "%s" does not exist, change Type to create a new List.', $list->getType());
            $errors['errors'][] = __('Type "%1" does not exist, change Type to create a new List.', $list->getType());
            //M1 > M2 Translation End
        } else if ($list->getType() == 'Co') {

            $errors['errors'][] = __('Contracts creation not allowed using CSV upload.');
        }

        if (!isset($errors['errors']) || count($errors['errors']) <= 0) {
            $list->save();
        }

        return array(
            'errors' => $errors,
            'list' => $list
        );
    }

    private function _importListHeader($list, &$fileContents, &$errors)
    {
        $settings = array();
        $next = null;

        do {
            $data = fgetcsv($fileContents);
            $property = preg_replace('/\s+/', '', strtolower($data[0]));
            $value = preg_replace("/\r|\n/", '', $data[1]);
            switch ($property) {
                case 'listcode':
                    $list->setErpCode($value);
                    $list->setErpAccountLinkType('E');
                    break;
                case 'title':
                    $list->setTitle($value);
                    $list->setLabel($value);
                    break;
                case 'type':
                    $typeVal = ucfirst(strtolower($value));
                    $list->setType($typeVal);
                    break;
                case 'active':
                    $value = preg_replace('/\s+/', '', strtoupper($data[1]));
                    if ($value == 'Y')
                        $list->setActive(1);
                    break;
                case 'notes':
                    $list->setNotes($value);
                    break;
                case 'accountlinktype':
                    $this->_accountLinkTypeFound = true;
                    if (!array_key_exists(strtoupper($value), $this->linkTypes)) {
                        //M1 > M2 Translation Begin (Rule 55)
                        //$errors['warnings'][] = $this->__('ERP Account Link Type "%s" does not exist. Defaulted to: No Specific Link', $value);
                        $errors['warnings'][] = __('ERP Account Link Type "%1" does not exist. Defaulted to: No Specific Link', $value);
                        //M1 > M2 Translation End
                        $list->setErpAccountLinkType('N');
                    } else {
                        $list->setErpAccountLinkType(strtoupper($value));
                    }
                    break;
                case 'mandatorylist':
                case 'defaultlist':
                case 'autoload':
                    $initial = $property == 'autoload' ? 'Q' : strtoupper(substr($data[0], 0, 1));
                    $value = preg_replace('/\s+/', '', strtoupper($data[1]));
                    if ($typeVal == 'Fa' && $initial == 'M' && $value == 'Y') {
                        break;
                    }
                    if ($value == 'Y' && !in_array($initial, $this->_settings)) {
                        $this->_settings[] = $initial;
                    } elseif ($value != 'Y' && ($key = array_search($initial, $this->_settings)) !== false) {
                        unset($this->_settings[$key]);
                    }
                    break;
                case 'accounts':
                    $this->_exclude['accounts'] = $value;
                    $next = $property;
                    break;
                case 'products':
                    $this->_exclude['products'] = $value;
                    $next = $property;
                    break;
                case 'restrictions':
                    $next = $property;
                    break;
            }
        } while (!feof($fileContents) && !$next);

        return $next;
    }

    private function _importListAccounts($list, &$fileContents, &$errors)
    {
        $invalidLinkType = array();

        //$list->setErpAccountLinkType('E');

        $next = null;
        $errorAccounts = array();
        $accounts = array();

        do {
            $row = fgets($fileContents);
            $data = explode(',', $row);

            $property = preg_replace('/\s+/', '', strtolower($data[0]));
            if (in_array($property, array('header', 'products', 'restrictions', 'counties', 'countries', 'addresses'))) {
                if ($property = 'products') {
                    $this->_exclude['products'] = isset($data[1]) ? $data[1] : null;
                }
                $next = $property;
            } elseif (!empty($property)) {
                $accounts[] = $data[0];
                $errorAccounts[] = $data[0];
            }
        } while (!feof($fileContents) && !$next);

        if (empty($accounts)) {
            $this->_noAccountsSupplied = true;
            //$errors['errors'][] = $this->__('ERP Accounts cannot be blank.');
            return $next;
        }

        if (count($accounts) > 0) {
            $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
            /* @var $collection Epicor_Comm_Model_Resource_Customer_Erpaccount_Collection */
            /* $collection->addFieldToFilter('account_number', array('in' => $accounts));
              Added a check for both account no and short_code. */
            $collection->addFieldToFilter(
                array(
                'account_number',
                'short_code'
                ), array(
                array('in' => $accounts),
                array('in' => $accounts),
                )
            );
            $erpAccounts = $collection->getItems();
            if (!$erpAccounts) {
                $list->setErpAccountLinkType('N');
            }
            foreach ($erpAccounts as $account) {
                if (array_search($list->getErpAccountLinkType(), array("B2B" => "B", "B2C" => "C"))) {
                    if ($this->linkTypes[$list->getErpAccountLinkType()] != $account->getaccountType()) {
                        $key = array_search(strtolower($account->getAccountNumber()), array_map('strtolower', $accounts)) ?: array_search(strtolower($account->getShortCode()), array_map('strtolower', $accounts));
                        array_push($invalidLinkType, $accounts[$key]);
                        $collection->removeItemByKey($account->getId());
                    }
                }

                if ((($key = array_search(strtolower($account->getAccountNumber()), array_map('strtolower', $errorAccounts))) !== false) || ((($key = array_search(strtolower($account->getShortCode()), array_map('strtolower', $errorAccounts))) !== false))) {
                    if ($account->getAccountNumber() !== $errorAccounts[$key] && $account->getShortCode() !== $errorAccounts[$key]) {
                        $importVal = strcasecmp($account->getAccountNumber(), $errorAccounts[$key]) === 0 ? $account->getAccountNumber() : $account->getShortCode();
                        if (!in_array(strtolower($importVal), array_map('strtolower', $invalidLinkType))) {
                            $errors['warnings'][] = __('Account %1 mapped to %1 during import', $errorAccounts[$key], $importVal);
                        }
                    }
                    unset($errorAccounts[$key]);
                }
            }
        }
        $list->addErpAccounts($collection->getItems());

        if (count($errorAccounts) > 0) {
            //M1 > M2 Translation Begin (Rule 55)
            //$errors['warnings'][] = $this->__('Accounts(s) %s do not exist and were not assigned to list', join(', ', $errorAccounts));
            $errors['warnings'][] = __('Accounts(s) %1 do not exist and were not assigned to list', join(', ', $errorAccounts));
            //M1 > M2 Translation End
        }


        $invalidLinkType = array_diff_assoc($invalidLinkType, $errorAccounts);
        if (count($invalidLinkType) > 0) {
            //M1 > M2 Translation Begin (Rule 55)
            //$errors['warnings'][] = $this->__('ERP Account %s was not added, as it does not match the ERP Account Link Type', join(', ', $invalidLinkType));
            $errors['warnings'][] = __('ERP Account %1 was not added, as it does not match the ERP Account Link Type', join(', ', $invalidLinkType));
            //M1 > M2 Translation End
        }
        return $next;
    }

    /**
     * Processes a csv import
     * 
     * @param \Epicor\Lists\Model\ListModel $list
     * @param string $fileContents
     * @param array $errors
     * @return null
     */
    private function _importListProducts($list, &$fileContents, &$errors)
    {
        $next = null;
        $productHelper = $this->commProductHelper;
        /* @var $productHelper Epicor_Comm_Helper_Product */

        do {
            $row = fgets($fileContents);
            $sample = substr($row, 0, 5);
        } while (!feof($fileContents) && strpos($sample, '/**') > -1);

        $titles = explode(',', strtoupper(trim($row)));
        $processedTitles = array();
        foreach ($titles as $x => $title) {
            if (trim($title) === "BREAK QTY") {
                $title = "BREAK";
            }

            if (trim($title) === "DESCRIPTION") {
                $title = "BREAK_DESCRIPTION";
            }

            $processedTitles[$x] = str_replace(" ", "_", trim($title));
        }
        $skuCol = array_search('SKU', $processedTitles);
        $uomCol = array_search('UOM', $processedTitles);

        $products = array();
        $errorProducts = array();
        $priceBreakUp=array();
        if (($skuCol === false) || ($uomCol === false)) {
            $errors['errors'][] = __('Invalid headers in file - must contain SKU, UOM');
        } else {
            while ($row = fgets($fileContents)) {
                $product = explode(',', $row);
                $property = preg_replace('/\s+/', '', strtolower($product[0]));

                if (in_array($property, array('accounts', 'header', 'restrictions'))) {
                    $next = $property;
                    break;
                }
                $sku = trim($product[$skuCol]);
                //skip for blank row
                if(!$sku) {
                    continue;
                }
                $uom = trim($product[$uomCol]);
                 if ($list->getType() == 'Pr') {
                    $priceBreakUp[] = array_combine($processedTitles, $product);
                }
                if ($product = $productHelper->findProductBySku($sku, $uom, false)) {
                    /* @var $product Epicor_Comm_Model_Product */
                    $products[] = $product;

                    if ($product->getTypeId() == 'grouped') {
                        foreach ($product->getTypeInstance(true)->getAssociatedProducts($product) as $children) {
                            //if (strpos($children->getSku(), $productHelper->getUOMSeparator()) !== false) {
                            //Neither CUPG or CCCN will create products with blank <unitOfMeasure> / <unitOfMeasureCode> 
                            //So there is no need to check this strpos()
                            $childSku[] = $children->getSku();
                            if (!empty($childSku)) {
                                $products[] = $children;
                            }
                        }
//                        if (!$uom) {
//                            $errors['warnings'][] = $this->__('No UOM specified for Product %s, all UOMs have been added', $product->getSku());
//                        }
                        if ($product->getEccUom() != $uom) {
                            //M1 > M2 Translation Begin (Rule 55)
                            //$errors['warnings'][] = $this->__('No valid UOM specified for Product %s, all UOMs have been added', $product->getSku());
                            $errors['warnings'][] = __('No valid UOM specified for Product %1, all UOMs have been added', $product->getSku());
                            //M1 > M2 Translation End
                        }
                    }
                } elseif (!empty($sku)) {
                    $errorProducts[] = $sku . ($uom ? ' ' . $uom : '');
                }
            }

            if (count($errorProducts) > 0) {
                //M1 > M2 Translation Begin (Rule 55)
                //$errors['warnings'][] = $this->__('Product(s) %s do not exist and were not added to list.', join(', ', $errorProducts));
                $errors['warnings'][] = __('Product(s) %1 do not exist and were not added to list.', join(', ', $errorProducts));
                //M1 > M2 Translation End
            }
        }

        if (count($products) == 0 && count($errorProducts) == 0) {
            $errors['warnings'][] = __('No product lines were found in the file.');
        }
        if ($list->getType() == 'Pr') {
            $priceBreakFormat = $this->getPriceBreakUp($list, $priceBreakUp, $errors);
            $list->addProducts($products, $priceBreakFormat);
        } else {
            $list->addProducts($products);
        }
        return $next;
    }

    /**
     * Adds Products From Csv to List
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param string $file
     * @return array $errors
     */
    public function importCsvProducts($list, $file)
    {
        $errors = array();

        $fileContents = fopen($file, "rb");
        $this->_importListProducts($list, $fileContents, $errors);
        fclose($fileContents);

        if (count($errors) > 0) {
            return $errors;
        } else {
            return false;
        }
    }

    /**
     * Converts status from letter A, or I to Active, Inactive    
     */
    public function getStatus($status)
    {
        return $status == 'A' ? 'Active' : 'Inactive';
    }

    /**
     * Get customer address
     *
     * @param $addressId
     * @param $customerId
     * @return string $options
     */
    function customerSelectedAddressById($addressId, $customerId)
    {
        if ($customerId)
            $loadHelper = $this->commonHelper->customerListAddressesById($addressId, $customerId);
        $customerData = $this->customerCustomerFactory->create()->load($customerId);
        $defaultContractAddress = $customerData->getEccDefaultContractAddress();
        $options .= '<option value="">No Default Address</option>';
        if ($loadHelper) {
            foreach ($loadHelper as $code => $address) {
                $defaultSelect = ($code == $defaultContractAddress ? "selected=selected" : "");
                $options .= '<option value="' . $code . '" ' . $defaultSelect . '>' . $address->getName() . '</option>';
            }
        }
        return $options;
    }

    /**
     * get exclude value
     *
     * @param $exclude     
     */
    function setExcludeValue(&$list, $excludeValue, &$errors, $type)
    {
        $stringToReplace = array('/exclude/', '/=/', '/"/', '/\'/', '/\r|\n/');
        $stringReplacement = array('', '', '', '', '');
        $excludeValue = preg_replace($stringToReplace, $stringReplacement, $excludeValue);
        if (in_array(strtoupper($excludeValue), array('Y', 'N'))) {
            if ($type == 'Accounts') {
                $list->setErpAccountsExclusion($excludeValue);
            } else {
                if (strtoupper($excludeValue) == 'Y') {
                    $this->_settings[] = 'E';
                }
            }
        } else {
            $errors['errors'][] = __("{$type} exclude value {$excludeValue} is incorrect. Must be Y or N ");
        }
    }

    /**
     * check link type
     *
     * @param $exclude     
     */
    function checkLinkType(&$list, &$errors)
    {
        //Check if type has been set
        if (!$list->getErpAccountLinkType()) {
            $list->setErpAccountLinkType('N');
        }
        if (($list->getErpAccountLinkType() != 'N') && in_array($list->getErpAccountsExclusion(), array(null, '', 'N')) && $this->_noAccountsSupplied) {
            $list->setErpAccountLinkType('N');
            $errors['warnings'][] = __("Link Type has been set to 'N', as account exclude attribute is not set and no accounts are supplied");
        }
    }

    private function _importListRestrictions($list, &$fileContents, &$errors)
    {
        do {
            $row = fgets($fileContents);
            $data = explode(',', $row);
            $property = preg_replace('/\s+/', '', strtolower($data[0]));
            if (in_array($property, array('accounts', 'header', 'addresses', 'products', 'counties', 'postcodes'))) {
                $next = $property;
            }
        } while (!feof($fileContents) && !$next);

        $titles = explode(',', strtolower(trim($row)));

        switch ($titles[0]) {
            case 'addresses':

                $next = $this->_importListAddresses($list, $fileContents, $errors);
                break;
            case 'countries':
                $next = $this->_importListCountries($list, $fileContents, $errors);
                break;
            case 'states':
            case 'counties':
                $next = $this->_importListCounties($list, $fileContents, $errors);
                break;
            case 'postcodes':
            case 'zipcodes':
                $next = $this->_importListPostcodes($list, $fileContents, $errors);
                break;
            case 'products':
            case 'header':
            case 'accounts':
        }

        return $next;
    }

    /**
     * Processes a csv import
     * 
     * @param \Epicor\Lists\Model\ListModel $list
     * @param string $fileContents
     * @param array $errors
     * @return string
     */
    private function _importListAddresses($list, &$fileContents, &$errors)
    {
        $helper = $this->commMessagingHelper;
        do {
            $row = fgets($fileContents);
            $sample = substr($row, 0, 5);
        } while (!feof($fileContents) && strpos($sample, '/**') > -1);
        $next = null;
        $titles = explode(',', strtoupper(trim($row)));
        $addressTitles = array();
        foreach ($titles as $x => $title) {
            $addressTitles[$x] = trim($title);
        }
        $processedTitles = array_map('strtolower', $addressTitles);
        $addressCodeCol = array_search('addresscode', $processedTitles);
        $nameCol = array_search('name', $processedTitles);
        $address1Col = array_search('address1', $processedTitles);
        $address2Col = array_search('address2', $processedTitles);
        $address3Col = array_search('address3', $processedTitles);
        $cityCol = array_search('city', $processedTitles);
        $countyCol = array_search('county', $processedTitles);
        $countryCol = array_search('country', $processedTitles);
        $postcodeCol = array_search('postcode', $processedTitles);
        $addresses = array();
        while (!feof($fileContents) && !$next) {
            $row = fgets($fileContents);
            $rowData = explode(',', $row);
            $property = preg_replace('/\s+/', '', strtolower($rowData[0]));
            if (in_array($property, array('accounts', 'header', 'countries', 'products', 'counties', 'postcodes'))) {
                $next = $property;
            }
            if ($rowData[$addressCodeCol] == '' || strtolower($rowData[$addressCodeCol]) == 'counties') {
                continue;
            }
            $countryCode = $helper->getCountryCodeMapping($rowData[$countryCol], 'e2m');
            $countryModel = $this->directoryCountryFactory->create()->loadByCode($countryCode);
            $collection = $this->directoryRegionFactory->create()->getResourceCollection()
                ->addCountryFilter($countryModel->getId())
                ->load();
            if ($countryModel->getId() && $collection->count() > 0) {
                $region = $this->directoryRegionFactory->create()->loadByCode($rowData[$countyCol], $countryModel->getId());
                $regionCode = $region->getCode();
                if (!empty($regionCode)) {
                    $county = $region->getCode();
                } else {
                    $region = $this->directoryRegionFactory->create()->loadByName($rowData[$countyCol], $countryModel->getId());
                    $regionCode = $region->getCode();
                    if (!empty($regionCode)) {
                        $county = $region->getCode();
                    }
                }
                if (!empty($county)) {
                    $addresses = array('address_code' => $rowData[$addressCodeCol], 'name' => $rowData[$nameCol],
                        'address1' => $rowData[$address1Col], 'address2' => $rowData[$address2Col], 'address3' => $rowData[$address3Col],
                        'city' => $rowData[$cityCol], 'county' => $county, 'country' => $countryModel->getId(),
                        'postcode' => $rowData[$postcodeCol]
                    );
                    $addressObject = $this->dataObjectFactory->create();
                    $addressObject->setData($addresses);
                    $finalAddress[$rowData[$addressCodeCol]] = $addressObject;
                } else {
                    //M1 > M2 Translation Begin (Rule 55)
                    //$errors['warnings'][] = $this->__('Invalid County %s in address section', $rowData[$countyCol]);
                    $errors['warnings'][] = __('Invalid County %1 in address section', $rowData[$countyCol]);
                    //M1 > M2 Translation End
                }
            } else {
                //M1 > M2 Translation Begin (Rule 55)
                //$errors['warnings'][] = $this->__('Invalid Country %s in address section', $rowData[$countryCol]);
                $errors['warnings'][] = __('Invalid Country %1 in address section', $rowData[$countryCol]);
                //M1 > M2 Translation End
            }
        }
        if ($finalAddress) {
            $list->addAddresses($finalAddress);
        }
        return $next;
    }

    /**
     * Processes a csv import
     * 
     * @param \Epicor\Lists\Model\ListModel $list
     * @param string $fileContents
     * @param array $errors
     * @return string
     */
    private function _importListCountries($list, &$fileContents, &$errors)
    {
        $next = null;
        $countries = array();
        $messageHelper = $this->commMessagingHelper;
        do {
            $row = fgets($fileContents);
            $data = explode(',', $row);
            $property = preg_replace('/\s+/', '', strtolower($data[0]));
            if (in_array($property, array('accounts', 'header', 'addresses', 'products', 'counties', 'postcodes'))) {
                $next = $property;
            } elseif (!empty($property)) {
                $countryCode = $messageHelper->getCountryCodeMapping(trim($data[0]), 'e2m');
                $countryModel = $this->directoryCountryFactory->create()->loadByCode($countryCode);
                $id = $countryModel->getId();
                if (!empty($id)) {
                    $countryObject = $this->dataObjectFactory->create();
                    $countryObject->setData('country', $countryModel->getId());
                    $finalCountry[$countryModel->getId()] = $countryObject;
                } else {
                    //M1 > M2 Translation Begin (Rule 55)
                    //$errors['warnings'][] = $this->__('Invalid Country %s in country section', $data[0]);
                    $errors['warnings'][] = __('Invalid Country %1 in country section', $data[0]);
                    //M1 > M2 Translation End
                }
            }
        } while (!feof($fileContents) && !$next);
        if ($finalCountry) {
            $list->addCountries($finalCountry);
        }
        return $next;
    }

    /**
     * Processes a csv import
     * 
     * @param \Epicor\Lists\Model\ListModel $list
     * @param string $fileContents
     * @param array $errors
     * @return string
     */
    private function _importListCounties($list, &$fileContents, &$errors)
    {
        $next = null;
        $counties = array();
        $helper = $this->commMessagingHelper;
        do {
            $row = fgets($fileContents);
            $data = explode(',', $row);
            $property = preg_replace('/\s+/', '', strtolower($data[0]));
            if (in_array($property, array('accounts', 'header', 'addresses', 'products', 'countries', 'postcodes'))) {
                $next = $property;
            } elseif (!empty($property)) {

                $countryCode = $helper->getCountryCodeMapping(trim($data[0]), 'e2m');
                $countryModel = $this->directoryCountryFactory->create()->loadByCode($countryCode);
                $collection = $this->directoryRegionFactory->create()->getResourceCollection()
                    ->addCountryFilter($countryModel->getId())
                    ->load();
                if ($countryModel->getId() && $collection->count() > 0) {
                    $region = $this->directoryRegionFactory->create()->loadByCode($data[1], $countryModel->getId());
                    $regionCode = $region->getCode();
                    if (!empty($regionCode)) {
                        $county = $region->getCode();
                    } else {
                        $region = $this->directoryRegionFactory->create()->loadByName($data[1], $countryModel->getId());
                        $regionCode = $region->getCode();
                        if (!empty($regionCode)) {
                            $county = $region->getCode();
                        }
                    }
                    
                    $regionCode = $region->getCode();
                    if (!empty($regionCode)) {
                        $countyObject = $this->dataObjectFactory->create();
                        $countyObject->setData('county', $county);
                        $countyObject->setData('country', $countryModel->getId());
                        $uniqueKey = $countryModel->getId() . '-' . $county;
                        $finalCounty[$uniqueKey] = $countyObject;
                    } else {
                        //M1 > M2 Translation Begin (Rule 55)
                        //$errors['warnings'][] = $this->__('Invalid region %s in county section', $data[1]);
                        $errors['warnings'][] = __('Invalid region %1 in county section', $data[1]);
                        //M1 > M2 Translation End
                    }
                } else {
                    //M1 > M2 Translation Begin (Rule 55)
                    //$errors['warnings'][] = $this->__('Invalid country %s in county section', $data[0]);
                    $errors['warnings'][] = __('Invalid country %1 in county section', $data[0]);
                    //M1 > M2 Translation End
                }
            }
        } while (!feof($fileContents) && !$next);

        if ($finalCounty) {
            $list->addCounties($finalCounty);
        }
        return $next;
    }

    /**
     * Processes a csv import
     * 
     * 
     * @param \Epicor\Lists\Model\ListModel $list
     * @param \Epicor\Lists\Model\ListModel $list
     * @param string $fileContents
     * @param array $errors
     * @return string
     */
    private function _importListPostcodes($list, &$fileContents, &$errors)
    {
        $next = null;
        $postcodes = array();
        $helper = $this->commMessagingHelper;
        $listHelper = $this;
        do {
            $row = fgets($fileContents);
            $data = explode(',', $row);
            $property = preg_replace('/\s+/', '', strtolower($data[0]));
            if (in_array($property, array('accounts', 'header', 'addresses', 'products', 'counties', 'countries'))) {
                $next = $property;
            } elseif (!empty($property)) {
                $countryCode = $helper->getCountryCodeMapping($data[0], 'e2m');
                $countryModel = $this->directoryCountryFactory->create()->loadByCode($countryCode);
                $id = $countryModel->getId();
                if (!empty($id)) {
                    $postcodeObject = $this->dataObjectFactory->create();
                    $postcode = $listHelper->formatPostcode(trim($data[1]));
                    $postcodeObject->setData('postcode', $postcode);
                    $postcodeObject->setData('country', $countryModel->getId());
                    $uniqueKey = $countryModel->getId() . '-' . $postcode;
                    $finalPostcode[$uniqueKey] = $postcodeObject;
                } else {
                    //M1 > M2 Translation Begin (Rule 55)
                    //$errors['warnings'][] = $this->__('Invalid country %s in postcode section', $data[0]);
                    $errors['warnings'][] = __('Invalid country %1 in postcode section', $data[0]);
                    //M1 > M2 Translation End
                }
            }
        } while (!feof($fileContents) && !$next);
        if ($finalPostcode) {
            $list->addPostcodes($finalPostcode);
        }
        return $next;
    }

    /**
     * Format postcode to save in form of regex in table 
     * 
     * @param $postcode
     * @return  $postcode
     */
    public function formatPostcode($postcode)
    {

        if (strpos($postcode, '*') > 0) {
            $postcode = '^' . str_replace('*', '.*', $postcode) . '$';
        }

        return $postcode;
    }

    /**
     * Check duplicate country for country restriction
     * @param $listId
     * @param $country
     * @returns count
     */
    public function checkDuplicateCountry($listId, $country)
    {
        $addressCollection = $this->listsResourceListModelAddressCollectionFactory->create();
        $restrictionTable = $this->resourceConnection->getTableName('ecc_list_address_restriction');
        $addressCollection->getSelect()->join(array('restrictions' => $restrictionTable), 'main_table.id=restrictions.address_id');

        $addressCollection->addFieldToFilter('restrictions.list_id', $listId)
            ->addFieldToFilter('restriction_type', \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_COUNTRY)
            ->addFieldToFilter('country', $country);
        return count($addressCollection->getItems());
    }

    /**
     * Check duplicate county for county restriction
     * @param $listId
     * @param $country
     * @param $county
     * @returns count
     */
    public function checkDuplicateCounty($listId, $country, $county)
    {
        $addressCollection = $this->listsResourceListModelAddressCollectionFactory->create();
        $restrictionTable = $this->resourceConnection->getTableName('ecc_list_address_restriction');
        $addressCollection->getSelect()->join(array('restrictions' => $restrictionTable), 'main_table.id=restrictions.address_id');

        $addressCollection->addFieldToFilter('restrictions.list_id', $listId)
            ->addFieldToFilter('restriction_type', \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_STATE)
            ->addFieldToFilter('country', $country)
            ->addFieldToFilter('county', $county);
        return count($addressCollection->getItems());
    }

    /**
     * Check duplicate postcode for postcode restriction
     * @param $listId
     * @param $country
     * @returns count
     */
    public function checkDuplicatePostcode($listId, $country, $postcode)
    {
        $addressCollection = $this->listsResourceListModelAddressCollectionFactory->create();
        $restrictionTable = $this->resourceConnection->getTableName('ecc_list_address_restriction');
        $addressCollection->getSelect()->join(array('restrictions' => $restrictionTable), 'main_table.id=restrictions.address_id');

        $addressCollection->addFieldToFilter('restrictions.list_id', $listId)
            ->addFieldToFilter('restriction_type', \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_ZIP)
            ->addFieldToFilter('country', $country)
            ->addFieldToFilter('postcode', $postcode);
        return count($addressCollection->getItems());
    }


    public function getErpCodes($listIds)
    {
        $collection = $this->listsResourceListModelCollectionFactory->create();
        /* @var $list Epicor_Lists_Model_ListModel */
        $collection->addFieldToFilter('id', array('in' => $listIds));
        $resultVals = $collection->getColumnValues('erp_code');
        $eCommaList = implode(', ', $resultVals);
        return $eCommaList;
    }

    public function getListDatas($listIds)
    {
        $collection = $this->listsResourceListModelCollectionFactory->create();
        /* @var $list Epicor_Lists_Model_ListModel */
        $collection->addFieldToFilter('id', array('in' => $listIds));
        $resultVals = $collection->getItems();
        return $resultVals;
    }

    public function resetLocationFilter()
    {
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        $sessionHelper->setValue('ecc_selected_branchpickup', '');
        $locHelper = $this->commLocationsHelper;
        /* @var $locHelper Epicor_Comm_Helper_Locations */
        $displayedLocations = $locHelper->getCustomerAllowedLocations();
        if (!empty($displayedLocations)) {
            $locHelper->setCustomerDisplayLocationCodes(array_keys($displayedLocations));
        }
    }

    public function getAddressStatus($address)
    {
        $expiryDate = strtotime($address->getExpiryDate());
        $activationDate = strtotime($address->getActivationDate());
        $nowTime = strtotime("now");
        if (!$activationDate && !$expiryDate) {
            return 'Active';
        }
        if ($activationDate && $activationDate > $nowTime) {
            return 'Inactive';
        }
        if ($expiryDate && $expiryDate < $nowTime) {
            return 'Expired';
        }

        return 'Active';
    }

    /**
     * Validates a list code
     * 
     * @return array
     */
    public function validateNewListCode($request)
    {
        $response = array('error' => 1);
        if ($request->isPost()) {
            $data = $request->getPost();
            if (isset($data['erp_code']) && !empty($data['erp_code'])) {
                $list = $this->listsListModelFactory->create()->load($data['erp_code'], 'erp_code');
                if ($list->isObjectNew()) {
                    $response['error'] = 0;
                }
            }
        }
        return $response;
    }
         /**
     * Adds Pricing data From Csv to product array
     *
     * @param array $priceBreak
     * @return array $key
     */
    public function getPriceBreakUp($list, $records, &$errors)
    {
        $skuArray = array();
        $skuCurrency = array();
        $invalidCurrency=array();
        $invalidPrice=array();
        $invalidBreak=array();
        $invalidBreakPrice=array();
        $currencyHelper = $this->commonLocaleFormatCurrencyHelper;
        $currencyList = $currencyHelper->getAllowedCurrencies();
        $priceTable = $this->resourceConnection->getTableName('ecc_list_product_price');
        $listId = ($list) ? $list->getId() : '';
        $i = 0;
        $nonBreakProduct = array();
        foreach ($records as $recordKey => $record) {
            $nonBreak = false;
            $resourceProduct =  $this->catalogProductFactory->create()->getResource();
            /* @var $resourceProduct \Epicor\Comm\Model\ResourceModel\Product */
            //check if product is a group, if so combine sku and uom
            $productId  =$resourceProduct->getIdBySku($record['SKU']);
            $productType =  $resourceProduct->getAttributeRawValue($productId, 'type_id', 0);
            $productType=isset($productType['type_id'])?$productType['type_id'] :null;
            if($productType == 'grouped'){
                $record['SKU'] = $record['SKU'].$this->commHelper->getUOMSeparator().$record['UOM'];
                $records[$recordKey]['SKU'] =  $record['SKU'];
            }
            //die;
            $price_break['qty'] = $record['BREAK'];
            $price_break['price'] = $record['BREAK_PRICE'];
            $price_break['description'] = $record['BREAK_DESCRIPTION'];
            
            if (!is_numeric($record['BREAK']) && trim($record['BREAK']) != '' && !is_numeric($record['BREAK_PRICE']) && trim($record['BREAK_PRICE']) != '') {
                $nonBreakProduct[$recordKey] = $recordKey;
                continue;
            }
            if (!is_numeric($record['BREAK']) && trim($record['BREAK']) != '' && !is_numeric($record['BREAK_PRICE']) && trim($record['BREAK_PRICE']) != '') {
                $nonBreakProduct[$recordKey] = $recordKey;
                continue;
            }
            if(!array_key_exists($record['CURRENCY'], $currencyList) && !is_numeric($record['BREAK']) && !is_numeric($record['BREAK_PRICE']) && !is_numeric($record['PRICE']) ){
                continue;
            }
            else if (!array_key_exists($record['CURRENCY'], $currencyList) && is_numeric($record['PRICE']) ) {
                $invalidCurrency[] = $record['SKU']." ".$record['UOM'];
                $nonBreakProduct[$recordKey] = $recordKey;
                continue;
            }
            if(!is_numeric($record['PRICE']) && !is_numeric($record['BREAK']) && !is_numeric($record['BREAK_PRICE']) ){
               $nonBreakProduct[$recordKey] = $recordKey;
                continue;
            }
            elseif (!is_numeric($record['PRICE'])) {
                $invalidPrice[] = $record['SKU']." ".$record['UOM'];
                $nonBreakProduct[$recordKey] = $recordKey;
                continue;
            }
            if ((!is_numeric($record['BREAK']) && trim($record['BREAK']) != '') ||
                (trim($record['BREAK']) == '' && is_numeric($record['BREAK_PRICE']))) {
                $nonBreak = true;
                $invalidBreak[] = $record['SKU']." ".$record['UOM'];
                $nonBreakProduct[$recordKey] = $recordKey;
            }

            //skip to add break if both are missing break_qty + break_price
            if ((!is_numeric($record['BREAK']) && trim($record['BREAK']) == '') &&
                (!is_numeric($record['BREAK_PRICE']) && trim($record['BREAK_PRICE']) == '')) {
                $nonBreak = true;
            }

            if ((!is_numeric($record['BREAK_PRICE']) && trim($record['BREAK_PRICE']) != '') ||
                (trim($record['BREAK_PRICE']) == '' && is_numeric($record['BREAK']))) {
                $nonBreak = true;
                $invalidBreakPrice[] = $record['SKU']." ".$record['UOM'];
                $nonBreakProduct[$recordKey] = $recordKey;
            }
            if ($nonBreak == false) {
                $price_breaks[$record['SKU'] . '_' . $record['CURRENCY']][] = $price_break;
            }
        }
        $readConnection=$this->resourceConnection->getConnection('core_read');
        foreach ($records as $recordKey => $record) {
            if(array_key_exists($recordKey, $nonBreakProduct) && !is_numeric($record['PRICE'])){
                continue;
            }
            foreach ($record as $key => $value) {
                if (!in_array($record['SKU'] . '_' . $record['CURRENCY'], $skuCurrency) ) {
                    $priceId = 'new_' . $i;
                    if ($listId) {
                        $queryselectet="
                       SELECT main_table.id, listtable.id AS price_id FROM ecc_list_product AS main_table
                                INNER JOIN
                                         ecc_list_product_price AS listtable ON main_table.id = listtable.list_product_id
                                WHERE
                                        (sku = "."'" . $record['SKU'] . "'".") AND (list_id = ".$listId.") AND (listtable.currency = " . "'" . $record['CURRENCY'] . "'".");";
                        
                        $query=$readConnection->fetchRow($queryselectet);
                        if($query){
                            $priceId=$query['price_id'];
                        }
                    }

                  
                                        
                    if(!$record['CURRENCY'] && !$record['PRICE']){
                       // Do nothing do not insert record
                    }
                    elseif(!$record['CURRENCY'] && $record['PRICE']){
                       // Do nothing do not insert record
                    }else{
                    $skuCurrency[] = $record['SKU'] . '_' . $record['CURRENCY'];
                    $currencyData['id'] = $priceId;
                    $currencyData['currency'] = $record['CURRENCY'];
                    $currencyData['price'] = $record['PRICE'];
                    $currencyData['price_breaks'] = isset($price_breaks[$record['SKU'] . '_' . $record['CURRENCY']])?$price_breaks[$record['SKU'] . '_' . $record['CURRENCY']]:'';
                    //$currencyData['price_breaks'] = @$price_breaks[$record['SKU'] . '_' . $record['CURRENCY']];

                    $skuArray[$record['SKU']][$priceId] = $currencyData;
                 }
                     $i++;
                }
            }
           
        }
        if ($invalidCurrency) {
            $errors['warnings'][] = __('Invalid currency code: %1.', join(', ', $invalidCurrency));
        }
        if ($invalidPrice) {
            $errors['warnings'][] = __('Invalid Price: %1.', join(', ', $invalidPrice));
        }
        if ($invalidBreak) {
            $errors['warnings'][] = __('Invalid Break: %1.', join(', ', $invalidBreak));
        }
        if ($invalidBreakPrice) {
            $errors['warnings'][] = __('Invalid Break Price: %1.', join(', ', $invalidBreakPrice));
        }
        return $skuArray;
    }
}
