<?php

namespace rdx\cronlog\RegexDisplay;

use rdx\cronlog\Result;

class RegexDisplay {

	/** @var list<class-string<self>> */
	static public array $types = [
		self::class,
		RegexDisplayCount::class,
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

	public function isGraphable() : bool {
		preg_match_all($this->pattern, 'xxxx', $matches);
		return count($matches) > 1;
	}

	/**
	 * @return ?list<int>
	 */
	public function getGraphable(Result $result) : ?array {
		preg_match_all($this->pattern, $result->output, $matches, PREG_SET_ORDER);
		if (count($matches) == 1 && count($matches[0]) == 2) {
			if (is_numeric($matches[0][1])) {
				return [(int) $matches[0][1]];
			}
		}

		if (count($matches) == 1 && count($matches[0]) > 1) {
			return array_map(function(string $value) : ?int {
				return is_numeric($value) ? (int) $value : null;
			}, array_slice($matches[0], 1));
		}

		if (count($matches) > 1 && count($matches[0]) == 2) {
			return array_map(function(string $value) : ?int {
				return is_numeric($value) ? (int) $value : null;
			}, array_column($matches, 1));
		}

		return null;
	}

	/**
	 * @param array<string, list<num>> $graphData
	 * @return list<array<string, int>>
	 */
	public function getGraphs(array $graphData) : array {
		$graphs = [];
		foreach ($graphData as $date => $nums) {
			foreach (array_values($nums) as $i => $num) {
				$graphs[$i][$date] = $num;
			}
		}
		return $graphs;
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
