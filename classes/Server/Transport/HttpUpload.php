<?php
class dropr_Server_Transport_HttpUpload extends dropr_Server_Transport_Abstract 
{
    
    public function handle()
    {
        // check for the http header of the client
        if (!isset($_POST['client']) || !is_string($_POST['client'])) {
            throw new Exception("No client header set!");
        }
        
        $client = $_POST['client'];
        
        
        // Try to get the method we are called
        
        // xxx check the check
        if (!isset($_POST['metaData']) || !is_string($_POST['metaData']) || (!$metadata = unserialize($_POST['metaData']))) {
            error_log(print_r($_POST['metaData'], true));
        	throw new Exception("No client metadata set!");
        }
        
        #print_r($_FILES);exit;
        
        $return = array();
        foreach ($metadata as $k => $messageData) {
            
            // xxx check the existence of the indexes
            $messageId  = $messageData['messageId'];
            $channel    = $messageData['channel'];
            $priority   = $messageData['priority'];
            $messageRef = $messageData['message'];

            try {

                if (!isset($_FILES[$messageRef])) {
                    throw new dropr_Server_Exception("message not in fileupload!");
                }
                
                if (!is_uploaded_file($_FILES[$messageRef]['tmp_name'])) {
                    // could not move the uploaded file - whyever
                    $return[$messageId]['inqueue'] = false;
                    continue;
                }
                
                $file = new SplFileInfo($_FILES[$messageRef]['tmp_name']);
                $message = new dropr_Server_Message($client, $messageId, $file, $channel, $priority);
                
                $this->getStorage()->put($message);
                
                // ok, it's in the queue, lets notify the sender
                $return[$messageId]['inqueue'] = true;  
            
            } catch (Exception $e) {
                // something bad happened
                $return[$messageId]['inqueue'] = false;  
            }
        }
            
        #echo "Time after curl: ";
        #echo (time() - $time);
        #echo "\n";
        #exit;
        
        // write the result back to the sender
        echo serialize($return);
        
    
    }
    
}
