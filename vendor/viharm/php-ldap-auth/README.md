# phpLDAPauth #

|           |                                                             |
|:----------|:------------------------------------------------------------|
| Version   | 2.5.1+                                                      |
| Changes   | https://bitbucket.org/viharm/phpldapauth/commits            |
| Download  | https://bitbucket.org/viharm/phpldapauth/downloads          |
| Issues    | https://bitbucket.org/viharm/phpldapauth/issues             |
| License   | Modified BSD (3-clause)                                     |
| Language  | PHP                                                         |

*phpLDAPauth* is a PHP library for authenticating with a LDAP server. It returns a true/false value for authentication if supplied with the correct parameters.


## Features

* Authentication: Validate a user's credentials against an existing directory service.
* Authorisation: Check if an authenticated user is authorised, based on directory group membership.
* User details fetching: If authenticated, then fetch user's attributes from the directory.


## Installation


### Pre-requisites

* PHP 5+ with LDAP support
* Standard web framework (web server, etc.)
* *phpKhelper* (for debugging, included as a submodule)
* Directory server (e.g., *OpenLDAP*)


### Download


#### Archive

Get the release archives from the downloaded link provided at the top of this page.


#### Clone

Clone repository.

```
git clone --recurse-submodules \
https://bitbucket.org/viharm/phpldapauth.git
```

Remember to clone recursively (`--recurse-submodules`) to ensure cloning the submodules.


### Deploy

Extract the contents of the archive into the required directory. You should have a directory structure like the following:

* `<APPLICATION>/ldap/README.md`
* `<APPLICATION>/ldap/LICENSE.txt`
* `<APPLICATION>/ldap/VERSION.txt`
* `<APPLICATION>/ldap/example.php`
* `<APPLICATION>/ldap/phpldapauth.php`
* `<APPLICATION>/ldap/Lib/`
* `<APPLICATION>/ldap/Lib/fl_lib.inc.php`
* `<APPLICATION>/ldap/Lib/kint/`
* `<APPLICATION>/ldap/Lib/kint/...`


## Usage ##

This library requires a precise set of parameters supplied as associative arrays to work properly.

Use in your code by creating an object from the class and call the authentication method

```
$Dir = new cl_Dir($DirHost,$DirConf) ;
$AuthResult = $Dir->fn_Auth($Request) ;
```

The enclosed file `example.php` demonstrates basic functionality. Additional details are provided in this section.


### Input parameters/arguments ###

Directory settings variables in a pair of associative arrays of strings. The minimum configuration for a typical *OpenLDAP* installation on *Debian Wheezy* is shown in the example below


#### Directory host settings ####

```
$DirHost = array (
  'ky_Locn' => 'localhost' ,
  'ky_Port' => '389'
) ;
```

##### Host location #####

`$DirHost['ky_Locn']` specifies the location of the directory host.


##### Host port #####

`$DirHost['ky_Port']` specifies the port to connect to for accessing the directory service.


#### Directory configuration setings ####

```
$DirConf = array (
  'ky_LdapType'          => 'openldap ,
  'ky_LdapVer'           => 3 ,
  'ky_LdapFollowReferral' => FALSE ,
  'ky_LdapTLS'            => FALSE ,
  'ky_BaseDn'            => 'dc=domain,dc=tld',
  'ky_UsernameAttrib'    => 'uid' ,
  'ky_GroupnameAttrib'   => 'cn' ,
  'ky_GroupMemberAttrib' => 'memberuid' ,
  'ky_UserContainerRdn'  => 'ou=Users' ,
  'ky_GroupContainerRdn' => 'ou=Groups' ,
  'ar_GroupSearchFilter' => array (
    'objectClass=posixGroup' ,
    'objectClass=sambaGroupMapping'
  )
) ;
```


##### Basic usage #####

The following configuration options allow basic usage.


###### Directory type ######

`$DirConf['ky_LdapType']` specifies the type of the LDAP directory server.

This can be one of the following three types:

  *  `openldap`
  *  `ad-ds`
  *  `ad-lds`
  
This field is optional, the default value is `openldap`.


###### Directory base DN ######

`$DirConf['ky_BaseDn']` specifies the base DN of the directory tree.

In rare cases this can include the users container to authenticate users. But in this case `$DirConf['ky_UserContainerRdn']` should be left empty.

However such an approach will prevent checking group membership if the groups are in a different container to the users.

This field is required.


###### User name attribute ######

`$DirConf['ky_UsernameAttrib']` specifies the attribute name used by the directory to store the usernames.

This cannot be mapped to any field of choice, as this is used to formulate the DN of the user which will bind to the directory.

If the directory service supports binding by e-mail then this can be mapped to the appropriate `mail` field. 

This field is optional, the default value is `uid`.


###### User container RDN ######

`$DirConf['ky_UserContainerRdn']` specifies the RDN of the container used to store the users in the tree; e.g., `ou=Users`.

