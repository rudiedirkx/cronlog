<?php

namespace rdx\cronlog;

class Type extends Model {
	public static $_table = 'types';

	/** @var list<self> */
	protected static array $_all;

	static public function validate( array $data ) : bool {
		self::presave($data);
		return !empty($data['description']);
	}

	static public function presave( array &$data ) {
		parent::presave($data);

		$data['handling'] = 0;
		if (!empty($data['handling_delete'])) $data['handling'] += 1;
		if (!empty($data['handling_notify'])) $data['handling'] += 2;
		unset($data['handling_delete'], $data['handling_notify']);

		$data['enabled'] = (int) !empty($data['enabled']);
	}

	static public function findBySubject( string $subject ) : ?self {
		self::$_all ??= self::all(['enabled' => 1]);

		foreach ( self::$_all as $type ) {
			if ( $type->matchesSubject($subject) ) {
				return $type;
			}
		}
		return null;
	}

	public function matchesSubject( string $subject ) : bool {
		return preg_match($this->subject_regex, $subject) > 0;
	}

	protected function get_handling_delete() : bool {
		return ($this->handling & 1) > 0;
	}

	protected function get_handling_notify() : bool {
		return ($this->handling & 2) > 0;
	}

	protected function relate_num_results() {
		return $this->to_count(Result::$_table, 'type_id');
	}

	/**
	 * @return Result[]
	 */
	protected function get_results() : array {
		return Result::all('type_id = ? ORDER BY sent DESC', [$this->id]);
	}

	/**
	 * @return Result[]
	 */
	protected function get_anominal_results() : array {
		return Result::all("nominal = '0' AND type_id = ? ORDER BY sent DESC", [$this->id]);
	}

	protected function relate_trigger_ids() {
		return $this->to_many_scalar('trigger_id', Trigger::TYPES_TABLE, 'type_id');
	}

	protected function relate_triggers() {
		return $this->to_many_through(Trigger::class, 'trigger_ids')->order('o, trigger');
	}

	public function __toString() {
		return (string) $this->description;
	}
}
