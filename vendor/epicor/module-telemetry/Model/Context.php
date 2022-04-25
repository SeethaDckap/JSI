<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Epicor\Telemetry\Model\Context\UserContext;
use Epicor\Telemetry\Model\Context\SessionContext;
use Epicor\Telemetry\Model\Context\OperationContext;
use Epicor\Telemetry\Model\Context\LocationContext;
use Epicor\Telemetry\Model\Context\ApplicationContext;
use Epicor\Telemetry\Model\Context\DeviceContext;
use Epicor\Telemetry\Model\Context\CloudContext;
use Epicor\Telemetry\Model\Context\InternalContext;

/**
 * Class Context
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class Context
{
    const COOKIE_AI_USER    = 'ai_user';
    const COOKIE_AI_SESSION = 'ai_session';

    /**
     * @var array
     */
    private $properties;

    /**
     * @var mixed
     */
    private $userId;

    /**
     * @var mixed
     */
    private $sessionId;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var mixed
     */
    private $sessionCreated;

    /**
     * @var mixed
     */
    private $sessionLastRenewed;

    /**
     * @var mixed
     */
    private $instrumentationKey;

    /**
     * @var Utils
     */
    private $utils;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var UserContext
     */
    private $userContext;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var OperationContext
     */
    private $operationContext;

    /**
     * @var LocationContext
     */
    private $locationContext;

    /**
     * @var ApplicationContext
     */
    private $applicationContext;

    /**
     * @var DeviceContext
     */
    private $deviceContext;

    /**
     * @var CloudContext
     */
    private $cloudContext;

    /**
     * @var InternalContext
     */
    private $internalContext;

    /**
     * Context constructor.
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param UserContext $userContext
     * @param SessionContext $sessionContext
     * @param OperationContext $operationContext
     * @param LocationContext $locationContext
     * @param ApplicationContext $applicationContext
     * @param DeviceContext $deviceContext
     * @param CloudContext $cloudContext
     * @param InternalContext $internalContext
     * @param Utils $utils
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        UserContext $userContext,
        SessionContext $sessionContext,
        OperationContext $operationContext,
        LocationContext $locationContext,
        ApplicationContext $applicationContext,
        DeviceContext $deviceContext,
        CloudContext $cloudContext,
        InternalContext $internalContext,
        Utils $utils
    )
    {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->utils = $utils;
        $this->userContext = $userContext;
        $this->sessionContext = $sessionContext;
        $this->operationContext = $operationContext;
        $this->locationContext = $locationContext;
        $this->applicationContext = $applicationContext;
        $this->deviceContext = $deviceContext;
        $this->cloudContext = $cloudContext;
        $this->internalContext = $internalContext;
        $this->properties = [];

        try {
            $this->userContext->setId($this->getCurrentUserId());
        } catch (InputException $e) {
        } catch (CookieSizeLimitReachedException $e) {
        } catch (FailureToSendException $e) {
        }
        $this->sessionContext->setId($this->getCurrentSessionId());

        $operationId = $this->utils->getGuid();
        $this->operationContext->setId($operationId);

        // Initialize client ip.
        if (array_key_exists('REMOTE_ADDR', $_SERVER)
            && sizeof(explode('.', $_SERVER['REMOTE_ADDR'])) >= 4
        ) {
            $this->locationContext->setIp($_SERVER['REMOTE_ADDR']);
        }
        $this->internalContext->setSdkVersion('php:0.4.6');
    }

    /**
     * Current User Id
     * @return mixed|string
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function getCurrentUserId()
    {
        $aiUser = $this->cookieManager->getCookie(self::COOKIE_AI_USER);
        if ($aiUser) {
            $parts = explode('|', $aiUser);
            if (count($parts) > 0) {
                $this->userId = $parts[0];
            }
        }

        if ($this->userId === null) {
            $this->userId = $this->utils->getGuid();
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $metadata->setPath('/');
            $this->cookieManager->setPublicCookie(
                self::COOKIE_AI_USER,
                $this->userId,
                $metadata
            );
        }
        return $this->userId;
    }

    /**
     * Current Session of User
     * @return mixed|string
     */
    public function getCurrentSessionId()
    {
        $aiSession = $this->cookieManager->getCookie(self::COOKIE_AI_SESSION);
        if ($aiSession) {
            $parts = explode('|', $aiSession);
            $len = count($parts);

            switch (true) {
                case ($len > 0):
                    $this->sessionId = $parts[0];
                    break;
                case ($len > 1):
                    $this->sessionCreated = strtotime($parts[1]);
                    break;
                case ($len > 2):
                    $this->sessionLastRenewed = strtotime($parts[2]);
                    break;
            }
        }
        return $this->sessionId;
    }

    /**
     * Setting the Telemetry instrumentation key
     * @param mixed $instrumentationKey
     */
    public function setInstrumentationKey($instrumentationKey)
    {
        $this->instrumentationKey = $instrumentationKey;
    }

    /**
     * The instrumentation key for your Application Insights application.
     * @return mixed
     */
    public function getInstrumentationKey()
    {
        return $this->instrumentationKey;
    }

    /**
     * The application context object.
     * Allows you to set properties that will be attached to all telemetry about the application.
     * @return ApplicationContext
     */
    public function getApplicationContext()
    {
        return $this->applicationContext;
    }

    /**
     * The cloud context object.
     * Allows you to set properties that will be attached to all telemetry about the cloud placement of an application.
     * @return CloudContext
     */
    public function getCloudContext()
    {
        return $this->cloudContext;
    }

    /**
     * @return DeviceContext
     */
    public function getDeviceContext()
    {
        return $this->deviceContext;
    }

    /**
     * @return LocationContext
     */
    public function getLocationContext()
    {
        return $this->locationContext;
    }

    /**
     * @return OperationContext
     */
    public function getOperationContext()
    {
        return $this->operationContext;
    }

    /**
     * @return SessionContext
     */
    public function getSessionContext()
    {
        return $this->sessionContext;
    }

    /**
     * @return UserContext
     */
    public function getUserContext()
    {
        return $this->userContext;
    }

    /**
     * @return InternalContext
     */
    public function getInternalContext()
    {
        return $this->internalContext;
    }

    /**
     * Get the additional custom properties array.
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Set the additional custom properties array.
     * @param array $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }
}