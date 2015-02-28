Compiler Component
=======
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
