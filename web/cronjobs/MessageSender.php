<?php
abstract class MessageSender
{
	public function run()
	{
		$start = self::_logMsg("== START: processing Messages ==");
		$messages = self::_getAndMarkMessages();
		self::_logMsg("GOT " . count($messages) . ' Message(s): ');
		foreach	($messages as $message){
			self::_logMsg("    Looping message(ID=" . $message->getId() . ': ');
			try {
				Dao::beginTransaction();
				EmailSender::sendEmail($message->getFrom(), $message->getTo(), $message->getSubject(), $message->getBody(), $message->getAttachAssetIds());
				$message->setStatus(Message::STATUS_SENT)
					->save();
				Dao::commitTransaction();
				
				self::_logMsg("    SUCCESS sending message(ID=" . $message->getId() . ': ' . $ex->getMessage());
			} catch(Exception $ex) {
				Dao::rollbackTransaction();
				$message->setStatus(Message::STATUS_FAILED)
					->save();
				self::_logMsg("    ERROR sending message(ID=" . $message->getId() . ': ' . $ex->getMessage());
				self::_logMsg("    ERROR sending message(ID=" . $message->getId() . ': ' . $ex->getTraceAsString());
			}
		}
	}
	private static function _logMsg($msg, $className, $funcName) {
		$now = new UDate();
		echo trim($now) . '(UTC)::' . $className . '::' . $funcName . ': ' . $msg . "\n";
		return $now;
	}
	private static function _getAndMarkMessages()
	{
		$randId = StringUtilsAbstract::getRandKey();
		Message::updateByCriteria('transId = ? and status = ?', 'active = 1 and type = ? and status = ?', array($randId, Message::STATUS_SENDING, Message::TYPE_EMAIL, Message::STATUS_NEW));
		return Message::getAllByCriteria('transId = ? and type = ?', array($randId, Message::STATUS_SENDING));
	}
}

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
MessageSender::run();