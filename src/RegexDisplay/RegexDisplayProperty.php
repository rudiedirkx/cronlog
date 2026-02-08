<?php

namespace rdx\cronlog\RegexDisplay;

use rdx\cronlog\Result;

class RegexDisplayProperty extends RegexDisplay {

	static public function matchesSearchInput(string $search) : ?string {
		if (preg_match('#^graph:(timing|size)$#', $search, $match)) {
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
		if ($this->pattern == 'timing') {
			$value = $result->timing;
		}
		elseif ($this->pattern == 'size') {
			$value = $result->output_size;
		}
		else {
			return null;
		}

		return [(int) $value];
	}

}
