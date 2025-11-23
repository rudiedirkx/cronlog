<?php

namespace rdx\cronlog\RegexDisplay;

use rdx\cronlog\Result;

class RegexDisplayCount extends RegexDisplay {

	static public function matchesSearchInput(string $search) : ?string {
		if (preg_match('#^count(/.+/[a-z]*)$#', $search, $match)) {
			return $match[1];
		}
		return null;
	}

	public function format(Result $result) : ?string {
		return preg_match_all($this->pattern, $result->output, $matches);
	}

	public function getGraphable(Result $result) : ?array {
		return [preg_match_all($this->pattern, $result->output, $matches)];
	}

}
