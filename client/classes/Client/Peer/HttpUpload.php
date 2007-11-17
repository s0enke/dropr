<?php
class pmq_Client_Peer_HttpUpload extends pmq_Client_Peer_Abstract
{
    public function connect()
    {
        // hier connection aufbauen etc
    }

    public function send(array &$messages, pmq_Client_Storage_Abstract $storage)
    {
        // messages zusammentueten und in einem rutsch versenden

        if ($storage->getType() == pmq_Client_Storage_Abstract::TYPE_FILE) {
            return $this->httpFileUpload($messages);
        } else {
            throw new Exception("not implemented");
        }
    }

    public function oldSend(array &$handles, pmq_Client_Storage_Abstract $storage)
    {
        // messages zusammentueten und in einem rutsch versenden

        if ($storage->getType() == pmq_Client_Storage_Abstract::TYPE_FILE) {
            return $this->httpFileUpload($handles);
        } else {
            throw new Exception("not implemented");
        }
    }

    private function httpFileUpload(array $messages) {

        $fNamesCurl = array();
        foreach ($fNames as $k => $fName) {
            $fNamesCurl['c_' . $k] = '@' . $fName;
        }
        // ACHTUNG: extremes Geloet!!!

        $connomains = array(
            $this->getUrl()
        );
        
        $mh = curl_multi_init();
        
        foreach ($connomains as $i => $url) {
            $conn[$i] = curl_init();
            
            curl_setopt($conn[$i], CURLOPT_URL, $url);
            curl_setopt($conn[$i], CURLOPT_POST, true);
            curl_setopt($conn[$i], CURLOPT_POSTFIELDS, $fNamesCurl);
            
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
            print "Curl multi read error $mrc\n";
        }
        
        // retrieve data
        foreach ($connomains as $i => $url) {
            if (($err = curl_error($conn[$i])) == '') {
                $res[$i]=curl_multi_getcontent($conn[$i]);
            } else {
                print "Curl error on handle $i: $err\n";
            }
            curl_multi_remove_handle($mh,$conn[$i]);
            curl_close($conn[$i]);
        }
        curl_multi_close($mh);
        
        return unserialize($res[0]);
    }
}