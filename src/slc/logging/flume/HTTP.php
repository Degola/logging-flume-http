<?php

namespace slc\logging\flume;

/**
 *
 * User: Sebastian Lagemann <sl@honeytracks.com>
 * Date: 13.11.2012
 * Time: 15:40
 *
 * Small and absolutely simple flume http driver class which allows writing to a flume http source
 */

class HTTP {
	const DEBUG = false;
	protected $config = null;
	protected $Connection = null;

	public function __construct(array $config) {
		$this->config = (object) $config;

		if(!isset($this->config->Url))
			throw new HTTP_Exception('CONFIGURATION_MISMATCH', array('Configuration' => $this->config));

		if(!isset($this->config->SocketConnectTimeout)) {
			$this->config->SocketConnectTimeout = 50;
		}
		if(!isset($this->config->Timeout)) {
			$this->config->Timeout = 30;
		}
		if(!isset($this->config->DeliveryTries)) {
			$this->config->DeliveryTries = 3;
		}
	}

	protected function callHTTP($data) {
		$data = json_encode($data);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->config->Url);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_TIMEOUT_MS, $this->config->Timeout);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, $this->config->SocketConnectTimeout);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, 1.1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: '.strlen($data)
		));

		$content = curl_exec($curl);
		$error = curl_error($curl);
		if($error != '') {
			throw new HTTP_Exception('REQUEST_FAILED', array('response' => $content));
		}
		curl_close($curl);
		return $content;
	}
	public function addLogEntry($data, $timestamp = null, array $headers = array()) {
		if(is_null($timestamp))
			$timestamp = time();

		$exception = null;
		for($i = 0; $i < $this->config->DeliveryTries; $i++) {
			try {
				$result = $this->callHTTP(array(array(
					'headers' => array_merge(array('timestamp' => $timestamp), $headers),
					'body' => json_encode($data)
				)));
				return new HTTP_Packet('ok', $result);
			} catch (\Exception $exception) {
			}
		}
		return new HTTP_Packet('error', $exception->getMessage());

	}

}

?>