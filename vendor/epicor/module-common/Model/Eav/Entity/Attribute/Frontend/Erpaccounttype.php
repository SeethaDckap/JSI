<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Eav\Entity\Attribute\Frontend;


class Erpaccounttype extends \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
{

    /**
     * Retrieve Input Renderer Class
     *
     * @return string
     */
    public function getInputRendererClass()
    {
        return 'Epicor\Common\Block\Adminhtml\Form\Element\Erpaccounttype';
    }

}
