<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Importexport;

class Storemapping extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Importexport
{


    public function execute()
    {
        if (isset($_FILES['import_epicor_comm_settings_file']['tmp_name'])) {
            if (!$_FILES['import_epicor_comm_settings_file']['tmp_name']) {
                $this->messageManager->addErrorMessage(__("Please select an input file"));
                //M1 > M2 Translation Begin (Rule p2-3)
                //Mage::app()->getResponse()->setRedirect($_SERVER['HTTP_REFERER'])->sendResponse();
                $resultRedirect = $this->resultRedirectFactory->create();
                $this->_redirect('*/*/');
               // return $resultRedirect;
                //M1 > M2 Translation End
            } else {
                $this->_serializedArray = file_get_contents($_FILES['import_epicor_comm_settings_file']['tmp_name']);
                $unserializedArray = unserialize($this->_serializedArray);
                if (!isset($unserializedArray['core'])) {
                    $this->importAction();
                } else {
                    //M1 > M2 Translation Begin (Rule p2-8)
                    //mage::register('importfile', $this->_serializedArray);
                    $this->_registry->register('importfile', $this->_serializedArray);
                    //M1 > M2 Translation End
//                    $this->loadLayout();
//                    $this->renderLayout();
                }
            }
        }       
        
        $resultPage = $this->_resultPageFactory->create();

        return $resultPage;
        
    }

    }
