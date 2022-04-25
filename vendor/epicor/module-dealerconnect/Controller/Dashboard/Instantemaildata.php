<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Dashboard;

use Magento\Framework\Controller\Result\JsonFactory;

class Instantemaildata extends \Epicor\Dealerconnect\Controller\Dashboard
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $customerSession;

    protected $dealerReminderFactory;

    /**
     * @var JsonFactory
     */
    protected $jsonResultFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        JsonFactory $jsonResultFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Model\DealerReminderFactory $dealerReminderFactory
    )
    {
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->dealerReminderFactory = $dealerReminderFactory;
        $this->jsonResultFactory = $jsonResultFactory;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    /**
     * Index action
     */
    public function execute()
    {
        $resultJson = $this->jsonResultFactory->create();
        try {
            $result = $this->dealerReminderFactory->create()->instantEmailData();
            if (empty($result)) {
                $responseData = ["success" => 0, "error" => 'No Data To Send'];
                $resultJson->setData($responseData);
            }else {
                $responseData["success"] = 1;
                $responseData["claimStatusData"] = $result;
                $data = $this->getRequest()->getPost();
                $responseData["options"] = $data;
                $resultJson->setData($responseData);
            }
        } catch (\Exception $e) {
            $responseData = ["success" => 0, "error" => $e->getMessage()];
            $resultJson->setData($responseData);
        }
        return $resultJson;
    }

}
