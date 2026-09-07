<?php

/**
 * Area関連のバリデーションエラーを表す例外
 * 複数フィールドのエラーメッセージをまとめて保持できるようにしている
 */
if (!class_exists('AreaValidationException')) {
    class AreaValidationException extends \Exception
    {
        /** @var array<string, string> */
        private $errors;

        public function __construct(array $errors)
        {
            $this->errors = $errors;
            parent::__construct('Area validation failed');
        }

        /**
         * @return array<string, string>
         */
        public function getErrors(): array
        {
            return $this->errors;
        }
    }
}

/**
 * Area入力値のバリデーションを行うクラス
 *
 * 意図的にDBに依存しない純粋なロジックとして切り出している。
 * これにより Core/Database.php の実装を待たずに単体テストできる。
 *
 * NOTE: areasテーブルの実際のカラム構成（name, features, latitude,
 * longitude, address）に合わせている。user_idはクライアント入力ではなく
 * 認証情報（セッション）から取得するため、ここではバリデーション対象外。
 */
class AreaValidator
{
    // 緯度・経度の有効範囲（Zero Trust: フロントのバリデーションだけに頼らずバックエンドでも検証する）
    private const LATITUDE_MIN = -90;
    private const LATITUDE_MAX = 90;
    private const LONGITUDE_MIN = -180;
    private const LONGITUDE_MAX = 180;

    private const MAX_NAME_LENGTH = 255;
    private const MAX_ADDRESS_LENGTH = 255;
    private const MAX_TAG_NAME_LENGTH = 50;
    private const MAX_TAG_COUNT = 10;

    /**
     * 入力値をバリデーションし、正規化済みのデータを返す
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed> バリデーション済みのデータ（tagsはタグ名の配列）
     * @throws AreaValidationException
     */
    public function validate(array $input): array
    {
        $errors = [];

        $name = trim((string)($input['name'] ?? ''));
        if ($name === '') {
            $errors['name'] = '地域名は必須です';
        } elseif (mb_strlen($name) > self::MAX_NAME_LENGTH) {
            $errors['name'] = '地域名は' . self::MAX_NAME_LENGTH . '文字以内で入力してください';
        }

        // features・address はDB上nullable（任意項目）
        $featuresRaw = trim((string)($input['features'] ?? ''));
        $features = $featuresRaw === '' ? null : $featuresRaw;

        $addressRaw = trim((string)($input['address'] ?? ''));
        $address = $addressRaw === '' ? null : $addressRaw;
        if ($address !== null && mb_strlen($address) > self::MAX_ADDRESS_LENGTH) {
            $errors['address'] = '住所は' . self::MAX_ADDRESS_LENGTH . '文字以内で入力してください';
        }

        $tagNames = $this->validateTags($input['tags'] ?? [], $errors);

        $latitude = null;
        if (isset($input['latitude']) && $input['latitude'] !== '' && $input['latitude'] !== null) {
            if (!is_numeric($input['latitude'])) {
                $errors['latitude'] = '緯度は数値で入力してください';
            } else {
                $latitude = (float)$input['latitude'];
                if ($latitude < self::LATITUDE_MIN || $latitude > self::LATITUDE_MAX) {
                    $errors['latitude'] = '緯度は' . self::LATITUDE_MIN . '〜' . self::LATITUDE_MAX . 'の範囲で入力してください';
                }
            }
        }

        $longitude = null;
        if (isset($input['longitude']) && $input['longitude'] !== '' && $input['longitude'] !== null) {
            if (!is_numeric($input['longitude'])) {
                $errors['longitude'] = '経度は数値で入力してください';
            } else {
                $longitude = (float)$input['longitude'];
                if ($longitude < self::LONGITUDE_MIN || $longitude > self::LONGITUDE_MAX) {
                    $errors['longitude'] = '経度は' . self::LONGITUDE_MIN . '〜' . self::LONGITUDE_MAX . 'の範囲で入力してください';
                }
            }
        }

        if (!empty($errors)) {
            throw new AreaValidationException($errors);
        }

        return [
            'name' => $name,
            'features' => $features,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address,
            'tags' => $tagNames,
        ];
    }

    /**
     * タグ配列のバリデーション。重複除去したタグ名の配列を返す。
     * エラーがあれば参照渡しの$errorsに追加する。
     *
     * @param mixed $rawTags
     * @param array<string, string> $errors
     * @return array<string>
     */
    private function validateTags($rawTags, array &$errors): array
    {
        if (!is_array($rawTags)) {
            $errors['tags'] = 'タグの形式が不正です';
            return [];
        }

        if (count($rawTags) > self::MAX_TAG_COUNT) {
            $errors['tags'] = 'タグは' . self::MAX_TAG_COUNT . '個までです';
            return [];
        }

        $tagNames = [];
        foreach ($rawTags as $rawTag) {
            if (!is_string($rawTag) && !is_numeric($rawTag)) {
                $errors['tags'] = 'タグの形式が不正です';
                return [];
            }

            $tagName = trim((string)$rawTag);
            if ($tagName === '') {
                continue;
            }

            if (mb_strlen($tagName) > self::MAX_TAG_NAME_LENGTH) {
                $errors['tags'] = 'タグは' . self::MAX_TAG_NAME_LENGTH . '文字以内で入力してください';
                return [];
            }

            $tagNames[] = $tagName;
        }

        return array_values(array_unique($tagNames));
    }
}
