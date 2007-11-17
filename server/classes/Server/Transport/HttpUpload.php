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
        

     
        $return = array();
        foreach ($_FILES as $k => $fStruct) {
            // XXX: erst move_uploaded_file für uns (security)
            
            $messageId = $fStruct['name'];
            try {
                /// XXX tmp dir config
                $tempName = tempnam('/tmp', 'pmq_');
            
                if (!move_uploaded_file($fStruct['tmp_name'], $tempName)) {
                    // could not move the uploaded file - whyever
                    $return[$fStruct['name']]['inqueue'] = false;
                    continue;
                }
                
                // create message object and put it into the queue
                // XXX it's currently bundled with the client filesystem
                // storage
                // try to split the filename
                
                $file = new SplFileInfo($tempName);
                
                // xxx add type
                list($priority, $messageId, ) = explode('_', $fStruct['name']);
                
                $message = new pmq_Server_Message($client, $messageId, $file, $priority);
                
                $this->getStorage()->put($message);
                
                // ok, it's in the queue, lets notify the sender
                $return[$fStruct['name']]['inqueue'] = true;  
            
            } catch (Exception $e) {
                // something bad happened
                $return[$fStruct['name']]['inqueue'] = false;  
            }
        }
            
        // write the result back to the sender
        echo serialize($return);    
    
    }
    
}