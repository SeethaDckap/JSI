<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller;

/**
 * Redirect to WEB Track
 *
 */
abstract class webtrack extends \Magento\Framework\App\Action\Action {

    const WBBTRACK_URL_CONFIG_PATH = 'Epicor_Comm/integrations/webtrack_url';       
     
     /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->_logger = $logger;
    }
}
