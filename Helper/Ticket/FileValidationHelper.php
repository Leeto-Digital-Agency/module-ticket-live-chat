<?php

namespace Leeto\TicketLiveChat\Helper\Ticket;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class FileValidationHelper extends AbstractHelper
{
    /**
     * @param Context $context
     */
    public function _construct(
        Context $context,
    ) {
        parent::__construct($context);
    }

    /**
     * @param array $filesData
     * @return array
     */
    public function validateFiles($filesData)
    {
        $isValid = true;
        $errorMessage = '';
        $filesSize = 0;
        $maxFileSize = $this->getMaximumFilesSize();
        // converted to bytes
        $maxFileSizeConverted = $maxFileSize * 1024 * 1024;
        $resultData = [
            'success' => $isValid,
            'errorMessage' => $errorMessage,
        ];

        foreach ($filesData as $fileInfo) {
            $fileName = $fileInfo[1];
            $fileNameParts = explode('.', $fileName);
            $fileType = end($fileNameParts);
            $fileSize = $fileInfo[2];
            $allowedTypes = explode(',', $this->getAllowedFileExtensions());

            if (!in_array($fileType, $allowedTypes)) {
                $isValid = false;
                $errorMessage = 'Invalid file(s) detected.';
                $resultData = [
                    'error' => true,
                    'message' => $errorMessage
                ];
                break;
            }
            $filesSize += $fileSize;
        }

        if ($filesSize > $maxFileSizeConverted) {
            $resultData = [
                'error' => true,
                'message' => 'Maximum files size exceeded!'
            ];
        }

        return $resultData;
    }

    /**
     * @return string
     */
    public function getAllowedFileExtensions()
    {
        return $this->scopeConfig
            ->getValue(
                'support/ticket_files_upload/allowed_extensions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * @return string
     */
    public function getMaximumFilesSize()
    {
        return $this->scopeConfig
            ->getValue(
                'support/ticket_files_upload/maximum_files_size',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * @return string
     */
    public function getMaximumFilesToUpload()
    {
        return $this->scopeConfig
            ->getValue(
                'support/ticket_files_upload/maximum_files',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }
}
