IcyMat ORM
========================

Simple ORM mechanism created for my projects. For using this ORM you should use MySQL database.

#How to use

Each Entity class should extends `\IcyMat\ORM\BaseEntity` class. Each table at the database should contain integer and auto incremented `id` field. Example Entity class should looks like:

```php
<?php
class ExampleEntity extends \IcyMat\ORM\BaseEntity
{
    protected static $name = 'table_from_database';
    protected $fields = [
        'field_1',
        'field_2',
        'field_3'
    ];
}
```

##Get and set data
Set data:
```php
<?php
$exampleEntity->set('field_2', $value);
```

Get data:
```php
<?php
$exampleEntity->get('field_2');
```