Although it is possible to include the users container in `$DirConf['ky_BaseDn']` described earlier, it is always good practice to keep them separate to allow additional functionality like group membership checking.

This field is optional, there is no default value.


##### Advanced usage #####

In addition to the above basic usage for user authentication the following configuration options allow additional features and usage.


###### LDAP protocol version ######

`$DirConf['ky_LdapVer']` specifies the LDAP protocol version to use with the directory server.

Version `3` is the current and the preferred version by most directory services.

This field is optional, the default value is `3`.


###### LDAP referrals ######

`$DirConf['ky_LdapFollowReferral']` specifies whether or not to follow referrals once connected to the directory server.

This field is optional, the default value is a boolean `FALSE`.


###### Connection encryption ######

`$DirConf['ky_LdapTLS']` specifies whether or not to use TLS once connected to the directory server.

The use of SSL (`ldaps`) is discouraged since SSL is deprecated in favour of TLS. Please note that this approach first connects to the directory server without encryption (typically on the standard port 389). It then negotiates with the server to upgrade the connection to secure TLS.

An existing SSL connection (`ldaps`) cannot be upgraded to TLS.

This field is optional, the default value is a boolean `FALSE`.


###### Group name attribute ######

`$DirConf['ky_GroupnameAttrib']` specifies the attribute name used by the directory to store the group name.

This is useful when it is desirable to authenticate a user only when they are members of a group.

This field is optional, the default value is `cn`.


###### Group member attribute ######

`$DirConf['ky_GroupMemberAttrib']` specifies the attribute name used by the directory to store the member usernames inside the group object.

This is useful when it is desirable to authenticate a user only when they are members of a group.

This field is optional, the default value is `memberuid`.


###### Group container RDN ######

`$DirConf['ky_GroupContainerRdn']` specifies the RDN of the container used to store the groups in the tree; e.g., `ou=Groups`.

If the users and the groups are in the same container in the directory tree (or in the base of the tree), then it is possible to specify the DN of that container in `$DirConf['ky_BaseDn']`.

Although it is possible to include the groups container in `$DirConf['ky_BaseDn']` described earlier, it is always good practice to keep them separate.

This parameter is optional, there is no default value.


###### Group search filters ######

If the directory structure is such that the groups are set up as non-standard objects then it is possible to over-ride the default group filters according to the environment.

`$DirConf['ar_GroupSearchFilter']` is a non-associative array of filters. Each array item is a filter criteria which is combined in `OR` logic by *phpLDAPauth* at runtime.

This parameter is optional. The default filter consists of the following `OR`d criteria:

*  `objectClass=posixGroup`
*  `objectClass=sambaGroupMapping`


#### Requests ####

Search requests are packaged in a 'Request' associative array of strings

```
$Request = array (
  'ky_UserKeyword'  => 'username' ,
  'ky_UserPassword' => 'password' ,
  'ky_UserDomain'   => 'userdomain' ,
  'ky_GroupKeyword' => 'usersgroup' ,
) ;
```


##### Username #####

`$Request['ky_UserKeyword']` specifies the username to be authenticated.

This username is used to formulate a DN which is used to bind with the directory for authentication.

This field is required.


##### Password #####

`$Request['ky_UserPassword']` specifies the password to be used with the username for authentication.

This is simply passed in plain text to the directory service.

This field is required.


##### User domain #####

`$Request['ky_UserDomain']` specifies the domain which the user belongs to.

This is required for Active Directory (both DS and LDS) servers.

This field is not required for OpenLDAP servers, so can be ignore; but it is required for AD DS and AD LDS directory servers (selected using `$DirConf['ky_LdapType']`), there is not default value. It is the deployer's responsbility to ensure a reasonable value is specified if the type of LDAP server selected requires this value.


##### Group #####

`$Request['ky_GroupKeyword']` specifies the group name to check user's membership.

This can be used to check if the user is member of a group.

This field is optional, there is no default value.


#### User attributes ####

If authenticated, _phpLDAPauth_ provides an option to fetch user's details. The most common use case is to synchronise a local application database with the latest information from the directory service.

This requires a list of attribute names in an associative array of strings. A simple example is shown below.

```
$RequiredAttributes = array (
  'cn' ,
  'sn' ,
  'displayName' ,
  'objectClass' ,
  'mail'
  ) ;

```


### Response ###

Function returns an associative array of three boolean elements and a fourth array (or `NULL`) element

```
$Result = array (
  'ky_User_Authenticated' => FALSE,
  'ky_Group_Exists'       => FALSE,
  'ky_Group_ContainsUser' => FALSE,
  'ar_UserAttrib'         => NULL
) ;
```


#### User authentication ####

`$Result['ky_User_Authenticated']` is set to `TRUE` if the user specified in the request `$Request['ky_UserKeyword']` successfully binds to the directory.


#### Group existence ####

`$Result['ky_Group_Exists']` is set to `TRUE` if the group specified in the request `$Request['ky_GroupKeyword']` is found.

