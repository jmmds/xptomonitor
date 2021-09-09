<?php
  // $GLOBALS['bl_DebugSwitch'] = TRUE ; // for debugging

  // Include the phpLDAPauth library (also automatically includes the debuggin library)
  require_once ( realpath ( __DIR__ . DIRECTORY_SEPARATOR . 'phpldapauth.php' ) ) ;

  // Define directory host array
  $ar_DirHost = array (
   'ky_Locn' => 'localhost' ,
   'ky_Port' => '389'
  ) ;
  
  // Define directory configuration array
  $ar_DirConf = array (
   'ky_LdapType'           => 'openldap' ,
   'ky_LdapVer'            => 3 ,
   'ky_LdapFollowReferral' => FALSE ,
   'ky_LdapTLS'            => FALSE ,
   'ky_BaseDn'             => 'dc=domain,dc=tld' ,
   'ky_UsernameAttrib'     => 'uid' ,
   'ky_GroupnameAttrib'    => 'cn' ,
   'ky_GroupMemberAttrib'  => 'memberuid' ,
   'ky_UserContainerRdn'   => 'ou=Users' ,
   'ky_GroupContainerRdn'  => 'ou=Groups' ,
   'ar_GroupSearchFilter'  => array (
     'objectClass=posixGroup' ,
     'objectClass=sambaGroupMapping'
   )
  ) ;

  // Formulate the authentication request. Warning this contains the password!
  $ar_Request = array (
    'ky_UserKeyword'  => 'username' ,
    'ky_UserPassword' => 'password' ,
    'ky_UserDomain'   => 'DOMAIN' ,
    'ky_GroupKeyword' => 'usersgroup'
  ) ;

  // Formulate an array with attributes to be requested
  $ar_RequestAttrib = array (
    'cn' ,
    'sn' ,
    'displayName' ,
    'objectClass' ,
    'mail' ,
    'jpegPhoto' ,
  ) ;

  // Create phpLDAPauth object
  $ob_Dir = new cl_Dir ( $ar_DirHost , $ar_DirConf ) ;

  // Call the authentication function and store the result
  $ar_AuthResult = $ob_Dir->fn_Auth ( $ar_Request , $ar_RequestAttrib ) ;

  // Destroy user password from the working variable
  $ar_Request['ky_UserPassword'] = NULL ;

  fn_Debug ( 'Returned result' , $ar_AuthResult ) ;
  
  // Check if the user is authenticated
  if ( $ar_AuthResult['ky_User_Authenticated'] === TRUE ) {
  	// User is authenticated
    echo ( 'User ' . $ar_Request['ky_UserKeyword'] . ' logged in' ) ;
    
    // Fetch users details
    echo ( $ar_Request['ky_UserKeyword'] . '\'s details:<br />' ) ;
    foreach ($ar_AuthResult['ar_UserAttrib'] as $sr_Attrib=>$ar_Val) {
      echo ( $sr_Attrib . ':<br />' );
      foreach ( $ar_Val as $vr_Key=>$vr_Val ) {
        if (is_int($vr_Key)) {
          echo ( $vr_Val . '<br />' );
        }
      }
    }
  } // User authenticated
  else {
    echo ( 'Username or Password is invalid' ) ;
  } // User not authenticated
?>