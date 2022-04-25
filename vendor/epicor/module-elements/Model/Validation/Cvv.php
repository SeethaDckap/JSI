<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elements\Model\Validation;

class Cvv
{
    const CONFIG_ENABLE = "payment/elements/CVVEnabled";

    const SUCCESS_RESULTS = "payment/elements/cvv_successful_results";

    const TYPE = "CVV";

    /**
     * @var \Epicor\Elements\Model\Config\Source\Cvvresults
     */
    private $cvvResultsArray;

    /**
     * @var string
     */
    private $error = "";

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Cvv constructor.
     *
     * @param \Epicor\Elements\Model\Config\Source\Cvvresults $cvvResultsArray
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Epicor\Elements\Model\Config\Source\Cvvresults $cvvResultsArray,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->cvvResultsArray = $cvvResultsArray;
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
        $ResultsArrayOption = $this->cvvResultsArray->toOptionArray();

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
                    foreach ($ResultsArrayOption as $option) {
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