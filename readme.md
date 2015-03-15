Compiler Component
=======
[![Build Status](https://scrutinizer-ci.com/g/RobinCK/compiler/badges/build.png?b=master)](https://scrutinizer-ci.com/g/RobinCK/compiler/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/RobinCK/compiler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/RobinCK/compiler/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/kasseler/compiler/v/stable.svg)](https://packagist.org/packages/kasseler/config) 
[![Total Downloads](https://poser.pugx.org/kasseler/compiler/downloads.svg)](https://packagist.org/packages/kasseler/compiler) 
[![Latest Unstable Version](https://poser.pugx.org/kasseler/compiler/v/unstable.svg)](https://packagist.org/packages/kasseler/compiler) 
[![License](https://poser.pugx.org/kasseler/compiler/license.svg)](https://packagist.org/packages/kasseler/compiler)

The compiler converts the string data in php.

### Requirements
 - PHP >= 5.4
 
### Installation
```sh
$ composer require kasseler/compiler
```

### Usage
```php
$compiler = new Compiler('substr|md5|is_array'); //allow functions
$compiler->run("is_array([1, 2, 3, 5])"); //return true
$compiler->run("substr('my function compile', 3, 8)"); //return string 'function'
$compiler->run("[1, 2, 3, 4, 5]"); //return array [1, 2, 3, 4, 5]
$compiler->run('{"assoc":[1, 2, 3, 4, 5]}'); //return assoc array ['assoc' => [1, 2, 3, 4, 5]]
$compiler->run("md5('654')"); //return hash md5 'ab233b682ec355648e7891e66c54191b'
$compiler->run("true"); //return boolean true
```
