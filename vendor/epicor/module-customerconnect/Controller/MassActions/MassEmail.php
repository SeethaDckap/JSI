<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\MassActions;

class MassEmail extends \Epicor\Customerconnect\Controller\MassActions
{

    /**
     * Deletes array of given List
     *
     * @return void
     */
    public function execute()
    {
        //get form data
        $requestConfig = [];
        $responseConfig = [];
        $data = $this->getRequest()->getPost();
        if(is_string($data['email_params'])){
            parse_str($data['email_params'], $emailParams);
        }elseif(is_array($data['email_params'])){
            $emailParams = $data['email_params'];
        }
        if(!isset($data['entity_key'])){
            $key = $data['massaction_prepare_key'];
            $ids = explode(',', $data[$key]);
        }else{
            $ids = array($data['entity_key']);
        }

        $accountNumber = $this->getAccountNumber();
        $entityDocument = $data['entity_document'];
        $error = 0;

        //set form values
        $customerSession = $this->customerSession;
        $customerSession->setPreqToAddr($emailParams['to']);
        $customerSession->setPreqCcAddr($emailParams['cc']);
        $customerSession->setPreqBccAddr($emailParams['bcc']);
        $customerSession->setPreqMsg($emailParams['message']);
        $customerSession->setPreqSub($emailParams['subject']);

        //construct PREQ message
        $message = $this->customerconnectMessageRequestPreq;

        $messageTypeCheck = $message->getHelper()->getMessageType('PREQ');

        if ($message->isActive() && $messageTypeCheck) {
            foreach ($ids as $id) {
                $requestConfig[] = array("entityKey" => $id, "retryCount" => 0);
                $responseConfig[] = array("entityKey" => $id, "success" => false);
            }
            $requestConfigStore = serialize($requestConfig);
            $responseConfigStore = serialize($responseConfig);
            $preqModel = $this->loadEntity();
            $preqModel->setRequestConfig($requestConfigStore)
                ->setResponseConfig($responseConfigStore)
                ->setEntityDocument($entityDocument)
                ->setEmailParams(serialize($emailParams))
                ->setAccountNumber($accountNumber)
                ->setReadyStatus($error);

            try {
                $preqModel->save();
                $response = json_encode(array('message' => __('Email request sent for processing'), 'type' => 'success', 'id' => $preqModel->getId()));
            } catch (\Exception $e) {
                $response = json_encode(array('message' => __($e->getMessage()), 'type' => 'error'));
                $this->logger->debug($e->getMessage());
            }

        } else {
            $response = json_encode(array('message' => __('Email request not available'), 'type' => 'error'));
        }

        session_write_close();
        $this->getResponse()->setBody( $response);

    }

}
