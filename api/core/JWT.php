<?php

namespace LogicLeap\StockManagement\core;

class JWT
{
    private const HASHING_ALGORITHM = 'sha256';

    private static string $hash;
    private static array $header = [
        'typ' => 'JWT',
        'alg' => 'HS256'];

    /**
     * @param string $text Text to be encoded.
     * @return string Url special chars replaced base64 encoded string.
     */
    public static function base64UrlEncode(string $text): string
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }

    /**
     * @param array $payload Payload to generate the JWT token.
     * @return string JWT token string.
     */
    public static function generateToken(array $payload): string
    {
        self::$hash = $_ENV['JWT_SECRET'];

        $payload = json_encode($payload);

        $base64UrlHeader = self::base64UrlEncode(json_encode(self::$header));
        $base64UrlPayload = self::base64UrlEncode($payload);

        $signature = hash_hmac(self::HASHING_ALGORITHM,
            $base64UrlHeader . "." . $base64UrlPayload, self::$hash, true);

        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Check whether token is valid or not. <b>NOTE</b> This function will not check whether token is expired or not.
     * @param string $jwt JWT token to check
     * @return bool True if valid, False otherwise.
     */
    public static function isValidToken(string $jwt): bool
    {
        self::$hash = $_ENV['JWT_SECRET'];

        $tokenParts = explode('.', $jwt);
        $payload = base64_decode($tokenParts[1]);
        $signatureProvided = $tokenParts[2];

        $generatedSignature = self::generateToken($payload);
        if ($generatedSignature === $signatureProvided)
            return true;
        return false;
    }

    /**
     * @param string $jwt JWT token to check.
     * @return bool True if expired, False if not
     * @throws \Exception Throws an error if any occured.
     */
    public static function isExpired(string $jwt): bool
    {
        $tokenParts = explode('.', $jwt);
        $payload = base64_decode($tokenParts[1]);

        $expiration = new \DateTime(json_decode($payload)->exp);
        $now = new \DateTime();
        return ($now->diff($expiration) > 0);
    }

    /**
     * Get token payload content.
     * @param string $jwt JWT token to decode.
     * @return array JWT token payload as an associative array.
     */
    public static function getTokenPayload(string $jwt): array
    {
        $tokenParts = explode('.', $jwt);
        return json_decode(base64_decode($tokenParts[1]));
    }
}