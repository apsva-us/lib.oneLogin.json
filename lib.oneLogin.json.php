<?php

//
// lib.oneLogin.json.php
//
// Thu Dec  5 11:24:25 EST 2013
//
// Copyright (c) 2013, Christopher Brown
// All rights reserved.
// 
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met: 
// 
// 1. Redistributions of source code must retain the above copyright notice, this
//    list of conditions and the following disclaimer. 
// 2. Redistributions in binary form must reproduce the above copyright notice,
//    this list of conditions and the following disclaimer in the documentation
//    and/or other materials provided with the distribution. 
// 
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
// ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
// DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
// ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
// (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
// LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
// ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
// (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
// SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
// 
// The views and conclusions contained in the software and documentation are those
// of the authors and should not be interpreted as representing official policies, 
// either expressed or implied, of Arlington Public Schools.
//



//-------------------------------------------------------------------------//


require_once 'lib.crypt-sqAES.php';

function oneLogin_apiToken($filename="./token.api") {

  $hash  = "change-this-encryption-string";
  $code  = file_get_contents($filename);

  $token  = sqAES::decrypt($hash, $code);

  return ($token);

}


//-------------------------------------------------------------------------//


function oneLogin_embeddingToken($filename="./token.embedding") {

  $hash  = "change-this-encryption-string";
  $code  = file_get_contents($filename);

  $token  = sqAES::decrypt($hash, $code);

  return ($token);
 
}


//-------------------------------------------------------------------------//













//-------------------------------------------------------------------------//


function oneLogin_writeCache($filename='./oneLogin.cache') {

   $indices=array ( "id","email","custom_attribute_aps_uid");

   return (file_put_contents($filename, serialize( oneLogin_cquery_userlist($indices))));

}


//----------//


function oneLogin_readCache($filename='./oneLogin.cache') {

        return (unserialize(file_get_contents($filename)));

}

//-------------------------------------------------------------------------//













//-------------------------------------------------------------------------//


function json_to_array($obj) {

    $nil = array ("@attributes" => array ( "nil"=> "true") );

    if(is_object($obj)) $obj = (array) $obj;

    if(is_array($obj)) {
        $new = array();
        foreach($obj as $key => $val) {
            $new[$key] = json_to_array($val);
        }
    }

    else $new = $obj;

    if ( $new === $nil ) return $false;
    else return $new;

}

//-------------------------------------------------------------------------//



function oneloginAPI($url, $action="GET", $postFields="", $apiVer=2, $is_xml=false) {

    $fullURL = "https://app.onelogin.com/api/v".$apiVer.$url;


    if ($is_xml) $headers = array('Content-type: application/xml','Content-Length: ' . (strlen($postFields).'' ));
    else         $headers = array('Content-Length: ' . (strlen($postFields).'' ));

    $ch = curl_init();

    switch($action){
        case "POST":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            break;
        case "GET":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            break;
        case "PUT":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            break;
        case "DELETE":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            break;
        default:
            break;
    }

    curl_setopt($ch, CURLOPT_USERPWD, oneLogin_apiToken());
    curl_setopt($ch, CURLOPT_URL, $fullURL);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, false);

    //curl_setopt($ch, CURLOPT_MAXREDIRS, 5 );
    //curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
    //curl_setopt($ch, CURLOPT_TIMEOUT, 90);

    //var_dump ($headers);
    //var_dump ($fullURL);
    //var_dump ($postFields);

    $http_result = curl_exec($ch);
    $error       = curl_error($ch);
    $http_code   = curl_getinfo($ch ,CURLINFO_HTTP_CODE);

    curl_close($ch);

    //echo "action:".$action."\n";
    //echo "error:".$error."\n";
    //echo "code:". $http_code."\n";
    //echo "--[url]---------------------\n";  var_dump ($fullURL); echo "--------------------------\n"; 
    //echo "--[request]-----------------\n";  var_dump ($postFields); echo "--------------------------\n"; 
    //echo "--[result]------------------\n";  var_dump ($http_result); echo "--------------------------\n"; 
    //break;


    if ( ($error) or ($http_code != "200" ) ) {

      echo "error:".$error."\n";
      echo "code:". $http_code."\n";
      var_dump ($http_result);
      return false;

    } else {

        if ($http_result === " ") return $http_code;
        else return  json_to_array( json_decode ( json_encode ( simplexml_load_string ( &$http_result ) ) ) );

    }

}

