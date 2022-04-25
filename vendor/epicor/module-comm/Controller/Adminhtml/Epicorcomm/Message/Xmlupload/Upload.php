<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Xmlupload;

class Upload extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Xmlupload
{

    /**
     * @var \Epicor\Common\Helper\XmlFactory
     */
    protected $commonXmlHelper;

    /**
     * @var \Epicor\Comm\Helper\MessagingFactory
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Common\Helper\XmlFactory $commonXmlHelper,
        \Epicor\Comm\Helper\MessagingFactory $commMessagingHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commonXmlHelper = $commonXmlHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $xmlHelper = $this->commonXmlHelper->create();
            /* @var $xmlHelper \Epicor\Common\Helper\Xml */
            $messageHelper = $this->commMessagingHelper->create();
            /* @var $messageHelper \Epicor\Comm\Helper\Messaging */
            $data = $this->getRequest()->getPost();
            $this->_registry->register('posted_xml_data', $data, true);

            if ($data['input_type'] == \Epicor\Comm\Block\Adminhtml\Message\Xmlupload\Form::XML_FILE_UPLOAD) {
                $xml_file = $_FILES['xml_file']['tmp_name'];
                $xml_message = file_get_contents($xml_file);
            } else if ($data['input_type'] == \Epicor\Comm\Block\Adminhtml\Message\Xmlupload\Form::XML_TEXT_UPLOAD) {
                $xml_message = base64_decode($data['post-xml']);
                $this->_registry->register('posted_data', $data);
            }
            $xml_message = mb_convert_encoding($xml_message, 'UTF-8', mb_detect_encoding($xml_message, 'UTF-8, ISO-8859-1, Windows-1252', true));
            $xml = trim(preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $xml_message));

            try {
                $messageObj = $xmlHelper->convertXmlToVarienObject($xml);

                if ($messageObj !== false && $messageObj->getMessages() && $messageObj->getMessages()->getRequest()) {
                    if (!empty($messageObj->getMessages()->getRequest())) {

                        $response = $messageHelper->processSingleMessage($xml);
                        if (!$response->getIsValidXml()) {
                            $this->messageManager->addErrorMessage('Invalid XML (100)');
                        } elseif (!$response->getIsSuccessful()) {
                            $this->messageManager->addErrorMessage($response->getMsg());
                        } else {
                            $this->messageManager->addSuccessMessage('XML Message process completed successfully');
                        }
                    } else {
                        $index = 1;
                        $error = false;
                        foreach ($messageObj->getMessages()->getRequest() as $messageItem) {
                            if (!$messageItem->get_attributes()) {
                                continue;
                            }
                            $messageItem_Obj = $this->dataObjectFactory->create(array('messages' => $this->dataObjectFactory->create(array('request' => $messageItem))));
                            $messageItem_Xml = $xmlHelper->convertVarienObjectToXml($messageItem_Obj);

                            $response = $messageHelper->processSingleMessage($messageItem_Xml);
                            if (!$response->getIsValidXml() || !$response->getIsSuccessful()) {
                                $error = true;
                                $this->messageManager->addErrorMessage('Message ' . $index . ' : ' . $response->getMsg());
                            }
                            $index++;
                        }
                        if ($error) {
                            $this->messageManager->addWarningMessage('XML Message processing completed with errors');
                        } else {
                            $this->messageManager->addSuccessMessage('XML Message processing complete');
                        }
                    }
                } else {
                    $this->messageManager->addErrorMessage('Invalid XML (103)');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $resultPage = $this->_resultPageFactory->create();

        return $resultPage;
    }

}
