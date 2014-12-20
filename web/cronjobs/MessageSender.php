<?php
require_once dirname(__FILE__) . '/../bootstrap.php';
abstract class MessageSender
{
	public static function run()
	{
		$start = self::_logMsg("== START: processing Messages ==", __CLASS__, __FUNCTION__);
		$messages = self::_getAndMarkMessages();
		self::_logMsg("GOT " . count($messages) . ' Message(s): ', __CLASS__, __FUNCTION__);
		foreach	($messages as $message){
			self::_logMsg("    Looping message(ID=" . $message->getId() . ': ', __CLASS__, __FUNCTION__);
			try {
				Dao::beginTransaction();
				EmailSender::sendEmail($message->getFrom(), $message->getTo(), $message->getSubject(), $message->getBody(), $message->getAttachmentAssetIdArray());
				$message->setStatus(Message::STATUS_SENT)
					->save();
				Dao::commitTransaction();
				
				self::_logMsg("    SUCCESS sending message(ID=" . $message->getId() . ').', __CLASS__, __FUNCTION__);
			} catch(Exception $ex) {
				Dao::rollbackTransaction();
				$message->setStatus(Message::STATUS_FAILED)
					->save();
				self::_logMsg("    ERROR sending message(ID=" . $message->getId() . ': ' . $ex->getMessage(), __CLASS__, __FUNCTION__);
				self::_logMsg("    ERROR sending message(ID=" . $message->getId() . ': ' . $ex->getTraceAsString(), __CLASS__, __FUNCTION__);
			}
		}
		$end = new UDate();
		self::_logMsg("== FINISHED: " . count($messages) . " Message(s) == ", __CLASS__, __FUNCTION__);
	}
	private static function _logMsg($msg, $className, $funcName) {
		$now = new UDate();
		echo trim($now) . '(UTC)::' . $className . '::' . $funcName . ': ' . $msg . "\n";
		return $now;
	}
	private static function _getAndMarkMessages()
	{
		$randId = StringUtilsAbstract::getRandKey();
		Message::updateByCriteria('transId = ?, status = ?', 'active = 1 and status = ?', array($randId, Message::STATUS_SENDING, Message::STATUS_NEW));
		return Message::getAllByCriteria('transId = ? and status = ?', array($randId, Message::STATUS_SENDING));
	}
}

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
MessageSender::run();