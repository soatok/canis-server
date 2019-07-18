<?php
declare(strict_types=1);
namespace Soatok\Canis\Splices;

use Soatok\AnthroKit\Splice;

/**
 * Class Files
 * @package Soatok\Canis\Splices
 */
class Files extends Splice
{
    /**
     * @param int $accountId
     * @param string $name
     * @param int $filesize
     * @param string $type
     * @param string $hash
     * @param array $cdnUrls
     * @param string $localPath
     * @return int|null
     * @throws \Exception
     */
    public function create(
        int $accountId,
        string $name,
        int $filesize,
        string $type,
        string $hash,
        array $cdnUrls = [],
        string $localPath = ''
    ): ?int {
        $this->db->beginTransaction();
        $fileId = $this->db->insertGet(
            'canis_files',
            [
                'uploaded_by' => $accountId,
                'filename' => $name,
                'filesize' => $filesize,
                'filetype' => $type,
                'filehash' => $hash,
                'realpath' => $localPath,
                'cdn_urls' => json_encode($cdnUrls),
            ],
            'fileid'
        );
        if (!$this->db->commit()) {
            return null;
        }
        return (int) $fileId;
    }
}
