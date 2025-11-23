<?php

namespace rdx\cronlog\RegexDisplay;

use rdx\cronlog\Result;

class RegexDisplaySum extends RegexDisplay {

	static public function matchesSearchInput(string $search) : ?string {
		if (preg_match('#^sum(/.+/[a-z]*)$#', $search, $match)) {
			return $match[1];
		}
		return null;
	}

	public function format(Result $result) : ?string {
		preg_match_all($this->pattern, $result->output, $matches);
		if (count($matches) > 1) {
			$sums = array_map(array_sum(...), array_slice($matches, 1));
			return count($sums) == 1 ? $sums[0] : print_r($sums, true);
		}

		return trim(print_r($matches, true));
	}

	public function getGraphable(Result $result) : ?array {
		preg_match_all($this->pattern, $result->output, $matches);
		if (count($matches) == 2 && is_numeric($matches[1][0] ?? '')) {
			return [(int) array_sum($matches[1])];
		}
		return null;
	}

}
