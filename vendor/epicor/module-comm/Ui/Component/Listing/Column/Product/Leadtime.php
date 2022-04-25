<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Ui\Component\Listing\Column\Product;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Leadtime extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.lead_time_days';
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
           
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $html = '';
                    $days = $item[$fieldName];
                    $text = $item['lead_time_text'];
                    if (!is_null($days)) {
                        $html .= '<strong>' . __('Days') . ':</strong> <span class="col-lead_time_days">' . $item[$fieldName] . '</span><br />';
                    }

                    if (!is_null($text)) {
                        $html .= '<strong>' . __('Text') . ':</strong> <span class="col-lead_time_text">' . $text . '</span>';
                    }
        
                    $item[$fieldName] = $html;
                }
            }
        }

        return $dataSource;
    }
}
