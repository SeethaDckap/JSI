<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account;


/**
 * Manage controller
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Manage extends \Epicor\SalesRep\Controller\Generic
{

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

      /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(\Epicor\SalesRep\Controller\Context $context)
    {
        $this->registry = $context->getRegistry();
        $this->customerCustomerFactory = $context->getCustomerCustomerFactory();
        $this->logger = $context->getLogger();
        $this->salesRepAccountManageHelper = $context->getSalesRepAccountManageHelper();
        parent::__construct($context);
    }
    
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
        if ($this->salesRepAccountManageHelper->isManagingChild()) {
            $title = __('Managing Sales Rep Account') . ': ' . $this->salesRepAccountManageHelper->getManagedSalesRepAccount()->getName();
        } else {
            $title = __('My Sales Rep Account');
        }
        if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
            $pageMainTitle->setPageTitle($title);
        }
        
        return $resultPage;
    }

    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        $helper->registerAccounts();

        $base = $helper->getBaseSalesRepAccount();
        $managed = $helper->getManagedSalesRepAccount();

        if ($base->getId() != $managed->getId() && !$this->getRequest()->getPost() && !$this->getRequest()->getActionName() == 'reset') {
            //M1 > M2 Translation Begin (Rule p2-4)
            //$link = '<a href="' . Mage::getUrl('*/*/reset') . '">' . $this->__('Return to My Sales Rep Account') . '</a>';
            $link = '<a href="' . $this->_url->getUrl('*/*/reset') . '">' . __('Return to My Sales Rep Account') . '</a>';
            //M1 > M2 Translation End
            //M1 > M2 Translation Begin (Rule 55)
            //$this->generic->addSuccess($this->__('You are currently managing Sales Rep Account: %s, %s', $managed->getName(), $link));
            $this->messageManager->addSuccessMessage(__('You are currently managing Sales Rep Account: %1, %2', $managed->getName(), $link));
            //M1 > M2 Translation End
        }

        return parent::dispatch($request);
    }

    protected function _redirectToSalesReps()
    {
        $params = array();

        $salesRepId = $this->getRequest()->getParams('salesrepacc');

        if (empty($salesRepId)) {
            $params['salesrepacc'] = $salesRepId;
        }

        $this->_redirect('*/*/salesreps', $params);
    }

    protected function _processSalesRep($ids, $action)
    {
        $error = false;
        $this->registry->register('isSecureArea', true, true);

        if (!empty($ids)) {
            $helper = $this->salesRepAccountManageHelper;
            /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

            $salesRepAccount = $helper->getManagedSalesRepAccount();

            foreach ($ids as $id) {
                $customer = $this->customerCustomerFactory->create()->load($id);
                try {
                    if ($customer->isObjectNew()) {
                        $error = true;
                        //M1 > M2 Translation Begin (Rule 55)
                        //$session->addError($this->__('1Unable to find the Sales Rep to %s', $action));
                        $this->messageManager->addErrorMessage(__('1Unable to find the Sales Rep to %1', $action));
                        //M1 > M2 Translation End
                    } else if ($customer->getEccSalesRepAccountId() != $salesRepAccount->getId()) {
                        $error = true;
                        //M1 > M2 Translation Begin (Rule 55)
                        //$session->addError($this->__('2Unable to find the Sales Rep to %s', $action));
                        $this->messageManager->addErrorMessage(__('2Unable to find the Sales Rep to %1', $action));
                        //M1 > M2 Translation End
                    } else {
                        if ($action == 'delete' && !$customer->delete()) {
                            $error = true;
                            $this->messageManager->addErrorMessage('Could not delete Sales Rep Account ' . $customer->getEmailAddress());
                        } else if ($action == 'unlink') {
                            $customer->setEccSalesRepAccountId(false);
                            $customer->save();
                        }
                    }
                } catch (\Exception $e) {
                    //M1 > M2 Translation Begin (Rule 55)
                    //$session->addError('Could not %s Sales Rep Account ' . $customer->getEmailAddress(), $action);
                    $this->messageManager->addErrorMessage('Could not %1 Sales Rep Account ' . $customer->getEmailAddress(), $action);
                    //M1 > M2 Translation End
                    $this->logger->critical($e);
                }
            }
        } else {
            $error = true;
        }

        $this->registry->unregister('isSecureArea');

        return $error;
    }

}
