<?php
abstract class dropr_Client_Peer_Abstract
{
    private static $instances = array();

    private $transportMethod;
    private $peerUrl;
    private $key;

    public static function getInstance($method, $url = false)
    {
        if ($url === false) {
            if (!list($method, $url) = explode(';', $method)) {
                throw new dropr_Client_Exception("Could not explode method and url from '$method'");
            }
        }

        $key = $method . ';' . $url;
        if (!isset(self::$instances[$key])) {
            // Guess the classname from transport method
            $className = 'dropr_Client_Peer_' . ucfirst($method);
            self::$instances[$key] = new $className($method, $url);
        }

        return self::$instances[$key];
    }
    
    protected function __construct($method, $url)
    {
        $this->transportMethod = $method;
        $this->peerUrl = $url;
        $this->key = $method.';'.$url;
    }

    public function getTransportMethod()
    {
        return $this->transportMethod;
    }

    public function getUrl()
    {
        return $this->peerUrl;
    }

    public function getKey() {
        return $this->key;
    }

    abstract public function send(array &$messages);    

    /**
     * polls the peer for processed messages
     */
    abstract public function poll(array $messageIds);
    
    /**
     * sends a message directly and give feedback immediately
     * 
     * @return string	the answer
     * @throws	dropr_Client_Exception	If something went wrong 
     */
    abstract public function sendDirectly(dropr_Client_Message $message);
    
}
