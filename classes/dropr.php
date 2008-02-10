<?php
/**
 * dropr main class
 * 
 *  - constants
 *  - logging
 *  - autoloading
 * 
 * @author Soenke Ruempler <soenke@ruempler.eu>
 */
class dropr 
{
    /**
     * The message is processed
     */
    const MESSAGE_PROCESSED = 1;
    
    /**
     * Mapping for error-levels
     *
     * @var array
     */
    private static $errorLevels = array(
        LOG_ERR,
        LOG_CRIT,
        LOG_WARNING,
        LOG_INFO,
        LOG_DEBUG,
    );
    
    /**
     * the current log-level
     *
     * @var int
     */
    private static $logLevel = LOG_DEBUG;
    
    /**
     * set the log level
     *
     * @param unknown_type $logLevel
     */
    public static function setLogLevel($logLevel)
    {
    	self::$logLevel = $logLevel;
    }
    
    /**
     * logging for the dropr services. can be
     *  - syslog for daemons
     *  - error_log for http processes
     * 
     * currently only syslog is implemented
     *
     * @param string    $message
     * @param int       $level
     */
    public static function log($message, $level = LOG_INFO)
    {
        if ($level <= self::$logLevel) {
            syslog($level, $message);
        }
    }
    
    public static function autoload($className)
    {
	    if (strpos($className, 'dropr_') !== 0) { 
	       return; 
	    }
	    
	    
	    $file = substr(str_replace('_', '/', $className), 6) . '.php';
	    require self::$classRoot . $file;
    }
    
    private static $classRoot;
    
    public static function setClassRoot($classRoot)
    {
    	self::$classRoot = $classRoot;
    }
}

/*
 * enable autoloading
 */
spl_autoload_register(array('dropr', 'autoload'));

/*
 * set the class root path once
 */
dropr::setClassRoot(realpath(dirname(__FILE__)) . '/');

/*
 * set the syslog variables
 */
openlog('dropr', LOG_ODELAY | LOG_PID, LOG_DAEMON);
