<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Listing\Renderer;


class ContractCode extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXmlHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Common\Helper\Xml $commonXmlHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    ) {
        $this->commonXmlHelper = $commonXmlHelper;
        $this->commHelper = $commHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $rowData = $row->getData();
        $rowNumber = array_key_exists('invoice_number', $rowData) ? $row->getInvoiceNumber() : $row->getOrderNumber();   // added for cuis or cuos screen
        $contracts = $this->commonXmlHelper->varienToArray($row->getContracts());
        $contracts = $contracts ? $contracts : $this->commonXmlHelper->varienToArray($row->getContractCode());
        if (is_array($contracts)) {
            $contractList = '';
            foreach ($contracts as $contract) {
                if (is_array($contract)) {
                    foreach ($contract as $key => $contractCode) {
                        $contractList .= ($key > 0) ? '</br>' : '';
                        $contractList .= $this->commHelper->retrieveContractTitle($contractCode);
                    }
                    return '<span id= "contract_code_heading_' . $rowNumber . '" style = "display:block">multiple</span><span id = "contract_codes_' . $rowNumber . '" style = "display:none">' . $contractList . '</span>';
                } else {
                    return $this->commHelper->retrieveContractTitle($contract);
                }
            }
        } else {
            return $contracts ? $this->commHelper->retrieveContractTitle($contracts) : null;
        }
    }

}