//-------------------------------------------------------------------------//













//-------------------------------------------------------------------------//


function oneLogin_query_userlist($indices = array ("id","email")) {

    // This uses the normal form of the userlist query

    $list = oneloginAPI("/users.xml");


    // build the indexes
    // 
    //  This builds an arbitrary set of indicies in the [0] elemant of the returned array structure
    //
    //  The result here, for say an index on id, is that you can find a specific user by id,
    //    without searching, by indirect reference.
    //
    //  Find user with id=10001 by using the form $list[index]["id"][10001]]
    //
    //  Find user with email=me@there.com by using the form $list[index]["email"]["me@there.com"]]
    //
    //  Indexing on attributes "id" and "email" are the default, but any UNIQUE property will work
    //
    //  The function oneLogin_user() is provided to simplify this referencing
    //


    if  ($list["user"]) {

     foreach ($list["user"] as $key=>$val) {
      if ($list["user"]["member-of"]) $list["user"]["member-of"] = explode (";",$list["user"]["member-of"]); 
     }

     foreach ( $indices as $indexKey=>$uindex) { 
       foreach ($list["user"] as $key=>$val) { 
            if ($list["user"][$key][$uindex]) { 
                 $list["index"][$uindex][$list["user"][$key][$uindex]]=$key;
            }
       }
     }

    return $list;

    } else return;

}


//-------------------------------------------------------------------------//


function oneLogin_cquery_userlist($indices = array ("id","email"), $status=-1) {

    //
    // This uses the custom form of the userlist query, which returns custom attributes,
    //   and allow for filtering based on user status.
    //
 
    if ( $status >= 0 ) { 
      $list = oneloginAPI("/users.xml", "GET", "include_custom_attributes=true&status=".$status);
    } else {
      $list = oneloginAPI("/users.xml", "GET", "include_custom_attributes=true");
    }


    // build the indexes
    // 
    //  This builds an arbitrary set of indicies in the [0] elemant of the returned array structure
    //
    //  The result here, for say an index on id, is that you can find a specific user by id,
    //    without searching, by indirect reference.
    //
    //  Find user with id=10001 by using the form $list[index]["id"][10001]]
    //
    //  Find user with email=me@there.com by using the form $list[index]["email"]["me@there.com"]]
    //
    //  Indexing on attributes "id" and "email" are the default, but any UNIQUE property will work
    //
    //  The function oneLogin_user() is provided to simplify this referencing
    //

	
    if  ($list["user"]) {

	
	 // If only one user is returned, then the record needs to be bumped out to a one 
	 // element array, because that is how they are returned when there are more then one	
	 //
	 if ( ! is_array($list["user"][0]) ) {
		 
		 $user=$list["user"];
		 unset ($list["user"]);
		 $list["user"][0]=$user;
		 
 	 }

	 	 
     foreach ($list["user"] as $key=>$val) {
      if ($list["user"]["member-of"]) $list["user"]["member-of"] = explode (";",$list["user"]["member-of"]); 
     }

     foreach ( $indices as $indexKey=>$uindex) { 
       foreach ($list["user"] as $key=>$val) { 
            if ($list["user"][$key][$uindex]) { 
                 $list["index"][$uindex][$list["user"][$key][$uindex]]=$key;
            }
       }
     }

    return $list;

    } else return;

}



//-------------------------------------------------------------------------//


function oneLogin_user(&$oneloginUsers, $uid, $index="email") {

  // Use an index generated by oneLogin_query_userlist() to directly 
  //   reference a user record by attribute value, and return the 
  //   user record from the array variable storaing the data
  // 
  //  The default property/index is email
  //

  if ($oneloginUsers["index"][$index][$uid] ) {
    return ( $oneloginUsers["user"][$oneloginUsers["index"][$index][$uid]] );
  } else return;

} 




//-------------------------------------------------------------------------//


