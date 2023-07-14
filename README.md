# Micoli\Elql


A flat database manager. Each models are read/persisted in a (`YAML`|`JSON`) file.

That library is based upon [`symfony/serializer`](https://symfony.com/doc/current/components/serializer.html) and [`symfony/expression-language`](https://symfony.com/doc/current/components/expression_language.html) components. So basically, every model that can be serialized/deserialized by those components wan be used.

When `select`ing, `updat`ing, `delet`ing records, each constraints are expressed as an `expression-language` string.
A `record` object is available in the constraint expression and represent the evaluated record. 


[![Build Status](https://github.com/micoli/elql/workflows/Tests/badge.svg)](https://github.com/micoli/elql/actions)
[![Coverage Status](https://coveralls.io/repos/github/micoli/Elql/badge.svg?branch=main)](https://coveralls.io/github/micoli/Elql?branch=main)
[![Latest Stable Version](http://poser.pugx.org/micoli/elql/v)](https://packagist.org/packages/micoli/elql)
[![Total Downloads](http://poser.pugx.org/micoli/elql/downloads)](https://packagist.org/packages/micoli/elql)
[![Latest Unstable Version](http://poser.pugx.org/micoli/elql/v/unstable)](https://packagist.org/packages/micoli/elql) [![License](http://poser.pugx.org/micoli/elql/license)](https://packagist.org/packages/micoli/elql)
[![PHP Version Require](http://poser.pugx.org/micoli/elql/require/php)](https://packagist.org/packages/micoli/elql)

## Installation

This library is installable via [Composer](https://getcomposer.org/):

```bash
composer require micoli/elql
```

## Requirements

This library requires PHP 8.0 or later.

## Project status

While this library is still under development, it is still in early development status. It follows semver version tagging.

## Quick start

### Create an instance of Elql manager

Database are per directory. all models are persisted in a separate file.

```php
$database = new Elql(
    new FilePersister(
        '/var/lib/database',
        new MetadataManager(),
        YamlEncoder::FORMAT,
    ),
);
```

The persisted records are loaded in memory before each CRUD action if they were not already loaded.


### Record in memory addition
 
Add some record of proper serializable model.

```php
$database->add(
    new Baz(1, 'a', 'a'),
    new Baz(2, 'b', 'b'),
    new Baz(3, 'c', 'c'),
    new Baz(4, 'd', 'd'),
    new Foo(1, 'aa', new DateTimeImmutable()),
);
```

### Basic crud function are available

#### Select

```php
$records = $database->find(Baz::class, 'record.id==3');
````

#### Update

An updater callback is used to help case/case model updates

```php
$database->update(Baz::class, function (Baz $record) {
    $record->firstName = $record->firstName . '-updated';

    return $record;
}, 'record.id==3');
```

#### Delete

```php
$database->delete(Baz::class, 'record.id in [1,4]');
```

#### Count

```php
print $database->count(Baz::class, 'record.id in [1,4]');
```

#### You need to flush the in-memories tables to disk to persist them

```php
$database->persister->flush();
```


```
/var/lib/database/Foo.yaml
/var/lib/database/Bar.yaml
```


## Attributes

### Table($name)

Instead of using the class-name as filename, you can specify a specific name using that attribute.

```php
#[Table('b_a_z')]
class Baz
{
    public function __construct(
        public readonly int $id,
        public string $firstName,
        public string $lastName,
    ) {
    }
}
```

N.B.:

if you don't want to use that attribute, you can specify some filenames in the `MetadataManager` constructor.

```php
$database = new Elql(
    new FilePersister(
        '/var/lib/database',
        new MetadataManager([
            Foo::class=>'foo_table'
        ]),
        YamlEncoder::FORMAT,
    ),
);
```

In that was, when `Foo` records will be persisted, the filename will be `foo_table.yaml`.

### Unique($expression, $indexName)

Before adding a record to a model table, the unique constraints are evaluated to guarantee uniqueness of records.

```php
#[Unique('record.id')]
#[Unique('[record.firstName,record.lastName]', 'fullname')]
class Baz
{
    public function __construct(
        public readonly int $id,
        public string $firstName,
        public string $lastName,
    ) {
    }
}
```

A `Micoli\Elql\Exception\NonUniqueException` are triggered in case of a constraint is not respected.
