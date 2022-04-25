<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper\Messaging;


/**
 * 
 * Messaging cache helper
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Cache extends \Epicor\Common\Helper\Data
{

    private $_cacheState;

    /**
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXml;

    /**
     * @var \Epicor\Common\Model\Xmlvarien
     */
    protected $commonXmlvarien;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
    
    public function __construct(
        \Epicor\Common\Helper\Context $context,
        \Magento\Framework\App\Cache\StateInterface $state,
        \Epicor\Common\Helper\Xml $commonXml,
        \Epicor\Common\Model\Xmlvarien $commonXmlvarien
    ) {
        $this->commonXml = $commonXml;
        $this->commonXmlvarien = $commonXmlvarien;
        $this->_cacheState = $state;
        $this->commHelper = $context->getCommHelper();
        parent::__construct($context);
    }
    /**
     * Gets the cache for a given message & keys
     * 
     * @param string $message
     * @param array $keys
     * 
     * @return mixed
     */
    public function getCache($message, $keys)
    {
        $data = false;
        //M1 > M2 Translation Begin (Rule 12)
        //if (Mage::app()->useCache('message')) {
        if ($this->_cacheState->isEnabled(\Epicor\Common\Model\Cache\Type\Message::TYPE_IDENTIFIER)) {
            //$cache = Mage::app()->getCacheInstance();
            $cache = $this->cache;
            //M1 > M2 Translation End
            /* @var $cache Mage_Core_Model_Cache */
            $cacheKey = $message . '_' . md5(implode('_', $keys));

            $data = $cache->load($cacheKey);

            if (!empty($data)) {
                //M1 > M2 Translation Begin (Rule 65)
                //$data = unserialize($data);
                $data = unserialize($data);
                if(in_array($message,$this->commHelper->getArrayMessages())){
                    $data = $this->commonXml->convertXmlToArraynew($data);
                    $data = $data['messages'];
                }else{                
                    $data = $this->commonXml->convertXmlToVarienObject($data);
                    $data = $data->getData('product');
                }
                //M1 > M2 Translation End
            }
        }

        return $data;
    }

    /**
     * Sets the cache for a given message & 
     * 
     * @param string $message
     * @param array $keys
     * @param mixed $data
     * 
     */
    public function setCache($message, $keys, $data, $customLifeTime = null)
    {

        //M1 > M2 Translation Begin (Rule 12)
        //if (Mage::app()->useCache('message')) {
        if ($this->_cacheState->isEnabled(\Epicor\Common\Model\Cache\Type\Message::TYPE_IDENTIFIER)) {
            //$cache = Mage::app()->getCacheInstance();
            $cache = $this->cache;
            /* @var $cache Mage_Core_Model_Cache */
            //M1 > M2 Translation End
            $cacheKey = $message . '_' . md5(implode('_', $keys));
            $lifeTime = $customLifeTime ?: $this->scopeConfig->getValue('Epicor_Comm/caching/message_lifetime', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $tags = array(
                'MESSAGE',
                strtoupper($message)
            );

            foreach ($keys as $key) {
                if (!empty($key)) {
                    $tags[] = $key;
                }
            }
            //M1 > M2 Translation Begin (Rule 65)
            //$cache->save(serialize($data), $cacheKey, $tags, $lifeTime);
            if(in_array($message,$this->commHelper->getArrayMessages())){
                $data2['product'] = $data;
                $data = $this->commonXml->convertArrayToXml($data2);
            }else{                
                $data = $this->commonXmlvarien->setData('product',$data);
                $data = $this->commonXml->convertVarienObjectToXml($data);
            }
            $cache->save(serialize($data), $cacheKey, $tags, $lifeTime);
            //M1 > M2 Translation End
        }
    }

    /**
     * Delete Cache for the given keys
     * 
     * @param array $keys
     * 
     */
    public function deleteCache($keys)
    {

        //M1 > M2 Translation Begin (Rule 12)
        //if (Mage::app()->useCache('message')) {
        if ($this->_cacheState->isEnabled(\Epicor\Common\Model\Cache\Type\Message::TYPE_IDENTIFIER)) {
            //$cache = Mage::app()->getCacheInstance();
            $cache = $this->cache;
            //M1 > M2 Translation End
            /* @var $cache Mage_Core_Model_Cache */

            foreach ($keys as $key) {
                $cache->remove($key);
            }
        }
    }

    public function cleanCache($keys)
    {
        //M1 > M2 Translation Begin (Rule 12)
        //if (Mage::app()->useCache('message')) {
        if ($this->_cacheState->isEnabled(\Epicor\Common\Model\Cache\Type\Message::TYPE_IDENTIFIER)) {
            //$cache = Mage::app()->getCacheInstance();
            $cache = $this->cache;
            //M1 > M2 Translation End
            /* @var $cache Mage_Core_Model_Cache */
            $cache->clean($keys);
        }
    }

}
