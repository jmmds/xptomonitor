**phpKhelper**

|             |                                                          |
|:------------|:---------------------------------------------------------|
| Version     | 1.4.1                                                    |
| Download    | https://gitlab.com/viharm/phpKhelper/tags                |
| Repository  | https://gitlab.com/viharm/phpKhelper.git                 |
| Issues      | https://gitlab.com/viharm/phpKhelper/issues              |
| License     | BSD (3-clause)                                           |
| Language    | PHP                                                      |

*phpKhelper* is a companion PHP library to accompany Kint (http://kint-php.github.io/kint/)


# Install


## Pre-requisites

The file `phpKhelper.lib.inc.php` is a launcher for the *Kint* library.

*Kint* is required (http://kint-php.github.io/kint/) along with its dependencies


## Download

Download the debug module.


### Archive

Download archive of the latest version from the download link provided at the top of this page.


### Clone repository

Clone the repository into the required location; remember to pull sub-modules by recursion
```
git clone --recurse-submodules \
https://gitlab.com/viharm/phpKhelper.git
```
This ensures that *Kint* is also downloaded.


## Deploy
You should have a directory structure like the following:

* `<YOURINCLUDEPATH>/phpKhelper/README.md`
* `<YOURINCLUDEPATH>/phpKhelper/LICENSE`
* `<YOURINCLUDEPATH>/phpKhelper/VERSION`
* `<YOURINCLUDEPATH>/phpKhelper/phpKhelper.lib.inc.php`
* `<YOURINCLUDEPATH>/phpKhelper/kint.php`


# Configure

Simply include the debug script in your code. If your include path is in the sub-directory `Lib`, then you can use the following code to include all files in that directory ending with `.inc.php`.

```php
  $sr_Filename = '' ;
  foreach (
    glob ( 
      dirname(__FILE__) . DIRECTORY_SEPARATOR .
      'Lib' .  DIRECTORY_SEPARATOR .
      '*.inc.php'
    ) as $sr_Filename
  ) {
    include_once(realpath($sr_Filename)) ;
  }
```

Replace the above values with those relevant/appropriate to the application environment.


# Usage

Simply pass your desired variables to *phpKhelper*

```php
fn_Debug ( 'Some message describing the output' , $VariableToDebug ) ;
```

This simply passes the variable to *Kint*.

If this is the only desired feature then *phpKhelper* is not required and it is recommended to use *Kint* directly.

Debugging can be enabled by setting boolean `$GLOBALS['bl_DebugSwitch']` to `TRUE`.

```
$GLOBALS['bl_DebugSwitch'] = TRUE ;
```


## Additional features

Some additional features have been included.


### Obfuscation

If your users have to send you debug output, then this may assist by not having them manually remove their sensitive information.

Obfuscation can be carried out on strings variable or on string items inside arrays.


#### Strings

Obfuscate strings by passing a third non-null parameter

```php
fn_Debug ( 'Some message describing the output' , $StringToDebug , 'AnyNonNullVariable' ) ;
```

This could be anything, even an empty string.


#### Strings inside arrays

If the sensitive data is inside an array then simply pass the array as usual, followed by the key name whose value is to be obfuscated.

Key name comparison is currently case-sensitive.


##### Single key value

If obfuscation is required for the values for all occurences of a single key, the third argument should be a string with the exact key name.

e.g., for the array
```
$ArrayToDebug = array (
  'username' => 'john' ,
  'password' => 'secret'
) ;
```

... the following call to debug will obfuscate the value (`secret`) of the key `password`.

```php
fn_Debug ( 'Some message describing the output' , $ArrayToDebug , 'password' ) ;
```


##### Multiple key values

If obfuscation is required for the values for all occurences of more than one key, the third argument should be a non-associative array with each item as a string with the exact key names to obfuscated.

e.g., for the array
```
$ArrayToDebug = array (
  'username' => 'john' ,
  'password' => 'secret' ,
  'proxy'    => array (
    'proxyhost' => 'localhost' ,
    'proxyuser' => 'usernameforproxy' ,
    'proxypw'   => 'passwordforproxy'
  )
) ;
```

... the following call to debug will obfuscate the value (`secret`) of the key `password` as well as the value (`passwordforproxy`) of the key `proxypw`.
```php
fn_Debug ( 'Some message describing the output' , $ArrayToDebug , array('password','proxpw') ) ;
```


### Debug override

If the global debug is disabled (`$GLOBALS['bl_DebugSwitch'] = FALSE ;`), then the fourth parameter can override this to debug only specific parts of your script.

```php
fn_Debug ( 'Some message describing the output' , $VariableToDebug , NULL , TRUE ) ;
```

Since this is controlled locally, remember to disable it as it overrides the global setting, so the latter will have no effect on this.


### Fallback debug

Some frameworks may suppress Kint's output (I haven't found any so far).

In this case, the fifth parameter can cause the debug output to be displayed as PHP errors.

```php
fn_Debug ( 'Some message describing the output' , $StringToDebug , NULL , NULL , TRUE ) ;
```

Currently this only works for string variables.


# Known limitations

See

* [Open issues](https://gitlab.com/viharm/phpKhelper/issues?scope=all&utf8=%E2%9C%93&state=opened)
* [Unresolvable issues](https://gitlab.com/viharm/phpKhelper/issues?scope=all&utf8=%E2%9C%93&state=all&label_name[]=Outcome_Wontfix)


# Support

For issues, queries, suggestions and comments please create an issue using the link provided at the top of this page.


# Contribute

Please feel free to clone/fork and contribute via pull requests. Donations also welcome, simply raise an issue as described above.

Please make contact for more information.


# Development environment ##
Developed on..

* *Debian Wheezy*
* *Debian Jessie*
* *Apache* 2.2
* *Apache* 2.4
* *PHP* 5.4
* *PHP* 5.5
* *PHP* 5.6
* *Kint* (as of 2015-06-15)
* *Kint* 2.1.2


# License

Licensed under the modified BSD (3-clause) license.

A copy of the license is available...
* in the enclosed `LICENSE` file.
* at http://opensource.org/licenses/BSD-3-Clause


# Reference


## Kint

*Kint* debugging library (http://kint-php.github.io/kint/). Licensed under the MIT license

Copyright (c) 2013 Jonathan Vollebregt (jnvsor at gmail dot com), Rokas Šleinius ( raveren at gmail dot com)


# Credits


## Codiad

*Codiad* web based IDE (https://github.com/Codiad/Codiad). Licensed under a MIT-style license.

Copyright (c) Codiad & Kent Safranski (codiad.com)


#### CodeGit

*CodeGit* *Git* plugin for *Codiad* (https://github.com/Andr3as/Codiad-CodeGit), used under a MIT-style license.

Copyright (c) Andr3as <andranode@gmail.com>


#### Ungit

*Ungit* client for *Git* (https://github.com/FredrikNoren/ungit) used under the MIT license

Copyright (C) Fredrik Norén


## GitLab

Hosted by *GitLab* code repository (gitlab.com).

