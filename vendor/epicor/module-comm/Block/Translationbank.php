<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block;


/**
 * Translation bank
 * 
 * Add any translations here that are from models/ helpers in the comm module
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Translationbank extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Class constructor
     */
    public function _construct()
    {
        parent::_construct();

        __('Product Codes:');
        __('Product Code:');
    }

}
