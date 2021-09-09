<?php
  $GLOBALS['bl_DebugSwitch'] = TRUE ; // for debugging

  // Include the phpDBauth library (also automatically includes the debugging library)
  require_once ( realpath ( __DIR__ . DIRECTORY_SEPARATOR . 'phpdbauth.php' ) ) ;

  // Specify database parameters
  $var__Database = array (
    "key__Database_Host"     => "localhost" ,
    "key__Database_Port"     => 3306 ,
    "key__Database_Name"     => "dbname" ,
    "key__Database_User"     => "dbusername" ,
    "key__Database_Password" => "dbuserpass" ,
  ) ;

  
  // * Specify data table parameters
  $var__Table = array (
    'key__Table_Name'             => 'UsersTable' ,
    'key__Table_ColumnUsername'   => 'UserUsername' ,
    'key__Table_ColumnRole'       => 'UserRole' ,
    'key__Table_DefaultRoleValue' => 'READ ONLY'
) ;

  // Request
  $ar_Request = array (
    "ky_UserKeyword"  => "tonyh" ,
    "ky_UserPassword" => "SuperSecretPassword" ,
    "ky_GroupKeyword" => "actorsgroup" ,
  ) ;

  // Formulate an array with attributes to be requested
  $ar_InsertVal = array (
    'UserName'  => 'Anthony Hopkins',
    'UserEmail' => 'tony.hopper@domain.tld'
  ) ;

  // Call the verification function and store the result
  $ar_Db_VerificationSummary = fn__Database_Verify (
    $ar_Request ,
    $var__Table ,
    'pdo-mysql' ,
    $var__Database ,
    NULL ,
    $ar_InsertVal
  ) ;

  fn_Debug ( 'Returned result' , $ar_Db_VerificationSummary ) ;
  
  // Check if the user is verified
  if ( $ar_Db_VerificationSummary['key__Database_Connection'] === TRUE ) {
  	// Database connected
    echo ( 'Database connection was successful.<br />' ) ;
  } // DB connected
  else {
  	// Database not connected
    echo ( 'Did/could not connect to the database.<br />' ) ;
  } // DB not connected
  if ( $ar_Db_VerificationSummary['key__Database_UserFound'] === TRUE )  {
    // User found
    echo ( 'User was found in the database.<br />' ) ;
  } // User found
  else {
  	// User not found
    echo ( 'User not found in the database.<br />' ) ;
  } // User not found
  if ( $ar_Db_VerificationSummary['key__Database_UserAdded'] === TRUE )  {
    // User added
    echo ( 'User was added to the database.<br />' ) ;
  } // User added
  else {
  	// // User not added
    echo ( 'User not added to the database.<br />' ) ;
  } // // User not added
  if ( $ar_Db_VerificationSummary['key_Database_DetailAdded'] === TRUE )  {
    // User detail added
    echo ( 'User detail was added to the database.<br />' ) ;
  } // User detail added
  else {
  	// User detail not added
    echo ( 'User detail not added to the database.<br />' ) ;
  } // User detail not added
?>