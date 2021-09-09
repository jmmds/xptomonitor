<?php
                                             
  /**
  * @package   phpKhelper
  * @version   See VERSION
  * @author    Viharm
  * @brief     A companion PHP library to accompany Kint (http://kint-php.github.io/kint/)
  * @detail    This script acts as a companion to the excellent Kint PHP debug
  *            tool. It adds some simple features like password obfuscation.
  * @copyright Copyright (C) 2015~18, Viharm
  *            Under modified BSD (3-clause) license
  *            (see LICENSE or http://opensource.org/licenses/BSD-3-Clause)
  **/
  
  /**
  * Copyright (c) 2015~18, Viharm
  * 
  * All rights reserved.
  * 
  * Redistribution and use in source and binary forms, with or without
  * modification, are permitted provided that the following conditions are met:
  * 
  * 1. Redistributions of source code must retain the above copyright notice, this
  *    list of conditions and the following disclaimer.
  * 
  * 2. Redistributions in binary form must reproduce the above copyright notice,
  *    this list of conditions and the following disclaimer in the documentation
  *    and/or other materials provided with the distribution.
  * 
  * 3. Neither the name of the copyright holder nor the names of its
  *    contributors may be used to endorse or promote products derived from
  *    this software without specific prior written permission.
  * 
  * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
  * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
  * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
  * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
  * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
  * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
  * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
  * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
  * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
  * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
  **/
  

  // $GLOBALS['bl_DebugSwitch'] = TRUE ;
  
  if ( ! function_exists ( 'fn_LoadDebugger' ) ) {
    function fn_LoadDebugger () {
      require_once (
        realpath (
          dirname(__FILE__) .
          DIRECTORY_SEPARATOR .
          'kint.php'
        )
      ) ;
      ini_set('log_errors', 'on') ;
      ini_set('display_errors', 'on') ;
      ini_set('html_errors', 'on') ;
      ini_set('display_startup_errors', 'on') ;
      ini_set('error_reporting', '-1') ;
      error_reporting ( E_ALL ) ;
      error_reporting ( -1 ) ;
      error_reporting ( E_ALL | E_STRICT ) ;
    }
  }
  
  if ( ! function_exists ( 'fn_StrObfuscate' ) ) {
    function fn_StrObfuscate($ag_StringIn) {
      return '********' ;
/* Costly encryption of the string to be obfuscated is unnecessary as it is not decrypted
However the function is retained as a separate block to allow room for future functionality.
        if ( function_exists('password_hash') ) {
          return password_hash ( $ag_StringIn , PASSWORD_BCRYPT ) ;
        } elseif ( function_exists('crypt') ) {
          return crypt($ag_StringIn) ;
        } else {
          return hash ( 'whirlpool' , $ag_StringIn ) ;
        }
*/
    }
  }
  
  if ( ! function_exists('fn_ArrRecurse') ) {
    function fn_ArrRecurse ( array &$ag_InArray , $ag_ItemKey ) {
      return array_walk_recursive (
        $ag_InArray ,
        function ( &$vr_Item , $ky_Item , $ag_ag_ItemKey ) {
          if ( strcmp ( $ky_Item , $ag_ag_ItemKey ) == 0 ) {
            $vr_Item = fn_StrObfuscate($vr_Item) ;
          }
        } ,
        $ag_ItemKey
      ) ;
    }
  }
  
  if ( ! function_exists('fn_Debug') ) {
    function fn_Debug (
      $ag_DebugMessage = '' ,     // Simple string describing what is being debugged.
      $ag_DebugOutput = NULL ,    // actual output to debug, mixed. this is passed to Kint.
      $ag_DebugObfuscate = NULL , // name of key to obfuscate if $ag_DebugOutput is array, any non-null (even '') if it is a string.
      $ag_DebugSwitch = NULL ,    // over-ride global debug switch
      $ag_ErrorTrigger = NULL     // debug output as a trigger_error if the parent framework suppresses Kint.
    ) {
      if(is_null($ag_DebugSwitch)) {
        if ( ! is_null($GLOBALS['bl_DebugSwitch']) ) {
          $ag_DebugSwitch = $GLOBALS['bl_DebugSwitch'] ;
        } else {
          $ag_DebugSwitch = FALSE ;
        }
      }
      
      if ( $ag_DebugSwitch === TRUE ) {
        if ( ! class_exists ( 'Kint' , FALSE ) ) {
          fn_LoadDebugger() ;
        }
        echo ('<hr />'.$ag_DebugMessage.'<br />') ;
        if (!is_null($ag_DebugObfuscate)) {
          if (is_string($ag_DebugOutput)) {
            $ag_DebugOutput = fn_StrObfuscate($ag_DebugOutput) ;
          } elseif (is_array($ag_DebugOutput)) {
            if (is_array($ag_DebugObfuscate)) {
              foreach ( $ag_DebugObfuscate as $vr_ItemToObfuscate ) {
                if ( ! fn_ArrRecurse ( $ag_DebugOutput , $vr_ItemToObfuscate ) ) {
                  trigger_error (
                    'Could not find specified array key, output not obfuscated. Sensitive data may be exposed.' ,
                    E_USER_ERROR
                  ) ;
                }
              }
            } else {
              if ( ! fn_ArrRecurse ( $ag_DebugOutput , $ag_DebugObfuscate ) ) {
                trigger_error (
                  'Could not find specified array key, output not obfuscated. Sensitive data may be exposed.' ,
                  E_USER_ERROR
                ) ;
              }
            }
          }
        }
        if (!is_null($ag_DebugOutput)) +s($ag_DebugOutput) ;
        echo('<hr />') ;
        if ( $ag_ErrorTrigger === TRUE ) {
          trigger_error ( $ag_DebugOutput , E_USER_ERROR ) ;
        }
      }
    }
  }
  
  if ( $GLOBALS['bl_DebugSwitch'] === TRUE ) fn_LoadDebugger() ;
  
  // fn_Debug ( 'Check for password' , array ( 'password' => 'secret' ) , 'password' ) ;
  // fn_Debug ( 'Secret provided' , 'secret' , 'anything' ) ;
  
?>