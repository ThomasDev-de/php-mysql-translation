# php-mysql-translation
## REQUIREMENT
- php 8.0+
- php pdo & mysql extension
- MySQL or MariaDB
---
## INSTALLATION

1. Create required database table `translation`

```sql
create table translation
(
    `key`   varchar(255)     primary key,
    `de`      text           not null, -- Set the default language to not null
    `en`      text           null -- Add additional required language codes and set them to null 
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
---
## USAGE
We insert a record into the translation table.
```sql
INSERT INTO `translation (`key`, `de`, `en`) VALUES ('sayHello', 'Hallo %s %s, schön dich zu treffen!', 'Hello %s %s, nice to meet you!');
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
As can be seen in the example above, conversion statement can be defined in the text.  
Conversion statement start with the % character.  
More information about this on [www.php.net](https://www.php.net/manual/en/function.sprintf.php).

---
The Translate.php class always loads all records from the `translation` table. If you want to load only certain records, it is recommended to set a `prefix` before the translation key.

**One example of this:**  
We insert a record with prefix into the translation table.
```sql
INSERT INTO `translation (`key`, `de`, `en`) VALUES ('signIn.headline', 'Bitte melden Sie sich an.', 'Please sign up.');
```
| key             | de                                  | en                             |
|-----------------|-------------------------------------|--------------------------------|
| sayHello        | Hallo %s %s, schön dich zu treffen! | Hello %s %s, nice to meet you! |
| signIn.headline | Bitte melden Sie sich an.           | Please sign up.                |

Call the function Translate::setPrefix(prefix)
```php
<?php
Translate::setPrefix('signIn');
echo Translate::of('headline'); // Please sign up.
?>
```

---