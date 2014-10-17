# SpeedLoader - idea collection...!
Load PHP classes faster...some thoughts from my mind

## What is the idea?
There are a lot of optimizations already around for autoloading.

Some techniques:
- autoload classmap
  - classname is an array key, so no string operation
  - put into a file / memory / ...
- put a class collection into a single file
  - only one "autoload" process for multiple files
  - very fast!

What is missing?
- a self learning solution
  - autodetect enviroment based best solution
    - what caches are available?
  - dynamic start / end point for class collection
    - a generic class collection: e.g. until the end of bootstrap
    - a class collection for a specific MVC action or URL?
    - events and/or self defined
- combine best practices from all projects
- easy switch between development/production mode

## First steps
- get different test projects for time measurement
  - small / medium / large
- run with different autoloading techniques
- check results...


## Other minds
- use this so usage is easy: https://getcomposer.org/doc/04-schema.md#files
- composer only?

## References
https://github.com/EvanDotPro/EdpSuperluminal
https://github.com/symfony/symfony/tree/master/src/Symfony/Component/ClassLoader
https://github.com/symfony/symfony/blob/master/src/Symfony/Component/ClassLoader/ClassCollectionLoader.php
https://github.com/composer/composer/tree/master/src/Composer/Autoload
https://github.com/mtdowling/ClassPreloader


