<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Listing\Renderer;


/**
 * Location display
 *
 * @author Gareth.James
 */
class Location extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        array $data = []
    ) {
        $this->commLocationsHelper = $commLocationsHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $location = $row->getLocationCode();
        if ($row->getLocationCode()) {
            $locHelper = $this->commLocationsHelper;
            $location = $locHelper->getLocationName($row->getLocationCode());
            if (!$location) {
                $location = $row->getLocationCode();
            }
        }
        return $location;
    }

}
