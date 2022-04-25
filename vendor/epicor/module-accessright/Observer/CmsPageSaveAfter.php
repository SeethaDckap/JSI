<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Observer;

use Epicor\AccessRight\Service\CmsPages;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CmsPageSaveAfter
 * @package Epicor\AccessRight\Observer
 */
class CmsPageSaveAfter implements ObserverInterface
{
    /**
     * @var CmsPages
     */
    private $cmsPages;

    /**
     * CmsPageSaveAfter constructor.
     * @param CmsPages $cmsPages
     */
    public function __construct(
        CmsPages $cmsPages
    ) {
        $this->cmsPages = $cmsPages;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $cmsObject = $observer->getEvent()->getData('data_object');
        if ($cmsObject->isObjectNew()) {
            $this->cmsPages->addCmsPageInRule($cmsObject->getId());
        }
    }
}
