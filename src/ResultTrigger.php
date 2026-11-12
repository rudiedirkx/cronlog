<?php

namespace rdx\cronlog;

use db_generic_keyless_model;

class ResultTrigger extends db_generic_keyless_model {
	public static $_table = 'results_triggers';

	public function init() : void {
		if ( $this->nominal !== null ) {
			$this->nominal = (bool) $this->nominal;
		}
	}

}
