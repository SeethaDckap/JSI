<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $groupId = $this->getRequest()->getParam('group_id', null);
        if ($groupId && $this->canRender('delete')) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\''.__(
                        'Are you sure you want to do this?'
                    ).'\', \''.
                    $this->urlBuilder->getUrl('*/*/delete',
                        ['group_id' => $groupId]).'\', {data: {}})',
                'sort_order' => 20,
            ];
        }

        return $data;
    }
}
