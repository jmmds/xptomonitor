# PsmLDAPauth

|           |                                                                   |
|:----------|:------------------------------------------------------------------|
| Version   | 1.1.3                                                             |
| Changes   | https://gitlab.com/viharm/PsmLDAPauth/-/merge_requests/11/commits |
| Download  | https://gitlab.com/viharm/PsmLDAPauth/-/tags                      |
| Issues    | https://gitlab.com/viharm/PsmLDAPauth/-/issues                    |
| License   | GNU GPL v3                                                        |
| Language  | PHP                                                               |

This is a module for authenticating *PHP Server Monitor* users against a LDAP directory


## Installation


### Pre-requisites

The file in this repository `psmldapauth.php` is launcher for the module. Additional dependencies include the following.

*  *phpLDAPauth* (https://bitbucket.org/viharm/phpldapauth)
*  *phpDBauth* (https://bitbucket.org/viharm/phpdbauth)


### Download

Download the LDAP module


#### Archive

Get the release archives from the download link provided at the top of this page.


### Composer

From v1.1.0 onwards, *PsmLDAPauth* is enabled for *Composer*, and is available on *Packagist* as `viharm`/`psm-ldap-auth`. Although this library can be installed standalone or included in any project with *Composer* usage, please note that this library is specifically developed for use with *PHP Server Monitor* only.


#### Standalone

Although there is no perceivable use for this library without the *PHP Server Monitor* (*PSM*) framework/project, a standalone copy of this project can be installed with *Composer*

```
php compser.phar create-project viharm/psm-ldap-auth PsmLDAPauth
```

The above command will install *PsmLDAPauth* in a sub-directory `PsmLDAPauth` of the current working directory.


#### Dependency

To make *Composer* automatically install *PsmLDAPauth* as a dependency include the following in your `composer.json`

```json
{
	"require": {
    "viharm/psm-ldap-auth": "^1.1"
	}
}
```

This will install *PsmLDAPauth* in the standard `vendor` sub-directory of a typical *Composer*-enabled project.


#### Clone repository

Clone the repository into the `vendor` directory of *PHP Server Monitor* installation; remember to pull sub-modules by recursion
```
git clone --recurse-submodules \
https://gitlab.com/viharm/PsmLDAPauth.git \
/PATH/TO/PHPSERVERMON/vendor/PsmLDAPauth/
```


### Deploy

Extract the contents of the archive into the `.../PATH/TO/PHPSERVERMON/vendor/` directory. You should have a directory structure like the following:

* `/PATH/TO/PHPSERVERMON/vendor/PsmLDAPauth/README.md`
* `/PATH/TO/PHPSERVERMON/vendor/PsmLDAPauth/LICENSE`
* `/PATH/TO/PHPSERVERMON/vendor/PsmLDAPauth/VERSION`
* `/PATH/TO/PHPSERVERMON/vendor/PsmLDAPauth/psmldapauth.php`
* `/PATH/TO/PHPSERVERMON/vendor/PsmLDAPauth/phpldapauth/`
    * `/PATH/TO/PHPSERVERMON/vendor/PsmLDAPauth/phpLDAPauth/phpldapauth.php`
    * `/PATH/TO/PHPSERVERMON/vendor/PsmLDAPauth/phpLDAPauth/...`
* `/PATH/TO/PHPSERVERMON/vendor/PsmLDAPauth/phpDBauth/`
    * `/PATH/TO/PHPSERVERMON/vendor/PsmLDAPauth/phpDBauth/phpdbauth.php`
    * `/PATH/TO/PHPSERVERMON/vendor/PsmLDAPauth/phpDBauth/...`

Although LDAP functionality is deployed in the vendor directory tree, *PsmLDAPauth* depends on code integrated into the following files of the *PHP Server Monitor* framework.

* `/PATH/TO/PHPSERVERMON/src/templates/default/module/config/config.tpl.html`
* `/PATH/TO/PHPSERVERMON/src/psm/Module/Config/Controller/ConfigController.php`
* `/PATH/TO/PHPSERVERMON/src/lang/en_US.lang.php`
* `/PATH/TO/PHPSERVERMON/src/psm/Service/User.php`
* Associated language files in `/PATH/TO/PHPSERVERMON/src/lang/`


## Configuration

*PsmLDAPauth* is not configurable by the user during deployment. The following options are available in the configuration section web interface in the "Authentication" tab.

* Enable
* Host
* Port
* Type (and domain, if *AD*)
* Version
* Referrals
* Base DN
* Username attribute
* Group name attribute
* Group membership attribute
* User container RDN
* Group container RDN

The description of these parameters is discussed below.


### Basic setup


#### Directory authentication status

Checking this configures *PHP Server Monitor* to use the LDAP authentication module.


#### Directory host

Specifies the location of the host where the directory service can be found.

If not specified, default of `localhost` is used.


#### Directory service port

Specifies the port of the host on which the directory service can be found.

If not specified, the *OpenLDAP* default of `389` is used.


#### Base DN

Specifies the base DN of the directory.

This is a mandatory setting. There is no default value.


### Optional features and settings

The example `conf.ini` shown earlier includes the minimum options required to get started.

Additional configuration options include.


#### Directory type

Specifies the type of LDAP directory used. This can be one of the following:

* `OpenLDAP`
* `AD LDS`
* `AD DS`

If not specified, default of `OpenLDAP` is used.


##### Active Directory domain

If using Active Directory, then please specify (in addition to the above) the server type and the user's domain as follows:

For *Active Directory Domain Services* (AD DS) provide the NETBIOS domain.

For *Active Directory Lightweight Directory Services* (AD LDS) provide the DNS domain.


#### Group name

This can be used to specify the LDAP group which a user must belong to, to be authenticated.

If this option is not specified, group membership is not checked, and any user who can bind to the directory using their directory username and password is authenticated.


#### Username attribute

Specifies the attribute used to identify user names in the directory environment..

If not specified, the *OpenLDAP* default of `uid` is used.


#### Group name attribute

Specifies the attribute used to identify group names in the directory environment..

If not specified, the *OpenLDAP* default of `cn` is used.


#### Group membership identification

Specifies the attribute in a group entry. *PsmLDAPauth* assumes that the the user-group relation is stored in the group item. The directory username (e.g., uid) of each member of a group is stored as a separate value. 

If not specified, the *OpenLDAP* default of `memberuid` is used.


#### Users container

This is used to specify the relative distinguished name (RDN) of the container of users in the directory base. e.g., if all users are inside the organisational unit represented by the DN `ou=Users,dc=domain,dc=tld`, then the users container RDN would be `ou=Users`.

This parameter is optional; there is not default value.

If the directory environment has a container for user objects then it is highly recommended to use this setting.


#### Groups container

This is used to specify the RDN of the container of groups in the directory base. e.g., if all groups are inside the organisational unit represented by the DN `ou=Groups,dc=domain,dc=tld`, then the users container RDN would be `ou=Groups`.

If not specified, then no separate DN for the group objects is formulated, but is mapped to the base DN of the directory.

If a group membership restriction is applied, and if the group objects are in a specific container (not in the directory base) then group container RDN should be specified here otherwise authentication will always fail.


#### LDAP version

This is used to specify version of the LDAP protocol used to communicate with the directory service. The possible choices are either `2` or `3`.

If not specified, the default of `3` is used. LDAP v2 protocol was deprecated in 2003 (RFC3494).


#### Role based authorisation

*PsmLDAPauth* offers role-based access for authenticated users.


##### Default role

This is applied to directory authenticated users which are not found in the users table in the database.

This action is performed by the module only once (or if the user was deleted from the users table in the database), as subsequent authentication loops will always find the user in the table.

If the user's permissions need to be changed, their role must be updated in the users configuration interface in *PHP Server Monitor*.


## Logic

*PsmLDAPauth* consists of the main `psmldapauth.php` which controls the logic flow of the module and two discrete helper scripts

01. *phpLDAPauth* to interact with the directory service;
02. *phpDBauth* to interact with the database;

The authentication logic work flow is as follows:

01. User enters credentials on the *PHP Server Monitor* login screen;
02. *PHP Server Monitor* passes those credentials to the native *User* service library.
03. The native *User* service library extracts the directory configuration from the database and passes this along with the login credentials to `psmldapauth.php`;
03. `psmldapauth.php` passes those credentials first to *phpLDAPauth* in the request, along with the directory host and configuration;
04. *phpLDAPauth* uses these credentials to bind with the directory service, and check group membership if requested;
05. *phpLDAPauth* returns the result to `psmldapauth.php`;
06. `psmldapauth.php` then sends the authenticated users details to *phpDBauth* in a separate request, along with the database host and configuration;
07. *phpDBauth* looks up the user in the database table;
    1. If user is not found then it adds the user to the table and applies the default role (specified in the directory configuration);
    2. If it finds the user then it takes no action in the database table;
08. The native *User* service library takes over and logs the user into the application;

Access to data has to be managed through *PHP Server Monitor*'s methods of permissions and authorisation and in the users table.


## Known limitations

Limitations of *PsmLDAPauth* are a combination of those of its [pre-requisites](#pre-requisites)


## Support

For more information on installation, configuration and more additional options please refer to the documentation wiki.

Debugging can be enabled by setting boolean `$GLOBALS['bl_DebugSwitch']` to `TRUE`.

```
$GLOBALS['bl_DebugSwitch'] = TRUE ;
```

For all other issues, queries, suggestions and comments please create an issue at the link provided at the top of this page.


## Contribute

Please feel free to clone/fork and contribute via pull requests. Donations also welcome, simply create an issue at the link provided at the top of this page.

Please make contact for more information.


## Environment ##
Platform and software stack known to be compatible:

* Server OS
    * *Debian Wheezy*
    * *Debian Jessie*
    * *Ubuntu* 14.04
* Client OS
    * *Debian Wheezy*
    * *Debian Jessie*
    * *Windows* 7
* Web servers
    * *Apache* 2.2
    * *Apache* 2.4
    * *Caddy* 2.2
* *PHP*
    * 5.4
    * 5.5
    * 5.6
    * 7.0
    * 7.4
* Directory servers
    * *OpenLDAP* 2.4
    * *Active Directory* (both *DS* and *LDS*) on *Windows* Server 2012
* Database servers
    * *MySQL* 5.4
    * *MySQL* 5.5


## License

Copyright (C) MMXX viharm

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

See enclosed [`LICENSE`][LICENSE?at=master] file.


## References


### PHP Server Monitor user authentication logic

* http://www.phpservermonitor.org/


## Credits


### Tools


#### Kint

*Kint* debugging library (http://kint-php.github.io/kint/), used under the MIT license

Copyright (c) 2013 Jonathan Vollebregt (jnvsor at gmail dot com), Rokas Šleinius (raveren at gmail dot com)


### Utilities


#### Codiad

*Codiad* web based IDE (https://github.com/Codiad/Codiad), used under a MIT-style license.

Copyright (c) Codiad & Kent Safranski (codiad.com)


#### CodeGit

*CodeGit* *Git* plugin for *Codiad* (https://github.com/Andr3as/Codiad-CodeGit), used under a MIT-style license.

Copyright (c) Andr3as <andranode@gmail.com>


#### VS Code

*Visual Studio Code* code editor, used under the *Microsoft Software License*.


#### Ungit

*Ungit* client for *Git* (https://github.com/FredrikNoren/ungit) used under the MIT license

Copyright (C) Fredrik Norén


#### GitLab

Hosted by *GitLab* code repository (gitlab.com).


#### Packagist

*Composer* package hosting provided by *Packagist* (https://packagist.org).


### Guidance

* [@dopeh](https://github.com/dopeh)
* [@sadortun](https://github.com/sadortun)
