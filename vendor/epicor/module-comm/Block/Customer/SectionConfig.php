<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer;

use Magento\Framework\App\ObjectManager;

class SectionConfig extends \Magento\Customer\Block\SectionConfig
{

    /**
     * Magento 2.3.4 compatibility issue with sectionNamesProvider
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Config\DataInterface $sectionConfig
     * @param array $data
     * @param string[] $clientSideSections
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Config\DataInterface $sectionConfig,
        array $data = [],
        array $clientSideSections = []
    )
    {
        if(class_exists(\Magento\Customer\Block\SectionNamesProvider::class)) {
            $data["sectionNamesProvider"] = ObjectManager::getInstance()->get(\Magento\Customer\Block\SectionNamesProvider::class) ?: null;
        }
        parent::__construct($context,$sectionConfig, $data,$clientSideSections);
    }
}
