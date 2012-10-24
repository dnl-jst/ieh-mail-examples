<?php

class IehMail
{
	/**
	 * mail type: mailbox
	 */
	const MAIL_TYPE_MAILBOX = 1;

	/**
	 * mail type: redirect
	 */
	const MAIL_TYPE_REDIRECT = 2;

	/**
	 * public api url of ieh-mail.de
	 */
	const SERVICE_URL = 'https://www.ieh-mail.de/public-api/';

	/**
	 * @var string
	 */
	protected $sApiKey;

	public function __construct($sApiKey)
	{
		if (empty($sApiKey))
		{
			throw new InvalidArgumentException('api-key must be given');
		}

		$this->sApiKey = $sApiKey;
	}

	public function createMailbox($sEmail)
	{
		return $this->createMail($sEmail, self::MAIL_TYPE_MAILBOX);
	}

	public function createRedirect($sEmail, $sTarget)
	{
		return $this->createMail($sEmail, self::MAIL_TYPE_REDIRECT, $sTarget);
	}

	public function renewMail($iEmailId, $sAuthKey)
	{
		$aArguments = array(
			'email_id' => $iEmailId,
			'auth_key' => $sAuthKey
		);

		return $this->doRequest('renew-mail', $aArguments);
	}

	public function deleteMail($iEmailId, $sAuthKey)
	{
		$aArguments = array(
			'email_id' => $iEmailId,
			'auth_key' => $sAuthKey
		);

		return $this->doRequest('delete-mail', $aArguments);
	}

	public function getMessageList($iEmailId, $sAuthKey)
	{
		$aArguments = array(
			'email_id' => $iEmailId,
			'auth_key' => $sAuthKey
		);

		return $this->doRequest('get-message-list', $aArguments);
	}

	public function deleteMessage($iEmailId, $sAuthKey, $iMessageId)
	{
		$aArguments = array(
			'email_id' => $iEmailId,
			'auth_key' => $sAuthKey,
			'message_id' => $iMessageId
		);

		return $this->doRequest('delete-message', $aArguments);
	}

	protected function createMail($sEmail, $iMailType, $sMailTarget = '')
	{
		if ($iMailType === self::MAIL_TYPE_REDIRECT && empty($sMailTarget))
		{
			throw new InvalidArgumentException('mail target must be set if creating a redirect');
		}

		@list($sMailName, $sDomain) = explode('@', $sEmail);

		switch ($iMailType)
		{
			case self::MAIL_TYPE_MAILBOX:
				$sMailType = 'mailbox';
				$sMailTarget = '';
			break;

			case self::MAIL_TYPE_REDIRECT:
				$sMailType = 'redirect';
			break;

			default:
				throw new InvalidArgumentException('unknown iMailType ' . $iMailType);
		}

		$aArguments = array(
			'mail_name' => $sMailName,
			'mail_domain' => $sDomain,
			'mail_type' => $sMailType,
			'mail_target' => $sMailTarget,
			'flag_terms_and_conditions' => 'yes'
		);

		$oResponse = $this->doRequest('create-mail', $aArguments);

		return $oResponse;
	}

	protected function doRequest($sMethod, $aArguments)
	{
		$hCurl = curl_init();

		$sParams = '';

		$aArguments['api_key'] = $this->sApiKey;

		foreach ($aArguments as $sKey => $sValue)
		{
			$sParams .= $sKey . '=' . urlencode($sValue) . '&';
		}

		$sParams = rtrim($sParams, '&');

		$sUrl = self::SERVICE_URL . $sMethod . '/';

		curl_setopt($hCurl, CURLOPT_URL, $sUrl);
		curl_setopt($hCurl, CURLOPT_POST, true);
		curl_setopt($hCurl, CURLOPT_POSTFIELDS, $sParams);
		curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($hCurl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($hCurl, CURLOPT_SSL_VERIFYPEER, true);

		$sResponse = curl_exec($hCurl);

		return json_decode($sResponse);
	}

	public function isError($oResponse)
	{
		return isset($oResponse->error_code);
	}
}