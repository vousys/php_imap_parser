<? 
    // --------------------------------------------------------------------- 
    /*
    *
    *                         Imap Parser Class 
    *
    * =====================================================================
    *
    * Create by         Veronica Osorio - Vousys  
    *                   Based on https://stackoverflow.com/a/12478538/2271755
    *
    * Ej :
    *            try {
    *
    *                 $messages = $parser->get_messages($server, $mailbox_account, $mailbox_password,false);
    *                 echo "<h1>Messages:</h1> <pre>"; print_r($messages);  echo "</pre>"; 
    *            }
    *            catch (customException $e) {
    *              echo $e->errorMessage();
    *            }
    *
    *
    // ---------------------------------------------------------------------  */

  


class imap_parser {
    
    var $imap_obj;


    function _construct() {
        $this->imap_obj_obj = null;
    }
    
 
 
 
    // --------------------------------------------------------------------- 
    /*
    *
    * Funcionalidad: Get all messages
    *
    * @param string server         ej:  {localhost:110/pop3}INBOX    
    * @param string username       ej:  youremail@yourdomain.com
    * @param string password       ej:  XXXXX   
    * @param boolean files_create  ej:  true = Create attachment file in a path    
    * @param string file_path      ej:  ../mypath/img/    
    * @return $messages[] 
                                        Array
                                        (
                                            [0] => Array
                                                (
                                                    [fromaddress] => youremail@yourdomain.com
                                                    [from] => YOURNAME
                                                    [to] => youremail@yourdomain.com
                                                    [subject] => Fwd: Dni
                                                    [body] => bla bla bla  
                                                    [message_id] => <093c2eda-ac3a-44b5-bdec-585f45e3b754@Spark>
                                                    [date] => 1511983968
                                                    [attachs] => Array
                                                        (
                                                            
                                                            [0] => Array
                                                                (
                                                                    [is_attachment] => 1
                                                                    [filename] => Image.jpeg
                                                                    [name] => 
                                                                    [attachment] => 
                                                                )

                                                            [1] => Array
                                                                (
                                                                    [is_attachment] => 1
                                                                    [filename] => Image-1.jpeg
                                                                    [name] => 
                                                                    [attachment] => 
                                                                )

                                                        )

                                                )

                                        )


    * @access public
    *
    ---------------------------------------------------------------------  */

    function get_messages($server, $username, $password,$files_create=false,$file_path='') {

        // OPEN CONECTION
        $this->imap_obj = imap_open($server, $username, $password) or die("imap connection error");
        

        // COUNT MSG 
        $message_count = imap_num_msg($this->imap_obj);
        
        // FETCH ALL MSG
        $messages = array();
        for ($m = 1; $m <= $message_count; ++$m){
            $messages[] = $this->parse_message($m , $files_create , $file_path);
            
        } 

        //imap_setflag_full($this->imap_obj, $i, "\\Seen");
        //imap_mail_move($this->imap_obj, $i, 'Trash');

        imap_close($this->imap_obj);
        return($messages);

    } //Fcion

    // --------------------------------------------------------------------- 
    /*
    *
    * Funcionalidad: Parse Message
    *
    * @param number  message_num   ej:  1  
    * @param boolean files_create  ej:  true = Create attachment file in a path    
    * @param string file_path      ej:  ../mypath/img/    
    * @return $attachs[] 
                                           [0] => Array
                                                (
                                                    [fromaddress] => youremail@yourdomain.com
                                                    [from] => YOURNAME
                                                    [to] => youremail
                                                    [subject] => Fwd: Dni
                                                    [message_id] => <093c2eda-ac3a-44b5-bdec-585f45e3b754@Spark>
                                                    [date] => 1511983968
                                                    [body] => bla bla bla  
                                                    [attachs] => Array ()
                                                )
    * @access public
    *
    ---------------------------------------------------------------------  */

