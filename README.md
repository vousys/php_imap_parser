# php_imap_parser
PHP Imap Parser- Simple Class

# How to use it ?

include "imap-parser.php";

    try {
    
        // Create object
         $parser  = new imap_parser();
         
         // Parse all message
         $messages = $parser->get_messages($server, $mailbox_account, $mailbox_password,false);
         
         // Debug
         echo "<h1>Messages:</h1>";
         echo "<pre>"; print_r($messages);  echo "</pre>"; 
    }
    catch (customException $e) {
      echo $e->errorMessage();
    }

