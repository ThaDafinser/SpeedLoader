# SpeedLoader - load only one file

[![Build Status](https://travis-ci.org/ThaDafinser/SpeedLoader.svg?branch=master)](https://travis-ci.org/ThaDafinser/SpeedLoader)

Since autoloading takes more and more time, it has become more important to lower that time.
`SpeedLoader` aims to solve that problem easily.

## Is this something new?

No. There are a couple of solutions around, but all of them are having some problems, that's why i "reinvited the wheel".

## Example

```php
// composer autoloading
require 'vendor/autoload.php';

//since composer is always needed, exclude all classes loaded until here
$classesNoLoad = array_merge(get_declared_interfaces(), get_declared_traits(), get_declared_classes());

//execute your app...

//now cache
$classes = array_merge(get_declared_interfaces(), get_declared_traits(), get_declared_classes());
$classes = array_diff($classes, $classesNoLoad);

$cache = new SpeedLoader\BuildCache();
$cache->cache($classes);

file_put_contents('data/cache/classes.php.cache', '<?php ' . "\n" . $cache->getCacheString());
```

## Why concat classes 

Finding and opening a lot of files on the filesystem is expensive.
(similar reason why you should combine JS or CSS files...but there its HTTP)

## Alternatives

[EdpSuperluminal](https://github.com/EvanDotPro/EdpSuperluminal)
- not maintained
- ZF2 only

[Symfony](https://github.com/symfony/symfony/blob/master/src/Symfony/Component/ClassLoader/ClassCollectionLoader.php) 
- only file output supported

[ClassPreloader](https://github.com/mtdowling/ClassPreloader)
- class hierarchy can be wrong (e.g. a class requires an interface and the interface comes later in the file...but in the meantime autoloader have loaded the interface -> "cannot redeclare error")

## Benchmarks
http://stackoverflow.com/questions/8240726/are-there-performance-downsides-while-using-autoloading-classes-in-php
https://mwop.net/blog/245-Autoloading-Benchmarks.html
