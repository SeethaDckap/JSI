<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Backend;


/**
 * Default Erp account backend controller
 * 
 * Updates the Default ERP code if the Erp account changes
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class RequiredDateVsDdaRequest extends \Magento\Framework\App\Config\Value
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,   
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        \Epicor\B2b\Controller\Context $epicorContext,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->scopeConfig = $epicorContext->getScopeConfig();
        $this->registry = $epicorContext->getRegistry();
        $registry = $epicorContext->getRegistry();
        parent::__construct(
            $context,
            $registry,
            $this->scopeConfig,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function beforeSave()
    {
        $path = $this->getPath();
        $value = $this->getValue();
        $isDda = null;
        if($value){
            $scope = ($this->getScopeId()) ? 'stores' : 'default';
            if($path == "checkout/options/required_date" && $this->scopeConfig->getValue("epicor_comm_enabled_messages/dda_request/active", $scope, $this->getScopeId())) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unable to save Required date, as DDA Request is already enable.'));
            } elseif($path == "epicor_comm_enabled_messages/dda_request/active" && $this->scopeConfig->getValue("checkout/options/required_date", $scope, $this->getScopeId())) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unable to enable  DDA Request, As Required Date is already enable.'));
            }
        }
        parent::beforeSave();
    }

}