function oneLogin_query_user($id) {

  if ($id) {

       $user = oneloginAPI("/users/".$id.".xml");

       if ($user["member-of"]) $user["member-of"] = explode (";",$user["member-of"]); 

       if     ($user["title"]=="Record Not Found") return; 
       elseif (is_numeric($user["id"])) return ($user);
       else   return;

  }

}


//-------------------------------------------------------------------------//


function oneLogin_cquery_user($id) {

  //
  // This uses the custom form of the userlist query, which returns custom attributes,
  //

  if ($id) {

       $user = oneloginAPI("/users/".$id.".xml", "GET", "include_custom_attributes=true");

       if ($user["member-of"]) $user["member-of"] = explode (";",$user["member-of"]); 

       if     ($user["title"]=="Record Not Found") return; 
       elseif (is_numeric($user["id"])) return ($user);
       else   return;

  }

}


//-------------------------------------------------------------------------//


function oneLogin_query_events() {

    return oneloginAPI("/events.xml", "GET", "", 1);

}


//-------------------------------------------------------------------------//


function oneLogin_query_task_list() {

   $oneloginTasks = oneloginAPI("/admin_tasks.xml");

   // The trick here is that if there is only one task, then $oneloginTasks["admin-task"]
   //   is not an array of task arrays, put just the single task array
   //    (the return structure is inconsistant) so we have to test and adapt

   if ( $oneloginTasks["admin-task"] ) {

     $tasklist= array();

     if ( is_array ($oneloginTasks["admin-task"][0]) ) {

       foreach ($oneloginTasks["admin-task"] as $key => $index ) {

         $tasklist[]=$oneloginTasks["admin-task"][$key];

       }

     } else  $tasklist[]=$oneloginTasks["admin-task"];


     foreach ($tasklist as $key => $index ) {
       $itasklist[$tasklist[$key]["id"]]=$tasklist[$key];
     }


     if (!is_null($itasklist[5])) unset ($itasklist[5]);
     if (!is_null($itasklist[118])) unset ($itasklist[118]);

     $oneloginTasks["admin-task"]=$itasklist;

     return $oneloginTasks;


   } else  return;

}


//-------------------------------------------------------------------------//


