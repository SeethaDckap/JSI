<?php
namespace Silk\CustomForms\Block\Adminhtml;

class DealerRequest extends \Magento\Backend\Block\Template
{
    protected $dealerRequestModel;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Silk\CustomForms\Model\DealerRequest $dealerRequestModel, 
        array $data = []
    ) {
        $this->dealerRequestModel = $dealerRequestModel;
        parent::__construct($context, $data);
    }

    public function getRecord(){
        $id = $this->getRequest()->getParam('id');
        $request = $this->dealerRequestModel->load($id, "entity_id");
        return $request;
    }
}
