<?php
namespace Silk\CustomForms\Block\Adminhtml;

class ContactSupport extends \Magento\Backend\Block\Template
{
    protected $contactSupportModel;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Silk\CustomForms\Model\ContactSupport $contactSupportModel, 
        array $data = []
    ) {
        $this->contactSupportModel = $contactSupportModel;
        parent::__construct($context, $data);
    }

    public function getRecord(){
        $id = $this->getRequest()->getParam('id');
        $request = $this->contactSupportModel->load($id, "entity_id");
        return $request;
    }
}
