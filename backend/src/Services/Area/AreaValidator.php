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
    // ライフスタイルデータ（人口・昼夜人口比率・平均年齢・主要産業・交通アクセス）は
    // 「分かる範囲で構いません」（地域登録フォームPDF記載）の任意項目・フリーテキストのため、
    // addressと同じ上限文字数にしている
    private const MAX_LIFESTYLE_FIELD_LENGTH = 255;
    // 「その他」欄はライフスタイルデータの他項目よりまとまった分量を書けるようにしている
    // （地域詳細編集画面PDFの「その他、地域の特色があれば記入してください」欄）
    private const MAX_OTHER_LENGTH = 1000;
    private const MAX_TAG_NAME_LENGTH = 50;
    private const MAX_TAG_COUNT = 10;
    // NOTE: challenges（課題点・問題点）／expected_future（期待する未来）は、
    // area_challenge_requestsテーブル（企業・自治体が実現したいこと掲示板）へ
    // 移管したため、Area本体のバリデーションとしては任意項目として扱う
    // （地域登録フォームで入力されれば、登録者の最初の投稿として作成する）。
    private const MAX_CHALLENGES_LENGTH = 2000;
    private const MAX_EXPECTED_FUTURE_LENGTH = 2000;

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

        $population = $this->validateLifestyleField($input['population'] ?? '', 'population', '人口', $errors);
        $dayNightPopulationRatio = $this->validateLifestyleField(
            $input['day_night_population_ratio'] ?? '',
            'day_night_population_ratio',
            '昼夜人口比率',
            $errors
        );
        $averageAge = $this->validateLifestyleField($input['average_age'] ?? '', 'average_age', '平均年齢', $errors);
        $mainIndustry = $this->validateLifestyleField($input['main_industry'] ?? '', 'main_industry', '主要産業', $errors);
        $transitAccess = $this->validateLifestyleField(
            $input['transit_access'] ?? '',
            'transit_access',
            '交通アクセス',
            $errors
        );
        $other = $this->validateOther($input['other'] ?? '', $errors);

        $tagNames = $this->validateTags($input['tags'] ?? [], $errors);

        // 課題点・問題点／期待する未来：任意項目（area_challenge_requestsへの最初の投稿として
        // 使われる。入力されなければ地域登録時点では投稿を作成しない）
        $challenges = trim((string)($input['challenges'] ?? ''));
        if ($challenges !== '' && mb_strlen($challenges) > self::MAX_CHALLENGES_LENGTH) {
            $errors['challenges'] = '課題点・問題点は' . self::MAX_CHALLENGES_LENGTH . '文字以内で入力してください';
        }

        $expectedFuture = trim((string)($input['expected_future'] ?? ''));
        if ($expectedFuture !== '' && mb_strlen($expectedFuture) > self::MAX_EXPECTED_FUTURE_LENGTH) {
            $errors['expected_future'] = '期待する未来は' . self::MAX_EXPECTED_FUTURE_LENGTH . '文字以内で入力してください';
        }

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
            'population' => $population,
            'day_night_population_ratio' => $dayNightPopulationRatio,
            'average_age' => $averageAge,
            'main_industry' => $mainIndustry,
            'transit_access' => $transitAccess,
            'other' => $other,
            'tags' => $tagNames,
            // challenges/expected_futureは両方入力されている場合のみ、
            // area_challenge_requestsへの最初の投稿としてAreaService側で使う
            'challenges' => $challenges === '' ? null : $challenges,
            'expected_future' => $expectedFuture === '' ? null : $expectedFuture,
        ];
    }

    /**
     * ライフスタイルデータの各項目（人口・昼夜人口比率・平均年齢・主要産業・交通アクセス）は
     * すべて任意項目・フリーテキストのため、共通の空文字→null変換・文字数チェックのみ行う。
     *
     * @param mixed $rawValue
     * @param array<string, string> $errors
     */
    private function validateLifestyleField($rawValue, string $field, string $label, array &$errors): ?string
    {
        $value = trim((string) $rawValue);
        if ($value === '') {
            return null;
        }

        if (mb_strlen($value) > self::MAX_LIFESTYLE_FIELD_LENGTH) {
            $errors[$field] = $label . 'は' . self::MAX_LIFESTYLE_FIELD_LENGTH . '文字以内で入力してください';
        }

        return $value;
    }

    /**
     * 「その他」欄（自由記述）のバリデーション。他のライフスタイル項目より長い文章を想定するため
     * 上限文字数だけ別定数にしている（空文字→null変換のロジックは共通）。
     *
     * @param mixed $rawValue
     * @param array<string, string> $errors
     */
    private function validateOther($rawValue, array &$errors): ?string
    {
        $value = trim((string) $rawValue);
        if ($value === '') {
            return null;
        }

        if (mb_strlen($value) > self::MAX_OTHER_LENGTH) {
            $errors['other'] = 'その他は' . self::MAX_OTHER_LENGTH . '文字以内で入力してください';
        }

        return $value;
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
