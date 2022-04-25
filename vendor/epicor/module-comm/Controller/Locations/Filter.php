<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Locations;

class Filter extends \Epicor\Comm\Controller\Locations
{

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Framework\Session\Generic $generic
    )
    {
        $this->commLocationsHelper = $commLocationsHelper;
        parent::__construct(
            $context,
            $commProductHelper,
            $catalogProductFactory,
            $generic);
    }

    public function execute()
    {
        if ($data = $this->getRequest()->getPost()) {
            $helper = $this->commLocationsHelper;
            /* @var $helper Epicor_Comm_Helper_Locations */
            $postedLocations = @$data['locations_filter'] ?: array();
            $helper->setCustomerDisplayLocationCodes($postedLocations);
            $this->_redirect($helper->getUrlDecoder()->decode($data['return_url']));
        }
    }

}
