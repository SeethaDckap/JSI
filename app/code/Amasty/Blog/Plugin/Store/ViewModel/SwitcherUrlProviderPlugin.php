<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Blog
 */


declare(strict_types=1);

namespace Amasty\Blog\Plugin\Store\ViewModel;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\ViewModel\SwitcherUrlProvider;
use Zend\Uri\Http;

class SwitcherUrlProviderPlugin
{
    const STORE_PARAM_NAME = '___store';
    const FROM_STORE_PARAM_NAME = '___from_store';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Http
     */
    private $http;

    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        EncoderInterface $encoder,
        StoreManagerInterface $storeManager,
        Http $http
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->encoder = $encoder;
        $this->storeManager = $storeManager;
        $this->http = $http;
    }

    /**
     * @param SwitcherUrlProvider $subject
     * @param callable $proceed
     * @param Store $store
     * @return string
     */
    public function aroundGetTargetStoreRedirectUrl(SwitcherUrlProvider $subject, callable $proceed, Store $store)
    {
        if ($this->request->getModuleName() == 'amblog') {
            $params = [
                self::STORE_PARAM_NAME => $store->getCode(),
                self::FROM_STORE_PARAM_NAME => $this->storeManager->getStore()->getCode(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->encoder->encode($this->getUrlWithoutParams()),
            ];
            $url = $this->urlBuilder->getUrl('stores/store/redirect', $params);
        } else {
            $url = $proceed($store);
        }

        return $url;
    }

    private function getUrlWithoutParams(): string
    {
        $params['_use_rewrite'] = true;
        $currentUrl = $this->urlBuilder->getUrl('*/*/*', $params);
        $uri = $this->http->parse($currentUrl);

        return $uri->getScheme() . '://' . $uri->getHost() . $uri->getPath();
    }
}
