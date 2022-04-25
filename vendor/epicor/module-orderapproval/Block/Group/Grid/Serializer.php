<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Block\Group\Grid;

Class Serializer extends \Magento\Backend\Block\Widget\Grid\Serializer
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Common::widget/grid/serializer.phtml');
        $this->setFormId('approval_group_form');
    }
}
