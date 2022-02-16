## Object life cycle

`#[PostConstruct]` is used on methods that need to get executed after dependency injection has finalized to perform any extra initialization.

```php
use Ray\Di\Di\PostConstruct;
```
```php
#[PostConstruct]
public function init()
{
    //....
}
```
