<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Sync;


class Stores
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    public function __construct(
        \Magento\Store\Model\System\Store $storeSystemStore
    ) {
        $this->storeSystemStore = $storeSystemStore;
    }
    public function toOptionArray($noSelectedText = false)
    {
        $storeModel = $this->storeSystemStore;
        /* @var $storeModel Mage_Adminhtml_Model_System_Store */

        $options = array();

        if (is_string($noSelectedText)) {
            $options[] = array(
                'label' => $noSelectedText,
                'value' => ''
            );
        }

        foreach ($storeModel->getWebsiteCollection() as $website) {
            /* @var $website Mage_Core_Model_Website */
            $websiteShow = false;
            $groupOptions = array();

            foreach ($storeModel->getGroupCollection() as $group) {
                /* @var $group Mage_Core_Model_Store_Group */
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }

                $websiteShow = true;
                $groupOptions[] = array(
                    'label' => $group->getName(),
                    'value' => 'store_' . $group->getDefaultStoreId()
                );
            }

            if ($websiteShow) {
                $options[] = array(
                    'label' => $website->getName(),
                    'value' => 'website_' . $website->getId()
                );

                if (!empty($groupOptions)) {
                    $options[] = array(
                        'label' => 'Store Groups',
                        'value' => $groupOptions
                    );
                }
            }
        }

        return $options;
    }

}
