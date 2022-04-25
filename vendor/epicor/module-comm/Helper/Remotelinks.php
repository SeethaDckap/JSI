<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper;


class Remotelinks extends \Epicor\Comm\Helper\Data
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\RemotelinksFactory
     */
    protected $commErpMappingRemotelinksFactory;

    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Epicor\Comm\Model\Erp\Mapping\RemotelinksFactory $commErpMappingRemotelinksFactory
    ) {
        $this->commErpMappingRemotelinksFactory = $commErpMappingRemotelinksFactory;
        parent::__construct($context);
    }
    /**
     * Checks to see if a message is enabled
     * 
     * @param string $base - Module the message resides in
     * @param string $messageType - (lowecase) message name
     * 
     * @return array
     */
    public function fieldSubstitution($object, $patternCode)
    {
        // get email_template model, so that custom var substitution (eg {{var $object.getSku()}}) can be performed        
        $template = $this->emailTemplateFactory->create();

        //get urlpattern saved in epicor_comm/erp_mapping_remotelinks for the supplied patterncode 
        $remoteLink = $this->commErpMappingRemotelinksFactory->create()->load($patternCode, 'pattern_code');
        /* @var $remoteLink Epicor_Comm_Model_Erp_Mapping_Remotelinks */
        $template->setTemplateText($remoteLink->getUrlPattern());
        $templateVars['object'] = $object;
        $url = $template->getProcessedTemplate($templateVars);          // return url pattern with specific variabled substituted 

        if ($remoteLink->getHttpAuthorization()) {
            //M1 > M2 Translation Begin (Rule p2-4)
            //$url = Mage::getUrl('epicor_comm/remotelinks/fetch', array('url' => $this->urlEncode($url), 'remotelink' => $patternCode));
            $url = $this->_getUrl('epicor_comm/remotelinks/fetch', array('url' => $this->urlEncode($url), 'remotelink' => $patternCode));
            //M1 > M2 Translation End
        }

        return $url;
    }

}
