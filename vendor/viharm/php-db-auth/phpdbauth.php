<?php
  /**
  * 
  * @package   phpDBauth
  * @version   Refer to VERSION
  * @author    Viharm
  * @brief     Database user lookup library
  * @detail    Searches for a username in a specified column in a specified
  *            table in a specified database on a specified host (or a 
  *            specified database connection resource).
  *            If a single match found then returns true
  * @copyright Copyright (C) 2015~2020, Viharm
  *            Under Modified BSD (3-Clause) License
  *            (see LICENSE or http://opensource.org/licenses/BSD-3-Clause)
  *
  **/
  
  /**
  * Request
  $ar_Request = array (
    "ky_UserKeyword"  => "username" ,
    "ky_UserPassword" => "password" ,
    "ky_GroupKeyword" => "usersgroup"
  ) ;

  * Specify data table parameters
  $var__Table = array (
    "key__Table_Name"             => "tblUser" ,
    "key__Table_ColumnUsername"   => "UserUsername" ,
    "key__Table_ColumnRole"       => "UserRole" ,
    "key__Table_DefaultRoleValue" => "READ ONLY"
  ) ;

  * Specify database parameters
  $var__Database = array (
    "key__Database_Host"     => "localhost" ,
    "key__Database_Port"     => 3306 ,
    "key__Database_Name"     => "databasename" ,
    "key__Database_User"     => "databaseusername" ,
    "key__Database_Password" => "databasepassword"
  ) ;

  * Specify an existing database connection object
  $var__Database_Connection = mysqli_connect (
    $var__Database [ "key__Database_Host" ] ,
    $var__Database [ "key__Database_User" ] ,
    $var__Database [ "key__Database_Password"] ,
    $var__Database [ "key__Database_Name" ] ,
    $var__Database [ "key__Database_Port" ]
  ) ;

  * User details
  $ag_InsertVal = array (
    "UserFullname"  => "username" ,
    "UserEmail" => "password"
  ) ;

  * Call the function - four alternatives:
  fn__Database_Verify ( $ar_Request , $var__Table , "mysqli" , NULL           , $var__Database_Connection ) ;
  fn__Database_Verify ( $ar_Request , $var__Table , "mysql"  , NULL           , $var__Database_Connection ) ;
  fn__Database_Verify ( $ar_Request , $var__Table , "mysqli" , $var__Database , NULL ) ;
  fn__Database_Verify ( $ar_Request , $var__Table , "mysql"  , $var__Database , NULL ) ;

  If $var__Database and $var__Database_Connection are both given,
  then $var__Database_Connection takes precedence
  because an existing connection is given priority over creating a new connection.

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

  // Function to insert a new record
  function fn_UpdateRecord (
    $ar_DataSet ,
    $ar_Criteria ,
    $sr_TableName ,
    $sr_DBExtn = 'pdo-mysql' ,
    $ar_DBInfo = NULL ,
    $ob_DBConn = NULL
  ) {
    // Review arguments
    fn_Debug ( 'Function arguments' , func_get_args() ) ;

    // Preset default return values
    $rt_Result = FALSE ;

    // Escapt input data for security
    fn_Debug ( 'Escaping data depending on selected extensions' , $ar_DataSet ) ;
    $ar_DataSetEscaped = array () ;
    foreach ( $ar_DataSet as $sr_Key=>$sr_Val ) {
      fn_Debug ( 'Selecting appropriate library extensions' , $sr_DBExtn ) ;
      switch ( ( substr ( $sr_DBExtn , 0 , 3 ) === "pdo" ? "pdo" : $sr_DBExtn ) ) { // If string starts with 'pdo', then switch for 'pdo', else the original string
        case "mysqli":
          // mysqli requested, proceed to escape string
          fn_Debug ( 'mysqli requested, escaping string' ) ;
          $sr_Val = mysqli_real_escape_string ( $ob_DBConn , $sr_Val ) ;
          break;
        case "mysql":
          // mysqli requested, proceed to escape string
          fn_Debug ( 'mysql requested, escaping string' ) ;
          $sr_Val = mysql_real_escape_string ( $sr_Val ) ;
          break;
        case "pdo":
          // pdo requested, proceed to escape string
          fn_Debug ( 'pdo requested, escaping skipped' ) ;
          break;
      }
      $ar_DataSetEscaped += array ( $sr_Key => $sr_Val ) ;
    }
    fn_Debug ( 'Escaped data set; Proceeding to formulate search query' , $ar_DataSetEscaped ) ;

    // Formulate a query string to insert new record
    fn_Debug ( 'Formulating SQL query to insert new record' ) ;
    $ar_InsertTask['ky_QueryText'] = "UPDATE `" . $sr_TableName . "` SET " ;
    $nm_ArrayCounter = 0 ;
    $nm_ArraySize = count($ar_DataSet);
    foreach ( $ar_DataSet as $sr_Column=>$vr_Val ) {
      fn_Debug ( 'Iteration for column' , $nm_ArrayCounter ) ;
      $ar_InsertTask['ky_QueryText'] .= "`" . $sr_Column . "`=";
      $ar_InsertTask['ky_QueryText'] .= "'" . $vr_Val . "'";
      if ( $nm_ArrayCounter < $nm_ArraySize - 1 ) {
        $ar_InsertTask['ky_QueryText'] .= ", " ;
      }
      // else {
      //   $ar_InsertTask['ky_QueryText'] .= "" ;
      // }
      $nm_ArrayCounter++ ;
    }
    $ar_InsertTask['ky_QueryText'] .= " WHERE " ;
    $nm_ArrayCounter = 0 ;
    $nm_ArraySize = count($ar_Criteria);
    foreach ( $ar_Criteria as $sr_Column=>$vr_Val ) {
      fn_Debug ( 'Iteration for criterion' , $nm_ArrayCounter ) ;
      $ar_InsertTask['ky_QueryText'] .= "`" . $sr_Column . "`=";
      $ar_InsertTask['ky_QueryText'] .= "'" . $vr_Val . "'";
      if ( $nm_ArrayCounter < $nm_ArraySize - 1 ) {
        $ar_InsertTask['ky_QueryText'] .= " AND " ;
      }
      // else {
      //   $ar_InsertTask['ky_QueryText'] .= "" ;
      // }
      $nm_ArrayCounter++ ;
    }

    fn_Debug ( 'Query formulated' , $ar_InsertTask['ky_QueryText'] ) ;
    fn_Debug ( 'Selecting appropriate library extensions' , $sr_DBExtn ) ;
    switch ( ( substr ( $sr_DBExtn , 0 , 3 ) === "pdo" ? "pdo" : $sr_DBExtn ) ) { // If string starts with 'pdo', then switch for 'pdo', else the original string
      case "mysqli":
        // mysqli requested, Run query
        fn_Debug ( 'mysqli requested, running query' ) ;
        $ar_InsertTask['ky_QueryResult'] = mysqli_query ( $ob_DBConn , $ar_InsertTask['ky_QueryText'] ) ;
        break;
      case "mysql":
        // mysql requested, Run query
        fn_Debug ( 'mysql requested, running query' ) ;
        $ar_InsertTask['ky_QueryResult'] = mysql_query ( $ar_InsertTask['ky_QueryText'] ) ;
        break;
      case "pdo":
        // pdo requested, proceed to run query
        fn_Debug ( 'pdo requested, running query' ) ;
        $ar_InsertTask['ky_QueryResult'] = $ob_DBConn->prepare($ar_InsertTask['ky_QueryText']) ;
        $ar_InsertTask['ky_QueryResult']->execute();
        fn_Debug ( 'Driver output of add operation' , $ar_InsertTask['ky_QueryResult']->errorInfo() ) ;
        break;
    }

    // Check query success
    fn_Debug ( 'Query submitted, checking results' , $ar_InsertTask['ky_QueryResult'] ) ;
    if (!$ar_InsertTask['ky_QueryResult']) {
      fn_Debug ( 'Query failed, could not add record' ) ;
    }
    else {
      fn_Debug ( 'Query successful, record added' ) ;
      $rt_Result = TRUE ;
      fn_Debug ( 'Return response set' , $rt_Result ) ;
    }

    // Check
    fn_Debug ( 'Overall status of query' , $ar_InsertTask ) ;

    // Free the memory associated with the adding the user
    fn_Debug ( 'Freeing memory for the result of the add query' , $ar_InsertTask['ky_QueryResult'] ) ;
    fn_Debug ( 'Selecting appropriate library extensions' , $sr_DBExtn ) ;
    switch ( ( substr ( $sr_DBExtn , 0 , 3 ) === "pdo" ? "pdo" : $sr_DBExtn ) ) { // If string starts with 'pdo', then switch for 'pdo', else the original string
      case "mysqli":
        // mysqli requested, proceed to free memory
        fn_Debug ( 'mysqli requested, not freeing memory, as an add query does not return a result identifier' ) ;
        break;
      case "mysql":
        // mysql requested, proceed to free memory
        fn_Debug ( 'mysql requested, not freeing memory, as an add query does not return a result identifier' ) ;
        break;
      case "pdo":
        $ar_InsertTask['ky_QueryResult']->closeCursor() ;
        $ar_InsertTask['ky_QueryResult'] = NULL ;
        fn_Debug ( 'Memory for the result of the search query freed' , $ar_InsertTask['ky_QueryResult'] ) ;
        break;
    }
    return($rt_Result) ;
  }

  function fn__Database_Verify (
    $ag_Request ,
    $arg__Table ,
    $arg__Database_Extension = "mysqli" ,
    $arg__Database = NULL ,
    $arg__Database_ConnectionObject = NULL ,
    $ag_InsertVal = NULL
  ) {
    
    fn_Debug ( 'Request' , $ag_Request , 'ky_UserPassword' ) ;
    fn_Debug ( 'Supplied table parameters' , $arg__Table ) ;
    fn_Debug ( 'DB extension library requested' , $arg__Database_Extension ) ;
    fn_Debug ( 'Supplied database connection configuration' , $arg__Database , 'key__Database_Password' ) ;
    fn_Debug ( 'Supplied database connection resource' , $arg__Database_ConnectionObject ) ;
    fn_Debug ( 'Supplied details to be inserted' , $ag_InsertVal ) ;
    
    // Temporary variable for compatibility with XatafaceLDAPauth v02.00.00
    $arg__Common['key__Common_SearchUserKeyword'] = $ag_Request['ky_UserKeyword'] ; 
    $arg__Common['key__Common_SearchUserPassword'] = $ag_Request['ky_UserPassword'] ;
    $arg__Common['key__Common_SearchGroupKeyword'] = $ag_Request['ky_GroupKeyword'] ;
    fn_Debug ( 'Transferred to holding variable for compatibility' , $arg__Common , 'key__Common_SearchUserPassword' ) ;
    
    // Transfer supplied connection object
    $arg__Database_Connection=array();
    fn_Debug ( 'Holding array for existing DB connection object created' , $arg__Common , 'key__Common_SearchUserPassword' ) ;
    fn_Debug ( 'Checking for supplied DB connection object/resource' , @$arg__Database_ConnectionObject ) ;
    if ( ! is_null($arg__Database_ConnectionObject) ) {
      $arg__Database_Connection["key__DatabaseConnection_Object"] = $arg__Database_ConnectionObject ;
      fn_Debug ( 'Non-null database connection found, mapped to internal variable' , $arg__Database_Connection["key__DatabaseConnection_Object"] ) ;
    } else {
      fn_Debug ( 'Null database connection. Will need to connect to the database later.' ) ;
    }
    
    // Preset worst case for return result;
    $ret__Database = array (
      "key__Database_Connection" => FALSE ,
      "key__Database_UserFound"  => FALSE ,
      "key__Database_UserAdded"  => FALSE ,
      'key_Database_DetailAdded'  => FALSE
    ) ;
    fn_Debug ( 'Preset default responses' , $ret__Database ) ;
    
    
    fn_Debug ( 'Checking for supplied database configuration' , $arg__Database , 'key__Database_Password' ) ;
    if ( $arg__Database != NULL ) {
      fn_Debug ( 'Checking for supplied database host' , @$arg__Database['key__Database_Host'] ) ;
      if ( ! array_key_exists ( 'key__Database_Host' , $arg__Database ) ) {
        $arg__Database [ "key__Database_Host" ] = "localhost" ;
        fn_Debug ( 'Database host not specified. Set "localhost" as default.' , $arg__Database["key__Database_Host"] ) ;
      }
      
      fn_Debug ( 'Checking for supplied database port' , @$arg__Database['key__Database_Port'] ) ;
      if ( ! array_key_exists ( 'key__Database_Port' , $arg__Database ) ) {
      $arg__Database["key__Database_Port"] = 3306 ;
      fn_Debug ( 'Database port not specified. Set "3306" as default.' , $arg__Database["key__Database_Port"] ) ;
      }
    }
    
    // Check if MySQL functionality exists
    fn_Debug ( 'Checking if MySQL functionality exists' ) ;
    if ( $arg__Database_Extension == "mysqli" && ! function_exists ( "mysqli_connect" ) ) {
      fn_Debug ( 'mysqli library requested, but not found' ) ;
      // trigger_error ( "Please install the PHP MySQLi module in order to use MySQL database verification." , E_USER_ERROR ) ;
    } elseif ( $arg__Database_Extension == "mysql" && ! function_exists ( "mysql_connect" ) ) {
      fn_Debug ( 'mysql library requested, but not found' ) ;
      // trigger_error ( "Please install the PHP MySQL module in order to use MySQL database verification." , E_USER_ERROR ) ;
    } elseif ( substr_compare ( $arg__Database_Extension , 'pdo' , 0 , 3 ) === 0 && !extension_loaded('pdo') ) {
      fn_Debug ( 'pdo library requested, but not found' ) ;
      // trigger_error ( "Please install the PHP PDO module in order to use MySQL database verification." , E_USER_ERROR ) ;
    } else {
      
      // Proceed to database
      fn_Debug ( 'MySQL functionality checked, proceeding to connect' ) ;
      
      // Check if existing connection object is supplied
      fn_Debug ( 'Checking if existing connection object is supplied' , @$arg__Database_Connection["key__DatabaseConnection_Object"] ) ;
      if ( @$arg__Database_Connection["key__DatabaseConnection_Object"] === NULL ) {
        // Existing connection object not found in supplied parameters; check supplied database parameters for new connection.
        fn_Debug ( 'Existing connection object not found in supplied parameters; checking supplied database parameters for new connection' , @$arg__Database , 'key__Database_Password' ) ;
        if ( $arg__Database == NULL ) {
          // Database configuration not found in supplied parameters
        } else {
          // Database connection configuration found, presetting variable
          $arg__Database_Connection["key__DatabaseConnection_Object"] = NULL ;
          // checking extension
          fn_Debug ( 'Database connection configuration found, checking extension' , $arg__Database_Extension ) ;
          switch ( ( substr ( $arg__Database_Extension , 0 , 3 ) === "pdo" ? "pdo" : $arg__Database_Extension ) ) { // If string starts with 'pdo', then switch for 'pdo', else the original string
            case "mysqli":
              // mysqli requested, proceeding to connect
              fn_Debug ( 'mysqli requested, proceeding to connect' , $arg__Database , 'key__Database_Password' ) ;
              $arg__Database_Connection["key__DatabaseConnection_Object"] = mysqli_connect (
                $arg__Database [ "key__Database_Host" ] ,
                $arg__Database [ "key__Database_User" ] ,
                $arg__Database [ "key__Database_Password"] ,
                $arg__Database [ "key__Database_Name" ] ,
                $arg__Database [ "key__Database_Port" ]
              ) ;
              // Check for connection errors
              fn_Debug ( 'Checking for connection errors' , mysqli_connect_errno() ) ;
              if ( mysqli_connect_errno() ) {
                // Failed to connect to MySQL
                fn_Debug ( 'Failed to connect to MySQL; Retaining default return value' , mysqli_connect_error ( ) ) ;
                
              } //  mysqli failed
              else {
                // Successfully connected to MySQL
                $ret__Database ["key__Database_Connection" ] = TRUE ;
                fn_Debug ( 'Successfully connected to MySQL; set response' , $ret__Database ["key__Database_Connection" ] ) ;
              }
              break;
            case "mysql":
              // mysql requested, proceeding to connect
              fn_Debug ( 'mysql requested, proceeding to connect' , $arg__Database , 'key__Database_Password' ) ;
              $arg__Database_Connection["key__DatabaseConnection_Object"] = mysql_connect (
                $arg__Database["key__Database_Host"] . ":" . $arg__Database [ "key__Database_Port" ] ,
                $arg__Database["key__Database_User"] ,
                $arg__Database [ "key__Database_Password"]
              ) ;
              
              // Check for connection errors
              fn_Debug ( 'Checking for connection errors' , $arg__Database_Connection["key__DatabaseConnection_Object"] ) ;
              if ( ! $arg__Database_Connection["key__DatabaseConnection_Object"] ) {
                // Failed to connect to MySQL
                fn_Debug ( 'Failed to connect to MySQL' ) ;
                //trigger_error ( "Failed to connect to MySQL host." , E_USER_ERROR ) ;
              } else {
                // Successfully connected to MySQL
                fn_Debug ( 'Successfully connected to database server; proceeding to select database' , $ret__Database ["key__Database_Connection" ] ) ;
                
                // Select database
                if ( ! mysql_select_db ( $arg__Database['key__Database_Name'] , $arg__Database_Connection["key__DatabaseConnection_Object"] ) ) {
                  fn_Debug ( 'Could not select database; Proceeding to close connection' , mysql_error($arg__Database_Connection["key__DatabaseConnection_Object"]) ) ;
                  if ( mysql_close ( $arg__Database_Connection["key__DatabaseConnection_Object"] ) ) {
                    fn_Debug ( 'Connection to database closed' , mysql_error($arg__Database_Connection["key__DatabaseConnection_Object"]) ) ;
                    $arg__Database_Connection["key__DatabaseConnection_Object"] = NULL ;
                  } else {
                    fn_Debug ( 'Could not close connection to database' , mysql_error($arg__Database_Connection["key__DatabaseConnection_Object"]) ) ;
                    $arg__Database_Connection["key__DatabaseConnection_Object"] = NULL ;
                  }
                } else {
                  fn_Debug ( 'Selected database' , $arg__Database['key__Database_Name'] ) ;
                  $ret__Database ["key__Database_Connection" ] = TRUE ;
                  fn_Debug ( 'Response set' , $ret__Database ["key__Database_Connection" ] ) ;
                }
                
              }
              break;
            case "pdo":
              fn_Debug ( 'pdo requested, proceeding to connect' , $arg__Database , 'key__Database_Password' ) ;
              try {
                $arg__Database_Connection["key__DatabaseConnection_Object"] = new PDO (
                  explode ( '-' , $arg__Database_Extension )[1] . ':' .
                  'host=' . $arg__Database["key__Database_Host"] .
                  ';port=' . $arg__Database [ "key__Database_Port" ] .
                  ';dbname=' . $arg__Database [ "key__Database_Name" ] .
                  ';charset=utf8' ,
                  $arg__Database["key__Database_User"] ,
                  $arg__Database [ "key__Database_Password"]
                ) ;
              } catch (PDOException $arg__Database_ConnectionErrorObject ) {
                fn_Debug ( 'Failed to connect to database using PDO' , $arg__Database_ConnectionErrorObject->getMessage() ) ;
                //trigger_error ( "Failed to connect to database with PDO" , E_USER_ERROR ) ;
              }
              fn_Debug ( 'pdo connection attempted, setting return key' , $arg__Database_Connection["key__DatabaseConnection_Object"] ) ;
              if ( !is_null($arg__Database_Connection [ "key__DatabaseConnection_Object"]) ) {
                $ret__Database ["key__Database_Connection" ] = TRUE ;
              }
              break;
          } // switch
        } // else for ($arg__Database == NULL)
      } else {
      fn_Debug ( 'Connection object is supplied' , @$arg__Database_Connection["key__DatabaseConnection_Object"] ) ;
      }

      // Proceed with database operation only if there is a connection object to work with (either supplied or prepared)
      fn_Debug ( 'Checking if a workable database connection exists' , @$arg__Database_Connection["key__DatabaseConnection_Object"] ) ;
      if ( @$arg__Database_Connection["key__DatabaseConnection_Object"] === NULL || !@$arg__Database_Connection["key__DatabaseConnection_Object"] ) {
        fn_Debug ( 'No database connection to work with' ) ;
      } else {

        // Formulate SQL query
        fn_Debug ( 'Formulating SQL query to search for user' ) ;
        
        // Escape supplied username keyword
        fn_Debug ( 'Escaping username depending on selected extensions' , $arg__Common["key__Common_SearchUserKeyword"] ) ;
        
        fn_Debug ( 'Selecting appropriate library extensions' , $arg__Database_Extension ) ;
        switch ( ( substr ( $arg__Database_Extension , 0 , 3 ) === "pdo" ? "pdo" : $arg__Database_Extension ) ) { // If string starts with 'pdo', then switch for 'pdo', else the original string
          case "mysqli":
            // mysqli requested, proceed to escape string
            fn_Debug ( 'mysqli requested, escaping string' ) ;
            $arg__Common["key__Common_SearchUserKeyword"] = mysqli_real_escape_string ( $arg__Database_Connection["key__DatabaseConnection_Object"] , $arg__Common["key__Common_SearchUserKeyword"] ) ;
            break;
          case "mysql":
            // mysqli requested, proceed to escape string
            fn_Debug ( 'mysql requested, escaping string' ) ;
            // $arg__Common["key__Common_SearchUserKeyword"] = mysql_real_escape_string ( $arg__Common["key__Common_SearchUserKeyword"] , $arg__Database_Connection["key__DatabaseConnection_Object"] ) ;
            $arg__Common["key__Common_SearchUserKeyword"] = mysql_real_escape_string ( $arg__Common["key__Common_SearchUserKeyword"] ) ;
            break;
          case "pdo":
            // pdo requested, proceed to escape string
            fn_Debug ( 'pdo requested, escaping skipped' ) ;
            break;
        }
        fn_Debug ( 'Escaped username; Proceeding to formulate search query' , $arg__Common["key__Common_SearchUserKeyword"] ) ;
  
        $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Text" ] =
          "SELECT `" . $arg__Table [ "key__Table_ColumnUsername" ] .
          "` FROM `" . $arg__Table [ "key__Table_Name" ] .
          "` WHERE `" . $arg__Table [ "key__Table_ColumnUsername" ] . "`='" . $arg__Common [ "key__Common_SearchUserKeyword" ] . "'" ;
  
        // Run the query
        fn_Debug ( 'Running the search query; Selecting appropriate library extensions' , $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Text" ] ) ;
        switch ( ( substr ( $arg__Database_Extension , 0 , 3 ) === "pdo" ? "pdo" : $arg__Database_Extension ) ) { // If string starts with 'pdo', then switch for 'pdo', else the original string
          case "mysqli":
            // mysqli requested, proceed to run query
            fn_Debug ( 'mysqli requested, running query' , $arg__Database_Extension ) ;
            $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] = mysqli_query (
              $arg__Database_Connection["key__DatabaseConnection_Object"] ,
              $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Text" ]
            ) ;
            break;
          case "mysql":
            // mysql requested, proceed to run query
            fn_Debug ( 'mysql requested, running query' , $arg__Database_Extension ) ;
            $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] = mysql_query (
              $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Text" ] // ,
              // $arg__Database_Connection["key__DatabaseConnection_Object"]
            ) ;
            break;
          case "pdo":
            // pdo requested, proceed to run query
            fn_Debug ( 'pdo requested, running query' , $arg__Database_Extension ) ;
            $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] = $arg__Database_Connection["key__DatabaseConnection_Object"]->prepare (
              $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Text" ]
            ) ;
            $arg__Database_Connection["key__DatabaseConnection_QuerySearchUser"]["key__QuerySearch_Result"]->execute();
            break;
        }
  
        // Check query success
        fn_Debug ( 'Query submitted, checking results' , $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] ) ;
        if ( ! $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] ) {
          fn_Debug ( 'Query failed' ) ;
        } else {
  
          // Query successful. Check number of rows returned, but first select appropriate extension
          fn_Debug ( 'Query successful; Proceeding to count results; Selecting extension library' , $arg__Database_Extension ) ;
          switch ( ( substr ( $arg__Database_Extension , 0 , 3 ) === "pdo" ? "pdo" : $arg__Database_Extension ) ) { // If string starts with 'pdo', then switch for 'pdo', else the original string
            case "mysqli":
              // mysqli requested, Check number of rows returned
              fn_Debug ( 'mysqli requested, checking number of rows returned' ) ;
              $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_ReturnedRowCount" ] =  mysqli_num_rows ( $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] ) ;
              break;
            case "mysql":
              // mysql requested, Check number of rows returned
              fn_Debug ( 'mysql requested, checking number of rows returned' ) ;
              $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_ReturnedRowCount" ] =  mysql_num_rows ( $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] ) ;
              break;
            case "pdo":
              // pdo requested, Check number of rows returned
              fn_Debug ( 'pdo requested, checking number of rows returned' ) ;
              $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_ReturnedRowCount" ] =  $arg__Database_Connection["key__DatabaseConnection_QuerySearchUser"]["key__QuerySearch_Result"]->rowCount(); ;
              break;
          }
          
          // Check
          fn_Debug ( 'Overall status of query' , $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] ) ;
          
          fn_Debug ( 'Results counted' , $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_ReturnedRowCount" ] ) ;
          
          // Check for size of the result
          fn_Debug ( 'Organise results; evaluate results count' ,$arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_ReturnedRowCount" ] ) ;
          switch ( $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_ReturnedRowCount" ]  ) {
            case 0:
              // No results found
              fn_Debug ( 'No results found, inserting new record' , $arg__Common [ "key__Common_SearchUserKeyword" ] ) ;
  
              // Formulate a query string to insert new record
              fn_Debug ( 'Formulating SQL query to insert new record' ) ;
              $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Text" ] = "INSERT INTO `" . $arg__Table [ "key__Table_Name" ] . "`
                ( `" . $arg__Table [ "key__Table_ColumnUsername" ] . "`, `" . $arg__Table [ "key__Table_ColumnRole" ] . "`" ;

              // Columns
              // Check if details insertion is requested
              if ( ! is_null($ag_InsertVal) || $ag_InsertVal !='' ) {
                // Data set is not null, now check if it is an array
                fn_Debug ( 'Data set is not null, now check if it is an array' ) ;
                if (is_array($ag_InsertVal)) {
                  // Data set is an array, now check if it has one or more elements
                  fn_Debug ( 'Data set is an array, now check if it has one or more elements' ) ;
                  if (count($ag_InsertVal)>0) {
                    // Data set is a non-zero array, now insert record
                    fn_Debug ( 'Data set is a non-zero array, now insert record' ) ;
                    $nm_ArrayCounter = 0 ;
                    $nm_ArraySize = count($ag_InsertVal) ;
                    $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Text" ] .= ", " ;
                    foreach ( $ag_InsertVal as $sr_Column=>$vr_Val ) {
                      fn_Debug ( 'Iteration for column/field' , $nm_ArrayCounter ) ;
                      $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Text" ] .= "`" . $sr_Column . "`" ;
                      if ( $nm_ArrayCounter < $nm_ArraySize - 1 ) {
                        $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Text" ] .= ", " ;
                      }
                      $nm_ArrayCounter++ ;
                    } // foreach
                  } // Data set is a non-zero array
                  else {
                    // Data set is a zero-element array
                    fn_Debug ( 'Data set is a zero-element array' ) ;
                  } // Data set is a zero-element array
                } // Data set is an array
                else {
                  // Data set is not an array
                  fn_Debug ( 'Data set is not an array' ) ;
                } // Data set is not an array
              } // Data set is not null
              else {
                // Data set is null
                fn_Debug ( 'Data set is null' ) ;
              } // Data set is null
              $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Text" ] .= " ) VALUES ( '" . $arg__Common [ "key__Common_SearchUserKeyword" ] . "', '" . $arg__Table [ "key__Table_DefaultRoleValue" ] . "'" ;
              // Values
              // Check if details insertion is requested
              if ( ! is_null($ag_InsertVal) || $ag_InsertVal !='' ) {
                // Data set is not null, now check if it is an array
                if (is_array($ag_InsertVal)) {
                  // Data set is an array, now check if it has one or more elements
                  if (count($ag_InsertVal)>0) {
                    // Data set is a non-zero array, now insert record
                    $nm_ArrayCounter = 0 ;
                    $nm_ArraySize = count($ag_InsertVal) ;
                    $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Text" ] .= ", " ;
                    foreach ( $ag_InsertVal as $sr_Column=>$vr_Val ) {
                      fn_Debug ( 'Iteration for value' , $nm_ArrayCounter ) ;
                      $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Text" ] .= "'" . $vr_Val . "'" ;
                      if ( $nm_ArrayCounter < $nm_ArraySize - 1 ) {
                        $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Text" ] .= ", " ;
                      }
                      $nm_ArrayCounter++ ;
                    }
                    $ret__Database['key_Database_DetailAdded'] = TRUE ;
                  } // Data set is a non-zero array
                } // Data set is an array
              } // Data set is not null
              $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Text" ] .= " )" ;
              
              fn_Debug ( 'Query formulated' , $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Text" ] ) ;
              fn_Debug ( 'Selecting appropriate library extensions' , $arg__Database_Extension ) ;
              switch ( ( substr ( $arg__Database_Extension , 0 , 3 ) === "pdo" ? "pdo" : $arg__Database_Extension ) ) { // If string starts with 'pdo', then switch for 'pdo', else the original string
                case "mysqli":
                  // mysqli requested, Run query
                  fn_Debug ( 'mysqli requested, running query' ) ;
                  $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Result" ] = mysqli_query (
                      $arg__Database_Connection["key__DatabaseConnection_Object"] ,
                      $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Text" ]
                  ) ;
                  break;
                case "mysql":
                  // mysql requested, Run query
                  fn_Debug ( 'mysql requested, running query' ) ;
                  $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Result" ] = mysql_query (
                      $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Text" ] //,
                      // $arg__Database_Connection["key__DatabaseConnection_Object"]
                  ) ;
                  break;
                case "pdo":
                  // pdo requested, proceed to run query
                  fn_Debug ( 'pdo requested, running query' ) ;
                  $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Result" ] = $arg__Database_Connection["key__DatabaseConnection_Object"]->prepare (
                    $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Text" ]
                  ) ;
                  $arg__Database_Connection["key__DatabaseConnection_QueryAddUser"]["key__QueryAdd_Result"]->execute();
                  fn_Debug ( 'Driver output of add operation' , $arg__Database_Connection["key__DatabaseConnection_QueryAddUser"]["key__QueryAdd_Result"]->errorInfo() ) ;
                  break;
              }
  
              // Check query success
              fn_Debug ( 'Query submitted, checking results' , $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Result" ] ) ;
              if ( ! $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Result" ] ) {
                fn_Debug ( 'Query failed, could not add record' , $arg__Common [ "key__Common_SearchUserKeyword" ] ) ;
              } else {
                fn_Debug ( 'Query successful, record added' , $arg__Common [ "key__Common_SearchUserKeyword" ] ) ;
                $ret__Database [ "key__Database_UserAdded" ] = TRUE ;
                fn_Debug ( 'Return response set' , $ret__Database [ "key__Database_UserAdded" ] ) ;
              }
  
              // Check
              fn_Debug ( 'Overall status of query' , $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] ) ;
  
              // Free the memory associated with the adding the user
              fn_Debug ( 'Freeing memory for the result of the add query' , $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Result" ] ) ;
              fn_Debug ( 'Selecting appropriate library extensions' , $arg__Database_Extension ) ;
              switch ( ( substr ( $arg__Database_Extension , 0 , 3 ) === "pdo" ? "pdo" : $arg__Database_Extension ) ) { // If string starts with 'pdo', then switch for 'pdo', else the original string
                case "mysqli":
                  // mysqli requested, proceed to free memory
                  fn_Debug ( 'mysqli requested, not freeing memory, as an add query does not return a result identifier' ) ;
                  break;
                case "mysql":
                  // mysql requested, proceed to free memory
                  fn_Debug ( 'mysql requested, not freeing memory, as an add query does not return a result identifier' ) ;
                  break;
                case "pdo":
                  $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Result" ] -> closeCursor() ;
                  $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Result" ] = NULL ;
                  fn_Debug ( 'Memory for the result of the search query freed' , $arg__Database_Connection [ "key__DatabaseConnection_QueryAddUser" ] [ "key__QueryAdd_Result" ] ) ;
                  break;
              }
  
              break ;
  
            case 1:
              fn_Debug ( 'One result found; checking further' , $arg__Common [ "key__Common_SearchUserKeyword" ] ) ;
  
              // Go to the start of the result
              fn_Debug ( 'Selecting appropriate library extensions' , $arg__Database_Extension ) ;
              switch ( ( substr ( $arg__Database_Extension , 0 , 3 ) === "pdo" ? "pdo" : $arg__Database_Extension ) ) { // If string starts with 'pdo', then switch for 'pdo', else the original string
                case "mysqli":
                  // mysqli requested, Seek to beginning of the result
                  fn_Debug ( 'mysqli requested, seeking to the beginning of the result' ) ;
                  if ( mysqli_data_seek ( $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] , 0 ) ) {
                    fn_Debug ( 'Moved result pointer to the first row returned' ) ;
                  } else {
                    fn_Debug ( 'Failed to move result pointer to the first row returned' ) ;
                  }
                  break;
                case "mysql":
                  // mysql requested, Seek to beginning of the result
                  fn_Debug ( 'mysql requested, seeking to the beginning of the result' ) ;
                  if ( mysql_data_seek ( $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] , 0 ) ) {
                    fn_Debug ( 'Moved result pointer to the first row returned' ) ;
                  } else {
                    fn_Debug ( 'Failed to move result pointer to the first row returned' ) ;
                  }
                  break;
                case "pdo":
                  // pdo requested, Skipping seek operation
                  fn_Debug ( 'pdo requested, Skipping seek operation' ) ;
                  break;
              }
  
              // Fetch the data (since only one result is returned, a while loop to go through the rows returned is not required)
              fn_Debug ( 'Fetching data; but selecting appropriate library extensions' , $arg__Database_Extension ) ;
              switch ( ( substr ( $arg__Database_Extension , 0 , 3 ) === "pdo" ? "pdo" : $arg__Database_Extension ) ) { // If string starts with 'pdo', then switch for 'pdo', else the original string
                case "mysqli":
                  // mysqli requested, proceed to fetch data
                  fn_Debug ( 'mysqli requested, proceeding to fetch data' , $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ]) ;
                  $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Output" ] = mysqli_fetch_assoc ( $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] ) ;
                  break;
                case "mysql":
                  // mysql requested, proceed to fetch data
                  fn_Debug ( 'mysql requested, proceeding to fetch data' , $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] ) ;
                  $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Output" ] = mysql_fetch_assoc ( $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] ) ;
                  break;
                case "pdo":
                  // pdo requested, proceed to fetch data
                  fn_Debug ( 'pdo requested, proceeding to fetch data' , $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ]) ;
                  $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Output" ] = $arg__Database_Connection["key__DatabaseConnection_QuerySearchUser"]["key__QuerySearch_Result"]->fetch(PDO::FETCH_ASSOC) ;
                  break;
              }
  
              // Show the output
              fn_Debug ( 'Search data fetched' , $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ]["key__QuerySearch_Output"] ) ;
  
              // Compare the result
              fn_Debug (
                'Comparing result' ,
                array (
                  $arg__Database_Connection
                    ["key__DatabaseConnection_QuerySearchUser"]
                    ["key__QuerySearch_Output"]
                    [$arg__Table["key__Table_ColumnUsername"]] ,
                  $arg__Common["key__Common_SearchUserKeyword"]
                )
              ) ;
              if ( $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Output" ] [ $arg__Table [ "key__Table_ColumnUsername" ] ] == $arg__Common [ "key__Common_SearchUserKeyword" ] ) {
                // Match found, now adding user details
                $ret__Database [ "key__Database_UserFound" ] = TRUE ;
                fn_Debug ( 'Match found, updated response, now adding user details' ) ;
                // Check if details insertion is requested
                if ( ! is_null($ag_InsertVal) || $ag_InsertVal !='' ) {
                  // Data set is not null, now check if it is an array
                  fn_Debug ( 'Data set is not null, now check if it is an array' ) ;
                  if (is_array($ag_InsertVal)) {
                    // Data set is an array, now check if it has one or more elements
                    fn_Debug ( 'Data set is an array, now check if it has one or more elements' ) ;
                    if (count($ag_InsertVal)>0) {
                      // Data set is a non-zero array, now insert record
                      fn_Debug ( 'Data set is a non-zero array, now insert record' ) ;
                      $ret__Database['key_Database_DetailAdded'] = fn_UpdateRecord ( 
                        $ag_InsertVal ,
                        array ( $arg__Table['key__Table_ColumnUsername'] => $arg__Common [ "key__Common_SearchUserKeyword" ] ) ,
                        $arg__Table [ "key__Table_Name" ] ,
                        $arg__Database_Extension ,
                        NULL ,
                        $arg__Database_Connection["key__DatabaseConnection_Object"]
                      ) ;
                      if (!$ret__Database['key_Database_DetailAdded']) {
                        fn_Debug ( 'Details not added, response set' , $ret__Database["key_Database_DetailAdded"] ) ;
                      } // Details not added
                      else {
                        fn_Debug ( 'Details added, response set' , $ret__Database["key_Database_DetailAdded"] ) ;
                      } // Details added
                    } // Data set is a non-zero array
                    else {
                      // Data set is a zero-element array
                      fn_Debug ( 'Data set is a zero-element array' ) ;
                    } // Data set is a zero-element array
                  } // Data set is an array
                  else {
                    // Data set is not an array
                    fn_Debug ( 'Data set is not an array' ) ;
                  } // Data set is not an array
                } // Data set is not null
                else {
                  // Data set is null
                  fn_Debug ( 'Data set is null' ) ;
                } // Data set is null
              }
              else {
                fn_Debug ( 'Match not found, response unchanged' ) ;
              } // Match not found
              
              break ;
            default:
              fn_Debug ( 'More than one result found; Exiting loop' ) ;
              break;
          } // switch for number of results returned
          
          // Check
          fn_Debug ( 'Overall status of connection' , $arg__Database_Connection ) ;
  
          // Free the memory associated with the query result
          fn_Debug ( 'Freeing memory for the result of the search query' , $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] ) ;
          fn_Debug ( 'Selecting appropriate library extensions' , $arg__Database_Extension ) ;
          switch ( ( substr ( $arg__Database_Extension , 0 , 3 ) === "pdo" ? "pdo" : $arg__Database_Extension ) ) { // If string starts with 'pdo', then switch for 'pdo', else the original string
            case "mysqli":
              // mysqli requested, proceed to free memory
              fn_Debug ( 'mysqli requested, proceeding to free memory' ) ;
              mysqli_free_result (  $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] ) ;
              break;
            case "mysql":
              // mysql requested, proceed to free memory
              fn_Debug ( 'mysql requested, proceeding to free memory' ) ;
              if ( mysql_free_result (  $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] ) ) {
                fn_Debug ( 'Memory for the result of the search query freed' ) ;
              } else {
                fn_Debug ( 'Failed to free memory for the result of the search query' ) ;
              }
              break;
            case "pdo":
              $arg__Database_Connection [ "key__DatabaseConnection_QuerySearchUser" ] [ "key__QuerySearch_Result" ] -> closeCursor() ;
              $arg__Database_Connection["key__DatabaseConnection_QuerySearchUser"]["key__QuerySearch_Result"] = NULL ;
              fn_Debug ( 'Memory for the result of the search query freed' , $arg__Database_Connection["key__DatabaseConnection_QuerySearchUser"]["key__QuerySearch_Result"] ) ;
              break;
          } // switch
  
        } // Search query successful
  
        // Close the database connection
        fn_Debug ( 'Finished; Checking if database connection was initially supplied' , @$arg__Database_ConnectionObject ) ;
        if ( $arg__Database_ConnectionObject == NULL ) {
          fn_Debug ( 'Database connection was not supplied, checking if database parameters were supplied' , @$arg__Database , 'key__Database_Password' ) ;
          if ( $arg__Database == NULL ) {
            // No need to close the connection as nothing could have been done if both database arguments were NULL.
            fn_Debug ( 'Database parameters were not supplied, assuming no connections were open, so none to close' ) ;
          } else {
            fn_Debug ( 'Selecting appropriate library extensions' , $arg__Database_Extension ) ;
            switch ( ( substr ( $arg__Database_Extension , 0 , 3 ) === "pdo" ? "pdo" : $arg__Database_Extension ) ) { // If string starts with 'pdo', then switch for 'pdo', else the original string
              case "mysqli":
                // mysqli requested, proceeding to close connection
                fn_Debug ( 'mysqli requested, proceeding to close connection' ) ;
                if ( mysqli_close ( $arg__Database_Connection["key__DatabaseConnection_Object"] ) ) {
                  fn_Debug ( 'Connection to database closed' ) ;
                } else {
                  fn_Debug ( 'Could not close connection to database' ) ;
                }
                break;
              case "mysql":
                // mysql requested, proceeding to close connection
                fn_Debug ( 'mysql requested, proceeding to close connection' ) ;
                if ( mysql_close ( $arg__Database_Connection["key__DatabaseConnection_Object"] ) ) {
                  fn_Debug ( 'Connection to database closed' ) ;
                } else {
                  fn_Debug ( 'Could not close connection to database' ) ;
                }
                break;
              case "pdo":
                // pdo requested, proceeding to close connection
                fn_Debug ( 'pdo requested, proceeding to close connection' ) ;
                $arg__Database_Connection["key__DatabaseConnection_Object"] = NULL ;
                fn_Debug ( 'Connection to database closed' , $arg__Database_Connection["key__DatabaseConnection_Object"] ) ;
                break;
            }
          }
        } else {
          fn_Debug ( 'Database connection inherited, no closing required' , $arg__Database_Connection ) ;
        }
      } // Database connection available to work with.

    } // MySQL functionality exists
    
    fn_Debug ( 'Returning response' , $ret__Database ) ;
    return array (
      "key__Database_Connection" => $ret__Database [ "key__Database_Connection" ] ,
      "key__Database_UserFound"  => $ret__Database [ "key__Database_UserFound" ] ,
      "key__Database_UserAdded"  => $ret__Database [ "key__Database_UserAdded" ] ,
      "key_Database_DetailAdded" => $ret__Database [ "key_Database_DetailAdded" ]
    ) ;

  }
?>