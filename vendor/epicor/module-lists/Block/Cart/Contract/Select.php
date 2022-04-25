<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Caer\Contract;

/**
 * Contract select page grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Select extends \Epicor\Common\Block\Generic\Listing {

    protected function _setupGrid() {
        $this->_controller = 'cart_contractselectgrid';
        $this->_blockGroup = 'Epicor_Lists';
        $this->_headerText = __('Select Contract');
        $this->removeButton('add');
    }

    protected function _postSetup() {
        $this->setBoxed(true);
        parent::_postSetup();
    }

}
