<?php

namespace rdx\cronlog;

class Type extends Model {
	public static $_table = 'types';

	protected static $_all;

	static public function validate( array $data ) {
		self::presave($data);
		return !empty($data['description']);
	}

	static public function presave( array &$data ) {
		parent::presave($data);

		$data['handling'] = 0;
		empty($data['handling_delete']) or $data['handling'] += 1;
		empty($data['handling_notify']) or $data['handling'] += 2;
		unset($data['handling_delete'], $data['handling_notify']);

		$data['enabled'] = (int) !empty($data['enabled']);
	}

	static public function findBySubject( string $subject ) {
		if ( self::$_all === null ) {
			self::$_all = self::all(['enabled' => 1]);
		}

		foreach ( self::$_all as $type ) {
			if ( $type->matchesSubject($subject) ) {
				return $type;
			}
		}
	}

	public function matchesSubject( string $subject ) {
		return preg_match($this->subject_regex, $subject);
	}

	protected function get_handling_delete() {
		return ($this->handling & 1) > 0;
	}

	protected function get_handling_notify() {
		return ($this->handling & 2) > 0;
	}

	protected function relate_num_results() {
		return $this->to_count(Result::$_table, 'type_id');
	}

	protected function get_results() {
		return Result::all('type_id = ? ORDER BY sent DESC', [$this->id]);
	}

	protected function get_anominal_results() {
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
