<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

use Magento\Framework\App\ResponseInterface;

class Customerindex extends \Epicor\Comm\Controller\Returns\Index
{
    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_returns_create';
    const FRONTEND_RESOURCE_EDIT = 'Epicor_Customer::my_account_returns_edit';
    public function execute()
    {
        $session = $this->customerSession;
        /* @var $session Mage_Customer_Model_Session */

        $session->unsReturnGuestName();
        $session->unsReturnGuestEmail();

        $loadLayout = true;
        $returnId = $this->request->getParam('return');
        $erpReturn = $this->request->getParam('erpreturn');

        if (!empty($returnId)) {
            $return = $this->loadReturn($returnId, true);
            if (!$return) {
                $this->messageManager->addErrorMessage('Return not found');
                $this->_redirect('/');
                $loadLayout = false;
            }
        } else if (!empty($erpReturn)) {
            $helper = $this->commReturnsHelper;
            /* @var $helper Epicor_Comm_Helper_Returns */

            $return = $helper->loadErpReturn($helper->decodeReturn($erpReturn), null, true);

            if ($return) {
                $return = $this->loadReturn($return->getId(), false, $return);
            } else {
                $this->messageManager->addErrorMessage('Return not found');
                $this->_redirect('/');
                $loadLayout = false;
            }
        }

        if ($loadLayout) {
            //M1 > M2 Translation Begin (Rule p2-4)
            //$this->customerSession->setBeforeAuthUrl(Mage::getUrl('epicor_comm/returns/index'));
            $this->customerSession->setBeforeAuthUrl($this->_url->getUrl('epicor_comm/returns/index'));
            return $this->resultPageFactory->create();
            //M1 > M2 Translation End
        }
    }

}