This is set irrespective of whether the authenticated user is a member of the group or not.

This is set to `FALSE` if the user is not authenticated.


#### Group membership ####

`$Result['ky_Group_ContainsUser']` is set to `TRUE` if the user specified in the request `$Request['ky_UserKeyword']` belongs to the group specified int he request `$Request['ky_GroupKeyword']`.

This is set to `FALSE` if the user is not authenticated.


#### User details ####

`$Result['ar_UserAttrib']` is returned as an associative array if the user is authenticated.

```
$Result['ar_UserAttrib'] = array (
  'cn'            => array ( 'count' => 1 , 0 => 'Anthony Smith' ) ,
  'sn'            => array ( 'count' => 1 , 0 => 'Smith' ) ,
  'displayName'   => array ( 'count' => 1 , 0 => 'Anthony Smith' ) ,
  'objectClass'   => array ( 'count' => 5 , 0 => 'inetOrgPerson' , 1 => 'posixAccount' , 2 => 'top' , 3 => 'extensibleObject' , 4 => 'sambaSamAccount' ) ,
  'mail'          => array ( 'count' => 3 , 0 => 'anthony.smith@domain.tld' , 1 => 'anthony.smith@local' , 2 => 'tony.smith@domain.tld' )
  ) ;
```

Each element has string key which is equal to the attribute requested. The value of each element is an array with at least two elements, as follows.

* `count` contains the number of values for the specific attribute.
* `0`, `1`, `2`, etc. contain the values.

If a specific attribute is not found on the directory server, the sub-array structure is maintained to provide a consistent output, however the `count` is set to `0` and the value of `0` is `NULL`. For example if the attribute `loginShell` is requested, but is not found on the directory the following respons is received.

```
$Result['ar_UserAttrib'] = array (
  'loginShell'            => array ( 'count' => 0 , 0 => NULL ) ,
  ) ;
```

If the user is not authenticated, then the value of this element is `NULL`.

```
$Result['ar_UserAttrib'] = NULL
```


## Known limitations ##

For a full list of current known limitations see

* [Open issues](https://bitbucket.org/viharm/phpldapauth/issues?status=open)
* [Issues which won't be fixed](https://bitbucket.org/viharm/phpldapauth/issues?&status=wontfix)


## Support

Debugging can be enabled by setting boolean `$GLOBALS['bl_DebugSwitch']` to `TRUE`.

```
$GLOBALS['bl_DebugSwitch'] = TRUE ;
```

For issues, queries, suggestions and comments please create an issue (link at the top of this page).


## Contribute

Please feel free to clone/fork and contribute via pull requests. Bitcoin donations also welcome at `16is9G5dCHSnjnGxCUZRkjBWpS6a99ZA7G`. Please create an issue for alternative methods of donation or contribution.

Please make contact for more information.


## Environment ##
Platform and software stack known to be compatible:

* Server OS
    * *Debian Wheezy*
    * *Debian Jessie*
    * *Debian Stretch*
    * *Ubuntu* 14.04
* Client OS
    * *Debian Wheezy*
    * *Debian Jessie*
    * *Debian Stretch*
    * *Windows* 7
* Web servers
    * *Apache* 2.2
    * *Apache* 2.4
    * *Nginx* 1.10.3
* *PHP*
    * 5.4
    * 5.5
    * 7.0
* Directory servers
    * *OpenLDAP* 2.4
    * *AD* (both *DS* and *LDS*) on *Windows* Server 2012


## License ##

Licensed under the modified BSD (3-clause) license.

A copy of the license is available...

* in the enclosed [`LICENSE`](LICENSE?at=master) file.
* at http://opensource.org/licenses/BSD-3-Clause


## Credits


### Tools


#### Kint

*Kint* debugging library (http://raveren.github.io/kint/), used under the MIT license.

Copyright (c) 2013 Rokas Å leinius (raveren at gmail dot com).


### Utilities


#### Codiad

*Codiad* web based IDE (https://github.com/Codiad/Codiad), used under a MIT-style license.

Copyright (c) Codiad & Kent Safranski (codiad.com).


#### VS Code

*Visual Studio Code* code editor, used under the *Microsoft Software License*.


#### SmartGit

*SmartGit* client for *Git* (http://www.syntevo.com/smartgit/) used under SOFTWARE Non-Commercial License.

Copyright by syntevo GmbH.


#### Git Extensions

*Git Extensions* client for *Git* (https://gitextensions.github.io/) used under GNU GPL v3.

Copyright https://github.com/gitextensions.


#### jEdit

*jEdit* text editor (http://www.jedit.org/), used under the GNU GPL v2.

Copyright (C) jEdit authors.


#### BitBucket

Hosted by *BitBucket* code repository (www.bitbucket.org).

Powered by *Atlassian* (www.atlassian.com).


### Testing

* Radoslav Chovan
* [David Gleba](http://github.com/dgleba) (AD development)
