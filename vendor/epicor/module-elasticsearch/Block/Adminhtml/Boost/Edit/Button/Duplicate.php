<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Block\Adminhtml\Boost\Edit\Button;

/**
 * Button for boost duplicate
 *
 */
class Duplicate extends AbstractButton
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getBoost() && $this->getBoost()->getId())
        {
            $data = [
                'label' => __('Duplicate'),
                'class' => 'duplicate',
                'on_click' => sprintf("location.href = '%s';", $this->getUrl('*/*/duplicate', ['id' => $this->getBoost()->getId()])),
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
