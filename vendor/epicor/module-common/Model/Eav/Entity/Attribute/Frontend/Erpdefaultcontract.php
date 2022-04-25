<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Eav\Entity\Attribute\Frontend;


class Erpdefaultcontract extends \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
{

    /**
     * Retrieve Input Renderer Class
     *
     * @return string
     */
    public function getInputRendererClass()
    {
        return 'Epicor_Common_Block_Adminhtml_Form_Element_Erpdefaultcontract';
    }

}
