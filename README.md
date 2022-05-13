![PHP Version](https://img.shields.io/packagist/php-v/maharder/postgresql-orm)![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/Gokujo/postgresql-orm)![GitHub release (latest by date)](https://img.shields.io/github/v/release/Gokujo/postgresql-orm)

***

# PostgreSQL ORM Class

I was looking for a class that works with postgres but doesn't required to create and manage models. Only for transactions. 

This class uses the PHP PDO connection, so you have to enable `pdo_pgsql` in your php.ini. While I was writing this class I used PHP version 7.2. I recommend this version or a higher one.

It was my first commit to packagist so if some issues are encountered please let me know.

# Documentation

## Namespaces

### MaHarder

#### Classes

| Class | Description |
|-------|-------------|
| [`PostgreSQL`](./docs/classes/MaHarder/PostgreSQL.md) | PostgreSQL Class.|

## Usage

```php
$postgresql = new PostgreSQL('database', 'user', 'password', 'localhost', 5432);
```

### Standalone

Download latest release and extract it.

Either you include it with `include_once (__DIR__ . '/classes/PostgreSQL.php);` or call a use for it `use MaHarder\classes\PostgreSQL;`

If you change the paths, so use the include

### Composer

Run

```
composer require maharder/postgresql-orm
```

Now you can use the class

***
