<?php
declare(strict_types=1);
namespace Soatok\Canis\Splices;

use Interop\Container\Exception\ContainerException;
use ParagonIE\Quill\Quill;
use Slim\Container;
use Soatok\AnthroKit\Splice;
use Soatok\Canis\Utility;

/**
 * Class Vendors
 * @package Soatok\Canis\Splices
 */
class Vendors extends Splice
{
    /** @var Quill $quill */
    private $quill;

    /**
     * Splice constructor.
     * @param Container $container
     * @throws ContainerException
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        /** @var Quill $quill */
        $quill = $container['quill'];
        $this->quill = $quill;
    }

    /**
     * @param int $accountId
     * @param string $name
     * @param string $displayName
     * @param string $biography
     * @return int|null
     * @throws \Exception
     */
    public function create(
        int $accountId,
        string $name,
        string $displayName,
        string $biography
    ): ?int {
        $this->db->beginTransaction();
        $vendorId = $this->db->insertGet(
            'canis_vendors',
            [
                'name' => $name,
                'display_name' => $displayName,
                'biography' => $biography
            ],
            'vendorid'
        );
        if (!$vendorId) {
            $this->db->rollBack();
            return null;
        }
        $this->db->insert(
            'canis_vendor_accounts',
            [
                'vendorid' => $vendorId,
                'accountid' => $accountId
            ]
        );
        $this->db->commit();
        return (int) $vendorId;
    }

    /**
     * @param int $vendorId
     * @param string $publicKey
     * @return bool
     * @throws \ParagonIE\Sapient\Exception\HeaderMissingException
     * @throws \ParagonIE\Sapient\Exception\InvalidMessageException
     */
    public function addPublicKey(
        int $vendorId,
        string $publicKey
    ): bool {
        if ($this->db->exists(
            "SELECT count(*) FROM canis_vendor_public_keys WHERE publickey = ?",
            $publicKey
        )) {
            return false;
        }

        $this->db->beginTransaction();
        $publicKeyId = $this->db->insertGet(
            'canis_vendor_public_keys',
            [
                'vendorid' => $vendorId,
                'publickey' => $publicKey,
                'revoked' => false
            ],
            'publickeyid'
        );
        $vendorName = $this->db->cell(
            'SELECT name FROM canis_vendors WHERE vendorid = ?',
            $vendorId
        );
        if (!$this->db->commit()) {
            return false;
        }
        $message = json_encode([
            'action' => 'NEW-PUBLIC-KEY',
            'vendor' => $vendorName,
            'public-key' => $publicKey,
            'public-key-id' => $publicKeyId,
            'time' => (new \DateTime())->format(\DateTime::ISO8601)
        ]);
        $response = $this->quill->write($message);
        $data = (string) $response->getBody();
        $decoded = json_decode($data, true);

        $this->db->beginTransaction();
        $this->db->update(
            'canis_vendor_public_keys',
            [
                'chronicle_create' =>
                    $decoded['results']['summaryhash']
            ],
            [
                'publickeyid' => $publicKeyId
            ]
        );
        return $this->db->commit();
    }

    /**
     * @param int $publicKeyId
     * @return bool
     * @throws \ParagonIE\Sapient\Exception\HeaderMissingException
     * @throws \ParagonIE\Sapient\Exception\InvalidMessageException
     */
    public function revokePublicKey(
        int $publicKeyId
    ): bool {
        $row = $this->db->row(
            "SELECT pk.*, v.name AS vendor_name
             FROM canis_vendor_public_keys pk
             JOIN canis_vendors v ON pk.vendorid = v.vendorid
             WHERE publickeyid = ?",
            $publicKeyId
        );
        if (!$row) {
            return false;
        }
        $message = json_encode([
            'action' => 'REVOKE-PUBLIC-KEY',
            'vendor' => $row['vendor_name'],
            'public-key' => $row['publickey'],
            'public-key-id' => $publicKeyId,
            'time' => (new \DateTime())->format(\DateTime::ISO8601)
        ]);
        $response = $this->quill->write($message);
        $decoded = Utility::getResponseJson($response);

        $this->db->beginTransaction();
        $this->db->update(
            'canis_vendor_public_keys',
            [
                'revoked' =>
                    true,
                'chronicle_revoke' =>
                    $decoded['results']['summaryhash']
            ],
            [
                'publickeyid' => $publicKeyId
            ]
        );
        return $this->db->commit();
    }
}
