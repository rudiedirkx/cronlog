<?php

namespace rdx\cronlog;

class Type extends Model {
	public static $_table = 'types';

	protected static $_all;

	static public function validate( array $data ) {
		self::presave($data);
		return !empty($data['type']);
	}

	static public function presave( &$data ) {
		parent::presave($data);

		$data['handling'] = 0;
		empty($data['handling_delete']) or $data['handling'] += 1;
		empty($data['handling_notify']) or $data['handling'] += 2;
		unset($data['handling_delete'], $data['handling_notify']);

		$data['enabled'] = (int) !empty($data['enabled']);
	}

	static public function findByToAndSubject( $address, $subject ) {
		if ( self::$_all === null ) {
			self::$_all = self::all(['enabled' => 1]);
		}

		foreach ( self::$_all as $type ) {
			if ( $type->matchesToAndSubject($address, $subject) ) {
				return $type;
			}
		}
	}

	public function matchesToAndSubject( $to, $subject ) {
		if ( !$this->to_regex && !$this->subject_regex ) {
			return  false;
		}

		$to = !$this->to_regex || preg_match($this->to_regex, $to);
		$subject = !$this->subject_regex || preg_match($this->subject_regex, $subject);

		return $to &&  $subject;
	}

	protected function get_handling_delete() {
		return ($this->handling & 1) > 0;
	}

	protected function get_handling_notify() {
		return ($this->handling & 2) > 0;
	}

	protected function get_trigger_ids() {
		return self::$_db->select_fields(Trigger::TYPES_TABLE, 'trigger_id', array('type_id' => $this->id));
	}

	protected function get_num_results() {
		return self::$_db->count(Result::$_table, array('type_id' => $this->id));
	}

	protected function get_results() {
		return Result::all('type_id = ? ORDER BY sent DESC', array($this->id));
	}

	protected function get_triggers() {
		return $this->trigger_ids ? Trigger::all('id IN (?) ORDER BY o, trigger', array($this->trigger_ids)) : array();
	}

	public function __toString() {
		return (string) $this->description;
	}
}
