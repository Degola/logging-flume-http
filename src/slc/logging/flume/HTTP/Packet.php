<?php

namespace slc\logging\flume;

/**
 * Packets which were received from the receive method.
 */
class HTTP_Packet {
	protected $type;
	protected $data;
	public function __construct($type, $data) {
		$this->type = $type;
		$this->data = $data;
	}

	/**
	 * Fetches the packet's data.
	 *
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Fetches the packet's type.
	 *
	 * @return mixed
	 */
	public function getType() {
		return $this->type;
	}
}

?>