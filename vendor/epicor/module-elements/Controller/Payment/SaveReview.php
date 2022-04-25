<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elements\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class SaveReview extends Action
{
    /**
     * @var StoreManagerInterface|null
     */
    protected $storeManager;

    /**
     * SaveReview constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        StoreManagerInterface $storeManager = null
    ) {
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        parent::__construct(
            $context
        );
    }

    /**
     * @param $id
     * @param string $text
     * @return false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function prepareRefValue($id, $text = '')
    {
        $url = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);

        // Remove http or https from the url
        $disallowed = array('http://', 'https://');
        foreach($disallowed as $d) {
            if(strpos($url, $d) === 0) {
                $url = str_replace($d, '', $url);
            }
        }

        // trim trailing slash from the url
        $url = rtrim($url,"/");

        if ($text != '') {
            $ref = $id . ' : ' . $url;
        } else {
            $ref = $text . $id . ' : ' . $url;
        }

        // trim to 50 characters
        $ref = strlen($ref) > 50 ? substr($ref,0,50) : $ref;
        return $ref;
    }
}
