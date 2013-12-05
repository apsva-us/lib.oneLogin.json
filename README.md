lib.oneLogin.json
=================

php library for using the OneLogin API


This is a set of php functions to make using the OneLogin API a little easier.  It uses the JSON API methods, and allows for the standard and custom query calls.

The main workhourse is the oneloginAPI() function, which implements the generic JSON/REST calls.  The security tokens are stored in encrypted external files (though the hash key MUST be included in the code, so it's really just obfustication).

The rest of the functions are wrappers for different API calls.

The queries are limited to 100 users.  OneLogin does not provide multi-page user lists like ZenDesk provides (yet), but I assume that will come with time.

The write/readCache functions were made to allow for caching a user database to local disk for testing, since full queries are time consuming/bandwidth intensive.


List of Functions
=================
* oneLogin_apiToken
* oneLogin_embeddingToken

* oneLogin_writeCache(
* oneLogin_readCache

* json_to_array

* oneloginAPI

* oneLogin_cquery_userlist
* oneLogin_query_userlist

* oneLogin_cquery_user
* oneLogin_query_user
* oneLogin_query_events
* oneLogin_query_task_list
* oneLogin_query_apps

* oneLogin_update_task
* oneLogin_update_user

* oneLogin_delete_user

* oneLogin_setpassword

* oneLogin_user

* oneLogin_print_userapps
* oneLogin_print_task_list
* oneLogin_print_userlist


END


