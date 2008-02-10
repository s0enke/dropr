<?php
class dropr_Client_Peer_HttpUpload extends dropr_Client_Peer_Abstract
{
    const TYPE_QUEUE = 1;
    
    const TYPE_DIRECT = 2;
    
    public function connect()
    {
        // hier connection aufbauen etc
    }

    public function send(array &$messages)
    {
        return $this->httpFileUpload($messages, self::TYPE_QUEUE);
    }

    private function httpFileUpload(array &$messages, $type) {

        $uploadFields = array();
        
        // set the client name
        // XXX: geloet
        $uploadFields['client'] = trim(`hostname`);
        
        $metaData = array();
        foreach ($messages as $k => $message) {
            /* @var $message dropr_Client_Message */
            
            $metaData[$k] = array(
                'message'       => 'f_' . $k,
                'messageId'     => $message->getId(),
                'channel'		=> $message->getChannel(),
                'priority'      => $message->getPriority(),
                'messageType'	=> $type,
            );
            
            $content = &$message->getMessage();
            if ($content instanceof SplFileInfo) {
                // the message is a file, we can use curl directly
                $uploadFields['f_' . $k] = '@' . $content->getPathname();
            } elseif (is_string($content)) {
                // message content is a string, we've to build a tempfile first
                if (!$filename = tempnam('/tmp', 'dropr')) {
                    throw new Exception();
                }
                if (!file_put_contents($filename, $content)) {
                    throw new Exception();
                }
            } else {
                throw new dropr_Client_Exception("Currently only file transport is implemented.");
            }
        }
        
        $uploadFields['metaData'] = serialize($metaData);
        
        // ACHTUNG: extremes Geloet!!!

        $connomains = array(
            $this->getUrl()
        );
        
        $mh = curl_multi_init();

        foreach ($connomains as $i => $url) {
            $conn[$i] = curl_init();
            
            // set the timeout to 60 seconds
            curl_setopt($conn[$i], CURLOPT_TIMEOUT, 60);
            curl_setopt($conn[$i], CURLOPT_URL, $url);
            curl_setopt($conn[$i], CURLOPT_POST, true);
            curl_setopt($conn[$i], CURLOPT_POSTFIELDS, $uploadFields);
            
            curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
            
            curl_multi_add_handle ($mh,$conn[$i]);
        }
        
        // start performing the request
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        
        while ($active and $mrc == CURLM_OK) {
            // wait for network
            if (curl_multi_select($mh) != -1) {
                // pull in any new data, or at least handle timeouts
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        
        if ($mrc != CURLM_OK) {
            throw new dropr_Client_Exception("Curl multi read error $mrc");
        }
        
        // retrieve data
        foreach ($connomains as $i => $url) {
            if (($err = curl_error($conn[$i])) == '') {
                $res[$i]=curl_multi_getcontent($conn[$i]);
            } else {
                throw new dropr_Client_Exception("Curl error on handle $i: $err");
            }
            curl_multi_remove_handle($mh,$conn[$i]);
            curl_close($conn[$i]);
        }
        curl_multi_close($mh);
        
        #echo $res[0];
        
        if (!$return = unserialize($res[0])) {
            // XXX NO exception but return false and LOG!!
            throw new dropr_Client_Exception("Could not unserialize the result!");;
        }
        
        return $return;
    }
    
    public function poll(array $messageIds)
    {
        
    }
    
    public function sendDirectly(dropr_Client_Message $message)
    {
        return $this->httpFileUpload($messages, self::TYPE_DIRECT);
    }
}
