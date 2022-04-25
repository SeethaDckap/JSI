<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Block\Adminhtml\Connections\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{


    /**
     * Get button data.
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $id   = $this->getRequest()->getParam('entity_id', null);
        if ($id && $this->canRender('delete')) {
            $data = [
                'label'      => __('Delete'),
                'class'      => 'delete',
                'on_click'   => 'deleteConfirm(\''.__(
                    'Are you sure you want to do this?'
                ).'\', \''.
                    $this->urlBuilder->getUrl(
                        '*/*/delete',
                        ['entity_id' => $id]
                    ).'\', {data: {}})',
                'sort_order' => 20,
            ];
        }

        return $data;

    }//end getButtonData()


}//end class
