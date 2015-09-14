<?php
class MailFetcher
{
	public static function fetchLastestAttachment($host, $port, $user, $pass, $indexSearchString = '') {
		$imap_stream = imap_open('{' . $host . ':' . $port . '/pop3/ssl/novalidate-cert}INBOX', $user, $pass);
		$emails = imap_search($imap_stream, $indexSearchString);
		rsort($emails);
		$attachmentContent = null;
		foreach($emails as $email_number) {
			$attachments = self::_fetchAttachment($imap_stream, $email_number);
			foreach($attachments as $attachment) {
				if($attachment['is_attachment'] === true && trim($attachment['attachment']) !== '') {
					$attachmentContent = $attachment;
					break;
				}
			}
			if($attachmentContent !== null)
				break;
		}
		imap_close($imap_stream);
		return $attachmentContent;		
	}
	
	
	private static function _fetchAttachment($imap_stream, $email_number)
	{
		/*getmailstructure*/
		$attachments = array();
		$structure = imap_fetchstructure($imap_stream, $email_number);
		if(isset($structure->parts) && count($structure->parts))
		{
			for($i=0; $i<count($structure->parts); $i++)
			{
				$attachments[$i]=array(
					'is_attachment'=>false,
					'filename'=>'',
					'name'=>'',
					'attachment'=>''
				);

				if($structure->parts[$i]->ifdparameters)
				{
					foreach($structure->parts[$i]->dparameters as $object)
					{
						if(strtolower($object->attribute)=='filename')
						{
							$attachments[$i]['is_attachment']=true;
							$attachments[$i]['filename']=$object->value;
						}
					}
				}

				if($structure->parts[$i]->ifparameters)
				{
					foreach($structure->parts[$i]->parameters as $object)
					{
						if(strtolower($object->attribute)=='name')
						{
							$attachments[$i]['is_attachment']=true;
							$attachments[$i]['name']=$object->value;
						}
					}
				}
				if($attachments[$i]['is_attachment'])
				{
					$attachments[$i]['attachment'] = imap_fetchbody($imap_stream, $email_number, $i+1);
					
					/*4 = QUOTED-PRINTABLEencoding */
					if($structure->parts[$i]->encoding==3)
					{
						$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
					}
					/*3 = BASE64encoding */
					elseif($structure->parts[$i]->encoding==4)
					{
						$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
					}
				}
			}
		}
		return $attachments;
	}
}