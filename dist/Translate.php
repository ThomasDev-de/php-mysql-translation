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
	protected const DEFAULT_LANGUAGE = 'de';
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

    /**
     * If you already use a `PDO` object for your script, you can pass it directly to the class.
     * This way you avoid that a new instance is built.
     *
     * @param PDO|null $pdo
     * @return void
     */
    public static function setPDO(?PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    /**
     * The function replaces the call to setPDO, setLanguage and setPrefix.
     *
     * @see self::setPDO()
     * @see self::setLanguage()
     * @see self::setPrefix()
     *
     * @param PDO|null $pdo
     * @param string $languageCode
     * @param string|null $prefix
     * @return void
     */
    public static function prepare(?PDO $pdo = null, string $languageCode = self::DEFAULT_LANGUAGE, ?string $prefix = null): void
    {
        self::setPDO($pdo);
        self::setPrefix($prefix);
        self::setLanguage($languageCode);
    }

    /**
     * If a prefix is set, only datasets that start with the prefix are loaded.
     *
     * @param ?string $prefix
     * @return void
     */
    public static function setPrefix(?string $prefix = null): void
    {
        if (empty($prefix)) {
            self::$prefix = null;
        } else {
            // make a dot at the end
            self::$prefix = str_ends_with($prefix, ".") ? $prefix : $prefix . ".";
        }
        // we have to delete library
        self::$library = [];
        self::$libraryLoaded = false;
    }

    /**
     * Sets the language to be fetched from the database. If the language column is <code>null</code>,
     * the string from the default language is set.
     *
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
     * The first parameter is the name of the key to be translated.
     * If the value of the key contains parameters, they are passed as parameters when the function is called.
     *
     * @param string $key
     * @param ...$params
     * @return string
     */
    public static function of(string $key, ...$params): string
    {
        if (!self::$libraryLoaded) {
            self::getLibrary();
        }

        if(self::$prefix !== null)
        {
            $key = self::$prefix.$key;
        }


        if (isset(self::$library[self::$selectedLanguageCode][$key])) {

            $text = self::$library[self::$selectedLanguageCode][$key];

            if (!empty($params)) {
                $text = vsprintf($text, $params);
            }

            return $text;
        }

        return "Translation not found";
    }



    /**
     * @return PDO|null
     */
    protected static function getPDO(): ?PDO
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
	 * @return void
	 */
    private static function getLibrary(): void
    {
        if (!self::$libraryLoaded) {
            self::loadLibrary(self::$selectedLanguageCode);
            self::$libraryLoaded = true;
        }
    }

	/**
	 * @param string $languageCode
	 * @return void
	 */
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

	/**
	 * @return void
	 */
    public static function print(): void
    {
        echo '<pre>', print_r(self::$library), '</pre>';
    }
}