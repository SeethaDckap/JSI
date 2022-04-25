<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Helper\Advanced;


/**
 * Clear data helper, contains functions for clearing various data types
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Cleardata extends \Epicor\Common\Helper\Advanced\Cleardata
{

    /*
     * @var  \Epicor\Dealerconnect\Model\ResourceModel\EccPac\CollectionFactory
     */
    protected $pacCollection;
    
    protected $_configWriter;    
    
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;      
    
    public function __construct(
        \Epicor\Common\Helper\Context $context,
	\Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Epicor\Faqs\Model\ResourceModel\Vote\CollectionFactory $faqsResourceVoteCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Quotes\Model\ResourceModel\Quote\CollectionFactory $quotesResourceQuoteCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesResourceModelOrderCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Epicor\Dealerconnect\Model\ResourceModel\EccPac\CollectionFactory $pacCollection,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter            
    ) {
        $this->pacCollection = $pacCollection;
        $this->cache = $context->getCache();
        $this->_configWriter = $configWriter;
        
        parent::__construct($context,
                $commResourceCustomerErpaccountCollectionFactory,
		$metadataPool,
		$productMetadata,
                $faqsResourceVoteCollectionFactory,
                $customerResourceModelCustomerCollectionFactory,
                $quotesResourceQuoteCollectionFactory,
                $salesResourceModelOrderCollectionFactory,
                $resourceConnection);
    }
    
    
    /**
     * Deletes all PAC class & attributes & Attributes options 
     */
    public function clearPac()
    {
        $collection = $this->pacCollection->create();
        /* @var $collection \Epicor\Dealerconnect\Model\ResourceModel\EccPac\Collection */
        foreach ($collection->getItems() as $pac_data) {
            $pac_data->delete();
        }
        $this->deleteParentFromDeis();
        $this->deleteParentFromDeid();
    }
    
    
    public  function deleteParentFromDeis()
    {
        $getGridConfig = $this->scopeConfig->getValue("dealerconnect_enabled_messages/DEIS_request/grid_config", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $fields = unserialize($getGridConfig);
        $matchedValues = array();
        $correctValues = array();
        $pacValues = array();
        foreach ($fields as $keys => $fieldsValues) {
            if($fieldsValues['pacattributes'] !="") {
               $explodeVals = explode("_",$fieldsValues['pacattributes']);
               $matchedValues[$keys] = $fieldsValues;
               $pacValues[$fieldsValues['pacattributes']] = $fieldsValues['pacattributes'];
            } else {
               $correctValues[$keys] = $fieldsValues; 
            }
        }
        $this->setPacConfigurations($pacValues);
        $serializedVals = serialize($correctValues);
        $setConfig = $this->setConfig($serializedVals);
    }
    
    
    public  function deleteParentFromDeid()
    {
        $getGridConfig = $this->scopeConfig->getValue("dealerconnect_enabled_messages/DEID_request/grid_informationconfig", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $fields = unserialize($getGridConfig);
        $matchedValues = array();
        $correctValues = array();
        $pacValues = array();
        foreach ($fields as $keys => $fieldsValues) {
            if($fieldsValues['hiddenpac'] !="") {
               $jsonDecode = json_decode($fieldsValues['hiddenpac'],true);
               $matchedValues[$keys] = $fieldsValues;
               $pacValues[$jsonDecode['pacattribute']] = $jsonDecode['pacattribute'];
            } else {
                   $correctValues[$keys] = $fieldsValues; 
            }
        }
        $this->setDeidPacConfigurations($pacValues);
        $serializedVals = serialize($correctValues);
        $setConfig = $this->setDeidConfig($serializedVals);
        $this->cache->clean();
    }    
    
    public function setPacConfigurations($param) {
        $getGridConfig = $this->scopeConfig->getValue("dealerconnect_enabled_messages/DEIS_request/pac", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $explodeVals = explode(",",$getGridConfig);
        $pacArray = array();
        $pacArrayFilter = array();
        if((!empty($explodeVals)) && (!empty($param))) {
            foreach($explodeVals as $pacVals) {
               if(!in_array($pacVals, $param)) {
                 $pacArray[] =   $pacVals;
               } 
            }
            $this->setPacConfig($pacArray);
        }
    }    
    
    
    public function setDeidPacConfigurations($param) {
        $getGridConfig = $this->scopeConfig->getValue("dealerconnect_enabled_messages/DEID_request/pacinfo", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $explodeVals = explode(",",$getGridConfig);
        $pacArray = array();
        $pacArrayFilter = array();
        if((!empty($explodeVals)) && (!empty($param))) {
            foreach($explodeVals as $pacVals) {
               if(!in_array($pacVals, $param)) {
                 $pacArray[] =   $pacVals;
               } 
            }
            $this->setDeidPacConfig($pacArray);
        }
    }      
    
    
    public function setConfig($value)
    {
        $this->_configWriter->save('dealerconnect_enabled_messages/DEIS_request/grid_config', $value, $this->scopeConfig::SCOPE_TYPE_DEFAULT, 0);
        return $this;
    }     
    
    
    public function setPacConfig($value)
    {
        $implode = implode(",",$value);
        $this->_configWriter->save('dealerconnect_enabled_messages/DEIS_request/pac', $implode,$this->scopeConfig::SCOPE_TYPE_DEFAULT, 0);
        return $this;
    } 
    
    
    public function setDeidConfig($value)
    {
        $this->_configWriter->save('dealerconnect_enabled_messages/DEID_request/grid_informationconfig', $value, $this->scopeConfig::SCOPE_TYPE_DEFAULT, 0);
        return $this;
    }     
    
    
    public function setDeidPacConfig($value)
    {
        $implode = implode(",",$value);
        $this->_configWriter->save('dealerconnect_enabled_messages/DEID_request/pacinfo', $implode,$this->scopeConfig::SCOPE_TYPE_DEFAULT, 0);
        return $this;
    }       
    
}