function oneLogin_query_apps($email, $key_field="id") {

  if ($email) {

    $token = oneLogin_embeddingToken();
    $url = "https://app.onelogin.com/client/apps/embed2?token=".$token."&email=".$email;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    $http_result = curl_exec($ch);
    $error       = curl_error($ch);
    $http_code   = curl_getinfo($ch ,CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($error) {

      print $error;
      var_dump ($http_result);
      return;

    } else {

          return json_to_array( json_decode ( json_encode ( simplexml_load_string ( &$http_result ) ) ) ); 

    } 

  } 

} 


//-------------------------------------------------------------------------//













//-------------------------------------------------------------------------//

function oneLogin_update_task($action, $id) {

  if (($id) and ($action)) {

     $is_rejected = array ( "action" => "approve" );
     $is_active   = array ( "action" => array ( "approve", "reject" ));
     $is_approved = array ( "0" => NULL);

     $request = "perform_action=".$action;

     $taskRec=oneloginAPI("/admin_tasks/".$id, "PUT", $request);

//print_r ($taskRec);

         if ($taskRec["id"]) switch ( $action ) {


            case "approve": if  ( ($taskRec["id"]==$id) 
                            and   ($taskRec["available_actions"]===$is_approved))
                            return true;
                            else return false; 
                            break;

            case "reject":  if  ( ($taskRec["id"]==$id) 
                            and   ($taskRec["available_actions"]===$is_rejected))
                            return true;
                            else return false; 
                            break;

             case "active": if ( ($taskRec["id"]==$id) 
                            and  ($taskRec["available_actions"]===$is_active))
                            return true;
                            else return false; 
                            break;

            default      :  return $taskRec["id"]; 
                            break;


          } else return NULL;

  }
}
 

//-------------------------------------------------------------------------//


function oneLogin_update_user($id, $properties, $values = NULL ) {

// Can be called with just a single (attribute,value) pair OR
//  passwd a single associative array of arrays with multiple (attribute,value) pairs

  if ($id) {

     if ( is_array($properties) ) {

     // step through the array and build the attribute xml request

        $request = "\n<user>";

        foreach ($properties as $attribute => $value ) { 

            if ( $attribute === "roles") {

                  $request .= "\n\t<roles type='array'>";
                  $roles = explode (",",$value); 
                  foreach ($roles as $role)  $request .= "\n\t\t<role>".$role."</role>";
                  $request .= "\n\t</roles>";

            } else {

                $request .= "\n\t<".$attribute.">".$properties[$attribute]."</".$attribute.">";
 
            } // if role

        } // foreach 

        $request .= "\n</user>\n";

     } else {

     // only a single attribute/value pair was passed

        if ( $properties === "roles") {

           $roles = explode (",",$values); 
           $request = "\n<user>\n\t<roles type='array'>";
           foreach ($roles as $role)  $request .= "\n\t\t<role>".$role."</role>";
           $request .= "\n\t</roles>\n</user>\n";

     } else {

           $request = "\n<user>\n\t<".$properties.">".$values."</".$properties.">\n</user>\n";

        }
     }




     return oneloginAPI("/users/".$id.".xml", "PUT", $request, 2, true);



  }

}


//-------------------------------------------------------------------------//


function oneLogin_delete_user($id) {

  if ($id) {

    return oneloginAPI("/users/".$id.".xml", "DELETE");

  } else  return;

}


//-------------------------------------------------------------------------//


function oneLogin_setpassword($id) {

  if ($id) {

     $newpwd=substr(sha1(rand()), 0, 32);

     return oneloginAPI("/users/".$id."/set_password.xml", "PUT", "<user><password>$newpwd</password></user>");

  } else return;

}


//-------------------------------------------------------------------------//













//-------------------------------------------------------------------------//


function oneLogin_print_userapps($email) {

   $oneloginApps = oneLogin_query_apps($email);
   var_dump ($oneloginApps); break;

   if ( $oneloginApps["app"] ) {

      echo ("\n(".count($oneloginApps["app"]).") applications for: $email\n\n");

      foreach ($oneloginApps["app"] as $key => $index) { 
        echo (str_pad($oneloginApps["app"][$key]["id"],8," ",STR_PAD_LEFT)." = ".$oneloginApps["app"][$key]["name"]."\n"); 
      }
   }

} 


//-------------------------------------------------------------------------//


function oneLogin_print_task_list(&$oneloginTasks) {

   echo ("\n(".count($oneloginTasks["admin-task"]).") tasks in queue\n\n");

   if ( $oneloginTasks["admin-task"] ) {

   foreach ($oneloginTasks["admin-task"] as $key => $index) { 

     $user=oneLogin_cquery_user($oneloginTasks["admin-task"][$key]["user-id"]);

     echo (str_pad($oneloginTasks["admin-task"][$key]["id"],3," ",STR_PAD_LEFT)
          ." : ".$oneloginTasks["admin-task"][$key]["updated-at"]
          ." : ".str_pad($oneloginTasks["admin-task"][$key]["state"],15," ",STR_PAD_LEFT)
          ." : ".str_pad($oneloginTasks["admin-task"][$key]["taskable-type"],18," ",STR_PAD_LEFT)
          ." : userid[ ".$oneloginTasks["admin-task"][$key]["user-id"]." ]"
          ." : ". $user["email"]
          ."\n"); 

   }}

} 


//-------------------------------------------------------------------------//


function oneLogin_print_userlist(&$oneloginUsers) {

   echo ("\n(".(count($oneloginUsers["user"])).") users\n\n"); 

   if ($oneloginUsers["user"]) 
   foreach ($oneloginUsers["user"] as $key => $index) { if  ( $key ) {
      echo (" ".str_pad($oneloginUsers["user"][$key]["id"], 8 , " ",STR_PAD_LEFT)." : "
               .str_pad($oneloginUsers["user"][$key]["group-id"], 8 , " ",STR_PAD_LEFT)." : "
               .str_pad($oneloginUsers["user"][$key]["status"], 1 , " ",STR_PAD_LEFT)." : "
               .$oneloginUsers["user"][$key]["email"]
               ."\n"); 

   }}

}


//-------------------------------------------------------------------------//



?>
