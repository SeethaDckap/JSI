<?php
namespace Silk\SyncDecoder\Model\Message\Request;


class CTMTB extends \Epicor\Comm\Model\Message\Request
{

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('CTMTB');
        $this->setLicenseType('Customer');
        $this->setConfigBase('epicor_comm_enabled_messages/cmtb_request/');
    }


    /**
     * Bulds the XML request from the set data on this message.
     * @return bool successful message.
     */
    public function buildRequest()
    {
        $message = $this->getMessageTemplate();
        $tables = $this->getTables();
        $message['messages']['request']['body'] = array_merge($message['messages']['request']['body'], array(
                'tables' => $tables
            ));
        $this->setOutXml($message);
        return true;
    }

    private function getTables(){
        $tables = [];
        $tableNames = [
            "decoder_comb_segment_hdr",
            "decoder_comb_segment_dtl",
            "decoder_segment_hdr",
            "decoder_segment_dtl",
            "decoder_segment_dtl_breaks",
            "decoder_segment_hdr_rules",
            "decoder_template_assm_opts",
            "decoder_template_hdr",
            "decoder_template_hdr_dflt",
            "decoder_template_hdr_mask",
            "decoder_template_dtl",
            "decoder_template_dtl_rules",
            "decoder_template_dtl_val_rules"
        ];

        foreach ($tableNames as $name) {
            $tables[] = [
                "table" => [
                    "_attributes" => [
                        "includeSchema" => "Y",
                        "includeData" => "Y"
                    ],
                    "name" => $name
                ]
            ];
        }

        return $tables;
    }

    public function processResponse()
    {
        if ($this->getIsSuccessful()) {
            return $this->getResponse()->getVarienDataFromPath('tables/table');
        }

        return '';
    }

}
