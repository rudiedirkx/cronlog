<?php

namespace rdx\cronlog\RegexDisplay;

use rdx\cronlog\Result;

class RegexDisplay {

	/** @var list<class-string<self>> */
	static public array $types = [
		self::class,
		RegexDisplaySum::class,
		RegexDisplayTrigger::class,
	];

	public function __construct(
		public string $pattern,
	) {}

	public function format(Result $result) : ?string {
		preg_match_all($this->pattern, $result->output, $matches);
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

		return trim(print_r($matches, true));
	}

	public function isSingleCapture() : bool {
		preg_match_all($this->pattern, 'xxxx', $matches);
		return count($matches) === 2;
	}

	public function getGraphable(Result $result) : ?int {
		preg_match_all($this->pattern, $result->output, $matches);
		if (count($matches) == 2 && count($matches[1]) == 1) {
			if (is_numeric($matches[1][0])) {
				return (int) $matches[1][0];
			}
		}

		return null;
	}

	static public function fromSearchInput(string $search) : ?self {
		foreach (self::$types as $class) {
			if ($pattern = $class::matchesSearchInput($search)) {
				return new $class($pattern);
			}
		}
		return null;
	}

	static public function matchesSearchInput(string $search) : ?string {
		if (preg_match('#^(/.+/[a-z]*)$#', $search, $match)) {
			return $search;
		}
		return null;
	}

}
