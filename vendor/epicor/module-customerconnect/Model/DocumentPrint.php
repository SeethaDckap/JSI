<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model;


class DocumentPrint
{
    const IMAGE_PRINT_PATH = 'customerconnect/document/printoutput';
    const PDF_PRINT_PATH = 'customerconnect/document/pdfprint';
    /**
     * constant for DOWNLOAD DOC PATH
     */
    const DOWNLOAD_DOC_PATH = 'rest/default/V1/carts/customerconnect/downloadattachment';

    public static function getFileExtension($FileMimeType)
    {
        if ($FileMimeType) {
            $fileTypes = [
                'application/pdf' => '.pdf',
                'image/jpg' => '.jpg',
                'image/jpeg' => '.jpeg',
                'image/gif' => '.gif',
            ];

            foreach ($fileTypes as $type => $extension) {
                if ($FileMimeType === $type) {
                    return $extension;
                }
            }
        }
    }


    public static function getDocumentMimeType($binaryData): string
    {
        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        return (string)$fileInfo->buffer($binaryData);
    }

}