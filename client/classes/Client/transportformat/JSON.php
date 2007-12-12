<?php

/**
 * A JSON message format.
 *
 * It's almost type safe. The PHP5 json functions allow
 * only for arrays and objects to be en/de-coded. For
 * a queue it is more often than not sufficient to pass
 * a scalar (e.g. send a ID to a delete-that-stuff-queue).
 * This class allows for sending a scalar in JSON.
 *
 * @author Ingo Schramm <coding@ister.org>
 * @version $Id$
 */
class dropr_Client_TransportFormat_JSON extends dropr_Client_TransportFormat_Abstract
{

    /** 
     * Encode an item into JSON format.
     *
     * <code>
     * // PHP
     * $item = "hello world"
     * // JSON
     * {"type":"string","item":"hello world"}
     * </code>
     *
     * IMPORTANT NOTE: If you encode an object decoding will
     * result in an object of stdClass containing only the
     * public properties of the original and no methods. If you 
     * encode an array of objects decoding will result in
     * an array of arrays containing only the public properties 
     * of the original objects. It is in general not recommended
     * to send objects in JSON format. Use arrays to pass data
     * and feed empty objects on the other side or serialize
     * objects. But beware: in PHP5 serialization will kill all
     * non-public data unless you craft your own __sleep and __wakeup!
     *
     * <code>
     * $o    = new MyClass;
     * $data = $o->getData();
     * $json = $formatter->encode($data);
     * //----
     * $u    = new MyClass;
     * $data = $formatter->decode($json);
     * $u->setData($data);
     * //----
     * //or, even more tricky, send object as a string
     * $o    = new MyClass;
     * $json = $formatter->encode(serialize($o));
     * </code>
     *
     * @param mixed $item item to encode
     * @return mixed
     * @todo extend type safety to classes (reflection, well known object container class)
     */
    public function encode($item) 
    {
        if (is_resource($item)) {
            throw new dropr_Client_Exception('cannot encode resources');
        }
        $type   = ($item === null) ? 'null' : gettype($item);
        $item   = array('type' => $type, 'item' => $item);
        $result = json_encode($item);
        return $result;
    }
    
    /** 
     * Decode items from JSON format.
     *
     * Items will set to the proper type before returned.
     * IMPORTANT NOTE: see encode().
     *
     * @param string $item JSON string to decode
     * @return mixed
     */
    public function decode($item)
    {
        $result = json_decode($item, true);
        if (!(is_array($result) && isset($result['type']))) {
            throw new dropr_Client_Exception('broken JSON string or no type specified: ' . $item . '('.gettype($result).')');
        }
        $item = $result['item'];
        if (!settype($item, $result['type']))
            throw new dropr_Client_Exception('invalid type: ' . $result['type']);
        return $item;
    }
    
}
