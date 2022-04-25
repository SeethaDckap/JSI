<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Observer;

class ActionPreDispatchAdmin extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Handler for controller_action_predispatch event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return boolean
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $session = $this->backendAuthSession;
        /** @var $session Mage_Admin_Model_Session */
        $request = $this->request;
        $user = $session->getUser();

        $requestedActionName = $request->getActionName();
        $openActions = array(
            'forgotpassword',
            'resetpassword',
            'resetpasswordpost',
            'logout',
            'refresh' // captcha refresh
        );
        if (in_array($requestedActionName, $openActions)) {
            $request->setDispatched(true);
        } else {
            if ($user) {
                $user->reload();
            }
            if (!$user || !$user->getId()) {
                if ($request->getPost('login')) {
                    $postLogin = $request->getPost('login');
                    $username = isset($postLogin['username']) ? $postLogin['username'] : '';
                    $password = isset($postLogin['password']) ? $postLogin['password'] : '';
                    $session->login($username, $password, $request);
                    $request->setPost('login', null);
                }
                if (!$request->getParam('forwarded')) {
                    if ($request->getParam('isIframe')) {
                        $request->setParam('forwarded', true)
                            ->setRouteName('adminhtml')
                            ->setControllerName('index')
                            ->setActionName('deniedIframe')
                            ->setDispatched(false);
                    } elseif ($request->getParam('isAjax')) {
                        $request->setParam('forwarded', true)
                            ->setRouteName('adminhtml')
                            ->setControllerName('index')
                            ->setActionName('deniedJson')
                            ->setDispatched(false);
                    } else {
                        $request->setParam('forwarded', true)
                            ->setRouteName('adminhtml')
                            ->setControllerName('auth')
                            ->setActionName('login')
                            ->setDispatched(false);
                    }
                    return false;
                }
            }
        }

        $session->refreshAcl();
    }

}