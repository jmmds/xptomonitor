# phpDBauth

|           |                                                             |
|:----------|:------------------------------------------------------------|
| Version   | 2.7.0                                                       |
| Changes   | https://bitbucket.org/viharm/phpdbauth/commits              |
| Download  | https://bitbucket.org/viharm/phpdbauth/downloads            |
| Issues    | https://bitbucket.org/viharm/phpdbauth/issues               |
| License   | Modified BSD (3-clause)                                     |
| Language  | PHP                                                         |

*phpDBauth* is a PHP library for looking up users in a database table field. It returns a boolean value if the user if found, if supplied with the correct parameters. If not found, the user is added to the same database table field; and a specified default role is applied to a specified role column in the same table. Additional details of the user (like name, email, etcl.) can also be optionally added to the database.


## Installation


### Pre-requisites

* PHP 5+ with MySQL support (either `PDO` or `mysqli` or `mysql`)
* Standard web framework (web server, etc.)
* Database server (currently only MySQL is supported)


### Download


#### Archive

Get the release archives from the downloaded link provided at the top of this page.


### Composer

From v2.5.0 onwards, *phpDBauth* is enabled for *Composer*, and is available on  *Packagist* as `viharm`/`php-db-auth`. You may either install it standalone or include it in your project as a dependency.


#### Standalone

```
php compser.phar create-project viharm/php-db-auth phpDBauth
```

The above command will install *phpDBauth* in a sub-directory `phpDBauth` of the current working directory.


#### Dependency

To make *Composer* automatically install *phpDBauth* as a dependency include the following in your `composer.json`

```json
{
	"require": {
    "viharm/php-db-auth": "^2.5.0"
	}
}
```

If you would prefer to install this dependency in a custom directory, please include the following in your `composer.json`

```json
{
	"require": {
    "php": ">=5.3.0",
    "mnsami/composer-custom-directory-installer": "1.1.*",
    "viharm/php-db-auth": "^2.5.0"
	},
 "config": {
    "vendor-dir": "Lib"
  },
  "extra": {
    "installer-paths": {
      "./Lib/php-db-auth": ["viharm/php-db-auth"]
    }
}
```

In the above example, *mnsami*'s *Composer Custom Directory Installer* is used to customise the install path.

    * `Lib` is the sub-directory for plugins
    * `./Lib/phpDBauth` is the install path for *phpDBauth*, relative to your project directory (`composer.json` location)


#### Clone

Clone repository.

```
git clone --recurse-submodules \
https://viharm@bitbucket.org/viharm/phpdbauth.git
```

Remember to clone recursively (`--recurse-submodules`) to ensure cloning the submodules.


### Deploy

Extract the contents of the archive into the required directory. You should have a directory structure like the following:

* `<APPLICATION>/db/README.md`
* `<APPLICATION>/db/LICENSE.txt`
* `<APPLICATION>/db/VERSION.txt`
* `<APPLICATION>/db/phpdbauth.php`
* `<APPLICATION>/db/example.php`
* `<APPLICATION>/db/Lib/`
* `<APPLICATION>/db/Lib/phpKhelper/`
* `<APPLICATION>/db/Lib/phpKhelper/phpKhelper.lib.inc.php`
* `<APPLICATION>/db/Lib/phpKhelper/...`


## Usage

This library requires a precise set of parameters supplied as associative arrays to work properly.

The enclosed file `example.php` demonstrates basic functionality. Additional details are provided in this section.

Use in your code by passing the correct parameters/arrays to the core function

```php
$LookupResult = fn__Database_Verify (
  $Request ,
  $Table ,
  $MysqlExtension ,
  $Database ,
  $Database_Connection ,
  $UserDetails
) ;
```

This function can utilise an existing connection to a database (`$Database_Connection`), or optionally can connect to the database (`$Database`) itself.


### Input parameters/arguments


#### Request ####

Search requests are packaged in a 'Request' associative array of strings as follows.

```php
$Request = array (
  'ky_UserKeyword' => 'username' ,
  'ky_UserPassword' => 'password' ,
  'ky_GroupKeyword' => 'usersgroup' ,
) ;
```


##### Username #####

`$Request['ky_UserKeyword']` specifies the username to be looked up.

This field is required.


##### Password #####

`$Request['ky_UserPassword']` is a password field for compatibility with other scripts.

This field is not required by the script and will be removed from future versions.

It is recommended that a `NULL` value be provided.


##### Group #####

`$Request['ky_GroupKeyword']` is a group field for compatibility with other scripts.

This field is not required by the script and will be removed from future versions.

It is recommended that a `NULL` value be provided.


#### Table settings

The information about the users table is consolidated in a separate associative array as follows.

