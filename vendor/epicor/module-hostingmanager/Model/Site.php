<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Epicor Hosting Site Model
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 * 
 * @method string getName()
 * @method string getUrl()
 * @method string getIsWebsite()
 * @method string getCode()
 * @method string getChildId()
 * @method string getCertificateId()
 * @method bool getIsDefault()
 * @method setName(string $name)
 * @method setUrl(string $url)
 * @method setIsWebsite(string $isWebsite)
 * @method setCode(string $code)
 * @method setChildId(string $childId)
 * @method setCertificateId(string $certificatedId)
 * @method setIsDefault(bool $is_default)
 */
class Site extends AbstractModel
{

    protected $_certificate;

    /**
     * @var \Epicor\HostingManager\Helper\Data
     */
    protected $hostingManagerHelper;

    /**
     * @var \Epicor\HostingManager\Model\CertificateFactory
     */
    protected $hostingManagerCertificateFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\HostingManager\Helper\Data $hostingManagerHelper,
        \Epicor\HostingManager\Model\CertificateFactory $hostingManagerCertificateFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->hostingManagerHelper = $hostingManagerHelper;
        $this->hostingManagerCertificateFactory = $hostingManagerCertificateFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {
        $this->_init('Epicor\HostingManager\Model\ResourceModel\Site');
    }

    public function afterSave()
    {
        /**
         * @todo if cert change and attached to a site, trigger service restart
         */
        if (
            $this->hasUrlChanged() ||
            $this->hasStoreChanged() ||
            $this->hasSSLChanged() ||
            $this->hasExtraDomainChanged()
        ) {

            $helper = $this->hostingManagerHelper;
            /* @var $helper \Epicor\HostingManager\Helper\Data */
            $helper->saveSiteFile($this);
            $filename ="www-data-RELOAD-NGINX";
            $tmpfname = sys_get_temp_dir()."/".$filename; 
            $handle = fopen($tmpfname, "w");
            fwrite($handle, "writing to tempfile");
            fclose($handle);            
            //shell_exec('sudo /etc/init.d/nginx reload');
        }

        parent::afterSave();
    }

    /**
     * Checks if a change in current data and original data has occured
     * 
     * @return bool
     */
    public function hasUrlChanged()
    {
        return $this->getData('url') != $this->getOrigData('url');
    }

    /**
     * Checks if a change in current data and original data has occured
     * 
     * @return bool
     */
    public function hasExtraDomainChanged()
    {
        return $this->getData('extra_domains') != $this->getOrigData('extra_domains');
    }

    /**
     * Checks if a change in current data and original data has occured
     * 
     * @return bool
     */
    public function hasStoreChanged()
    {
        return $this->getData('child_id') != $this->getOrigData('child_id') ||
            $this->getData('is_website') != $this->getOrigData('is_website') ||
            $this->getData('code') != $this->getOrigData('code');
    }

    /**
     * Checks if a change in current data and original data has occured
     * 
     * @return bool
     */
    public function hasSSLChanged()
    {
        return $this->getData('certificate_id') != $this->getOrigData('certificate_id');
    }

    public function beforeDelete()
    {

        $helper = $this->hostingManagerHelper;
        /* @var $helper \Epicor\HostingManager\Helper\Data */
        $helper->deleteSiteFile($this);
        parent::beforeDelete();
    }

    /**
     * 
     * @return \Epicor\HostingManager\Model\Certificate
     */
    public function getCertificate()
    {

        if (!$this->_certificate && !is_null($this->getCertificateId())) {
            $this->_certificate = $this->hostingManagerCertificateFactory->create()->load($this->getCertificateId());
        }

        return $this->_certificate;
    }

    /**
     * Load Site by a set of fields
     * 
     * @param array $attributes array( $field1 => $value1, $field2 => $field2)
     * @return \Epicor\HostingManager\Model\Site
     * @todo Make this part of the common module and change all epicor model to extend the new abstract
     */
    public function loadByAttributes($attributes)
    {
        $data = $this->getResource()->loadByAttributes($attributes);
        if($data){
            $this->setData($data);
        }
        return $this;
    }

}
