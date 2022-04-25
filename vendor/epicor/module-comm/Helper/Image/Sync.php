<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper\Image;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * This class is contains commen functions for syncing images from ERP to magento
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Sync extends \Epicor\Comm\Helper\Data
{

    protected $_validStores = array();

    /**
     * Checks if the given erp image has some valid stores
     * 
     * @param array $erpImage
     * 
     * @return boolean
     */
    public function hasValidStores($erpImage)
    {
        $validStores = false;

        if (isset($erpImage['stores']) && empty($erpImage['stores']) == false) {
            foreach ($erpImage['stores'] as $storeId) {
                if ($this->isValidStore($storeId)) {
                    $validStores = true;
                }
            }
        }

        return $validStores;
    }

    /**
     * Checks if the given store is valid
     * 
     * @param integer $storeId
     * 
     * @return boolean
     */
    public function isValidStore($storeId)
    {
        if (isset($this->_validStores[$storeId]) == false) {
            try {
                $store = $this->storeManager->getStore($storeId);
                $this->_validStores[$storeId] = true;
            } catch (NoSuchEntityException $noSuchEntityException) {
                $this->_validStores[$storeId] = false;
            } catch (Exception $ex) {
                $this->_validStores[$storeId] = false;
            }
        }

        return $this->_validStores[$storeId];
    }
    
    /**
     * Returns the assets folder where images are copied
     *
     * @return string
     */
    public function getMediaFolder()
    {
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //return Mage::getBaseDir() . DS . str_replace('/', DS, $this->scopeConfig->getValue('Epicor_Comm/assets/product_image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . DIRECTORY_SEPARATOR;
        //M1 > M2 Translation End
    }

}
