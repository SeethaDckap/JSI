<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\App\PageCache;

use Magento\Framework\App\ObjectManager;
use Epicor\Comm\Model\Serialize\Serializer\Json;

/**
 * Page unique identifier
 */
class Identifier extends \Magento\Framework\App\PageCache\Identifier
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend
     */
    protected $frontendHelper;

    protected $branchPickupHelper;

    protected $customerSession;

    /**
     * Identifier constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context $context
     * @param Json|null $serializer
     * @param \Epicor\Comm\Helper\Locations $commLocationsHelper
     * @param \Epicor\Lists\Helper\Frontend $frontendHelper
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Http\Context $context,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\Lists\Helper\Frontend $frontendHelper,
        \Epicor\BranchPickup\Helper\Data $BranchPickupHelper,
        \Magento\Customer\Model\Session $customerSession,
        Json $serializer = null
    ) {
        $this->request = $request;
        $this->context = $context;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->frontendHelper = $frontendHelper;
        $this->branchPickupHelper = $BranchPickupHelper;
        $this->customerSession = $customerSession;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Return unique page cache identifier
     *
     * @return string
     */
    public function getValue()
    {

        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $punchoutKey = '-PO';
        if($customer){
            $checkCustomer = $customer->getEccIsBranchPickupAllowed();
            $ErpAccount = $customer->getCustomerErpAccount();
            if($ErpAccount && $checkCustomer == "2"){
                $checkErp = $ErpAccount->getIsBranchPickupAllowed();
                if($checkErp && $checkErp != "2"){
                    $data = [
                        $this->request->isSecure(),
                        $this->request->getUriString(),
                        $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                            ?: $this->context->getVaryString(),
                        "branchpickup"
                    ];
                    if ($this->customerSession->getIsPunchout()) {
                        $data[] = $punchoutKey;
                    }
                    return sha1($this->serializer->serialize($data));
                }
            } else if($checkCustomer){
                $data = [
                    $this->request->isSecure(),
                    $this->request->getUriString(),
                    $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                        ?: $this->context->getVaryString(),
                    "branchpickup"
                ];
                if ($this->customerSession->getIsPunchout()) {
                    $data[] = $punchoutKey;
                }
                return sha1($this->serializer->serialize($data));
            }
        }
        if ($this->branchPickupHelper->checkGlobalBranchPickupAllowed()) {
            $data = [
                $this->request->isSecure(),
                $this->request->getUriString(),
                $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                    ?: $this->context->getVaryString(),
                "branchpickup"
            ];
            if ($this->customerSession->getIsPunchout()) {
                $data[] = $punchoutKey;
            }
            return sha1($this->serializer->serialize($data));
        }
        $fpcIdentifier = "";
        $data = [
            $this->request->isSecure(),
            $this->request->getUriString(),
            $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                ?: $this->context->getVaryString(),
        ];
        if ($this->customerSession->getIsPunchout()) {
            $data[] = $punchoutKey;
        }
        if ($this->frontendHelper->listsEnabled()) {
            $fpcIdentifier = $this->frontendHelper->getEscapedActiveLists();
        }
        if ($this->commLocationsHelper->isLocationsEnabled() == true) {
            if ($fpcIdentifier != "") {
                $fpcIdentifier = $fpcIdentifier . ",";
            }
            $fpcIdentifier = $fpcIdentifier . $this->commLocationsHelper->getEscapedCustomerDisplayLocationCodes();
        }
        if ($fpcIdentifier != "") {
            $data[] = $fpcIdentifier;
        }
        return sha1($this->serializer->serialize($data));
    }
}
