<?php 

include './lib.oneLogin.json.php';  


//   print a list of all users
//
//
//     oneLogin_print_userlist (oneLogin_cquery_userlist());
//




// print out an HTML file that has links to all apps a user has access to
//
//  NB: using /client/apps/select/login_id generates a URL that is specific to the USER
//      using /launch/id generates a URL that will work for ANYONE that has access to that app
//

//    $oneloginApps = oneLogin_query_apps("user@somewhere.us");
//
//    // print_r( $oneloginApps);
//
//    echo "<html>\n<hr>\n";
//    foreach ($oneloginApps as $key => $index) { 
//       echo ("<a href=\"https://app.onelogin.com/client/apps/select/".$oneloginApps[$key]["login_id"]."\">");
//       echo ($oneloginApps[$key]["name"]."</a><br>\n"); 
//     }
//
//    echo "<hr>\n";
//
//    foreach ($oneloginApps as $key => $index) { 
//       echo ("<a href=\"https://app.onelogin.com/launch/".$oneloginApps[$key]["id"]."\">");
//       echo ($oneloginApps[$key]["name"]."</a><br>\n"); 
//     }
//    echo "<hr>\n<html>";
// 


  

// list pending tasks
  // $oneloginTasks=oneLogin_query_task_list(); oneLogin_print_task_list($oneloginTasks);


// list new-self restrations
    //echo "\nSelf-Regstered:";oneLogin_print_userlist_aps(oneLogin_cquery_userlist(array (),6));




// Copy id to username for a group of Users

//   $o = oneLogin_cquery_userlist();
//   oneLogin_print_user_list($o);
// 
//   foreach ($o as $key => $index) {  if  ( $key ) {
// 
//      if ($o[$key]["group-id"]==999999) {
//   	  oneLogin_update_user($o[$key]["id"], "username", $o[$key]["id"] ); 
//      }
// 
//   }}
  





?>
