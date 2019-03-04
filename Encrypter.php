<?php

namespace App\Services;

use App\Services\Contracts\Encrypter as EncrypterContract;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;

class Encrypter implements EncrypterContract
{
    /**
     * The encryption key.
     *
     * @var string
     */
    protected $key;

    /**
     * The initial vector.
     *
     * @var string
     */
    protected $iv;

    /**
     * The algorithm used for encryption.
     *
     * @var string
     */
    protected $cipher = 'AES-128-CBC';

    /**
     * Create a new encrypter instance.
     *
     * @param string $key
     * @param string $iv
     */
    public function __construct($key, $iv)
    {
        if (!$this->supported($key)) {
            throw new \RuntimeException('The only supported ciphers are AES-128-CBC with 128 bit key lengths.');
        }

        $this->key = $key;
        $this->iv = $iv;
    }

    /**
     * Determine if the given key is valid.
     *
     * @param  string $key
     * @return bool
     */
    private function supported($key)
    {
        return mb_strlen($key, '8bit') === 16;
    }

    /**
     * Encrypt the given value.
     *
     * @param  string $value
     * @return string
     */
    public function encrypt($value)
    {
        $value = \openssl_encrypt($value, $this->cipher, $this->key, OPENSSL_RAW_DATA, $this->iv);

        if ($value === false) {
            throw new EncryptException('Could not encrypt the data.');
        }

        return base64_encode($value);
    }

    /**
     * Decrypt the given value.
     *
     * @param  string $value
     * @return string
     */
    public function decrypt($value)
    {
        $data = base64_decode($value);

        $decrypted = \openssl_decrypt($data, $this->cipher, $this->key, OPENSSL_RAW_DATA, $this->iv);

        if ($decrypted === false) {
            throw new DecryptException('Could not decrypt the data.');
        }

        return $decrypted;
    }
}
