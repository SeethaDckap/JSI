<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Quickstart;

class Save extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Quickstart {

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Config\Model\Config
     */
    protected $configConfig;

    /**
     * @var \Epicor\Common\Model\Config\Backend\ErpsFactory
     */
    protected $commonConfigBackendErpsFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Epicor\Common\Helper\Quickstart\SourceModelReader
     */
    protected $sourceModelReader;


    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected $reinitableConfig;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
     /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
     /**
     * all active shipping method carriers
     */
    protected $activeCarriers;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    protected $resource;

    /*
     * @array rowsToAdd - save all insert lines for table
     */
    protected $rowsToAdd = [];


    public function __construct(
            \Epicor\Comm\Controller\Adminhtml\Context $context,
            \Magento\Backend\Model\Auth\Session $backendAuthSession,
            \Epicor\Common\Helper\Data $commonHelper,
            \Magento\Config\Model\Config $configConfig,
            \Epicor\Common\Model\Config\Backend\ErpsFactory $commonConfigBackendErpsFactory,
            \Magento\Framework\DataObjectFactory $dataObjectFactory,
            \Epicor\Common\Helper\Quickstart\SourceModelReader $sourceModelReader,
            \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig,
            \Magento\Framework\App\ResourceConnection $resource

    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commonHelper = $commonHelper;
        $this->configConfig = $configConfig;
        $this->commonConfigBackendErpsFactory = $commonConfigBackendErpsFactory;
        $this->sourceModelReader = $sourceModelReader;
        $this->reinitableConfig = $reinitableConfig;
        $this->registry = $context->getRegistry();
        $this->commHelper = $commonHelper->getCommHelper();
        $this->connection = $resource->getConnection();
        $this->resource = $resource;

        parent::__construct($context, $backendAuthSession);
    }

    public function execute() {
        $this->commHelper->getShippingmethodList();
        $this->activeCarriers = $this->_registry->registry('shipping_carriers');
        $params = $this->getRequest()->getPost();

        $helper = $this->commonHelper;
        $colsDeleted = false;
        $error = false;
        $deletedCols = [];
        try {
            $mapping_data = $this->dataObjectFactory->create();

            foreach (\Epicor\Common\Helper\Quickstart::$CONFIG_FIELDS as $config) {
                foreach ($config['fields'] as $key => $field) {
                    if (strpos($key, 'mapping') === 0) {
                        $mapping_data->setData(substr($key, 8), $helper->arrayToVarian($field));
                    }
                }
            }
            foreach ($params as $key1 => $value1) {

                if ($key1 == 'mapping') {
                    
                    $deletedCols = [];
                    foreach ($value1 as $key2 => $value2) {
                        $changedFieldsProcessed = false;
                        $orig_data = $value2['origData'];
                        $element = $mapping_data->getData($key2);
                        $addCount = 0;
                        $delCount = 0;
                        if(!$changedFieldsProcessed){
                            $this->checkForChangedFields($value2, $element);
                            $changedFieldsProcessed = true;
                        }
                        foreach ($value2 as $id => $fields) {

                            if ($id == 'deleteRow' && $fields != '') {
                                $delCount++;
                                $colsDeleted = true;
                                $deletedCols = $value2[$id];
                            } elseif ($id == 'addRow') {
                                $addCount++;
                                $this->processAdditions($value2[$id], $element);
                                if ($colsDeleted) {
                                    $this->processDeletions($deletedCols, $element);
                                    $colsDeleted = false;
                                }
                            }
                        }
                        if ($delCount > 0 && $addCount == 0) {
                            $this->processDeletions($deletedCols, $element);
                        }
                    }
                    //add any new rows to the mapping tables
                    $this->multiInsert();
                } elseif (is_array($value1)) {

                    $groups = array();

                    foreach ($value1 as $key2 => $value2) {
                        if(($key2 == 'site_monitoring') && is_array($value2) && isset($value2['code_snippet'])){
                            $code_snippet = trim($value2['code_snippet']);
                            if($code_snippet != ''){
                                if(!preg_match('/<script/', $code_snippet)){
                                        $error[] = 'Invalid value for Site Monitoring Code Snippet. See example code snippet &lt;script src = "example.js"&gt;&lt;/script&gt;';
                                        continue;
                                }
                            }
                         }
                         
                        $groups[$key2] = array(
                            'fields' => array()
                        );
                        foreach ($value2 as $key3 => $value3) {
                            $path = $key1 . '/' . $key2 . '/' . $key3;

                            $groups[$key2]['fields'][$key3]['value'] = $value3;
                        }
                    }
                    if (isset($_FILES[$key1]['name']) && is_array($_FILES[$key1]['name'])) {
                        /**
                         * Carefully merge $_FILES and $_POST information
                         * None of '+=' or 'array_merge_recursive' can do this correct
                         */
                        foreach ($_FILES[$key1]['name'] as $groupName => $group) {
                            if (is_array($group)) {
                                foreach ($group as $fieldName => $field) {
                                    if (!empty($field)) {
                                        $groups[$groupName]['fields'][$fieldName] = array('value' => $field);
                                    }
                                }
                            }
                        }
                    }

                //--SF    $this->massInsert();
                    $this->configConfig
                            ->setSection($key1)
                            ->setWebsite(null)
                            ->setStore(null)
                            ->setGroups($groups)
                            ->save();
                }
            }
            $this->commonConfigBackendErpsFactory->create()->_afterSave(true, true);
        } catch (\Exception $e) {
            $error[] = $e->getMessage();
        }

        if (is_array($error)) {
            foreach ($error as $err) {
                $this->messageManager->addErrorMessage($err);
            }
        } else {

            $this->messageManager->addSuccessMessage('Settings Updated');
        }

        // have to do this otherwise magento won't pick up the updated config values
        //M1 > M2 Translation Begin (Rule P2-5.6)
        // Mage::getConfig()->reinit();
        $this->reinitableConfig->reinit();
        //M1 > M2 Translation End

        $this->_redirect('*/*/index');
    }

