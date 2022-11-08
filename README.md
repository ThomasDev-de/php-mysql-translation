# php-mysql-translation
## REQUIREMENT
- php 8.0+
- php pdo & mysql extension
- MySQL or MariaDB
## INSTALLATION

1. Create required database table `translation`

```sql
create table translation
(
    `key`   varchar(255)                         primary key,
    `de`      text                               not null, -- Set the default language to not null
    `en`      text                               null, -- Add additional required language codes and set them to null 
    `created` datetime default CURRENT_TIMESTAMP not null, -- optional, leave it out if you don't need it
    `updated` datetime                           null on update CURRENT_TIMESTAMP -- optional, leave it out if you don't need it
);
```
2. Include the `Translate.php` class into your script. (It is located in the dist/ folder)
```php
require_once "Translate.php";
```
3. Setup mysql connection
- If you have already created a pdo object in the script, tell it to the Translate.php class
```php
Translate::setPDO($myPDO);
```
- Otherwise, take a look at the getPDO function in the Translate.php class and build your PDO object there.
```php
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
```
## USAGE
We insert a record into the translation table
```sql
INSERT INTO translation (`key`, `de`, `en`) VALUES ('sayHello', 'Hallo %s %s, schön dich zu treffen!', 'Hello %s %s, nice to meet you!');
```
| key      | de                                  | en                             |
|----------|-------------------------------------|--------------------------------|
| sayHello | Hallo %s %s, schön dich zu treffen! | Hello %s %s, nice to meet you! |

Call the function Translate::of(key, ...params)
```php
<?php
// Note, German is the default language in my example
echo Translate::of('sayHello', 'Max', 'Mustermann'); // Hallo Max Mustermann, schön dich zu treffen
// Let's change the language to English
Translate::setLanguage('en');
echo Translate::of('sayHello', 'John', 'Doe'); // Hello John Doe, nice to meet you!
?>
```