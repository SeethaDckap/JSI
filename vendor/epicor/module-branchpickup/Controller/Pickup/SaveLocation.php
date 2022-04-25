<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Controller\Pickup;

class SaveLocation extends \Epicor\BranchPickup\Controller\Pickup
{


    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $commLocationFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\LocationFactory $commLocationFactory,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory
    ) {
        $this->commLocationFactory = $commLocationFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->response = $context->getResponse();
        parent::__construct(
            $context,$branchPickupHelper,$customerSession
        );
    }


public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            if ($data['locationid']) {
                $location = $this->commLocationFactory->create()->load($data['locationid']);
                /* @var $location Epicor_Comm_Model_Location */
                $regionExists =false;
                if (isset($data['county_id'])) {
                    $region = $this->directoryRegionFactory->create()->load($data['county_id']);
                    /* @var $region Mage_Directory_Model_Region */
                    $data['county_code'] = $region->getCode();
                } else {
                    if(isset($data['region'])) {
                      $data['county'] = $data['region'];
                      $regionExists = true;
                    }
                }
                $location->addData($data);
                
                if($regionExists) {
                    $location->setCounty($data['region']);
                    $location->setCountyCode($data['region']);
                }
                if (!$location->getSource()) {
                    $location->setSource('web');
                    $location->setDummy(0);
                }
                $location->save();
                $result['type'] = 'success';
                $result['data'] = $data;
                $this->response->setHeader('Content-type', 'application/json');
                $this->response->setBody(json_encode($result));
            }
        }
    }

}