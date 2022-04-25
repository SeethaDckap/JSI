<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Ui\Component\Listing\Column\Product;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Manufacturers extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.manufacturers';
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
                    $manufacturers = unserialize($item[$fieldName]);
                    if (!empty($manufacturers)) {
                        foreach ($manufacturers as $manufacturer) {
                            if ($manufacturer['primary'] == 'Y') {
                                $html .= '<strong>';
                            }

                            $html .= $manufacturer['name'];

                            if (!empty($manufacturer['product_code'])) {
                                $html .= ' | ' . __('SKU') . ': ' . $manufacturer['product_code'];
                            }

                            if ($manufacturer['primary'] == 'Y') {
                                $html .= '</strong>';
                            }

                            $html .= '<br />';
                        }
                    }
                    $item[$fieldName] = $html;
                }
            }
        }

        return $dataSource;
    }
}
