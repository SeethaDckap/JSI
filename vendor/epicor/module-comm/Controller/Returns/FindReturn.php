<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

class FindReturn extends \Epicor\Comm\Controller\Returns
{



public function execute()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            /* Do action stuff here */
            $errors = array();
            $findType = $this->getRequest()->getParam('find_type', false);
            $findValue = $this->getRequest()->getParam('find_value', false);

            if ($findType === false) {
                $errors[] = __('Find Return by Type Missing');
            }

            if ($findValue === false) {
                $errors[] = __('Find Value Missing');
            }

            if (empty($errors)) {
                $helper = $this->commReturnsHelper;
                /* @var $helper Epicor_Comm_Helper_Returns */
                $return = $helper->findReturn($findType, $findValue, true);

                if (empty($return['errors'])) {
                    $returnObj = $return['return'];
                    /* @var $returnObj Epicor_Comm_Model_Customer_ReturnModel */
                    if ($return['source'] == 'local') {
                        $returnObj->updateFromErp();
                    }

                    $returnObj->reloadChildren();

                    $this->registry->register('return_id', $returnObj->getId());
                    $this->registry->register('return_model', $returnObj);
                } else {
                    $errors = $return['errors'];
                }
            }

            $this->sendStepResponse('return', $errors);
        }
    }

    }
