<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Comm\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class MessageStatus extends \Magento\Ui\Component\Listing\Columns\Column
{
    private $log;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Epicor\Comm\Model\Message\Log $messageLog,
        array $components = [],
        array $data = [])
    {
        $this->log = $messageLog;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }


    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $option = $this->optionArray();
                $item[$this->getData('name')] = $option[$item['message_status']];
            }
        }

        return $dataSource;
    }

    private function optionArray()
    {
        return $this->log->getMessageStatuses();
    }
}