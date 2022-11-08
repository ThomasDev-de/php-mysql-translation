<?php

/**
 * Description of Translate
 * File: Translate.php
 * Created: 31.07.22 22:40
 * Deployment: PhpStorm
 *
 * @author Thomas Kirsch <t.kirsch@webcito.de>
 * @noinspection PhpUnused
 */
final class Translate
{
    /**
     * default language
     */
    public const DEFAULT_LANGUAGE = 'de';
    protected const DB_TYPE = 'mysql';
    protected const DB_CHARSET = 'utf8';
    protected const DB_HOST = '127.0.0.1';
    protected const DB_USER = 'demo_user';
    protected const DB_USER_PASSWORD = 'demo_user';
    protected const DB_DATABASE = 'demo';
    protected const DB_DATABASE_PORT = 3306;

    protected static ?PDO $pdo = null;
    protected static array $library = [];
    protected static ?string $prefix = null;
    protected static bool $libraryLoaded = false;
    protected static string $selectedLanguageCode = self::DEFAULT_LANGUAGE;

    public static function setPDO(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    /**
     * @return PDO|null
     */
    private static function getPDO(): ?PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $dns = [
            self::DB_TYPE . ":dbname=" . self::DB_DATABASE,
            "host=" . self::DB_HOST,
            "port=" . self::DB_DATABASE_PORT,
            "charset=" . self::DB_CHARSET
        ];

        try {
            // connect
            $pdo = new PDO(
                dsn: implode(";", $dns),
                username: self::DB_USER,
                password: self::DB_USER_PASSWORD,
                options: [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . self::DB_CHARSET,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
        } catch (PDOException $e) {
            throw new RuntimeException(
                message: $e->getMessage(),
                code: $e->getCode()
            );
        }

        return $pdo;
    }

    /**
     * @param ?string $prefix
     * @return void
     */
    public static function setPrefix(?string $prefix = null): void
    {
        if ($prefix === "") {
            self::$prefix = null;
        } else {
            self::$prefix = str_ends_with($prefix, ".") ? $prefix : $prefix . ".";
        }
    }

    /**
     * @param string $languageCode
     * @return void
     */
    public static function setLanguage(string $languageCode = self::DEFAULT_LANGUAGE): void
    {
        if (self::$selectedLanguageCode !== $languageCode) {
            self::$libraryLoaded = false;
        }
        self::$selectedLanguageCode = $languageCode;
    }

    /**
     * @param string $key
     * @param ...$params
     * @return string
     */
    public static function of(string $key, ...$params): string
    {
        if (!self::$libraryLoaded) {
            self::getLibrary();
        }

        if (isset(self::$library[self::$selectedLanguageCode][$key])) {
            $text = self::$library[self::$selectedLanguageCode][$key];

            if (!empty($params)) {
                $text = vsprintf($text->text, $params);
            }

            return $text;
        }

        return "Translation not found";
    }

    private static function getLibrary(): void
    {
        if (!self::$libraryLoaded) {
            self::loadLibrary(self::$selectedLanguageCode);
            self::$libraryLoaded = true;
        }
    }

    private static function loadLibrary(string $languageCode = self::DEFAULT_LANGUAGE): void
    {
        if (!isset(self::$library[self::$selectedLanguageCode][$languageCode])) {

            $pdo = self::getPDO();

            $where = "";
            if (null !== self::$prefix) {
                $where = "WHERE `key` LIKE CONCAT('" . self::$prefix . "', '%')";
            }

            if ($languageCode === self::DEFAULT_LANGUAGE) {
                $sql = "SELECT `key`, `$languageCode` FROM translation $where";
            } else {
                $sql = "
                    SELECT 
                        `key`, 
                        IF(`$languageCode` IS NULL, `" . self::DEFAULT_LANGUAGE . "`, `$languageCode`) as '$languageCode'
                    FROM translation
                    $where
                ";
            }

            $query = $pdo?->query($sql);
            $rows = $query->fetchAll();

            foreach ($rows as $row) {
                self::$library[self::$selectedLanguageCode][$row->key] = $row->{$languageCode};
            }
        }
    }

    public static function print(): void
    {
        echo '<pre>', print_r(self::$library), '</pre>';
    }
}