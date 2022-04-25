<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Sales\Order\Email;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;

class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,            
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
        parent::__construct($templateContainer,$identityContainer,$transportBuilder);
    }
    
    /**
     * Configure email template
     *
     * @return void
     */
    protected function configureEmailTemplate()
    {
        $versions = array("2.2.4","2.2.5");
        if(($this->productMetadata->getVersion() > '2.2.3') && ($this->productMetadata->getVersion() < '2.3.1')){
            $this->transportBuilder->setTemplateIdentifier($this->templateContainer->getTemplateId());
            $this->transportBuilder->setTemplateOptions($this->templateContainer->getTemplateOptions());
            $this->transportBuilder->setTemplateVars($this->templateContainer->getTemplateVars());
            $this->transportBuilder->setFrom($this->identityContainer->getEmailIdentity()); 
        } else {
            parent::configureEmailTemplate(); 
        }
    }
}