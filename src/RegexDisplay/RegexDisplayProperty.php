<?php

namespace rdx\cronlog\RegexDisplay;

use rdx\cronlog\Result;

class RegexDisplayProperty extends RegexDisplay {

	static public function matchesSearchInput(string $search) : ?string {
		if (preg_match('#^graph:(timing)$#', $search, $match)) {
			return $match[1];
		}
		return null;
	}

	public function isGraphable() : bool {
		return true;
	}

	public function format(Result $result) : ?string {
		return null;
	}

	public function getGraphable(Result $result) : ?array {
		return [(int) $result->timing];
	}

}
