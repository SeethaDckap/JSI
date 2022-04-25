<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Customer\ReturnModel;

use Magento\Framework\App\Config\ScopeConfigInterface;

class NewFileAttachments
{
    private $maxFileNameLength;
    private $fileErrors = [];
    private $scopeConfig;
    private $validNewAttachments = true;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
        $this->validateNewFilesAttachments();
    }

    public function getFileErrors()
    {
        return $this->fileErrors;
    }

    public function isValidAttachments(): bool
    {
        return $this->validNewAttachments;
    }

    private function validateNewFilesAttachments()
    {
        try {
            $attachments = [$this->getNewLineAttachments(), $this->getNewAdditionalAttachments()];
            foreach ($attachments as $newAttachment) {
                if (is_array($newAttachment) && !empty($newAttachment)) {
                    $this->processAttachments($newAttachment);
                }
            }
        } catch (\Exception $e) {
            $this->fileErrors[] = $e->getMessage();
        }
    }

    private function getNewLineAttachments()
    {
        return $_FILES['lineattachments']['name']['new'] ?? [];
    }

    private function getNewAdditionalAttachments()
    {
        return $_FILES['attachments']['name']['new'] ?? [];
    }

    /**
     * @param $fileName
     * @return bool
     * @throws \Exception
     */
    private function isValidFileNameLength($fileName): bool
    {
        $valid = true;
        if (!$fileName) {
            return false;
        }

        if ($this->getMaxFileNameLength() && $this->isMaxLengthExceeded($fileName)) {
            $error = "Error an attachment file name exceeds {$this->getMaxFileNameLength()} characters";
            $this->fileErrors[] = $error;
            $this->validNewAttachments = false;
            $valid = false;
        }
        return $valid;
    }

    private function isMaxLengthExceeded($fileName): bool
    {
        return  strlen($fileName) > $this->getMaxFileNameLength();
    }

    public function getMaxFileNameLength(): int
    {
        if (!$this->maxFileNameLength) {
            $this->maxFileNameLength = $this->scopeConfig
                ->getValue('Epicor_Comm/erp_file_settings/max_file_name_length');
        }
        return (int) $this->maxFileNameLength;
    }

    private function validateNewFileName($fileName)
    {
        $valid = false;
        try {
            if ($fileName) {
                $valid = $this->isValidFileNameLength($fileName);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return $valid;
    }

    /**
     * @param array $newLineAttachmentNames
     */
    private function processAttachments(array $newLineAttachmentNames)
    {
        foreach ($newLineAttachmentNames as $fileNames) {
            $this->validateFileNames($fileNames);
        }
    }

    /**
     * @param $fileNames
     */
    private function validateFileNames(array $fileNames)
    {
        $attachmentFileName = $fileNames['filename'] ?? false;
        if ($attachmentFileName && is_string($attachmentFileName)) {
            $this->validateNewFileName($attachmentFileName);
        } else {
            $this->validateFileNameInArray($fileNames);
        }
    }

    /**
     * @param array $fileNames
     */
    private function validateFileNameInArray(array $fileNames)
    {
        foreach ($fileNames as $fileInfo) {
            if ($attachmentFileName = $fileInfo['filename'] ?? false) {
                $this->validateNewFileName($attachmentFileName);
            }
        }
    }
}
