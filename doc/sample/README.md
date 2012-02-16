README sample

$ cd 01-db
$ php main.php

Timer start
begin Transaction["Koriym",21]
commit
Timer stop:[0.0001881] sec

Timer start
begin Transaction["Bear",19]
commit
Timer stop:[0.0000951] sec

Timer start
begin Transaction["Yoshi",33]
commit
Timer stop:[0.0001090] sec

Timer start
begin Transaction[]
commit
Timer stop:[0.0001211] sec

$ php original.php 
array (
  0 => 
  array (
    'Name' => 'Koriym',
    'Age' => '33',
  ),
  1 => 
  array (
    'Name' => 'Bear',
    'Age' => '34',
  ),
  2 => 
  array (
    'Name' => 'Yoshi',
    'Age' => '33',
  ),
)