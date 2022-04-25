<?php
namespace Silk\CustomAccount\Plugin;

class LoginPostPlugin
{

    /**
     * Change redirect after login to home instead of dashboard.
     *
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param \Magento\Framework\Controller\Result\Redirect $result
     */
    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        $result)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $redirect = $objectManager->create('\Magento\Framework\Controller\Result\Redirect');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $redirect->setPath('customaccount/dashboard/index');
        return $redirect;
    }

}