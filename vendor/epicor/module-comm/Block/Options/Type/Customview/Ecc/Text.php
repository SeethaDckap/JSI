<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Options\Type\Customview\Ecc;

class Text extends \Magento\Framework\View\Element\Template
{

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, array $data = [])
    {
        parent::__construct(
            $context, $data
        );
    }

    protected $_template = 'epicor_comm/options/customview/ecc/text.phtml';

}
