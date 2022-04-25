<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml\Sites\Column\Renderer;


/**
 * Renderer for Sites > Stores column, shows list of stores for the site
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Stores extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\System\Store $storeSystemStore,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->storeSystemStore = $storeSystemStore;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {

        $output = '<dl>';
        if ($row->getIsDefault()) {
            //M1 > M2 Translation Begin (Rule p2-6.5)
            //$defaultStore = Mage::app()->getDefaultStoreView();
            $defaultStore = $this->storeManager->getDefaultStoreView();
            //M1 > M2 Translation End
            $website = $defaultStore->getWebsite();
            $output .= '<dt>Website:</dt>';
            if ($website) {
                $output .= '<dd>' . $website->getName() . '</dd>';
            }
            $output .= '<dt>Stores:</dt>';
            foreach ($website->getStores() as $store) {
                if (!in_array($store->getId(), $row->getIgnoreStores()) || $store->getId() == $defaultStore->getId()) {
                    $output .= '<dd>' . $store->getName() . '</dd>';
                }
            }
        } elseif ($row->getIsWebsite()) {
            try {
                $output .= '<dt>Website:</dt>';
                $website = $this->storeManager->getWebsite($row->getChildId());
                if ($website) {
                    $output .= '<dd>' . $website->getName() . '</dd>';
                }
                $output .= '<dt>Stores:</dt>';
                foreach ($this->_getStores() as $store) {
                    if (!in_array($store->getId(), $row->getIgnoreStores()) && $store->getWebsiteId() == $row->getChildId()) {
                        $output .= '<dd>' . $store->getName() . '</dd>';
                    }
                }
            } catch (\Exception $e) {
                $output = '<dl><dt>Website Not Found. Website may have been Deleted</dt>';
            }
        } else {
            try {
                $store = $this->storeManager->getStore($row->getChildId());
                $website = $store->getWebsite();
                $output .= '<dt>Website:</dt>';
                if ($website) {
                    $output .= '<dd>' . $website->getName() . '</dd>';
                }
                $output .= '<dt>Stores:</dt>';
                if ($store) {
                    $output .= '<dd>' . $store->getName() . '</dd>';
                }
            } catch (\Exception $e) {
                $output = '<dl><dt>Store Not Found. Store may have been deleted</dt>';
            }
        }

        $output .= '</dl>';
        return $output;
    }

    private function _getStores()
    {
        if (!$this->registry->registry('stores_list')) {
            $storeModel = $this->storeSystemStore;
            /* @var $storeModel Mage_Adminhtml_Model_System_Store */
            $stores = $storeModel->getStoreCollection();
            $this->registry->register('stores_list', $stores);
        } else {
            $stores = $this->registry->registry('stores_list');
        }

        return $stores;
    }

}
