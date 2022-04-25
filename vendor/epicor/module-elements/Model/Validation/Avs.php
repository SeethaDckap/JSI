<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elements\Model\Validation;

class Avs
{
    const CONFIG_ENABLE = "payment/elements/AVSEnabled";

    const SUCCESS_RESULTS = "payment/elements/avs_successful_results";

    const TYPE = "AVS";

    /**
     * @var \Epicor\Elements\Model\Config\Source\Avsresults
     */
    private $avsResultsArray;

    /**
     * @var string
     */
    private $error = "";

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Avs constructor.
     *
     * @param \Epicor\Elements\Model\Config\Source\Avsresults $avsResultsArray
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Epicor\Elements\Model\Config\Source\Avsresults $avsResultsArray,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->avsResultsArray = $avsResultsArray;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string|null $responseCode
     *
     * @return bool
     */
    public function validate($responseCode)
    {
        $success = true;
        $resultsArrayOption = $this->avsResultsArray->toOptionArray();

        if ($this->scopeConfig->isSetFlag(self::CONFIG_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $successfulResults = explode(',',
                $this->scopeConfig->getValue(self::SUCCESS_RESULTS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );

            if (!in_array($responseCode, $successfulResults)) {
                $error = self::TYPE . " validation failed: ";
                if ($responseCode === null) {
                    $error .= self::TYPE . " Response Code Getting Blank.";
                } else {
                    $errorLabels = $responseCode;
                    foreach ($resultsArrayOption as $option) {
                        if ($responseCode == $option["value"]) {
                            $errorLabels = $option["label"];
                        }
                    }
                    $error .= $errorLabels;
                }

                $this->setError($error);
                $success = false;
            }
        }

        return $success;
    }

    public function setError($error = "")
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }
}