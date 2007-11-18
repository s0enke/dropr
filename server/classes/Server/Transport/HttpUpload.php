<?php
class pmq_Server_Transport_HttpUpload extends pmq_Server_Transport_Abstract 
{
    
    public function handle()
    {
        // check for the http header of the client
        if (!isset($_POST['client']) || !is_string($_POST['client'])) {
            throw new Exception("No client header set!");
        }
        
        $client = $_POST['client'];
        
        // xxx check the check
        if (!isset($_POST['metaData']) || !is_string($_POST['metaData']) || (!$metadata = unserialize($_POST['metaData']))) {
            throw new Exception("No client metadata set!");
        }
        
        #print_r($_FILES);exit;
        
        $return = array();
        foreach ($metadata as $k => $messageData) {
            
            // xxx check the existence of the indexes
            $messageId  = $messageData['messageId'];
            $priority   = $messageData['priority'];
            $messageRef = $messageData['message'];           

            try {

                if (!isset($_FILES[$messageRef])) {
                    throw new pmq_Server_Exception("message not in fileupload!");
                }
                
                /// XXX tmp dir config
                $tempName = tempnam('/tmp', 'pmq_');
            
                if (!move_uploaded_file($_FILES[$messageRef]['tmp_name'], $tempName)) {
                    // could not move the uploaded file - whyever
                    $return[$messageId]['inqueue'] = false;
                    continue;
                }
                
                $file = new SplFileInfo($tempName);
                $message = new pmq_Server_Message($client, $messageId, $file, $priority);
                
                $this->getStorage()->put($message);
                
                // ok, it's in the queue, lets notify the sender
                $return[$messageId]['inqueue'] = true;  
            
            } catch (Exception $e) {
                // something bad happened
                $return[$messageId]['inqueue'] = false;  
            }
        }
            
        // write the result back to the sender
        echo serialize($return);
    
    }
    
}