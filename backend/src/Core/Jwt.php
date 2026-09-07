<?php
// 自前のJWT(HS256)実装。
// この backend はフレームワーク・Composerなしの方針（README参照）のため、
// firebase/php-jwt 等のライブラリを追加せず、必要な最小限（発行・検証）だけを自前で書く。

class Jwt
{
    private const ALGORITHM = 'HS256';

    // config/jwt.php からシークレットを取得する。
    // 未設定のまま動かすとトークンの署名強度が保証できないため、ここで必ず落とす
    // （AGENTS.md 6節：環境変数は起動時にバリデーションする）。
    public static function getSecret(): string
    {
        $config = require __DIR__ . '/../../config/jwt.php';
        $secret = $config['secret'] ?? '';

        if ($secret === '') {
            throw new RuntimeException(
                'JWT_SECRET が設定されていません。config/jwt.local.php を用意するか、環境変数 JWT_SECRET を設定してください。'
            );
        }

        return $secret;
    }

    public static function getExpirySeconds(): int
    {
        $config = require __DIR__ . '/../../config/jwt.php';
        return $config['expirySeconds'] ?? 86400;
    }

    // ペイロード（連想配列）からJWTを発行する。exp/iatはここで付与する。
    public static function encode(array $payload): string
    {
        $secret = self::getSecret();

        $header = [
            'alg' => self::ALGORITHM,
            'typ' => 'JWT',
        ];

        $payload['iat'] = $payload['iat'] ?? time();
        $payload['exp'] = $payload['exp'] ?? (time() + self::getExpirySeconds());

        $headerEncoded  = self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_UNICODE));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));

        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    // JWTを検証してペイロードを返す。署名不正・期限切れの場合は例外を投げる。
    public static function decode(string $token): array
    {
        $secret = self::getSecret();

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new InvalidArgumentException('トークンの形式が不正です');
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $actualSignature = self::base64UrlDecode($signatureEncoded);

        // タイミング攻撃を避けるため hash_equals で比較する（==や!==は使わない）
        if (!hash_equals($expectedSignature, $actualSignature)) {
            throw new InvalidArgumentException('トークンの署名が不正です');
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        if (!is_array($payload)) {
            throw new InvalidArgumentException('トークンのペイロードが不正です');
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new InvalidArgumentException('トークンの有効期限が切れています');
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $padded = str_pad($data, strlen($data) % 4 === 0 ? strlen($data) : strlen($data) + (4 - strlen($data) % 4), '=');
        return base64_decode(strtr($padded, '-_', '+/'));
    }
}
