<?php
/**
 * dropr
 *
 * Copyright (c) 2007 - 2008 by the dropr project https://www.dropr.org/
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of dropr nor the names of its
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    dropr
 * @author     Soenke Ruempler <soenke@jimdo.com>
 * @author     Boris Erdmann <boris@jimdo.com>
 * @author     Thomas Göttgens <thomas@fivemile.org>
 * @copyright  2007-2008 Soenke Ruempler, Boris Erdmann
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
/**
 * MySQL Storage Driver
 */
class dropr_Client_Storage_MySQL extends dropr_Client_Storage_Abstract {
     const TYPE_SPOOL = '1';
     const TYPE_SENT = '2';
     private $hdb, $table;
     protected 
     function __construct($dsn) {
          //host=localhost;port=3307;dbname=testdb usw.
          $dsn = explode(";", $dsn);
          foreach($dsn as $v) {
               $v = explode("=", $v);
               $ {
                    $v[0]
               } = $v[1];
          }
          if (!$hdb = mysql_connect($db_server, $db_user, $db_pass)) {
               throw new dropr_Client_Exception("Error connecting to database!");
          }
          if (!mysql_select_db($db_name, $hdb)) {
               throw new dropr_Client_Exception("Error switching to table!");
          }
          $this->hdb = $hdb;
          $this->table = $table_name;
     }
     public 
     function saveMessage(dropr_Client_Message $message) {
          $priority = $message->getPriority();
          $peer = $this->encodeForDb($message->getPeer()->getKey());
          $channel = $this->encodeForDb($message->getChannel());
          $badName = true;
          mysql_query("LOCK TABLES " . $this->table . " WRITE", $this->hdb);
          while ($badName) {
               $fName = join('_', array(
                    $priority,
                    $this->getTimeStamp() ,
                    $peer,
                    $channel
               ));
               $exist = mysql_fetch_row(mysql_query("select count(*) from " . $this->table . " where name='" . $fName . "' and typ='" . self::TYPE_SPOOL . "'", $this->hdb));
               if ($exist[0] < 1) {
                    mysql_query("insert into " . $this->table . " values('" . self::TYPE_SPOOL . "','" . $fName . "'," . date("U") . ",'" . mysql_real_escape_string($message->getMessage() , $this->hdb) . "')", $this->hdb);
                    $badName = false;
               }
          }
          mysql_query("UNLOCK TABLES", $this->hdb);
          return $fName;
     }
     public 
     function getQueuedMessages($limit = null, &$peerKeyBlackList = null) {
          // expire blacklisted peers
          $now = time();
          foreach($peerKeyBlackList as $peerKey => $timeout) {
               if ($now > $timeout) {
                    unset($peerKeyBlackList[$peerKey]);
               }
          }
          $erg = mysql_query("select * from " . $this->table . " where typ='" . self::TYPE_SPOOL . "'", $this->hdb);
          $c = 1;
          $messages = array();
          while ($row = mysql_fetch_row($erg)) {
               $fName = $row[1];
               if ($limit && ($c > $limit)) {
                    break;
               }
               list($priority, $timeStamp, $encodedPeerKey, $encodedChannel) = explode('_', $fName, 4);
               $decodedPeerKey = $this->decodeFromDb($encodedPeerKey);
               $decodedChannel = $this->decodeFromDb($encodedChannel);
               $message = new dropr_Client_Message(NULL, $row[3], dropr_Client_Peer_Abstract::getInstance($decodedPeerKey) , $decodedChannel, $priority);
               $message->restoreId($fName);
               if (!isset($peerKeyBlackList[$decodedPeerKey])) {
                    $messages[$decodedPeerKey][] = $message;
                    $c++;
               }
          }
          return $messages;
     }
     public 
     function getMessage($messageId, dropr_Client_Peer_Abstract $peer) {
          return $messageId;
     }
     private 
     function encodeForDb($val) {
          return base64_encode($val);
     }
     private 
     function decodeFromDb($val) {
          return base64_decode($val);
     }
     private 
     function getTimeStamp() {
          $tName = (string)microtime();
          $spPos = strpos($tName, ' ');
          return substr($tName, $spPos + 1) . '-' . substr($tName, 2, $spPos - 2);
     }
     public 
     function getType() {
          return self::TYPE_DB;
     }
     public 
     function checkSentMessages(array & $messages, array & $result) {
          foreach($messages as $k => $message) {
               $msgId = $message->getId();
               if (isset($result[$msgId]['inqueue']) && ($result[$msgId]['inqueue'] === true)) {
                    if (!mysql_query("update " . $this->table . " set ts=" . date("U") . ", typ='" . self::TYPE_SENT . "' where name='" . $msgId . "' and typ='" . self::TYPE_SPOOL . "'", $this->hdb)) {
                         throw new dropr_Client_Exception("Database Error! " . mysql_error($this->hdb));
                    }
               }
               unset($message);
          }
     }
     public 
     function countQueuedMessages() {
          $erg = mysql_fetch_row(mysql_query("select count(*) from " . $this->table . " where typ='" . self::TYPE_SPOOL . "'", $this->hdb));
          return $erg[0];
     }
     public 
     function countSentMessages() {
          $erg = mysql_fetch_row(mysql_query("select count(*) from " . $this->table . " where typ='" . self::TYPE_SENT . "'", $this->hdb));
          return $erg[0];
     }
     public 
     function wipeSentMessages($olderThanMinutes) {
          $time = time() - ($olderThanMinutes * 60);
          mysql_query("delete from " . $this->table . " where ts <= $time and typ='" . self::TYPE_SENT . "'", $this->hdb);
          return mysql_affected_rows($this->hdb);
     }
}
