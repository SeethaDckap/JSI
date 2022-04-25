<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Ui\Component\MassAction\Customer;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;

/**
 * Class Options
 */
class Erpaccounttype implements JsonSerializable
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $accountType;

    /**
     * Additional options params
     *
     * @var array
     */
    protected $data;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $urlPath;

    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $paramName;

    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $additionalData = [];

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Epicor\Comm\Model\Source\Erpaccounttype $accountType,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->accountType = $accountType;
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if ($this->options === null) {
            $options = $this->accountType->toOptionArray();
            $this->prepareData();
            foreach ($options as $optionCode) {
                if(isset($optionCode['value'])){
                    $this->options[$optionCode['value']] = [
                    'type' => 'account_type' . $optionCode['value'],
                    'label' => $optionCode['label'],
                    ];

                    if ($this->urlPath && $this->paramName) {
                        $this->options[$optionCode['value']]['url'] = $this->urlBuilder->getUrl(
                            $this->urlPath,
                            [$this->paramName => $optionCode['value']]
                        );
                    }

                    $this->options[$optionCode['value']] = array_merge_recursive(
                        $this->options[$optionCode['value']],
                        $this->additionalData
                    );
                }
            }

            $this->options = array_values($this->options);
        }

        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
