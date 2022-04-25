<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Mapping;


/**
 * @method string getErpCode()
 * @method string getMagentoId()
 * @method setErpCode(string $erp_code)
 * @method setMagentoId(string $magento_code)
 * 
 * @method setPatternCode(string $value)
 * @method string getPatternCode()
 * @method setName(string $value)
 * @method string getName()
 * @method setUrlPattern(string $value)
 * @method string getUrlPattern()
 * @method setHttpAuthorization(string $value)
 * @method string getHttpAuthorization()
 * @method setAuthUser(string $value)
 * @method string getAuthUser()
 * @method setAuthPassword(string $value)
 * @method string getAuthPassword()
 */
class Remotelinks extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $emailTemplateFactory;

    public function __construct(
        \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->emailTemplateFactory = $emailTemplateFactory;
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\ResourceModel\Erp\Mapping\Remotelinks');
    }

    /**
     * 
     * @param \Magento\Framework\DataObject $object
     * 
     * @return string $url
     */
    public function getRemoteLinkUrl($object)
    {
        // get email_template model, so that custom var substitution (eg {{var $object.getSku()}}) can be performed
        $template = $this->emailTemplateFactory->create();
        /* @var $template \Magento\Email\Model\Template */
        $template->setTemplateText($this->getUrlPattern());
        // return url pattern with specific variabled substituted
        $url = $template->getProcessedTemplate(array('object' => $object));

        return $url;
    }

}
