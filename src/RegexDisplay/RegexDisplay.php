<?php

namespace rdx\cronlog\RegexDisplay;

class RegexDisplay {

	static public $types = [
		'default' => self::class,
		'sum' => RegexDisplaySum::class,
	];

	static public function make(string $type) : ?self {
		if (!isset(self::$types[$type])) return null;

		$class = self::$types[$type];
		return new $class();
	}

	public function format(array $matches) : string {
		if (count($matches) == 2 && count($matches[1]) == 1) {
			return trim($matches[1][0]);
		}

		if (count($matches[0]) == 0) {
			return '-';
		}

		if (count($matches) == 2) {
			$matches = $matches[1];
		}
		elseif (count($matches) > 2) {
			array_shift($matches);
		}

		return print_r($matches, true);
	}

}
