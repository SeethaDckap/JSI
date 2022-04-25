<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Remotelinkobjects
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;

    }
    public function toOptionArray()
    {
        //M1 > M2 Translation Begin (Rule P2-5.6)
        //$remotelinks = (array) Mage::getConfig()->getNode('adminhtml/remote_link_objects');
        $remotelinks = (array) $this->_scopeConfig->getValue('epicor_comm_mapping/remote_link_objects');

        //M1 > M2 Translation End

        return $remotelinks;
    }

}
