<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Ui\Component\Listing\Column\Product;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Product Location Actions
 */ 
class ProductlocationsActions extends Column
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
            $item[$this->getData('name')]['edit'] = [
                'href' => $this->context->getUrl(
                    'adminhtml/epicorcomm_locations/edit',
                    ['id' => $item['id']]
                ),
                'label' => __('View'),
                'hidden' => false,
            ];
            $item[$this->getData('name')]['delete'] = [
                'href' => $this->context->getUrl(
                    'adminhtml/epicorcomm_catalog_product/deletelocation',
                    ['id' => $item['id'],'productId' => $item['product_id']]
                ),
                'label' => __('Delete'),
                'hidden' => false,
            ];
        }

        return $dataSource;
    }
}
