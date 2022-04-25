<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elements\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ElementsConfigProvider
 * @package Epicor\Elements\Model
 */
class ElementsConfigProvider implements ConfigProviderInterface
{
    const XML_PAYMENT_ELEMENT_INSTRUCTIONS = 'payment/elements/instructions';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ElementsConfigProvider constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    private function getInstructions()
    {
        return $this->scopeConfig->getValue(
            self::XML_PAYMENT_ELEMENT_INSTRUCTIONS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array|\array[][]
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'elements' => [
                    'instructions' => $this->getInstructions()
                ]
            ]
        ];
    }
}
