<?php
declare(strict_types=1);
namespace Soatok\Canis\Splices;

use ParagonIE\Quill\Quill;
use Slim\Container;
use Interop\Container\Exception\ContainerException;
use Soatok\AnthroKit\Splice;
use Soatok\Canis\Utility;
use Soatok\DholeCrypto\Asymmetric;
use Soatok\DholeCrypto\Exceptions\CryptoException;
use Soatok\DholeCrypto\Key\AsymmetricPublicKey;
use Soatok\DholeCrypto\Keyring;

/**
 * Class Packages
 * @package Soatok\Canis\Splices
 */
class Packages extends Splice
{
    /** @var Quill $quill */
    private $quill;

    /**
     * Packages constructor.
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
     * @param int $vendorId
     * @param string $name
     * @param string $platform
     * @param string $type
     * @return int|null
     * @throws \Exception
     */
    public function create(
        int $vendorId,
        string $name,
        string $platform,
        string $type
    ): ?int {
        $this->db->beginTransaction();
        $packageId = $this->db->insertGet(
            'canis_packages',
            [
                'vendorid' => $vendorId,
                'name' => $name,
                'platform' => $platform,
                'type' => $type
            ],
            'packageid'
        );
        if (!$this->db->commit()) {
            return null;
        }
        return (int) $packageId;
    }

    /**
     * @param int $oldPackage
     * @param int $replacement
     * @return bool
     */
    public function deprecate(int $oldPackage, int $replacement): bool
    {
        $this->db->beginTransaction();
        $this->db->update(
            'canis_packages',
            [
                'deprecated' => true,
                'recommend' => $replacement
            ],
            [
                'packageid' => $oldPackage
            ]
        );
        return $this->db->commit();
    }

    /**
     * @param int $packageId
     * @return array
     */
    public function getById(int $packageId): array
    {
        $package = $this->db->row(
            "SELECT 
                v.name AS vendor_name,
                p.*
             FROM canis_packages p
             JOIN canis_vendors v ON p.vendorid = v.vendorid
             WHERE p.packageid = ?",
            $packageId
        );
        if (!$package) {
            return [];
        }
        $package['meta'] = $this->db->row(
            "SELECT * 
             FROM canis_package_meta 
             WHERE packageid = ?
             ORDER BY metaid DESC LIMIT 1",
            $packageId
        );
        return $package;
    }

    /**
     * @param int $packageId
     * @param int $publicKeyId
     * @param int $fileId
     * @param string $tagName
     * @param string $data
     * @param string $signature
     * @param bool $stable
     * @return bool
     * @throws CryptoException
     * @throws \ParagonIE\Sapient\Exception\HeaderMissingException
     * @throws \ParagonIE\Sapient\Exception\InvalidMessageException
     * @throws \SodiumException
     */
    public function release(
        int $packageId,
        int $publicKeyId,
        int $fileId,
        string $tagName,
        string $data,
        string $signature,
        bool $stable
    ): bool {
        $this->db->beginTransaction();
        $publicKeySerialized = $this->db->cell(
            "SELECT publickey FROM canis_vendor_public_keys WHERE publickeyid = ?",
            $publicKeyId
        );

        /** @var AsymmetricPublicKey $publicKey */
        $publicKey = (new Keyring())->load($publicKeySerialized);
        if (!Asymmetric::verify($data, $publicKey, $signature)) {
            return false;
        }

        $response = Utility::getResponseJson($this->quill->write($data));
        $hash = $response['results']['summaryhash'];
        $this->db->insert(
            'canis_package_releases',
            [
                'packageid' => $packageId,
                'tagname' => $tagName,
                'stable' => $stable,
                'publickeyid' => $publicKeyId,
                'fileid' => $fileId,
                'signature' => $signature,
                'chronicle_data' => $data,
                'chronicle_create' => $hash,
                'revoked' => false
            ]
        );
        return $this->db->commit();
    }

    /**
     * @param int $releaseId
     * @param string $reason
     * @param string|null $message
     * @return bool
     * @throws \ParagonIE\Sapient\Exception\HeaderMissingException
     * @throws \ParagonIE\Sapient\Exception\InvalidMessageException
     */
    public function revokeRelease(
        int $releaseId,
        string $reason,
        ?string $message = null
    ): bool {
        if (!$message) {
            $data = $this->db->row(
                "SELECT 
                v.name AS vendor_name,
                p.name AS package_name,
                pr.tagname AS release_version
             FROM canis_packages p
             JOIN canis_vendors v ON p.vendorid = v.vendorid
             JOIN canis_package_releases pr on p.packageid = pr.packageid
             WHERE pr.releaseid = ?
            ",
                $releaseId
            );
            if (empty($data)) {
                return false;
            }

            $message = json_encode([
                'action' => 'REVOKE-PACKAGE-UPDATE',
                'reason' => $reason,
                'vendor' => $data['vendor_name'],
                'package' => $data['package_name'],
                'version' => $data['release_version'],
                'time' => (new \DateTime())->format(\DateTime::ISO8601)
            ]);
        }
        $response = Utility::getResponseJson($this->quill->write($message));
        $hash = $response['results']['summaryhash'];

        $this->db->beginTransaction();
        $this->db->update(
            'canis_package_releases',
            [
                'chronicle_revoke' => $hash,
                'revoked' => true,
                'revoke_reason' => $reason
            ],
            [
                'releaseid' => $releaseId
            ]
        );
        return $this->db->commit();
    }

    /**
     * @param int $packageId
     * @param string $summary
     * @param array $tags
     * @return int|null
     * @throws \Exception
     */
    public function refreshMetadata(
        int $packageId,
        string $summary,
        array $tags
    ): ?int {
        $this->db->beginTransaction();
        $metaId = $this->db->insertGet(
            'canis_package_meta',
            [
                'packageid' => $packageId,
                'summary' => $summary,
                'tags' => json_encode($tags),
            ],
            'metaid'
        );
        if (!$this->db->commit()) {
            return null;
        }
        return (int) $metaId;
    }
}
