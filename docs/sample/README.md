README sample

$ cd 01-db

$ php original.php 
```php
<?php
array (
  0 => 
  array (
    'Name' => 'Koriym',
    'Age' => '19',
  ),
  1 => 
  array (
    'Name' => 'Bear',
    'Age' => '26',
  ),
  2 => 
  array (
    'Name' => 'Yoshi',
    'Age' => '33',
  ),
)
```
$ php main.php
```php
<?php
begin Transaction["Koriym",18]
commit
begin Transaction["Bear",35]
commit
begin Transaction["Yoshi",23]
commit
Timer start
Name:Koriym  Age:18
Name:Bear  Age:35
Name:Yoshi	Age:23
Timer stop:[0.0001631] sec
```