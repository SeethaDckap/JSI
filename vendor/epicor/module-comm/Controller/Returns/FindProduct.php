<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

class FindProduct extends \Epicor\Comm\Controller\Returns
{


    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Registry $registry
    )
    {
        $this->jsonHelper  = $jsonHelper;
        parent::__construct(
            $context,
            $commReturnsHelper,
            $customerSession,
            $commCustomerReturnModelFactory,
            $generic,
            $jsonHelper,
            $registry);
    }


public function execute()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            /* Do action stuff here */
            $errors = array();

            $helper = $this->commReturnsHelper;
            /* @var $helper Epicor_Comm_Helper_Returns */

            $findType = $this->getRequest()->getParam('search_type', false);
            $findValue = $this->getRequest()->getParam('search_value', false);
            $lines = array();

            if (empty($findType)) {
                $errors[] = __('Find Products Type Missing');
            }

            if (empty($findValue)) {
                $errors[] = __('Find Products Value Missing');
            }

            $returnUserType = $helper->getReturnUserType();

            if (empty($errors)) {

                $lines = array();

                if ($findType == 'order') {
                    $order = $helper->findLocalOrder($findValue);

                    if ($order && !$order->isObjectNew()) {
                        $erpOrderNum = $order->getEccErpOrderNumber();
                        if (empty($erpOrderNum) && $returnUserType != 'b2b') {
                            $errors[] = __('Not a Valid Order');
                        } else if (!empty($erpOrderNum)) {
                            $findValue = $erpOrderNum;
                        }
                    } else if ($returnUserType != 'b2b') {
                        $errors[] = __('Not a Valid Order');
                    }
                }

                if (empty($lines) && empty($errors)) {
                    $products = $helper->findProductsByMessage($findType, $findValue);

                    if (empty($products['errors'])) {
                        $lines = $products['products'];
                    } else {
                        $errors = $products['errors'];
                    }
                }
            }

            if (empty($errors)) {
                $result = array('lines' => $lines);
                if (!$helper->checkConfigFlag('allow_mixed_return')) {
                    $result['hide_add_sku'] = 1;
                    $result['restrict_type'] = $findType;
                }
            } else {
                if (!is_array($errors)) {
                    $errors = array($errors);
                }
                $result = array('errors' => $errors);
            }

            $this->getResponse()->setBody($this->jsonHelper->jsonEncode($result));
        }
    }

    }
