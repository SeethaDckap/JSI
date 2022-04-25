<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Esdm\Model\Payment;

use Epicor\Esdm\Helper\ClientTokenData;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const XML_PAYMENT_ESDM_INSTRUCTIONS = 'payment/esdm/instructions';

	private $tokenRequestData;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigProvider constructor.
     * @param ClientTokenData $clientTokenData
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
		ClientTokenData $clientTokenData,
        ScopeConfigInterface $scopeConfig = null
	) {
		$this->tokenRequestData = $clientTokenData->generateTokenRequestData();
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * @return mixed
     */
    private function getInstructions()
    {
        return $this->scopeConfig->getValue(
            self::XML_PAYMENT_ESDM_INSTRUCTIONS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array|array[]
     */
	public function getConfig()
	{
	    $data = $this->tokenRequestData;
	    $data['instructions'] = $this->getInstructions();

		return [
			'payment' => [
				'esdm' => $data,
			]
		];
	}
}
