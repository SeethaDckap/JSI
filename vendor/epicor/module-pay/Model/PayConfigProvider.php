<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Pay\Model;

use Epicor\Pay\Model\Config\Source\PonOptions;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

class PayConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string
     */
    protected $methodCode = 'pay';

    /**
     * @var Alipay
     */
    protected $method;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ScopeConfigInterface|null
     */
    private $scopeConfig;


    /**
     * @param PaymentHelper $paymentHelper
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface|null $scopeConfig
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig = null
    )
    {
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    private function getRedirectUrl()
    {
        return $this->urlBuilder->getUrl('checkout/onepage/success/');
    }

    /**
     * @return bool
     */
    private function poVisibility()
    {
        $empPoConfig = $this->getPoValue();

        if (($empPoConfig == PonOptions::PON_MANDATORY) || ($empPoConfig == PonOptions::PON_VISIBLE)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function poMandatory()
    {
        $empPoConfig = $this->getPoValue();

        if ($empPoConfig == PonOptions::PON_MANDATORY) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    private function getPoValue()
    {
        return $this->scopeConfig->getValue('payment/pay/epm_po');
    }

    /**
     * @return mixed
     */
    private function getPoMaxLength()
    {
        $length =  $this->scopeConfig->getValue('checkout/options/max_po_length');
        if ($length != '' || $length != null) {
            return $length;
        }
        return '255';
    }

    /**
     * @return mixed|string|void
     */
    private function poTitle()
    {
        return _('Purchase Order Number');
    }

    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                $this->methodCode => [
                    'redirectUrl' => $this->getRedirectUrl(),
                    'message' =>  $this->method->getMessage(),
                    'poVisibility' => $this->poVisibility(),
                    'poMandatory' => $this->poMandatory(),
                    'poTitle' => $this->poTitle(),
                    'poMaxLength' => $this->getPoMaxLength()
                ]
            ]
        ] : [];
    }
}