<?php

/**
 * This class represents a transport format
 * 
 * @author Ingo Schramm <coding@ister.org>
 * @version $Id$
 */
abstract class dropr_TransportFormat_Abstract
{
   
    /**
    *
    * @param mixed
    * @return mixed
    */
    public abstract function encode($item);
    
    /**
    *
    * @param mixed
    * @return mixed
    */
    public abstract function decode($item);

}

?>
