<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Cre\Model\Payment;

use Epicor\Cre\Helper\CreData;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigProvider
 * @package Epicor\Cre\Model\Payment
 */
class ConfigProvider implements ConfigProviderInterface
{
    const XML_PAYMENT_CRE_INSTRUCTIONS = 'payment/cre/instructions';

    /**
     * @var CreData
     */
	private $creRequestData;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigProvider constructor.
     * @param CreData $creData
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
		CreData $creData,
        ScopeConfigInterface $scopeConfig = null
	) {
		$this->creRequestData = $creData;
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * @return mixed
     */
    private function getInstructions()
    {
        return $this->scopeConfig->getValue(
            self::XML_PAYMENT_CRE_INSTRUCTIONS,
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
				'cre' =>  [
                    'data' =>$this->creRequestData->generateCreRequestData(),
                    'sdkUrl' => $this->getSdkUrl(),
                    'instructions' => $this->getInstructions()
                ],
			]
		];
	}

    /**
     * @return string
     */
    public function getSdkUrl()
    {
    	$isLive = $this->creRequestData->getLive();
    	if($isLive) {
    		$jsUrl = $this->creRequestData->getConfigValue('live_url');
    	} else {
    		$jsUrl = $this->creRequestData->getConfigValue('test_url');
    	}

        return  $jsUrl;
    }

}