```php
$Table = array (
  "key__Table_Name"             => "Users" ,
  "key__Table_ColumnUsername"   => "Username" ,
  "key__Table_ColumnRole"       => "Role" ,
  "key__Table_DefaultRoleValue" => "READ ONLY"
) ;
```
The key names must be exactly as specified above.


##### Table name

This parameter specifies the name of the table in the specified database in which the users information is stored.

```php
$Table['key__Table_Name'] = 'Users' ;
```

In the example above the table `Users` stores the users information in the database.

This parameter is mandatory, and there is no default value.


##### Username column

This parameter defines the name of the column/field in the users table in which to look for the username.

```php
$Table['key__Table_ColumnUsername'] = 'Username' ;
```

In the example above the column/field `Username` stores the usernames in the users table of the database.

This parameter is mandatory, and there is no default value.


##### Role column

This parameter defines the name of the column/field in the users table in which to store the default role when adding a new user.

```php
$Table['key__Table_ColumnRole'] = 'Role' ;
```

In the example above the column/field `Role` stores the roles in the users table of the database.

This parameter is mandatory, and there is no default value.


##### Default role

This parameter defines the default role to apply when adding a new user.

```php
$Table['key__Table_DefaultRoleValue'] = 'READ ONLY' ;
```

In the example above the role `READ ONLY` is applied to new users added to the users table of the database.

This parameter is mandatory, and there is no default value.


#### MySQL extension type

This parameter defines the type of database extension to be used to interact with the database.

**Note**: If an existing database connection object is supplied to _phpDBauth_, then the driver used to establish the connection must be specified here.


##### pdo-mysql

```php
$MysqlExtension = 'pdo-mysql' ;
```
Uses the PHP PDO extension for interaction with the *MySQL* database.


##### mysqli

```php
$MysqlExtension = 'mysqli' ;
```
Uses the PHP `mysqli` extension for interaction with the *MySQL* database.


##### mysql

```php
$MysqlExtension = 'mysql' ;
```
Uses the PHP `mysql` extension for interaction with the *MySQL* database.


#### Database parameters

For scenarios where an existing connection object to the database does not exist, this array is used to pass database connectivity information to _phpDBauth_, so that a new connection to the database may be established.

```php
$Database = array (
  "key__Database_Host"     => "localhost" ,
  "key__Database_Port"     => 3306 ,
  "key__Database_Name"     => "databasename" ,
  "key__Database_User"     => "databaseusername" ,
  "key__Database_Password" => "databasepassword" ,
) ;
```

If a connection object (`$Database_Connection`) is provided to _phpDBauth_ the database connection information (`$Database`) is ignored to avoid the need to establish a new connection to the database.

If connection details are provided to _phpDBauth_ it attempts to gracefully close the database connection after the authentication task is completed.

It is mandatory to provide either this parameter (database connection information) or the database connection object (`$Database_Connection`). The default value for this parameter is `NULL`.


#### Database connection

For scenarios where an existing connection object to the database is available, it can be passed to _phpDBauth_ as `$Database_Connection`.

This provides an option for the script which calls _phpDBauth_ to establish a connection to the database prior to calling _phpDBauth_. This is also useful in situations where the calling script performs other actions on the same database and has an open connection object available, thus avoiding the need for an additional connection to the database for authentication.

```php
$Database_Connection = $ExistingDb->Connection();
```

If a connection object (`$Database_Connection`) is provided to _phpDBauth_ the database connection information (`$Database`) is ignored to avoid the need to establish a new connection to the database.

If a connection object is provided to _phpDBauth_ it leaves the database connection open after the authentication task is completed. This is because it assumes that the calling script performs other actions and requires an open connection to the database.

**Note**: It is the responsibility of the opening script to close the database connection if this option is used.

The following is an example of establishing a connection to the database using the `mysqli` _PHP_ driver.
```php
$ExistingDb->Connection = mysqli_connect (
  $Database [ "key__Database_Host" ] ,
  $Database [ "key__Database_User" ] ,
  $Database [ "key__Database_Password"] ,
  $Database [ "key__Database_Name" ] ,
  $Database [ "key__Database_Port" ]
) ;
```

It is mandatory to provide either this parameter (database connection object) or the database connection information (`$Database`). The default value for this parameter is `NULL`.


#### User details

_phpDBauth_ provides the option to add a user's details to the database. This can be done by providing it with an array of database columns/fields and values as follows.

```php
$ag_InsertVal = array (
  "UserFullname"  => "Anthony Stevens" ,
  "UserEmail" => "tony.s@domain.tld"
  ) ;
```

