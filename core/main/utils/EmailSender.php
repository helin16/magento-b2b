<?php
abstract class EmailSender
{
	public static function addEmail($from, $to, $subject, $body, array $attachments = array())
	{
		return Message::create($from, $to, $subject, $body, Message::TYPE_EMAIL, $attachments);
	}
	
	public static function sendEmail($from, $to, $subject, $body, array $attachmentAssetIds = array())
	{
		$settings = json_decode(SystemSettings::getSettings(SystemSettings::TYPE_EMAIL_SENDING_SERVER), true);
		var_dump($settings);
		//Create a new PHPMailer instance
		$mail = new PHPMailer;
		//Tell PHPMailer to use SMTP
		$mail->isSMTP();
		$mail->isHTML(true);
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = isset($settings['SMTPDebug']) ? $settings['SMTPDebug'] : 2;
		//Ask for HTML-friendly debug output
		$mail->Debugoutput = isset($settings['debugOutput']) ? $settings['debugOutput'] : 'html';
		//Set the hostname of the mail server
		$mail->Host = isset($settings['host']) ? $settings['host'] : "";
		//Set the SMTP port number - likely to be 25, 465 or 587
		$mail->Port = isset($settings['port']) ? $settings['port'] : 25;
		//Whether to use SMTP authentication
		$mail->SMTPAuth = isset($settings['SMTPAuth']) ? $settings['SMTPAuth'] : true;
		//Username to use for SMTP authentication
		$mail->Username = isset($settings['username']) ? $settings['username'] : "";
		//Password to use for SMTP authentication
		$mail->Password = isset($settings['password']) ? $settings['password'] : "";
		//Set who the message is to be sent from
		$mail->setFrom($from);
		//Set an alternative reply-to address
		//$mail->addReplyTo('replyto@example.com', 'First Last');
		//Set who the message is to be sent to
		$mail->addAddress($to);
		//Set the subject line
		$mail->Subject = $subject;
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
		//Replace the plain text body with one created manually
		//$mail->AltBody = 'This is a plain-text message body';
		//html body
		$mail->Body = $body;
		
		if(count($attachmentAssetIds) > 0) {
			foreach(Asset::getAllByCriteria('assetId in (' . implode(', ', array_fill(0, count($attachmentAssetIds), '?')) . ')', $attachmentAssetIds) as $asset) {
				//Attach an image file
				$mail->addAttachment($asset->getPath(), $asset->getFilename());
			}
		}
		
		//send the message, check for errors
		if (!$mail->send()) {
		    throw new CoreException('SENDING EMAIL ERROR: ' .$mail->ErrorInfo);
		}
		return true;
	}
}