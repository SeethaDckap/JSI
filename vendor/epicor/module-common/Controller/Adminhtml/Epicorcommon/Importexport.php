<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon;


/**
 * Common ImportExport controller
 *
 * This controls the import and export function in the admin
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 * 
 * when adding a table to  the array, the key values indicate what will be part of the addFieldToFilter parm
 * the Id value is the value of the table id (usually id or entity_id, but can be different)
 * 
 * 
 */
abstract class Importexport extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    protected $_backupFolder;
    protected $_serializedData;
    protected $_serializedFinal;
    protected $_unserializedArray;
    protected $_serializedArray;
    protected $_module;
    protected $_table;
    protected $_key;
    protected $_id;
    protected $_mappingTables;
    protected $_storesArray;
    protected $_importFile;
    protected $_excludedConfigTables = array('web/unsecure/base_url'
        , 'web/secure/base_url'
        , 'web/cookie/cookie_domain'
        , 'Epicor_Comm/licensing/type'
        , 'Epicor_Comm/licensing/erp'
        , 'Epicor_Comm/licensing/cert_file'
        , 'Epicor_Comm/licensing/username'
        , 'Epicor_Comm/licensing/password'
        , 'Epicor_Comm/licensing/company'
        , 'Epicor_Comm/licensing/ewa_username'
        , 'Epicor_Comm/licensing/ewa_password'
        , 'Epicor_Comm/licensing/p21_token_url'
    );

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Framework\Session\Generic $generic, 
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Common\Helper\Context $commonHelper
    )
    {
        $this->generic = $generic;
        $this->storeManager = $commonHelper->getStoreManager();
        $this->logger = $commonHelper->getLogger();
        $this->commonHelper = $commonHelper;
        parent::__construct($context, $backendAuthSession);
    }

    public function importConfigData()
    {
        // 
        // retrieve list of stores on system  do not load data for store id's that aren't on system   
        $allStores = $this->storeManager->getStores();
        foreach ($allStores as $eachStoreId => $val) {
            $storeId[] = $this->storeManager->getStore($eachStoreId)->getId();
        }

        foreach ($this->_unserializedArray as $arrayEntry) {

            if (!$this->getRequest()->getParam('importall') && in_array($arrayEntry['path'], $this->_excludedConfigTables)) {       // if includeall checkbox is clicked
                continue;
            }

            if ($arrayEntry['scope_id'] == 0) {                          // always import scope id 0  
                $this->importDataCommonProcessing($arrayEntry);
            } else {
                if (isset($this->_storesArray[$arrayEntry['scope_id']])) {   // only proceed if current scope_id was selected and placed in storesarray
                    $arrayEntry['scope_id'] = $this->_storesArray[$arrayEntry['scope_id']];     // change scope_id to mapped scope id      
                    $this->importDataCommonProcessing($arrayEntry);
                }
            }
        }
        $this->messageManager->addSuccessMessage(__("{$this->_module}_{$this->_table} data imported successfully"));
    }

    public function importTableData()
    {
        foreach ($this->_unserializedArray as $arrayEntry) {
            $this->importDataCommonProcessing($arrayEntry);
        }
        $this->messageManager->addSuccessMessage(__("{$this->_module}_{$this->_table} data imported successfully"));
    }

    public function importDataCommonProcessing($arrayEntry)
    {

        // load model according to the value of the supplied key 
       // $model = Mage::getModel("{$this->_module}" . "/" . "{$this->_table}")->getCollection();
               
        $modelpath = $this->_module.'/'.$this->_table; 
        $model = $this->commonHelper->getErpSourceReader()->getModel($modelpath)->getCollection();

        // build collection sql line by line using passed keys
        foreach ($this->_key as $key => $value) {                 // populate key with values to search on
            $model = $model->addFieldToFilter($key, array('eq' => $arrayEntry[$key]));      // build collection
        }
        $dataItem = $model->getFirstItem();                     // ensure only one item returned from collection

        unset($arrayEntry[$this->_id]);                         // remove old id field from saved data, so existing id is not overwritten
        if (!$dataItem->isObjectNew()) {                          // if object is not new, it is already on the table
            
            $arrayEntry[$this->_id] = $dataItem->getData($this->_id);   // save current id
          
            $dataItem->setData($arrayEntry);                            // apply all saved data to current id 
        }else{
            $dataItem =$this->commonHelper->getErpSourceReader()->getModel($modelpath)->setData($arrayEntry);
        }
        try {
            $dataItem->save();
        } catch (Exception $ex) {
            $this->errorMsg($ex);
        }
    }

    public function backupTables()
    {

        foreach ($this->_mappingTables as $key => $value) {
            $modelpath = $value['module'].'/'.$value['entity']; 
            $collection = $this->commonHelper->getErpSourceReader()->getModel($modelpath)->getCollection();
            if ($value['entity'] == 'config_data') {
                $excludeArray = array(  'web/unsecure/base_url',
                                        'web/secure/base_url',
                                        'web/cookie/cookie_domain',
                                        'web/unsecure/base_link_url',
                                        'Epicor_Comm/xmlMessaging/url',
                                        'epicor_comm_enabled_messages/cim_request/ewa_url',
                                        'web/secure/base_link_url');
                $collection->addFieldToFilter('path', array('nin' => $excludeArray));
            }
            $collection = $collection->getData();

            if (empty($collection)) {         // if no data on table, don't serialized it
                $this->_serializedData[$value['module']][$value['entity']] = array('key' => $value['key'], 'data' => 'No Data', 'id' => $value['id']);
            } else {
                $this->_serializedData[$value['module']][$value['entity']] = array('key' => $value['key'], 'data' => serialize($collection), 'id' => $value['id']);
            }

            if ($this->_serializedData[$value['module']][$value['entity']]) {
                //   Mage::getSingleton('core/session')->addSuccess(Mage::helper('epicor_common')->__("{$value['module']}_{$value['entity']} backed up Successfully"));
            } else {
                //   Mage::getSingleton('core/session')->addError(Mage::helper('epicor_common')->__("Error: {$value['entity']} did not backup successfully"));
            }
        }
        $this->_serializedFinal = serialize($this->_serializedData);
    }

    public function errorMsg($ex)
    {
        $this->logger->debug($ex);
        $this->generic->addError(__(" Error: {$this->_module}_{$this->_table} did not restore successfully"));
        //M1 > M2 Translation Begin (Rule p2-3)
        //Mage::app()->getResponse()->setRedirect($_SERVER['HTTP_REFERER'])->sendResponse();
        $this->_response->setRedirect($_SERVER['HTTP_REFERER'])->sendResponse();
        //M1 > M2 Translation End
    }

}
