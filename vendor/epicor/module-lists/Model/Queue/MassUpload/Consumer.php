<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Model\Queue\MassUpload;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Notification\NotifierInterface;
use Epicor\Lists\Helper\DataFactory as ListHelper;
use Epicor\Lists\Model\ImportManagement;
use Epicor\Lists\Api\Data\MassUploadInterface;
use Epicor\Lists\Model\Import;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Psr\Log\LoggerInterface;

/**
 * Consumer For CAAP Message Use For Sync.
 * Call Via Magento Queue Mechanism.
 */
class Consumer
{

    /**
     * NotifierInterface.
     *
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ListHelper
     */
    private $listHelper;

    /**
     * @var ImportManagement
     */
    private $importManagement;

    /**
     * Serializer interface instance.
     *
     * @var JsonSerializer
     */
    protected $serializer;

    /**
     * Consumer constructor.
     *
     * @param LoggerInterface   $logger
     * @param NotifierInterface $notifier
     * @param ListHelper        $listHelper
     * @param ImportManagement  $importManagement
     * @param JsonSerializer    $jsonSerializer
     */
    public function __construct(
        LoggerInterface $logger,
        NotifierInterface $notifier,
        ListHelper $listHelper,
        ImportManagement $importManagement,
        JsonSerializer $jsonSerializer
    ) {
        $this->logger           = $logger;
        $this->notifier         = $notifier;
        $this->listHelper       = $listHelper;
        $this->importManagement = $importManagement;
        $this->serializer       = $jsonSerializer;
    }

    /**
     * @param MassUploadInterface $csvInfo
     */
    public function process(MassUploadInterface $csvInfo)
    {
        try {
            $filename = $csvInfo->getFileName();
            $id = $csvInfo->getId();
            if ($filename) {
                $helper = $this->listHelper->create();
                /* @var $helper ListHelper */
                $this->logger->error($this->importManagement->getFolderPath()
                    .DIRECTORY_SEPARATOR.$filename);
                $result = $helper->importListFromCsv(
                    $this->importManagement->getFolderPath().DIRECTORY_SEPARATOR
                    .$filename
                );

                //Save Import.
                $this->saveImportLog($id, $result);

            }
        } catch (LocalizedException $e) {
            $this->notifier->addCritical(
                __('Error during list mass upload process occurred'),
                __('Error during list mass upload process occurred. Please check logs for detail')
            );
            $this->logger->critical('Something went wrong while Caap background order process. '
                .$e->getMessage());
            $this->logger->error($e);
        } catch (\Exception $e) {
            $this->notifier->addCritical(
                __('Error during list mass upload process occurred'),
                __('Error during list mass upload process occurred. Please check logs for detail')
            );
            $this->logger->critical('Something went wrong while Caap background order process. '
                .$e->getMessage());
            $this->logger->error($e);
        }//end try

    }//end process()

    /**
     * Save Import Log.
     *
     * @param $id
     * @param $result
     */
    private function saveImportLog($id, $result)
    {
        $messages = $this->setErrors($result);
        $import = $this->importManagement->getMassLogById($id);
        $import->setStatus(ImportManagement::STATUS_SUCCESS);
        $import->setMessage($this->serializer->serialize($messages));

        //Error Message.
        if (count($messages) > 0) {
            $import->setMessage($this->serializer->serialize($messages));
            $import->setStatus(ImportManagement::STATUS_SUCCESS_WITH_WARNING);
        }

        //Error Status.
        if (isset($result['list'])) {
            $list = $result['list'];
            if (!$list->getId()) {
                $import->setStatus(ImportManagement::STATUS_ERROR);
            }
        }

        //Save.
        $this->importManagement->saveMassLog($import);
    }

    /**
     * Set Array.
     *
     * @param array $result
     *
     * @return array
     */
    private function setErrors($result)
    {
        $messages = [];
        if (isset($result['errors']['errors'])
            && is_array($result['errors']['errors'])
        ) {
            foreach ($result['errors']['errors'] as $error) {
                $messages['errors'][] = $error;
            }
        }

        if (isset($result['errors']['warnings'])
            && is_array($result['errors']['warnings'])
        ) {
            foreach ($result['errors']['warnings'] as $error) {
                $messages['warnings'][] = $error;
            }
        }

        return $messages;
    }
}
