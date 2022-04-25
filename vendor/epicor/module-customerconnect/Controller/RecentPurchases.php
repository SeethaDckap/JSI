<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller;

/**
 * Recent Purchases controller
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
abstract class RecentPurchases extends \Epicor\Customerconnect\Controller\Generic {

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Locale\ResolverInterface $localeResolver, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct(
                $context, $customerSession, $localeResolver, $resultPageFactory, $resultLayoutFactory
        );
    }

}
