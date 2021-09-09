<?php
                                             
  /**
  * @package   LDAP Directory authentication script
  * @version   See VERSION
  * @author    Viharm
  * @brief     LDAP authentication library
  * @detail    Authenticates a username against a LDAP directory and returns
  *            boolean true if authenticated. Option to authenticate only 
  *            users belonging to a specific group. Settings are configurable
  *            to comply with various directory environments. See README.md.
  * @copyright Copyright (C) 2016~2020, Viharm
  *            Under modified BSD (3-clause) license
  *            (see LICENSE or http://opensource.org/licenses/BSD-3-Clause)
  **/

  if(!array_key_exists('bl_DebugSwitch',$GLOBALS)) { $GLOBALS['bl_DebugSwitch'] = FALSE ; }

  /* Set $GLOBALS['bl_DebugSwitch'] = TRUE for debugging */

  /* Include libraries */
  // Start iterating through all subdirectories under library path
  $sr_Directory = '' ;
  foreach ( glob ( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Lib' . DIRECTORY_SEPARATOR . '*' , GLOB_ONLYDIR ) as $sr_Directory ) {
    $sr_Filename = '' ;
    foreach (
      glob ( 
        $sr_Directory .
        DIRECTORY_SEPARATOR .
        '*.inc.php'
      ) as $sr_Filename
    ) {
      include_once(realpath($sr_Filename)) ;
    }
  }
  unset($sr_Filename) ;
  unset($sr_Directory) ;
  
    /**
     * This class provides boolean results of LDAP authentication
     * 
     * 
     * Parameters
     * 
     * $ar_DirHost = array (
     *  'ky_Locn' => 'localhost' ,
     *  'ky_Port' => '389'
     * ) ;
     * 
     * $ar_DirConf = array (
     *  'ky_LdapType'           => 'openldap' ,   // Options: 'openldap', 'ad-ds', 'ad-lds'
     *  'ky_LdapVer'            => 3 ,
     *  'ky_LdapFollowReferral' => FALSE ,
     *  'ky_LdapTLS'            => FALSE ,
     *  'ky_BaseDn'             => NULL ,
     *  'ky_UsernameAttrib'     => 'uid' ,
     *  'ky_GroupnameAttrib'    => 'cn' ,
     *  'ky_GroupMemberAttrib'  => 'memberuid' ,
     *  'ky_UserContainerRdn'   => 'ou=Users' ,
     *  'ky_GroupContainerRdn'  => 'ou=Groups' ,
     *  'ar_GroupSearchFilter'  => array (
     *    'objectClass=posixGroup' ,
     *    'objectClass=sambaGroupMapping'
     *  )
     * ) ;
     * 
     *  Future possibility of user search may require the following
     *  user searchfilter as an array item
     *  'ar_UserSearchFilter'  => array (
     *    'objectClass=InetOrgPerson' ,
     *    'objectClass=posixAccount' ,
     *    'objectClass=sambaSamAccount' ,
     *    'objectClass=extensibleObject' ,
     *    'objectClass=top'
     *  ) ,
     */

  class cl_Dir {
    
    // Private host and directory configuration should not be available to extended classes
    private $ar_Dir = array() ;
    
    function __construct (
      $ag_DirHost = array (
        'ky_Locn' => 'localhost' ,
        'ky_Port' => '398'
      ) ,
      $ag_DirConf = array (
        'ky_BaseDn' => NULL ,
        'ky_UsernameAttrib' => 'uid' ,
        'ky_GroupnameAttrib' => 'cn' ,
        'ky_GroupMemberAttrib' => 'memberuid'
      )
    ) {
      
      fn_Debug ( 'Class arguments- host' , $ag_DirHost ) ;
      fn_Debug ( 'Class arguments- conf' , $ag_DirConf ) ;
      
      $this->ar_Dir['ky_Host'] = $ag_DirHost ;
      $this->ar_Dir['ky_Conf'] = $ag_DirConf ;
      fn_Debug ( 'Arguments into private variables' , $this->ar_Dir ) ;
      
      /* Audit provided directory settings and set reasonable defaults where missing */
      
      // Host configuration
      if ( ! array_key_exists ( 'ky_Locn' , $this->ar_Dir['ky_Host'] ) | $this->ar_Dir['ky_Host']['ky_Locn'] == '' ) {
        $this->ar_Dir['ky_Host']['ky_Locn'] = 'localhost' ;
      }
      if ( ! array_key_exists ( 'ky_Port' , $this->ar_Dir['ky_Host'] ) | $this->ar_Dir['ky_Host']['ky_Port'] == '' ) {
        $this->ar_Dir['ky_Host']['ky_Port'] = 389 ;
      }
      // Directory configuration
      if ( ! array_key_exists ( 'ky_LdapVer' , $this->ar_Dir['ky_Conf'] ) | $this->ar_Dir['ky_Conf']['ky_LdapVer'] == '' ) {
        $this->ar_Dir['ky_Conf']['ky_LdapVer'] = 3 ;
      }
      if ( ! array_key_exists ( 'ky_LdapType' , $this->ar_Dir['ky_Conf'] ) | $this->ar_Dir['ky_Conf']['ky_LdapType'] == '' ) {
        $this->ar_Dir['ky_Conf']['ky_LdapType'] = 'openldap' ;
      }
      if ( ! array_key_exists ( 'ky_LdapFollowReferral' , $this->ar_Dir['ky_Conf'] ) | $this->ar_Dir['ky_Conf']['ky_LdapFollowReferral'] == '' ) {
        $this->ar_Dir['ky_Conf']['ky_LdapFollowReferral'] = FALSE ;
      }
      if ( ! array_key_exists ( 'ky_LdapTLS' , $this->ar_Dir['ky_Conf'] ) | $this->ar_Dir['ky_Conf']['ky_LdapTLS'] == '' ) {
        $this->ar_Dir['ky_Conf']['ky_LdapTLS'] = FALSE ;
      }
      if ( ! array_key_exists ( 'ky_UsernameAttrib' , $this->ar_Dir['ky_Conf'] ) | $this->ar_Dir['ky_Conf']['ky_UsernameAttrib'] == '' ) {
        $this->ar_Dir['ky_Conf']['ky_UsernameAttrib'] = 'uid' ;
      }
      if ( ! array_key_exists ( 'ky_GroupnameAttrib' , $this->ar_Dir['ky_Conf'] ) | $this->ar_Dir['ky_Conf']['ky_GroupnameAttrib'] == '' ) {
        $this->ar_Dir['ky_Conf']['ky_GroupnameAttrib'] = 'cn' ;
      }
      if ( ! array_key_exists ( 'ky_GroupMemberAttrib' , $this->ar_Dir['ky_Conf'] ) | $this->ar_Dir['ky_Conf']['ky_GroupMemberAttrib'] == '' ) {
        $this->ar_Dir['ky_Conf']['ky_GroupMemberAttrib'] = 'memberuid' ;
      }
      
      /* Check if user search filter is provided */
      // This feature is not used as users are not searched
      // if ( ! array_key_exists ( 'ar_UserSearchFilter' , $this->ar_Dir['ky_Conf'] ) ) {
        // // If the user search filter is not provided, then create a default array
        // $this->ar_Dir['ky_Conf']['ar_UserSearchFilter'] = array (
          // 'objectClass=InetOrgPerson' ,
          // 'objectClass=posixAccount' ,
          // 'objectClass=sambaSamAccount' ,
          // 'objectClass=extensibleObject' ,
          // 'objectClass=top'
        // ) ;
      // }
      
      /* Check if group search filter is provided */
      if ( ! array_key_exists ( 'ar_GroupSearchFilter' , $this->ar_Dir['ky_Conf'] ) ) {
        // If the group search filter is not provided, then create a default array
        $this->ar_Dir['ky_Conf']['ar_GroupSearchFilter'] = array (
          'objectClass=posixGroup' ,
          'objectClass=sambaGroupMapping'
        ) ;
      }
      
      /* Formulate search filter strings */
      
      // User search filter string
      // This feature is not used as users are not searched
      // $this->ar_Dir['ky_Conf']['ky_UserSearchFilter'] = '(|' ;
      // foreach ( $this->ar_Dir['ky_Conf']['ar_UserSearchFilter'] as $sr_ArrayItem ) {
        // $this->ar_Dir['ky_Conf']['ky_UserSearchFilter'] .= '('.$sr_ArrayItem.')' ;
      // }
      // $this->ar_Dir['ky_Conf']['ky_UserSearchFilter'] .= ')' ;
      // fn_Debug ( 'User search filter formulated' , $this->ar_Dir['ky_Conf']['ky_UserSearchFilter'] ) ;
      
      // Group search filter string
      $this->ar_Dir['ky_Conf']['ky_GroupSearchFilter'] = '(|' ;
      foreach ( $this->ar_Dir['ky_Conf']['ar_GroupSearchFilter'] as $sr_ArrayItem ) {
        $this->ar_Dir['ky_Conf']['ky_GroupSearchFilter'] .= '('.$sr_ArrayItem.')' ;
      }
      $this->ar_Dir['ky_Conf']['ky_GroupSearchFilter'] .= ')' ;
      fn_Debug ( 'Group search filter formulated' , $this->ar_Dir['ky_Conf']['ky_GroupSearchFilter'] ) ;
      
      
      
      /* Organise attributes required in search */
      
      // User search is not carried out hence this is not required; still kept for future use.
      //$this->ar_Dir['ky_Conf']['ky_UserSearchRequiredAttrib'] = array ( 
        //$this->ar_Dir['ky_Conf']['ky_UsernameAttrib']
      //) ;
      //fn_Debug ( 'User attributes required in user search' , $this->ar_Dir['ky_Conf']['ky_UserSearchRequiredAttrib'] ) ;
      
      $this->ar_Dir['ky_Conf']['ky_GroupSearchRequiredAttrib'] = array ( 
        $this->ar_Dir['ky_Conf']['ky_GroupnameAttrib'] ,
        $this->ar_Dir['ky_Conf']['ky_GroupMemberAttrib']
      ) ;
      fn_Debug ( 'Group attributes required in user search' , $this->ar_Dir['ky_Conf']['ky_GroupSearchRequiredAttrib'] ) ;
    }
    
    /* Function to authenticate against a specified directory server
     * 
     * Parameters
     *
     *   Search request in a 'Common' associative array of strings
     *   $ar_Request = array (
     *     'ky_UserKeyword'  => 'username' ,
     *     'ky_UserPassword' => 'password' ,
     *     'ky_UserDomain'   => 'DOMAIN' ,
     *     'ky_GroupKeyword' => 'usersgroup' ,
     *   ) ;
     *
     * Returned array
     *
     * $rt_Dir_Result = array (
     *  'ky_User_Authenticated' => FALSE,
     *  'ky_Group_Exists'       => FALSE,
     *  'ky_Group_ContainsUser' => FALSE,
     * ) ;
     *
     */
     
    public function fn_Auth ( $ag_Request , $ag_RequestAttrib = NULL ) {
      
      fn_Debug ( 'Arguments provided' , func_get_args() ) ;
      fn_Debug ( 'Directory' , $this->ar_Dir ) ;
      
      // Protected request can be accessed by child classes but not from outside the class.
      $ar_Dir_Auth = array() ;  
      $ar_Dir_Auth['ky_Request'] = $ag_Request ;
      fn_Debug ( 'Request' , $ar_Dir_Auth['ky_Request'], 'ky_UserPassword' ) ;
      
      // Pre-set worst case for return result;
      $rt_Dir_Result = array (
        'ky_User_Authenticated' => FALSE ,
        'ky_Group_Exists'       => FALSE ,
        'ky_Group_ContainsUser' => FALSE
      ) ;
      fn_Debug ( 'Default result' , $rt_Dir_Result ) ;
      $rt_UserAttrib = NULL ;
      fn_Debug ( 'Default attributes results' , $rt_UserAttrib ) ;

      // Remove asterisk from groupname to ensure that the group search filter does not have wildcards
      $ar_Dir_Auth['ky_Request']['ky_GroupKeyword'] = preg_replace (
        '/[\*]+/' ,
        '' ,
        $ar_Dir_Auth['ky_Request']['ky_GroupKeyword']
      ) ;
      fn_Debug ( 'Cleaned requested group keyword' , $ar_Dir_Auth['ky_Request']['ky_GroupKeyword'] ) ;
      
      // Check to see if user search base has been specified
      if ( ! array_key_exists ( 'ky_UserContainerRdn' , $this->ar_Dir['ky_Conf'] ) ) {
        // If not, then set the users DN for searches as the supplied directory base DN
        $ar_Dir_Auth['ky_Search']['ky_User']['ky_ContainerDn'] = $this->ar_Dir['ky_Conf']['ky_BaseDn'] ;
      } else {
        switch ( $this->ar_Dir['ky_Conf']['ky_UserContainerRdn'] ) {
          case NULL:
          case "":
            // If not, then set the users base DN for searches as the supplied directory base DN
            $ar_Dir_Auth['ky_Search']['ky_User']['ky_ContainerDn'] = $this->ar_Dir['ky_Conf']['ky_BaseDn'] ;
            break;
          default:
            // If provided, then set the users base DN for searches as a concatenated string
            // of the supplied user search base and directory base DN
            $ar_Dir_Auth['ky_Search']['ky_User']['ky_ContainerDn'] =
              $this->ar_Dir['ky_Conf']['ky_UserContainerRdn'] .
              ',' .
              $this->ar_Dir['ky_Conf']['ky_BaseDn']
            ;
        }
      }
      fn_Debug ( 'User search base set' , $ar_Dir_Auth['ky_Search']['ky_User']['ky_ContainerDn'] ) ;
      
      // Formulate a username, based on the type of LDAP directory
      fn_Debug ( 'Username to be formulated based on directory type. Checking if user domain is needed' , $this->ar_Dir['ky_Conf']['ky_LdapType'] ) ;
      // User domain is required only if the LDAP type is either AD DS or AD LDS
      if ( $this->ar_Dir['ky_Conf']['ky_LdapType'] === 'ad-ds' || $this->ar_Dir['ky_Conf']['ky_LdapType'] === 'ad-lds' ) {
        fn_Debug ( 'LDAP type specified is either AD DS or AD LDS. Checking if user domain is specified' ) ;
        if ( ! array_key_exists ( 'ky_UserDomain' , $ar_Dir_Auth['ky_Request'] ) || $ar_Dir_Auth['ky_Request']['ky_UserDomain'] == "" ) {
          fn_Debug ( 'User domain not specified, triggering user error.' ) ;
          trigger_error ( 'User domain not specified' , E_USER_ERROR ) ;
        } else {
          fn_Debug ( 'User domain is specified' , $ar_Dir_Auth['ky_Request']['ky_UserDomain'] ) ;
        }
      }
      switch ( $this->ar_Dir['ky_Conf']['ky_LdapType'] ) {
        case 'adds' :
        case 'ad-ds' :
          /* Username for AD DS is of the format NETBIOSDOMAIN\username */
          fn_Debug ( 'Configuring username for Active Directory Domain Services (AD DS)' ) ;
          $ar_Dir_Auth['ky_Search']['ky_User']['ky_Username'] =
            $ar_Dir_Auth['ky_Request'] ['ky_UserDomain'] .
            '\\' .
            $ar_Dir_Auth['ky_Request'] ['ky_UserKeyword'] ;
          break ;
        case  'adlds' :
        case  'ad-lds' :
          /* Username for AD LDS is of the format username@DOMAIN */
          fn_Debug ( 'Configuring username for Active Directory Lightweight Directory Services (AD LDS)' ) ;
          $ar_Dir_Auth['ky_Search']['ky_User']['ky_Username'] =
            $ar_Dir_Auth['ky_Request'] ['ky_UserKeyword'] .
            '@' .
            $ar_Dir_Auth['ky_Request'] ['ky_UserDomain'] ;
          break ;
        case '' :
        case 'openldap' :
        default :
          /* Username for OpenLDAP is the user's DN ; This is also the fallback */
          $ar_Dir_Auth['ky_Search']['ky_User']['ky_Username'] =
            $this->ar_Dir['ky_Conf']['ky_UsernameAttrib'] .
            '=' .
            $ar_Dir_Auth['ky_Request'] ['ky_UserKeyword'] .
            ',' .
            $ar_Dir_Auth['ky_Search']['ky_User']['ky_ContainerDn']
          ;
      }
      
      fn_Debug ( 'User DN formulated' ,     $ar_Dir_Auth['ky_Search']['ky_User']['ky_Username'] ) ;
      
      // Formulate the user search filter
      // Feature not used; code retained for future use
      //$ar_Dir_Auth['ky_Search']['ky_User']['ky_Filter'] = $this->ar_Dir['ky_Conf']['ky_UserSearchFilter'] ;
      //fn_Debug ( 'User search filter formulated' , $ar_Dir_Auth['ky_Search']['ky_User']['ky_Filter'] ) ;

      // Check to see if group search base has been specified
      if ( ! array_key_exists ( 'ky_GroupContainerRdn' , $this->ar_Dir['ky_Conf'] ) ) {
        // If not, then set the groups base DN for searches as the supplied directory base DN
        $ar_Dir_Auth['ky_Search']['ky_Group']['ky_ContainerDn'] = $this->ar_Dir['ky_Conf']['ky_BaseDn'] ;
      } else {
        switch ( $this->ar_Dir['ky_Conf']['ky_GroupContainerRdn'] ) {
          case NULL:
          case "":
            // If not, then set the groups base DN for searches as the supplied directory base DN
            $ar_Dir_Auth['ky_Search']['ky_Group']['ky_ContainerDn'] = $this->ar_Dir['ky_Conf']['ky_BaseDn'] ;
            break;
          default:
            // If provided, then set the groups base DN for searches as a concatenated string of the supplied group search base and directory base DN
            $ar_Dir_Auth['ky_Search']['ky_Group']['ky_ContainerDn'] =
              $this->ar_Dir['ky_Conf']['ky_GroupContainerRdn'] .
              ',' .
              $this->ar_Dir['ky_Conf']['ky_BaseDn']
            ;
        }
      }
      fn_Debug ( 'Group search base set' , $ar_Dir_Auth['ky_Search']['ky_Group']['ky_ContainerDn'] ) ;

      // Formulate a DN for the groupname.
      $ar_Dir_Auth['ky_Search']['ky_Group']['ky_Dn'] =
        $this->ar_Dir['ky_Conf']['ky_GroupnameAttrib'] .
        '=' .
        $ar_Dir_Auth['ky_Request']['ky_GroupKeyword'] .
        ',' .
        $ar_Dir_Auth['ky_Search']['ky_Group']['ky_ContainerDn']
      ;
      fn_Debug ( 'Group DN formulated' , $ar_Dir_Auth['ky_Search']['ky_Group']['ky_Dn'] ) ;
      
      // Formulate a search filter for the groupname
      // by adding groupname attribute to the global group search filter
      $ar_Dir_Auth['ky_Search']['ky_Group']['ky_Filter'] =
        '(&' .
        $this->ar_Dir['ky_Conf']['ky_GroupSearchFilter'] .
        '(' .
        $this->ar_Dir['ky_Conf']['ky_GroupnameAttrib'] .
        '=' .
        $ar_Dir_Auth['ky_Request']['ky_GroupKeyword'] .
        '))'
        ;
        fn_Debug ( 'Group search filter formulated' , $ar_Dir_Auth['ky_Search']['ky_Group']['ky_Filter'] ) ;

      // Check if LDAP functionality exists
      fn_Debug( 'Checking if LDAP functionality exists' ) ;
      if ( ! function_exists ( 'ldap_connect' ) ) {
          trigger_error (
            'Please install the PHP LDAP module in order to use LDAP authentication.' ,
            E_USER_ERROR
          ) ;
      } else {
        
        fn_Debug( 'LDAP functionality exists. Connecting to directory.' , $this->ar_Dir['ky_Host'] ) ;
        $ar_Dir_Auth['ky_Conn'] = ldap_connect (
          $this->ar_Dir['ky_Host']['ky_Locn'] ,
          $this->ar_Dir['ky_Host']['ky_Port']
        ) ;

        // If the connection was successfull...
        fn_Debug( 'Checking if LDAP connection was succesful' , $ar_Dir_Auth['ky_Conn'] ) ;
        if ( $ar_Dir_Auth['ky_Conn'] ) {
          
          fn_Debug( 'LDAP connection successful' , $ar_Dir_Auth['ky_Conn'] ) ;
  
          // Set LDAP protocol version (either 2 or 3.
          fn_Debug( 'Setting LDAP protocol version' ) ;
          
          // If failed to set LDAP protocal version...
          if (
            ! ldap_set_option (
              $ar_Dir_Auth['ky_Conn'] ,
              LDAP_OPT_PROTOCOL_VERSION ,
              $this->ar_Dir['ky_Conf']['ky_LdapVer']
            )
          ) {
            // ...then trigger error
            fn_Debug( 'Failed to set LDAP protocol version' , $this->ar_Dir['ky_Conf']['ky_LdapVer'] ) ;
            trigger_error ( 'Failed to set LDAP protocol version' , E_USER_ERROR ) ;
          } else {
            fn_Debug( 'LDAP protocol version successfully set' , $this->ar_Dir['ky_Conf']['ky_LdapVer'] ) ;
          }
          
          // Set LDAP referral option
          fn_Debug( 'Configuring LDAP referrals' ) ;
          if (
          	! ldap_set_option (
          	  $ar_Dir_Auth['ky_Conn'] ,
              LDAP_OPT_REFERRALS ,
              $this->ar_Dir['ky_Conf']['ky_LdapFollowReferral']
            )
          ) {
            // ...then trigger error
            fn_Debug( 'Failed to set LDAP referral option' , $this->ar_Dir['ky_Conf']['ky_LdapFollowReferral'] ) ;
          } else {
            fn_Debug( 'LDAP referral option successfully set' , $this->ar_Dir['ky_Conf']['ky_LdapFollowReferral'] ) ;
          }

          // Upgrade connection to TLS
          fn_Debug( 'Checking if LDAP TLS functionality exists' ) ;
          if ( function_exists ( 'ldap_start_tls' ) ) {
            fn_Debug( 'Checking request for TLS connection' , $this->ar_Dir['ky_Conf']['ky_LdapTLS'] ) ;
            if( $this->ar_Dir['ky_Conf']['ky_LdapTLS'] === TRUE ) {
              fn_Debug( 'Requested TLS connection to directory. Attempting to upgrade connection.' ) ;
              if (!ldap_start_tls($ar_Dir_Auth['ky_Conn'])) {
                fn_Debug( 'Failed to upgrade LDAP connection to TLS' ) ;
              } // Failed to upgrade LDAP connection to TLS
              else {
                fn_Debug( 'LDAP connection successfully upgraded to TLS' ) ;
              } // LDAP connection upgraded to TLS
            } // TLS requested for LDAP connection
          } // ldap_start_tls function exists
          else {
            fn_Debug( 'TLS capability does not exist in the PHP LDAP library. Will attempt to proceed without TLS.' ) ;
          }
          
          // ... then start by checking if username has been submitted.
          fn_Debug( 'Checking username in request' , $ar_Dir_Auth['ky_Request'] , 'ky_UserPassword' ) ;
          switch ( $ar_Dir_Auth['ky_Request']['ky_UserKeyword'] ) {
            case NULL:
            case "":
              // In case keyword for user search is null, escape and avoid binding at all
              fn_Debug( 'No username found in request, not binding to directory.' , $ar_Dir_Auth['ky_Request']['ky_UserKeyword'] ) ;
              break;
            default:
              
              // In case keyword for user search is non-null, proceed to bind and save bind success to boolean variable.
              fn_Debug( 'Username found in request, proceeding to bind with directory.' , $ar_Dir_Auth['ky_Request']['ky_UserKeyword'] ) ;
              $rt_Dir_Result['ky_User_Authenticated'] = ldap_bind (
                $ar_Dir_Auth['ky_Conn'] ,
                    $ar_Dir_Auth['ky_Search']['ky_User']['ky_Username'] ,
                $ar_Dir_Auth['ky_Request']['ky_UserPassword']
              ) ;

              // If bind was successful...
              fn_Debug ( 'Checking if binding was successful' , $rt_Dir_Result['ky_User_Authenticated'] ) ;
              if ( $rt_Dir_Result['ky_User_Authenticated'] ) {
                
                fn_Debug ( 'Binding successful' , $ar_Dir_Auth['ky_Request']['ky_UserKeyword'] ) ;

                // ... then evaluate group search keyword
                switch ( $ar_Dir_Auth['ky_Request']['ky_GroupKeyword'] ) {
                  case NULL:
                  case "":
                    // In case keyword for user search is null, escape and avoid searching in groups at all
                    fn_Debug ( 'No group name provided, not checking group membership' , $ar_Dir_Auth['ky_Request']['ky_GroupKeyword'] ) ;
                    break;
                  default:
                    // In case keyword for gruop search is non-null, proceed to search and save search results to resource variable.
                    $ar_Dir_Auth['ky_Search']['ky_Group']['ky_Result'] = ldap_search (
                      $ar_Dir_Auth['ky_Conn'] ,
                      //$ar_Dir_Auth['ky_Search']['ky_Group']['ky_Dn'] ,
                      $this->ar_Dir['ky_Conf']['ky_BaseDn'] ,
                      $ar_Dir_Auth['ky_Search']['ky_Group']['ky_Filter'] ,
                      $this->ar_Dir['ky_Conf']['ky_GroupSearchRequiredAttrib']
                    ) ;
                    fn_Debug ( 'Group search results' , $ar_Dir_Auth['ky_Search']['ky_Group']['ky_Result'] ) ;

                    // Get group entries
                    $ar_Dir_Auth['ky_Search']['ky_Group']['ky_ResultEntries'] = ldap_get_entries (
                      $ar_Dir_Auth['ky_Conn'] ,
                      $ar_Dir_Auth['ky_Search']['ky_Group']['ky_Result']
                    ) ;
                    fn_Debug ( 'Group search result entries' , $ar_Dir_Auth['ky_Search']['ky_Group']['ky_ResultEntries'] ) ;

                    /* Organise group entries */
                    fn_Debug ( 'Organising group entries in group search result' ) ;
                    // Cycle trhough each search result
                    for (
                      $nm_Counter_01 = 0 ;
                      $nm_Counter_01 < $ar_Dir_Auth['ky_Search']['ky_Group']['ky_ResultEntries']['count'] ;
                      $nm_Counter_01++
                    ) {
                      // Identify search results which contain the specified 'memberuid' attribute
                      fn_Debug ( 'Checking if this group has members' , $ar_Dir_Auth['ky_Search']['ky_Group']['ky_ResultEntries'][$nm_Counter_01] ) ;
                      fn_Debug ( 'Looking for memberuid attribute by comparing lower cases' , $this->ar_Dir['ky_Conf']['ky_GroupMemberAttrib'] ) ;
                      if (
                        array_key_exists (
                          strtolower($this->ar_Dir['ky_Conf']['ky_GroupMemberAttrib']) ,
                          $ar_Dir_Auth['ky_Search']['ky_Group']['ky_ResultEntries'][$nm_Counter_01]
                        )
                      ) {
                        
                        fn_Debug ( 'Members found, searching for requested user to identify group membership' ) ;
                        // Cycle through members of the qualifying group
                        for (
                          $nm_Counter_02 = 0 ;
                          $nm_Counter_02 <
                            $ar_Dir_Auth
                              ['ky_Search']
                              ['ky_Group']
                              ['ky_ResultEntries']
                              [$nm_Counter_01]
                              [strtolower($this->ar_Dir['ky_Conf']['ky_GroupMemberAttrib'])]
                              ['count']
                          ;
                          $nm_Counter_02++
                        ) {
                          
                          // If the group member matches the searched username
                          if (
                            $ar_Dir_Auth
                              ['ky_Search']
                              ['ky_Group']
                              ['ky_ResultEntries']
                              [$nm_Counter_01]
                              [strtolower($this->ar_Dir['ky_Conf']['ky_GroupMemberAttrib'])]
                              [$nm_Counter_02]
                            ===
                            $ar_Dir_Auth['ky_Request']['ky_UserKeyword']
                          ) {
                            // Set success flag
                            $rt_Dir_Result['ky_Group_ContainsUser'] = TRUE ;
                            fn_Debug ( 'Group contains the specified user; breaking from loop' , $rt_Dir_Result['ky_Group_ContainsUser'] ) ;
                            break ;
                          } else {
                            fn_Debug ( 'Specified user user not yet found in specified group' , $rt_Dir_Result['ky_Group_ContainsUser'] ) ;
                          }
                        }
                      } else {
                        fn_Debug ( 'Group has no members.' ) ;
                      }
                      
                      // Set boolean status of existence of group in the directory
                      $rt_Dir_Result['ky_Group_Exists'] = TRUE ;
                      fn_Debug ( 'Group existence flag set.' ,$rt_Dir_Result['ky_Group_Exists'] ) ;
                    }
                    // Free memory for group search
                    fn_Debug ( 'Freeing search result memory', $ar_Dir_Auth['ky_Search']['ky_Group']['ky_Result'] ) ;
                    ldap_free_result ( $ar_Dir_Auth['ky_Search']['ky_Group']['ky_Result'] ) ;
                }
                
                // Check if user attributes are requested
                if ( ! is_null($ag_RequestAttrib) || $ag_RequestAttrib !='' ) {
                  // Attribute request is not null, now check if it is an array
                  if (is_array($ag_RequestAttrib)) {
                    // Attribute request is an array, now check if it has one or more elements
                    if (count($ag_RequestAttrib)>0) {
                      // Attribute request is a non-zero array. Now fetch requested attributes
                      $ar_UserAttrib['ar_Search']['ob_Search'] = ldap_read (
                        $ar_Dir_Auth['ky_Conn'] ,
                        $ar_Dir_Auth['ky_Search']['ky_User']['ky_Username'] ,
                        'objectClass=*' ,
                        $ag_RequestAttrib ,
                        0 ,
                        0 ,
                        0 ,
                        LDAP_DEREF_NEVER
                      ) ;
                      fn_Debug ( 'Attributes fetch', $ar_UserAttrib['ar_Search']['ob_Search'] ) ;
                      $ar_UserAttrib['ar_Search']['ar_RawResult'] = ldap_get_entries (
                        $ar_Dir_Auth['ky_Conn'] ,
                        $ar_UserAttrib['ar_Search']['ob_Search']
                      ) ;
                      fn_Debug ( 'Attribute raw values', $ar_UserAttrib['ar_Search']['ar_RawResult'] ) ;
                      // Assess the quality of the previous command based on the number of results. It should exactly one!
                      switch ($ar_UserAttrib['ar_Search']['ar_RawResult']['count']) {
                        case 0:
                          fn_Debug ( 'No results found', $ar_UserAttrib['ar_Search']['ar_RawResult'] ) ;
                        case 1:
                          fn_Debug ( 'One result found', $ar_UserAttrib['ar_Search']['ar_RawResult'][0] ) ;
                          $rt_UserAttrib['dn']['count'] = 1 ;
                          $rt_UserAttrib['dn'][0] = $ar_UserAttrib['ar_Search']['ar_RawResult'][0]['dn'] ;
                          foreach ( $ag_RequestAttrib as $sr_AttribItem ) {
                            fn_Debug ( 'Processing item' , $sr_AttribItem ) ;
                            if ( array_key_exists ( strtolower($sr_AttribItem) , $ar_UserAttrib['ar_Search']['ar_RawResult'][0] ) ) {
                              fn_Debug ( 'Requested attribute exists on the directory', $sr_AttribItem ) ;
                              $rt_UserAttrib[$sr_AttribItem] = $ar_UserAttrib['ar_Search']['ar_RawResult'][0][strtolower($sr_AttribItem)] ;
                              fn_Debug ( 'Saved item' , $rt_UserAttrib[$sr_AttribItem] ) ;
                            } // Requested attribute exists on the directory
                            else {
                              fn_Debug ( 'Requested attribute not found on directory' , $sr_AttribItem ) ;
                              $rt_UserAttrib[$sr_AttribItem]['count'] = 0 ;
                              $rt_UserAttrib[$sr_AttribItem][0] = NULL ;
                            } // Requested attribute not found on directory
                          } // foreach
                          fn_Debug ( 'Attributes consolidated' , $rt_UserAttrib ) ;
                          break;
                        default:
                          fn_Debug ( 'More than one results found', $ar_UserAttrib['ar_Search']['ar_RawResult'] ) ;
                      } // switch
                    } // Attribute request is a non-zero array
                    else {
                      fn_Debug ( 'Attribute request is a zero-element array', $ag_RequestAttrib ) ;
                    } // Attribute request is a zero-element array
                  } // Attribute request is an array
                  else {
                    fn_Debug ( 'Attribute request is not an array', $ag_RequestAttrib ) ;
                  } // Attribute request is not an array
                } // Attribute request is not null
                else {
                  fn_Debug ( 'Attribute request is null', $ag_RequestAttrib ) ;
                } // Attribute request is null
              } else {
                // ... If bind failed then set the return boolean variable
                $rt_Dir_Result['ky_User_Authenticated'] = FALSE ;
                fn_Debug ( 'Binding failed, setting failure flag on returnable variable', $rt_Dir_Result['ky_User_Authenticated'] ) ;
              } // Bind unsuccessful
          }
          
          // Close connection. If failed...
          fn_Debug ( 'Closing LDAP connection', $ar_Dir_Auth['ky_Conn'] ) ;
          if ( ! ldap_close ( $ar_Dir_Auth['ky_Conn'] ) ) {
            // ...then trigger error
            trigger_error ( 'Failed to unbind and close the Directory connection' , E_USER_ERROR ) ;
          } else {
            fn_Debug ( 'Successfully closed LDAP connection' ) ;
          }

        } else {
          // ... else if connection was unsuccessful, then trigger error.
          trigger_error ( 'Unable to connect to Directory server' , E_USER_ERROR ) ;
        }
      }
      
      // Return output
      fn_Debug ( 'Returning output', $rt_Dir_Result ) ;
      fn_Debug ( 'Returning attributes', $rt_UserAttrib ) ;
      return array (
        "ky_User_Authenticated" => $rt_Dir_Result['ky_User_Authenticated'] ,
        "ky_Group_Exists"       => $rt_Dir_Result['ky_Group_Exists'] ,
        "ky_Group_ContainsUser" => $rt_Dir_Result['ky_Group_ContainsUser'] ,
        'ar_UserAttrib'         => $rt_UserAttrib
      ) ;
    }
  }
?>