<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Renderer;


/**
 * Expand column for PO lines
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Expand extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';

        $releasesMsg = $row->getReleases();

        if ($releasesMsg) {
            $releases = $releasesMsg->getasarrayRelease();
            if (count($releases) > 0) {
                $html = '<span class="plus-minus" type="po" id="releases-' . $row->getId() . '">+</span>';
            }
        }

        return $html;
    }

}
