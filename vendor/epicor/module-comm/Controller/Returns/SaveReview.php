<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

class SaveReview extends \Epicor\Comm\Controller\Returns
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

                $return->setSubmitted(1);

                if ($return->getErpReturnsNumber()) {
                    $return->setErpSyncAction('U');
                } else {
                    $return->setErpSyncAction('A');
                }

                $return->setErpSyncStatus('N');

                if ($return->save()) {
                    $this->registry->register('return_success', true);
                } else {
                    $errors[] = __('An error occurred saving your return, please try again later');
                }
            } else {
                $errors[] = __('Failed to find return to confirm. Please try again.');
            }

            $this->sendStepResponse('review', $errors);
        }
    }
}
