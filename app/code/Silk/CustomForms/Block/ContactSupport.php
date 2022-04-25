<?php
namespace Silk\CustomForms\Block;

class ContactSupport extends \Magento\Framework\View\Element\Template
{
    protected $countryInformationAcquirer;
    protected $_varFactory;
    private $logger;

	public function __construct(
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformationAcquirer,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Directory\Block\Data $directoryData,
        \Magento\Variable\Model\VariableFactory $varFactory,
        \Magento\Framework\View\Element\Template\Context $context
    )
	{
        $this->logger = $logger;
        $this->directoryData = $directoryData;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->_varFactory = $varFactory;
		parent::__construct($context);
	}
    public function _prepareLayout()  
    {  
        $this->pageConfig->getTitle()->set(__('Contact Us'));  
        return parent::_prepareLayout();  
    }  

    public function getFormAction()
    {
        return '/customforms/contactsupport/createpost';
    }

    public function getVariableValue() {
        $var = $this->_varFactory->create();
        $var->loadByCode('custom_form_email_map');
        return $var->getPlainValue();
    }



}
