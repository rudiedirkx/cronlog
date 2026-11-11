<?php

namespace rdx\cronlog;

class ResultTrigger extends Model {
	public static $_table = 'results_triggers';

	public function init() : void {
		if ( $this->nominal !== null ) {
			$this->nominal = (bool) $this->nominal;
		}
	}

}
