<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Ui\Component\Listing\Column\Product;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Customer sku Actions
 */ 
class ProductmessagelogActions extends Column
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
            $item[$this->getData('name')]['view'] = [
                'href' => $this->context->getUrl(
                    'adminhtml/epicorcomm_message_log/view',
                    ['id' => $item['id']]
                ),
                'label' => __('View'),
                'hidden' => false,
            ];
            $item[$this->getData('name')]['reprocess'] = [
                'href' => $this->context->getUrl(
                    'adminhtml/epicorcomm_message_log/reprocess',
                    ['id' => $item['id']]
                ),
                'label' => __('Reprocess'),
                'hidden' => false,
            ];
        }

        return $dataSource;
    }
}
