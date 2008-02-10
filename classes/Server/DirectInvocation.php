<?php
/**
 * Classes implementing this interface can directly be invoked by the
 * Server-API and they are able to send an answer right back to the client
 * 
 * this is very useful if you can't wait for an asyncronous answer
 *  
 */
interface dropr_Server_DirectInvocation
{
    /**
     * @return 	string			the
     * @throws	Exception		
     */
    public function invokeMessage(dropr_Server_Message $message);
}
