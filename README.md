[![Build Status](https://travis-ci.org/kherge-php/file-manager.svg?branch=master)](https://travis-ci.org/kherge-php/file-manager)
[![Packagist](https://img.shields.io/packagist/v/kherge/file-manager.svg)](https://packagist.org/packages/kherge/file-manager)
[![Packagist Pre Release](https://img.shields.io/packagist/vpre/kherge/file-manager.svg)](https://packagist.org/packages/kherge/file-manager)

File Manager
============

Manages strict read and write operations for files and file streams.

Usage
-----

```php
use KHerGe\File\File;

// Create a new file manager.
$file = new File('example.txt', 'r');

// Iterate through the contents of the file.
foreach ($file->iterate() as $buffer) {
    // ...
}
```

Install
-------

Add as a dependency to a [Composer][] managed project.

    composer require kherge/file-manager

[Composer]: https://getcomposer.org/

Documentation
-------------

Please refer to [`FileInterface`][] for documentation.

[`FileInterface`]: src/KHerGe/File/FileInterface.php

```php
use KHerGe\File\File;
use KHerGe\File\Memory;
use KHerGe\File\Stream;

// Create a manager for a file.
$manager = new File('example.txt', 'r');

// Create a manager for a string.
$manager = new Memory('example contents', false);

// Create a manager for a file stream.
$manager = new Stream($stream);
```

License
-------

File Manager is dual licensed under MIT and Apache 2.0.