    public function processAdditions($fields, $element) {
        foreach ($fields as $newId => $newFields) {
            $model = $this->sourceModelReader->getModel($element->getSourceModel());
            $tableAlias = explode('/', $element->getSourceModel());
            //create table name if epicor
            if (strpos($element->getSourceModel(), 'epicor') > -1 ||
                strpos($element->getSourceModel(), 'customerconnect') > -1) {
                
                $tableAlias = explode('/', $element->getSourceModel());
                $tableName = 'ecc_' . $tableAlias[1];
            } else {
                $tableName = implode('_', $tableAlias);
            }
            if ($element->getSourceModel() == 'epicor_comm/erp_mapping_shippingmethod') {
                $newFields['shipping_method'] = $this->activeCarriers[$newFields['shipping_method_code']];
            }

            $mappingFields = $element->getMappingFields();
            $fieldsToAdd = [];
            foreach ($newFields as $key3 => $value3) {
                if (is_array($value3) && $mappingFields[$key3]['type'] == 'multiselect') {
                    $value3 = implode(', ', $value3);
                }
                $fieldsToAdd[$key3] = $value3;
            }
            if ($element->getFieldsToFilter()) {
                foreach ($element->getFieldsToFilter()->getData() as $filter_field => $filter_value) {
                    $model->setData($filter_field, $filter_value);
                    $fieldsToAdd[$filter_field] = $filter_value;
                }
            }
            // don't add rows to table here, add at end of processing
            $this->rowsToAdd[$tableName][] = $fieldsToAdd;
        }
    }

    public function processDeletions($fields, $element) {
         $ids = explode(',', $fields);
         $model = $this->sourceModelReader->getModel($element->getSourceModel());
         
        foreach ($ids as $rowId) {
            $model = $model->load($rowId);
            
            if ($model->getId()) {
                try {
                    $model->delete();
                    $model->cleanModelCache();
                    $model->clearInstance();
                } catch (\Exception $e) {
                    $error[] = $e->getMessage();
                }
            }
        }
    }
     public function checkForChangedFields($data, $element) {
         $origData = unserialize(base64_decode($data['origData']));
         $toBeDeleted = array_flip(explode(',', $data['deleteRow']));
        //loop through remaining rows and compare with $data rows for changes
        $updatedRow = [];
        $validIdArray = ['id'=>'id', 'config_id'=>'config_id', 'class_id'=>'class_id'];
        foreach($origData as $orig){
            $idValid = array_intersect_key($validIdArray, $orig);
            $validIdKey = array_shift($idValid);
            if(array_key_exists($validIdKey, $orig)){
                //don't try to update if it's on the list to be deleted
                if(!array_key_exists($orig[$validIdKey], $toBeDeleted)){
                    // loop through all fields in existing data to see if they have changed
                    foreach($data[$orig[$validIdKey]] as $key2=>$value){
                        if($value != $orig[$key2]){
                            $updatedRow[$orig[$validIdKey]] = $data[$orig[$validIdKey]];
                        }

                    }
                }
            }
        }
        $this->processUpdates($updatedRow, $element);
     }
     /*
      * processUpdates
      */
     public function processUpdates($fields, $element) {
       foreach ($fields as $newId => $newFields) {

           $model = $this->sourceModelReader->getModel($element->getSourceModel())->load($newId);

           //this is added because the shipping method saves the active carriers name as the shipping_method, using the shipping method code
           if($element->getSourceModel() == 'epicor_comm/erp_mapping_shippingmethod'){
               $newFields['shipping_method'] = $this->activeCarriers[$newFields['shipping_method_code']];
           }

           $mappingFields = $element->getMappingFields();
           foreach ($newFields as $key3 => $value3) {
               if (is_array($value3) && $mappingFields[$key3]['type'] == 'multiselect') {
                   $value3 = implode(', ', $value3);
               }
               $model->setData($key3, $value3);

           }
           if ($element->getFieldsToFilter()) {
               foreach ($element->getFieldsToFilter()->getData() as $filter_field => $filter_value) {
                   $model->setData($filter_field, $filter_value);
               }
           }
           try {
               $model->save();
               unset($model);
           } catch (\Exception $e) {
               $error[] = $e->getMessage();
           }
        }
    }
     /*
     * Insert multiple rows at once
     */
    public function multiInsert(){
          foreach($this->rowsToAdd as $table=>$rows){
                foreach($rows as $row){
                    $this->connection->insertMultiple($table, $row);
                }
          }
          //empty rowsToAdd array
          $this->rowsToAdd = [];
    }

}