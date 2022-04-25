<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Contract;

class Link extends \Magento\Framework\View\Element\Html\Link {

    /**
     * @var array
     */
    public $contractAllowed = true;

    protected function _toHtml() {
        if ($this->contractAllowed) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }

}
