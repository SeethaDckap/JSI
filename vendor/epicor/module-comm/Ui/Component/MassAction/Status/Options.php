<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Comm\Ui\Component\MassAction\Status;


class Options implements \Zend\Stdlib\JsonSerializable
{
    /**
     * @var array
     */
    protected $options;

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
     * Additional options params
     *
     * @var array
     */
    protected $data;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        $data = []
    )
    {
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
    }


    function jsonSerialize()
    {
        if ($this->options === null) {
            $options = [
                [
                    'value' => 0,
                    'label' => 'Order Not Sent'
                ],
                [
                    'value' => 1,
                    'label' => 'Order Sent'
                ],
                [
                    'value' => 3,
                    'label' => 'Erp Error'
                ],
                [
                    'value' => 4,
                    'label' => 'Error - Retry Attempt Failure'
                ],
                [
                    'value' => 5,
                    'label' => 'Order Never Send'
                ],
            ];

            $this->prepareData();
            foreach ($options as $optionCode) {
                $this->options[$optionCode['value']] = [
                    'type' => 'customer_group_' . $optionCode['value'],
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

            $this->options = array_values($this->options);

        }

        return $this->options;
    }

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