In the example above the columns `UserFullname` and `UserEmail` in the users table of the database are updated with the values `Anthony Stevens` and `tony.s@domain.tld` respectively. If the user is beind added, then these details are stored in the table along with the username and the role. If the users exists in the table, then these details are updated.

If this parameter is not an array with at least one element, then it is ignored.

This parameter is optional, with a default value of `NULL`.


### Response

The core function returns and associative array of three boolean elements.

```
$Result = array (
  "key__Database_Connection" => FALSE ,
  "key__Database_UserFound"  => FALSE ,
  "key__Database_UserAdded"  => FALSE ,
  "key_Database_DetailAdded" => FALSE
) ;
```


#### Database connection result

`$Result['key__Database_Connection']` is set to `TRUE` if the database connection (if required) was succesful.


#### User lookup

`$Result['key__Database_UserFound']` is set to `TRUE` if the requested user is found in the specified database table field.


#### User addition

`$Result['key__Database_UserAdded']` is set to `TRUE` if, following a failure to find the user, the requested user was added to specified database table field.


#### User details addition

`$Result['key_Database_DetailAdded']` is set to `TRUE` if the user's details were added to specified database table fields.


## Known limitations ##

For a full list of current known limitations see

* [Open issues](https://bitbucket.org/viharm/phpdbauth/issues?status=open)
* [Issues which won't be fixed](https://bitbucket.org/viharm/phpdbauth/issues?&status=wontfix)


## Support

Debugging can be enabled by setting boolean `$GLOBALS['bl_DebugSwitch']` to `TRUE`.

```
$GLOBALS['bl_DebugSwitch'] = TRUE ;
```

For issues, queries, suggestions and comments please create an issue (link at the top of this page).


## Contribute

Please feel free to clone/fork and contribute via pull requests. Bitcoin donations also welcome at `1MtGwgawfGjJc4Ln7KA7Re6mJrAo6pGfjm`.

Please make contact for more information.


## Development environment

Platform and software stack known to be compatible:

* Server OS
    * *Debian Wheezy*
    * *Debian Jessie*
    * *Debian Stretch*
    * *Ubuntu* 14.04
    * *Ubuntu* 16.04
    * *Ubuntu* 18.04
* Client OS
    * *Debian Wheezy*
    * *Debian Jessie*
    * *Debian Stretch*
    * *Ubuntu* 14.04
    * *Ubuntu* 16.04
    * *Ubuntu* 18.04
    * *Windows* 7
* Web servers
    * *Apache* 2.2
    * *Apache* 2.4
    * *Nginx* 1.10.3
* *PHP*
    * 5.4
    * 5.5
    * 5.6
    * 7.0
* *MySQL*
    * 5.4
    * 5.5


## License

Licensed under the modified BSD (3-clause) license.

A copy of the license is available...

* in the enclosed [`LICENSE`](LICENSE?at=master) file.
* at http://opensource.org/licenses/BSD-3-Clause


## Credits


### Tools


#### Kint

*Kint* debugging library (https://kint-php.github.io/kint/). Licensed under the MIT license

Copyright (c) 2013 Jonathan Vollebregt (jnvsor@gmail.com), Rokas Šleinius (raveren@gmail.com)


### Utilities


#### Codiad

*Codiad* web based IDE (https://github.com/Codiad/Codiad). Licensed under a MIT-style license.

Copyright (c) Codiad & Kent Safranski (codiad.com)


#### CodeGit

*CodeGit* *Git* plugin for *Codiad* (https://github.com/Andr3as/Codiad-CodeGit), used under a MIT-style license.

Copyright (c) Andr3as <andranode@gmail.com>


#### Ungit

*Ungit* client for *Git* (https://github.com/FredrikNoren/ungit) used under the MIT license

Copyright (C) Fredrik Norén


#### SmartGit

*SmartGit* client for *Git* (http://www.syntevo.com/smartgit/) used under SOFTWARE Non-Commercial License 

Copyright by syntevo GmbH


#### Git Extensions

*Git Extensions* client for *Git* (https://gitextensions.github.io/) used under GNU GPL v3.

Copyright https://github.com/gitextensions.


#### jEdit

*jEdit* text editor (http://www.jedit.org/), used under the GNU GPL v2.

Copyright (C) jEdit authors.


#### BitBucket

Hosted by *BitBucket* code repository (www.bitbucket.org).

Powered by *Atlassian* (www.atlassian.com).


#### Composer

Dependency management provided by *Composer* (https://getcomposer.org).


#### Packagist

*Composer* package hosting provided by *Packagist* (https://packagist.org).


### Testing

* Radoslav Chovan
* [David Gleba](http://github.com/dgleba) (_MySQL_ driver detection/selection)

