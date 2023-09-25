<?php

namespace Leeto\TicketLiveChat\Helper\Ticket;

use Magento\Framework\App\Helper\AbstractHelper;

class FileValidationHelper extends AbstractHelper
{
    /**
     * @param array $filesData
     * @return array
     */
    public function validateFiles($filesData)
    {
        $isValid = true;
        $errorMessage = '';
        $filesSize = 0;
        $maxFileSize = 15 * 1024 * 1024;
        $resultData = [
            'success' => $isValid,
            'errorMessage' => $errorMessage,
        ];

        foreach ($filesData as $fileInfo) {
            $fileName = $fileInfo[1];
            $fileNameParts = explode('.', $fileName);
            $fileType = end($fileNameParts);
            $fileSize = $fileInfo[2];
            $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];

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

        if ($filesSize > $maxFileSize) {
            $resultData = [
                'error' => true,
                'message' => 'Maximum files size exceeded!'
            ];
        }

        return $resultData;
    }
}
