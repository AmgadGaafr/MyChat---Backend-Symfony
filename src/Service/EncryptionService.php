<?php

namespace App\Service;

use Defuse\Crypto\Crypto;

class EncryptionService
{
    public function generateKeys(): array
    {
        // Génération des clés RSA
        $config = [
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        // Création de la paire de clés
        $res = openssl_pkey_new($config);

        // Récupérer la clé privée
        openssl_pkey_export($res, $privateKey);

        // Récupérer la clé publique
        $publicKey = openssl_pkey_get_details($res);
        $publicKey = $publicKey["key"];

        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey
        ];
    }

    public function encryptPrivateKey(string $privateKey, string $token): string
    {
        $encryptedPrivateKey = Crypto::encryptWithPassword($privateKey, $token);
        
        return base64_encode($encryptedPrivateKey);
    }

    public function decryptPrivateKey(string $encryptedPrivateKey, string $token): string
    {
        $encryptedPrivateKey = base64_decode($encryptedPrivateKey);
        $decryptedPrivateKey = Crypto::decryptWithPassword($encryptedPrivateKey, $token);

        return $decryptedPrivateKey;
    }


    public function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}

