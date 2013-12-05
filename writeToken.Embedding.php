<?php 

// This will output an encrypted version of the token, which is only
//  slightly more secure than storing it in plain-text (since the hash
//  still must be in the code for decrypting)
//
// just pipe the output to a file called "token.embedding"
//

include '../lib.crypt-sqAES.php';

	 $token = "put-your-embedding-token-here";
	 $hash  = "change-this-encryption-string";

	
	$code = sqAES::encrypt($hash, $token);
	$key  = sqAES::decrypt($hash, $code);

 //  var_dump ($code);
 //  var_dump ($key);
 //  var_dump ($token);

    echo ($code."\n");

?>
