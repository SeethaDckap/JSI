<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Controller\Adminhtml;


/**
 * Faqs adminhtml controller
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 *
 */
abstract class Faqs extends \Epicor\Comm\Controller\Adminhtml\Generic
{
    //protected $_aclId = 'epicor_common/faqs';

    /**
     * Init actions
     *
     * @return \Epicor\Faqs\Controller\Adminhtml\Faqs
     */
    protected function _initPage()
    {
        // Load layout, set active menu and breadcrumbs
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Common::epicor');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage F.A.Q.'));

        return $resultPage;

    }
/**
     * Check the permission to run it
     *
     * @return boolean
     */
//    protected function _isAllowed()
//    {
////        return $this->backendAuthSession->isAllowed('Epicor_Faqs::faqs_management');
//        switch ($this->getRequest()->getActionName()) {
//            case 'new':
//            case 'save':
//                return $this->backendAuthSession->isAllowed('faqs/manage/save');
//             //   return $this->backendAuthSession->isAllowed('Epicor_Faqs::faqs_management');
//                break;
//            case 'delete':
//                return $this->backendAuthSession->isAllowed('faqs/manage/delete');
//           //     return $this->backendAuthSession->isAllowed('Epicor_Faqs::faqs_management');
//                break;
//            default:
//         //       return $this->backendAuthSession->isAllowed('faqs/manage');
//                return $this->backendAuthSession->isAllowed('Epicor_Faqs::faqs_management');
//                break;
//        }
//    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array
     * @return array
     */
    protected function _filterPostData($data)
    {
        if(isset($data['stores']) && $data['stores']) {
            $data['stores'] = implode(',', $data['stores']);
        }else{
            $data['stores'] = null;
        }
        return $data;
    }
}
