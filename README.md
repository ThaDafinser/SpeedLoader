# SpeedLoader - load only one file

[![Build Status](https://travis-ci.org/ThaDafinser/SpeedLoader.svg?branch=master)](https://travis-ci.org/ThaDafinser/SpeedLoader)

Since autoloading takes more and more time, it has become more important to lower that time.
`SpeedLoader` aims to solve that problem easily.

## Is this something new?

No. There are a couple of solutions around, but all of them are having some problems, that's why i "reinvited the wheel".

## Features

- independent (no framework relation)
- different compression modes
- save where and how you want (you get the complete cache as a string)

## Example

### Generate
Just execute the part of your application you want to cache.
Dont do this in your normal process - warm up your cache with a cronjob or similar
```php
// composer autoloading
require 'vendor/autoload.php';

//since composer is always needed, exclude all classes loaded until here
$classesNoLoad = array_merge(get_declared_interfaces(), get_declared_traits(), get_declared_classes());

//execute your app...(only the bootstrap)
$app = Zend\Mvc\Application::init($appConfig);

//find all loaded files
$classes = array_merge(get_declared_interfaces(), get_declared_traits(), get_declared_classes());
$classes = array_diff($classes, $classesNoLoad);

//cache it
$cache = new SpeedLoader\BuildCache();
$cache->cache($classes);

file_put_contents('data/cache/classes.php.cache', '<?php ' . "\n" . $cache->getCacheString());
```

### Include in your application
```php
if (file_exists('data/cache/classes.php.cache')) {
    require_once 'data/cache/classes.php.cache';
}
```

## Why concat classes 

Finding and opening a lot of files on the filesystem is expensive.
(similar reason why you should combine JS or CSS files...but there its HTTP)

## Alternatives

[EdpSuperluminal](https://github.com/EvanDotPro/EdpSuperluminal)
- not maintained
- only for ZF2
- only compressed possible

[Symfony](https://github.com/symfony/symfony/blob/master/src/Symfony/Component/ClassLoader/ClassCollectionLoader.php) 
- only output direct to a file
- no compression possible

[ClassPreloader](https://github.com/mtdowling/ClassPreloader)
- class hierarchy can be wrong (e.g. a class requires an interface and the interface comes later in the file...but in the meantime autoloader have loaded the interface -> "cannot redeclare error")

## Benchmarks
http://stackoverflow.com/questions/8240726/are-there-performance-downsides-while-using-autoloading-classes-in-php
https://mwop.net/blog/245-Autoloading-Benchmarks.html
