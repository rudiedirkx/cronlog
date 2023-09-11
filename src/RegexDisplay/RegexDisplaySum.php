<?php

namespace rdx\cronlog\RegexDisplay;

class RegexDisplaySum extends RegexDisplay {

	public function format(array $matches) : string {
		if (count($matches) > 1) {
			$sums = array_map(array_sum(...), array_slice($matches, 1));
			return count($sums) == 1 ? $sums[0] : print_r($sums, true);
		}

		return print_r($matches, true);
	}

}
