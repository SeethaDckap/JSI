<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Ui\Component\Listing\Column\Product;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Customer sku Actions
 */ 
class CustomerskuActions extends Column
{
    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $item[$this->getData('name')]['delete'] = [
                'href' => $this->context->getUrl(
                    'adminhtml/epicorcomm_customer_erpaccount/productcustomerskudelete',
                    ['entity_id' => $item['entity_id'], 'productId' => $item['product_id']]
                ),
                'label' => __('Delete'),
                'hidden' => false,
            ];
        }

        return $dataSource;
    }
}
