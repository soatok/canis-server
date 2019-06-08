<?php
declare(strict_types=1);
namespace Soatok\Canis\Splices;

use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Binary;
use ParagonIE\HiddenString\HiddenString;
use Slim\Container;
use Soatok\AnthroKit\Splice;
use Soatok\DholeCrypto\Exceptions\CryptoException;
use Soatok\DholeCrypto\Key\SymmetricKey;
use Soatok\DholeCrypto\Password;
use SodiumException;

/**
 * Class Accounts
 * @package Soatok\Canis\Splices
 */
class Accounts extends Splice
{
    /** @var SymmetricKey $passwordKey */
    private $passwordKey;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->passwordKey = $container->get('settings')['password-key'];
    }

    /**
     * @param string $login
     * @param HiddenString $password
     * @return int
     * @throws \Exception
     * @throws \SodiumException
     */
    public function createAccount(
        string $login,
        HiddenString $password
    ): int {
        $exists = $this->db->exists(
            'SELECT count(*) FROM accounts WHERE login = ?',
            $login
        );
        if ($exists) {
            return 0;
        }

        $accountId = $this->db->insertGet(
            'accounts',
            [
                'login' => $login
            ],
            'accountid'
        );
        $this->db->update(
            'accounts',
            [
                'pwhash' => (new Password($this->passwordKey))
                    ->hash($password, (string) $accountId)
            ],
            [
                'accountid' => $accountId
            ]
        );
        return $accountId;
    }

    /**
     * @param string $login
     * @param HiddenString $password
     * @return int|null
     * @throws CryptoException
     * @throws SodiumException
     */
    public function loginWithPassword(string $login, HiddenString $password): ?int
    {
        $row = $this->db->row('SELECT * FROM accounts WHERE login = ?', $login);
        if (empty($row)) {
            return null;
        }
        $hasher = new Password($this->passwordKey);
        if (!$hasher->verify($password, $row['pwhash'], (string) $row['accountid'])) {
            return null;
        }
        return (int) $row['accountid'];
    }

    /**
     * Create and return a device token which allows two-factor authentication
     * to be bypassed for up to [policy-determined, default 30] days.
     *
     * @param int $accountId
     * @return string
     * @throws SodiumException
     */
    public function createDeviceToken(int $accountId): string
    {
        $selector = random_bytes(20);
        $returnSecret = random_bytes(35);
        $hashed = \sodium_crypto_generichash(
            \ParagonIE_Sodium_Core_Util::store64_le($accountId) .
            $selector,
            $returnSecret
        );

        $this->db->insert('canis_account_known_device', [
            'accountid' => $accountId,
            'selector' => Base32::encode($selector),
            'validator' => Base32::encode($hashed)
        ]);
        return Base32::encode($selector . $returnSecret);
    }

    /**
     * @param string $token
     * @param int $accountId
     * @return bool
     * @throws SodiumException
     */
    public function checkDeviceToken(string $token, int $accountId): bool
    {
        $selector = Binary::safeSubstr($token, 0, 32);
        $validator = Binary::safeSubstr($token, 32);

        $hashed = \sodium_crypto_generichash(
            \ParagonIE_Sodium_Core_Util::store64_le($accountId) .
            Base32::decode($selector),
            Base32::decode($validator)
        );

        // TODO: Expiration time
        $stored = $this->db->cell(
            "SELECT validator 
            FROM canis_account_known_device
            WHERE selector = ? AND accountid = ?",
            $selector,
            $accountId
        );

        return hash_equals(Base32::decode($stored), $hashed);
    }
}
