<?php
abstract class EmailSender
{
	public static function sendEmail($from, $to, $subject, $body, array $attachments = array())
	{
		return Message::create($from, $to, $subject, $body, Message::TYPE_EMAIL, $attachments);
	}
}