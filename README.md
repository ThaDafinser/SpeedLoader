# SpeedLoader - idea collection...!
Load PHP classes faster...some thoughts from my mind

## What is the idea?
There are a lot of optimizations already around for autoloading.

### Some techniques
- autoload classmap
  - classname is an array key, so no string operation
  - put into a file / memory / ...
- put a class collection into a single file
  - only one "autoload" process for multiple files
  - very fast!

### Ideas
- a self learning solution
  - autodetect enviroment based best solution
    - what caches are available?
  - dynamic start / end point for class collection
    - a generic class collection: e.g. until the end of bootstrap
    - a class collection for a specific MVC action or URL?
    - events and/or self defined
- different preload classfiles route/request based
- combine best practices from all projects
- easy switch between development/production mode
- php extension for even more performance?
- create class groups into a file
  - e.g the root class extends an interface, abstract class and uses different Exceptions -> combine it into one file?
- register a class, which can be called to create the file and not wait for the end
  - @see also dynamic start/end point

### Good to know
- it depends how the classes are concated, if the __DIR__ or __FILE__ constant is converted to the absolute path
  - reflection vs just include the whole file content
- the Composer autoloader regenerate his classname, when it updates `ComposerAutoloaderInit039f35c06ae44c024976663d60a39345`
  - so this could be `cache validator` 
  - only include classes from vendor and not the own, so caching is not problematic

## First steps
- get different test projects for time measurement
  - small / medium / large
- run with different autoloading techniques
- check results...maybe use https://github.com/polyfractal/athletic


## Other minds
- autoload the autoloader automatically: https://getcomposer.org/doc/04-schema.md#files
- composer only?!

## References
https://github.com/composer/composer/blob/master/src/Composer/Autoload/ClassMapGenerator.php
https://github.com/EvanDotPro/EdpSuperluminal
https://github.com/symfony/symfony/tree/master/src/Symfony/Component/ClassLoader
https://github.com/symfony/symfony/blob/master/src/Symfony/Component/ClassLoader/ClassCollectionLoader.php
https://github.com/composer/composer/tree/master/src/Composer/Autoload
https://github.com/mtdowling/ClassPreloader


