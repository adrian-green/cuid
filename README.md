# Cuid for PHP 

This library provides a collision resistant id (hashes) for horizontal scaling and sequential lookup performance. This README will only cover basic detail and PHP specific implementation.

__Do [refer to the original project](http://usecuid.org/) for the full description of the project.__

This is my clone which is PHP 7+ only, for my own purposes, and uses [CSPRNG](https://www.php.net/manual/en/ref.csprng.php) `random_int` instead of `mt_rand`
See the [PHP version of cuid](https://github.com/endyjasmi/cuid) on which this is entirely based.

It's not performant, but is significantly quicker than the original.

## Requirement
1. PHP 7 and above

## Quickstart
Here's how to use it

Not with packagist as for my own use really.

In composer.json:

```json
{
    "require": {
        "adriangreen/cuid": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "https://github.com/adrian-green/cuid.git"
        }
    ]
}
```

```php
// Include composer autoloader
require 'vendor/autoload.php';

// Create a cuid instance
$cuid = new AdrianGreen\Cuid;

// Generate normal cuid
$normalCuid = $cuid->cuid(); // ci27flk5w0002adx5dhyvzye2

// Generate short cuid
$shortCuid = $cuid->slug(); // 6503a5k0

// You can also generate cuid using static method
$normalCuid = Cuid::cuid();
$shortCuid = Cuid::slug();

// There is also an alias method for better readability
$normalCuid = Cuid::make();
```

## License
This library is licensed under MIT as shown below. Exact copy of the license can be found in `LICENSE` file.

```
The MIT License (MIT)

Copyright (c) 2014 Endy Jasmi

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

```
