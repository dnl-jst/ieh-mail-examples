<?php

require_once('IehMail.php');

$sMyApiKey = 'my-api-key';

$oIehMail = new IehMail($sMyApiKey);

# create an email address, type mailbox
$oFirstResponse = $oIehMail->createMailbox(mt_rand(10000, 99999) . '@ieh-mail.de');

if (!$oIehMail->isError($oFirstResponse))
{
	echo 'ieh-Mail (mailbox) created (email-id: ' . $oFirstResponse->email_id . ', auth-key: ' . $oFirstResponse->auth_key . ')' . chr(10);
}
else
{
	echo 'ieh-Mail (mailbox) creation failed' . chr(10);
}

# create an email address, type redirect
$oSecondResponse = $oIehMail->createMailbox(mt_rand(10000, 99999) . '@ieh-mail.de', 'support@sitebench.de');

if (!$oIehMail->isError($oSecondResponse))
{
	echo 'ieh-Mail (redirect) created (email-id: ' . $oSecondResponse->email_id . ', auth-key: ' . $oSecondResponse->auth_key . ')' . chr(10);
}
else
{
	echo 'ieh-Mail (redirect) creation failed' . chr(10);
}

$oThirdResponse = $oIehMail->renewMail($oFirstResponse->email_id, $oFirstResponse->auth_key);

if (!$oIehMail->isError($oThirdResponse))
{
	echo 'renewed ieh-mail' . chr(10);
}
else
{
	echo 'renew ieh-Mail failed' . chr(10);
}

$oForthResponse = $oIehMail->getMessageList($oSecondResponse->email_id, $oSecondResponse->auth_key);

if (!$oIehMail->isError($oForthResponse))
{
	if ($oForthResponse->message_count == 0)
	{
		echo 'Keine Nachrichten vorhanden!' . chr(10);
	}
	else
	{
		foreach ($oForthResponse->messages as $oMessage)
		{
			echo 'message "' . $oMessage->subject , '" from "' . $oMessage->from . chr(10);
		}
	}
}
else
{
	echo 'getting message list failed' . chr(10);
}

$oIehMail->deleteMail($oFirstResponse->email_id, $oFirstResponse->auth_key);
$oIehMail->deleteMail($oSecondResponse->email_id, $oSecondResponse->auth_key);