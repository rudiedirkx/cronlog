<?php

namespace rdx\cronlog\RegexDisplay;

use rdx\cronlog\Result;

class RegexDisplayTrigger extends RegexDisplay {

	static public function matchesSearchInput(string $search) : ?string {
		if (preg_match('#^trigger:(\d+)$#', $search, $match)) {
			return $match[1];
		}
		return null;
	}

	public function isSingleCapture() : bool {
		return true;
	}

	public function format(Result $result) : ?string {
		return null;
	}

	public function getGraphable(Result $result) : ?int {
		if (!isset($result->triggers[$this->pattern])) return null;
		return $result->triggers[$this->pattern]->amount;
	}

}
