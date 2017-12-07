# php_imap_parser
PHP Imap Parser- Simple Class

# How to use it ?

include "imap-parser.php";

    try {

         $messages = $parser->get_messages($server, $mailbox_account, $mailbox_password,false);
         echo "<h1>Messages:</h1> <pre>"; print_r($messages);  echo "</pre>"; 
    }
    catch (customException $e) {
      echo $e->errorMessage();
    }

