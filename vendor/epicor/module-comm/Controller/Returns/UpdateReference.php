<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

class UpdateReference extends \Epicor\Comm\Controller\Returns
{
    public function execute()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            /* Do action stuff here */
            $errors = array();

            $return = $this->loadReturn();

            if (!$return->isObjectNew()) {
                $ref = $this->getRequest()->getParam('customer_ref', '');
                $caseNum = $this->getRequest()->getParam('case_number', '');

                if (!$return->getErpReturnsNumber() && $caseNum != $return->getRmaCaseNumber()) {
                    if (!empty($caseNum)) {
                        $helper = $this->commReturnsHelper;
                        /* @var $helper Epicor_Comm_Helper_Returns */

                        $caseInfo = $helper->findCase($caseNum);

                        if (!$caseInfo['valid']) {
                            $errors[] = __('Not a valid Case Number');
                        } else if (!empty($caseInfo['erp_return_number'])) {
                            $errors[] = __('A return already exists with the supplied Case Number');
                        } else {
                            $return->setRmaCaseNumber($caseNum);
                        }
                    } else {
                        $return->setRmaCaseNumber($caseNum);
                    }
                }

                $return->setCustomerReference($ref);
                $return->save();
            } else {
                $errors[] = __('Failed to find return to add reference to. Please try again.');
            }

            $this->sendStepResponse('return', $errors);
        }
    }

    }
