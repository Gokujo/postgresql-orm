***

# PostgreSQL

PostgreSQL Class.

* Full name: `\MaHarder\classes\PostgreSQL`

## Properties

### host

DB Host of PostgreSQL, default: localhost

```php
private string $host
```

***

### db

DB name

```php
private string $db
```

***

### user

DB username (user login)

```php
private string $user
```

***

### password

DB user password

```php
private string $password
```

***

### port

DB port, default: 5432

```php
private int $port
```

***

### pdo

DB Connection
When not all creditional are given returns a NULL

```php
private ?PDO $pdo
```

***

## Methods

### **__construct**

Class init / constructor.

```php
public __construct(string $db, string $user, string $password, string $host = "localhost", int $port = 5432): PDO|null
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$db` | **string** | Database name |
| `$user` | **string** | Database user |
| `$password` | **string** | Database password |
| `$host` | **string** | Database host, default: localhost |
| `$port` | **int** | Database port, default: 5432 |

***

### **__destruct**

Automatically disconnects PDO connection.

```php
public __destruct(): mixed
```

***

### **getConnection**

Public function to return setted PDO connection.

```php
public getConnection(): PDO|null
```

***

### **insertList**

For inserting multiple values into the same table.

```php
public insertList(string $table, array $names = [], array $data = []): array
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **string** | Table name |
| `$names` | **array** | List of keys |
| `$data` | **array** | Array of data arrays for keys |

**Return Value:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `[]` | **array** | Array of inserted values in DB |

***

### **insert**

Inserts single values into a table
After success execution returns complete inserted data with row id as array.

```php
public insert(string $table, array $data = [], string $id_field = "id"): array
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **string** | Table name |
| `$data` | **array** | Array of keys and their values |
| `$id_field` | **string** | Used for update data if dataset conflicts with existing entry, default: id |

**Return Value:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `[]` | **array** | Returns either array of data of inserted values or array with error and its description |

***

### **insertOrUpdate**

```php
public insertOrUpdate(string $table, array $data = [], string $field = "id"): array
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **string** | Table name |
| `$data` | **array** | Data that will be insert or updated in table |
| `$field` | **string** | ID field of the table for update entries, default: id |

**Return Value:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `[]` | **array** | Returns answer of the request in form of an array |

***

### **update**

Updates dataset in a table.

```php
public update(string $table, array $data = [], array $where = [
		'query' => '',
		'arr' => [],
		'arr_param' => 'AND',
	]): array
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **string** | Table name |
| `$data` | **array** | Array of keys and their values |
| `$where` | **array** | Array of parameters with predefined values |
| `$where['query']` | **string&#124;array** | Custom where query without &#039;WHERE&#039; |
| `$where['arr']` | **array** | Array of keys and values for where clause |
| `$where['arr_param']` | **string** | Binder between multiple search keys, default: AND |

**Return Value:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `[]` | **array** | List of updated dataset |

***

### **fetchAll**

Fetches all rows in a single table.

```php
public fetchAll(string $table, array $order = []): array
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **string** | Table name |
| `$order` | **array** | Sort arguements of the output in format column =&gt; sort direction, eg. &#039;date&#039; =&gt; &#039;ASC&#039; |

**Return Value:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `[]` | **array** | List of entries |

***

### **fetch**

Fetches entries in DB with custom definitions.

```php
public fetch(string $table, array $select, array $params = ['where' => ['query' => null, 'arr' => [], 'arr_param' => 'AND'], 'order' => [], 'limit' => 'ALL', 'offset' => 0]): array
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **string** | Table name |
| `$select` | **array** | List of DB columns of the table, default: * |
| `$params` | **array** | Predefined array of parameters for custom selects|
| `$params['where']` | **array** | Array of parameteres for where clause |
| `$params['query']` | **array&#124;string** | Custom raw sql select (where clause) array of strings or a single string, use without &#039;WHERE&#039; |
| `$params['arr']` | **array** | Array of where keys and these values |
| `$params['arr_param']` | **string** | Binder of multiple where array, default: AND |
| `$params['order']` | **array** | Array of ORDER by statement, use this way: &#039;name&#039; =&gt; &#039;SORT value&#039;, eg. &#039;date&#039; =&gt; &#039;ASC&#039; |
| `$params['limit']` | **int&#124;string** | Either ALL or an Integer to limit output, no use if ORDER is empty |
| `$params['offset']` | **int** | Skips first X entries, no use if ORDER is empty |

**Return Value:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `[]` | **array** | List of filtered entries |

***

### **delete**

Deletes an entry in table by ID.

```php
public delete(string $table, string $field, string $id): int|array
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **string** | Table name |
| `$field` | **string** | Columns for filter |
| `$id` | **string** | Value of columns |

**Return Value:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `rowCount()` | **int\|array** | Returns affected rows or error in form of an array |

***

### **deleteAll**

Deletes all entries in a table.

```php
public deleteAll(string $table, bool $restart = true, bool $cascade = true): int|array
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **string** | Table name |
| `$restart` | **bool** | Restart sequences of table, default: true |
| `$cascade` | **bool** | Automatically truncate all tables that have foreign-key references to any of the named table, default: true |

**Return Value:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `rowCount()` | **int\|array** | Returns affected rows or error in form of an array |

***

### **query**

Custom SQL query with secure parameters.

```php
public query(string $query, array $vals = []): string|array
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$query` | **string** | String with SQL query (for values use :column name) |
| `$vals` | **array** | Array of column names and their values |

**Return Value:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$out` | **string\|array** | Returns custom query request or error in form of an array |

***

### **defType**

Checks value and converts it into given type.

```php
protected defType(string|int|float|bool $value, string $type): float|int|string|bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$value` | **string&#124;int&#124;float&#124;bool** | The value for the column |
| `$type` | **string** | Type of the used value |

***

### **getComparer**

Checks value for sign for transmission into DB
If %sign% has been given on the first place before the value,
so function will transform it in right definition.

```php
protected getComparer(string $value): array
```

| First sign | Description |
|------------|-------------|
| ! | Negative statement. Transforms = into <> |
| <(=) | More (equal) than... statement. Transforms = into < or <= |
| >(=) | Less (equal) than... statement. Transforms = into > or >= |
| (!)% | (NOT) LIKE expression. Transforms = into (NOT) LIKE and value becomes % wrapped around (eg. %value%) |

For more statements please inform author

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$value` | **string** | Column value with or without a comparison sign |

**Return Value:** array

| Parameter | Type | Description |
|-----------|------|-------------|
| `sign` | **string** | statement sign (=, <>, <, >, etc) |
| `value` | **string** | output value with predefined type |

***

### **_prepareData**

Prepares array with splitted data
Splits in keys and values.

```php
private _prepareData(array $data = []): array
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **array** | Array with keys and values |

**Return Value:** array

| Parameter | Type | Description |
|-----------|------|-------------|
| `'0'` | **array** | Array of name keys |
| `'1'` | **array** | Array of values |

***

### **_connect**

Uses defined private variables to connect to pqsql db.

```php
private _connect(): null|PDO
```

***

***
> Automatically generated from source code comments on 2022-05-13 using [phpDocumentor](http://www.phpdoc.org/) and [saggre/phpdocumentor-markdown](https://github.com/Saggre/phpDocumentor-markdown)
