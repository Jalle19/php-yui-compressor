php-yui-compressor
==================

A modern PHP wrapper for the YUI compressor.

Installation
------------

Run `composer install` (install Composer if you haven't done so already). You will also need to have Java installed and available in your path (on Debian/Ubuntu-based systems you can install it using `sudo apt-get install default-jre`)

Usage
-----

The following snippet assumes you've included the Composer-generated autoloader.

```php
<?php

$yui = new \YUI\Compressor();

// Read the uncompressed conents
$css = file_get_contents('styles.css');
$script = file_get_contents('script.js');

// Compress the CSS
$yui->setType(\YUI\Compressor::TYPE_CSS);
$optimizedCss = $yui->compress($css);

// Compress the JavaScript
$yui->setType(\YUI\Compressor::TYPE_JS);
$optimizedScript = $yui->compress($script);
```

Credits
-------

Inspired by the work of gpbmike (https://github.com/gpbmike/PHP-YUI-Compressor)