    function parse_message($message_num,$files_create=false,$file_path='') {

            // GET HEADERS
            $header = imap_header($this->imap_obj, $message_num);
            //print_r($header);

            // GET INFO
            $message['fromaddress'] = $header->from[0]->mailbox.'@'.$header->from[0]->host;
            $message['from']        = $header->from[0]->personal;
            $message['to']          = $header->to[0]->mailbox;
            $message['subject']     = $header->subject;
            $message['message_id']  = $header->message_id;
            $message['date']        = $header->udate;

            $from                   = $message['fromaddress'];
            $from_email             = $message['from'];
            $to                     = $message['to'];
            $subject                = $message['subject'];

            //get message body
            $body = (imap_fetchbody($this->imap_obj, $message_num,1.1)); 
            if($body == '') $body = (imap_fetchbody($this->imap_obj, $message_num,1));
             
            $message['body']        =  quoted_printable_decode($body); 
 


        
            if ($this->debug) {
                echo $from_email . '</br>';
                echo $to . '</br>';
                echo $subject . '</br>';
            }


            // GET ATTACHs
            $structure = imap_fetchstructure($this->imap_obj, $message_num);
            $message["attachs"] = $this->get_attachs($message_num,$structure,$files_create,$file_path);        

            return($message);


    }





    // --------------------------------------------------------------------- 
    /*
    *
    * Funcionalidad: Get all attachs of this message
    *
    * @param number  message_num   ej:  1  
    * @param object structure      ej:  imap_fetchstructure  
    * @param boolean files_create  ej:  true = Create attachment file in a path    
    * @param string file_path      ej:  ../mypath/img/    
    * @return $attachs[] 
                                                     [attachs] => Array
                                                        (
                                                            
                                                            [0] => Array
                                                                (
                                                                    [is_attachment] => 1
                                                                    [filename] => Image.jpeg
                                                                    [name] => 
                                                                    [attachment] => 
                                                                )

                                                            [1] => Array
                                                                (
                                                                    [is_attachment] => 1
                                                                    [filename] => Image-1.jpeg
                                                                    [name] => 
                                                                    [attachment] => 
                                                                )

                                                        )
    * @access public
    *
    ---------------------------------------------------------------------  */

    function get_attachs($message_num,$structure,$files_create=false,$file_path=''){

        $attachments = array();
        $a           = 0;

        if(isset($structure->parts) && count($structure->parts)) {

            for($i = 0; $i < count($structure->parts); $i++) {

                            $attachments[$a] = array(
                                'is_attachment' => false,
                                'filename' => '',
                                'name' => '',
                                'attachment' => ''
                            );

                            if($structure->parts[$i]->ifdparameters) {
                                foreach($structure->parts[$i]->dparameters as $object) {
                                    if(strtolower($object->attribute) == 'filename') {
                                        $attachments[$a]['is_attachment'] = true;
                                        $attachments[$a]['filename'] = $object->value;
                                    }
                                }
                            } 


                            if($structure->parts[$i]->ifparameters) {
                                foreach($structure->parts[$i]->parameters as $object) {
                                    if(strtolower($object->attribute) == 'name') {
                                        $attachments[$a]['is_attachment'] = true;
                                        $attachments[$a]['name'] = $object->value;
                                    }
                                }
                            } 



                            if($attachments[$a]['is_attachment']) {
                                $attachments[$a]['attachment'] = imap_fetchbody($this->imap_obj, $message_num, $i+1);
                                if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                                    $attachments[$a]['attachment'] = base64_decode($attachments[$a]['attachment']);
                                }
                                elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
                                    $attachments[$a]['attachment'] = quoted_printable_decode($attachments[$a]['attachment']);
                                }

                                $a++;
                            }  

                 }     
                      
            } 


            // CREATE  FILE
            if ($files_create) {

                   foreach ($attachments as $key => $attachment) {
                        $name     = $attachment['name'];
                        $contents = $attachment['attachment'];
                        file_put_contents($file_path."/".$name, $contents);
                    }
            }
 
            return($attachments);
    }

} // Clase



?>
