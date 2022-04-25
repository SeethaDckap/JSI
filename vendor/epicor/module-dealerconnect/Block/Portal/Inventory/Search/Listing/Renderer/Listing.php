<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\Search\Listing\Renderer;


/**
 * 
 *
 * @author
 */
class Listing extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Dealer Inventory Listing Type
     * 
     * @var array 
     */
    protected $_listingType = [
        'S' => 'Sale',
        'L' => 'Lease'
    ];
    public function render(\Magento\Framework\DataObject $row)
    {
        $listing = $row->getListing();
        if (isset($this->_listingType[$listing])) {
            return $this->_listingType[$listing];
        }
        return $listing;
    }

}