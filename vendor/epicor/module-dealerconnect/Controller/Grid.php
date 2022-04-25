<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller;


/**
 * Grid controller, handles generic gird functions
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
abstract class Grid extends \Epicor\Customerconnect\Controller\Grid
{
    /**
     * @var \Epicor\Dealerconnect\Model\Dashboard
     */
    protected $dashboard;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Customerconnect\Model\Message\Request\Cuad $customerconnectMessageRequestCuad,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Dealerconnect\Model\Dashboard $dashboard
    ) {
        $this->dashboard = $dashboard;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $customerconnectHelper,
            $customerconnectMessageRequestCuad,
            $registry,
            $commonAccessHelper,
            $generic
        );
    }

    public function setGridDashboardConfigration()
    {
        $dashboardConfiguration = $this->dashboard->getDashboardConfiguration();
        $dashboardConfiguration = array_filter($dashboardConfiguration, function($data){
            return $data['allowed'];
        });
        $this->registry->register('dashboard_configuration', $dashboardConfiguration);
    }
}
