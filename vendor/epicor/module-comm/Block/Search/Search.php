<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Search;

use \Magento\Framework\View\Element\Template\Context;
/**
 * Class Search
 */
class Search extends \Epicor\AccessRight\Block\Template
{
    const FRONTEND_RESOURCE = 'Epicor_Checkout::catalog_search';


    /**
     * Html.
     *
     * @return string
     */
    public function toHtml()
    {
        if ($this->_isAllowed() === false) {
            return '';
        }

        return parent::toHtml();

    }//end toHtml()


}
