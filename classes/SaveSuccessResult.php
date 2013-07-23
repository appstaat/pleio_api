<?php

class SaveSuccessResult extends SuccessResult {
	public $guid = 0;

	public function __construct($result, $guid = 0) {
		parent::__construct ( $result );
		if ($guid) {
			$this->guid = $guid;
		}
	}

	public function export() {
		$result = parent::export ();
		if ($this->guid) {
			$result->guid = $this->guid;
		}
		return $result;
	}
}
?>