<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Remotelinks;


/**
 * Remote links custom variables model
 *
 * @category   Epicor
 * @package    Comm
 * @author    Sean Flynn
 */
class Variables extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Assoc array of configuration variables
     *
     * @var array
     */
    protected $_configVariables = array();

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Constructor
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->backendSession = $backendSession;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $remoteLinkName = '';
        $remoteLinkName = $this->backendSession->getRemoteLink();
        $this->setVariables($remoteLinkName);
    }

    /**
     * Retrieve option array of store contact variables
     *
     * @param boolean $withGroup
     * @return array
     */
    public function toOptionArray($withGroup = false)
    {
        $optionArray = array();

        foreach ($this->_configVariables as $variable) {
            $optionArray[] = array(
                'value' => $variable['value'],
                'label' => $variable['label']
            );
        }
        if ($withGroup && $optionArray) {
            $optionArray = array(
                'label' => __('Remote Link Variables'),
                'value' => $optionArray
            );
        }
        if (!$optionArray) {
            $emptyArray = array();
            $optionArray = array(
                'label' => __('No Remote Link Variables for the selected name'),
                'value' => $emptyArray
            );
        }
        return $optionArray;
    }

    public function setVariables($remoteLinkName)
    {
        if ($remoteLinkName && $remoteLinkName != 'none') {
            //M1 > M2 Translation Begin (Rule P2-5.6)
            // $json = $remoteLinkName ? json_encode(Mage::getConfig()->getNode("adminhtml/remote_links_values/{$remoteLinkName}")) : null;
            $json = $remoteLinkName ? json_encode( $this->_scopeConfig->getValue("adminhtml/remote_links_values/{$remoteLinkName}")) : null;

            //M1 > M2 Translation End

            if ($json) {
                $remotelinks = json_decode($json, true);
                foreach ($remotelinks as $key => $value) {
                    if (array_key_exists('value', $value)) {
                        $this->_configVariables[] = $value;
                    }
                }
            }
        }
    }

}
