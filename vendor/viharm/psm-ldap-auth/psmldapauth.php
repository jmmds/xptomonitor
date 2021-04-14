<?php
  /**
  * @package   PsmLDAPauth
  * @author    Viharm
  * @version   See VERSION file
  * @created   See VERSION file
  * @brief     This module provides LDAP authentication for PHP Server Monitor
  * @copyright Copyright (C) 2017, Viharm
  *            under the GNU General Public License version 3
  * 
  * Requires at least
  * - phpldapauth >= v02.03.00
  * - phpdbauth   >= v02.03.00
  *
  */

  /*----------------------------------------------------------------------
    PsmLDAPauth
    Copyright (C) 2018 viharm

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
  ----------------------------------------------------------------------*/



  if(!array_key_exists('bl_DebugSwitch',$GLOBALS)) { $GLOBALS['bl_DebugSwitch'] = FALSE ; }
  /* Set $GLOBALS['bl_DebugSwitch'] = TRUE for debugging */

  function psmldapauth ( $sr_Username , $sr_Passwd , $ar_DirConfigRaw , $ob_DbConn ) { // Returns true if authenticated, no group checking for further authorisation yet
    // Include the phpLDAPauth library
    // First try sub-folders as submodules if cloned recursively
    if ( ! @include_once ( realpath ( __DIR__ . DIRECTORY_SEPARATOR . 'phpLDAPauth' . DIRECTORY_SEPARATOR . 'phpldapauth.php' ) ) ) {
      // If not as a Git submodule then, assume that this (PsmLDAPauth) and the dependencies are all at the same level in .../vendor/viharm/ as per Composer
      if ( ! @include_once ( realpath ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'php-ldap-auth' . DIRECTORY_SEPARATOR . 'phpldapauth.php' ) ) ) {
        // If not at the same level then assume that the dependencies are in the vendor sub-directory
        require_once ( realpath ( __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'viharm' . DIRECTORY_SEPARATOR . 'php-ldap-auth' . DIRECTORY_SEPARATOR . 'phpldapauth.php' ) ) ;
        require_once ( realpath ( __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'viharm' . DIRECTORY_SEPARATOR . 'php-khelper' . DIRECTORY_SEPARATOR . 'phpKhelper.lib.inc.php' ) ) ;
      }
      else {
        require_once ( realpath ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'php-khelper' . DIRECTORY_SEPARATOR . 'phpKhelper.lib.inc.php' ) ) ;
      }
    }

    // Include the phpDBauth library, same logic as that for phpLDAPauth above.
    if ( ! @include_once ( realpath ( __DIR__ . DIRECTORY_SEPARATOR . 'phpDBauth' . DIRECTORY_SEPARATOR . 'phpdbauth.php' ) ) ) {
      if ( ! @include_once ( realpath ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'php-db-auth' . DIRECTORY_SEPARATOR . 'phpdbauth.php' ) ) ) {
        require_once ( realpath ( __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'viharm' . DIRECTORY_SEPARATOR . 'php-db-auth' . DIRECTORY_SEPARATOR . 'phpdbauth.php' ) ) ;
      }
    }

    // Review supplied arguments
    fn_Debug ( 'Supplied username' , $sr_Username ) ;
    fn_Debug ( 'Supplied password' , $sr_Passwd , TRUE ) ;
    fn_Debug ( 'Supplied directory configuration' , $ar_DirConfigRaw ) ;
    fn_Debug ( 'Supplied db connection' , $ob_DbConn ) ;

    // Sanitise raw configuration to replace zero-length strings extracted from database with null values, to ensure reasonable defaults are applied by phpLDAPauth.
    $ar_DirConfigRaw = array_replace (
      $ar_DirConfigRaw ,
      array_fill_keys (
        array_keys ( $ar_DirConfigRaw , '' ) ,
        NULL
      )
    ) ;

    // Formulate request
    $ar_Request = array (
      'ky_UserKeyword'  => $sr_Username ,
      'ky_UserPassword' => $sr_Passwd ,
      'ky_UserDomain'   => $ar_DirConfigRaw['authdir_userdomain'] ,
      'ky_GroupKeyword' => $ar_DirConfigRaw['authdir_groupname']
    ) ;
    
    // Formulate directory host and configuration
    $ar_DirHost = array (
     'ky_Locn' => $ar_DirConfigRaw['authdir_host_locn'] ,
     'ky_Port' => $ar_DirConfigRaw['authdir_host_port']
    ) ;
    $ar_DirConf = array (
     'ky_LdapType'           => $ar_DirConfigRaw['authdir_type'] ,
     'ky_LdapVer'            => $ar_DirConfigRaw['authdir_ldapver'] ,
     'ky_LdapFollowReferral' => $ar_DirConfigRaw['authdir_ldapfollowref']==='1' ? TRUE : FALSE ,
     'ky_BaseDn'             => $ar_DirConfigRaw['authdir_basedn'] ,
     'ky_UsernameAttrib'     => $ar_DirConfigRaw['authdir_usernameattrib'] ,
     'ky_GroupnameAttrib'    => $ar_DirConfigRaw['authdir_groupnameattrib'] ,
     'ky_GroupMemberAttrib'  => $ar_DirConfigRaw['authdir_groupmemattrib'] ,
     'ky_UserContainerRdn'   => $ar_DirConfigRaw['authdir_usercontainerrdn'] ,
     'ky_GroupContainerRdn'  => $ar_DirConfigRaw['authdir_groupcontainerrdn'] ,
     'ar_GroupSearchFilter'  => array (
       'objectClass=posixGroup' ,
       'objectClass=sambaGroupMapping'
     )
    ) ;
    fn_Debug ( 'Formulated directory host' , $ar_DirHost ) ;
    fn_Debug ( 'Formulated directory configuration' , $ar_DirConf ) ;

    // Formulate return variable
    $rt_Result = FALSE ;
    fn_Debug ( 'Return variable formulated' , $rt_Result ) ;
    
    // Create phpLDAPauth object
    $ob_Dir = new \cl_Dir ( $ar_DirHost , $ar_DirConf ) ;
    fn_Debug ( 'cl_Dir object created' , $ob_Dir ) ;

    // Call the authentication function and store the result
    $ar_AuthResult = $ob_Dir->fn_Auth($ar_Request) ;
  
    // Destroy directory object
    unset($ob_Dir) ;
    fn_Debug ( 'cl_Dir object destroyed' ) ;
  
    // Destroy user password from the working variable
    $ar_Request['ky_UserPassword'] = NULL ;
    fn_Debug ( 'password destroyed' ) ;

    // Check success of authentication
    if ( $ar_AuthResult['ky_User_Authenticated'] == TRUE ) {
      
      if ( is_null ( $ar_Request['ky_GroupKeyword'] ) | $ar_AuthResult['ky_Group_ContainsUser'] == TRUE ) {
      
        // If authenticated, then call the database function.
        $ar_Db_VerificationSummary = fn__Database_Verify (
          $ar_Request ,
          array (
            'key__Table_Name'             => PSM_DB_PREFIX . 'users' ,
            'key__Table_ColumnUsername'   => 'user_name' ,
            'key__Table_ColumnRole'       => 'level' ,
            'key__Table_DefaultRoleValue' => $ar_DirConfigRaw['authdir_defaultrole'] ,
          ) ,
          'pdo-mysql' ,
          NULL ,
          $ob_DbConn ,
          array (
            'password'        => uniqid('dummy_') ,
            'name'            => $sr_Username ,
            'mobile'          => '' ,
            'discord'         => '' ,
            'pushover_key'    => '' ,
            'pushover_device' => '' ,
            'webhook_url'     => '' ,
            'webhook_json'    => '' ,
            'telegram_id'     => '' ,
            'jabber'          => '' ,
            'email'           => ''
          )
        ) ;
    
        unset($ob_DbConn) ;
        fn_Debug ( 'Placeholder for DB object destroyed' ) ;
    
        // Check if the user was found in users_table or added to users_table. Can't be both.
        if ( $ar_Db_VerificationSummary [ "key__Database_UserFound" ] == TRUE ^ $ar_Db_VerificationSummary [ "key__Database_UserAdded" ] == TRUE ) {
    
          // User authenticated by directory; and either found in users_table or added to it.
          fn_Debug ( 'User authenticated by directory; and either found in users_table or added to it; Setting return response' , $ar_Db_VerificationSummary ) ;
      	  $rt_Result = TRUE ;
      	  
        } // user was found in users_table or added to users_table, not both
        else {
          
          // User authenticated, and not found in users_table, but could not be added to it.
          fn_Debug ( 'User authenticated, and not found in users_table, but could not be added to it.' , $var__Database_VerificationSummary ) ;
      	  
        } // authenticated, and not found in users_table, but could not be added to it
      
      } // Either no group name is specified or the user exists in the specified group
      
      else {
        fn_Debug ( 'User not member of the specified group' , $ar_Request['ky_GroupKeyword'] ) ;
      } // User not member of specified group
      
    } // Authenticated
    else {
      
      // User not authenticated by the directory service
      fn_Debug ( 'User not authenticated by the directory service; falling back to PSM internal auth method' , $ar_AuthResult ) ;

    } // not authenticated by the directory service
    
    // Return result
    return $rt_Result ;
    
  }
?>
