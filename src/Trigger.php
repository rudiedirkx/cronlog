<?php

namespace rdx\cronlog;

class Trigger extends Model {
	const TYPES_TABLE = 'triggers_types';

	public static $_table = 'triggers';

	protected function get_js_regex() {
		return $this->regex[0] === '/';
	}

	protected function get_type_ids() {
		return self::$_db->select_fields(Trigger::TYPES_TABLE, 'type_id', array('trigger_id' => $this->id));
	}

	protected function get_pretty_expect() {
		$expect = $this->expect;
		$num = trim($expect, ':<>');

		if ( $expect[0] === '<' ) {
			return "Must be less than $num";
		}
		elseif ( $expect[0] === '>' ) {
			return "Must be greater than $num";
		}
		elseif ( $expect[0] === ':' ) {
			$other = Trigger::find($num);
			return "Must equal '{$other->description}'";
		}

		return "Must equal $num";
	}

	static public function presave( array &$data ) {
		parent::presave($data);
	}

	static public function validate( array $data ) {
		self::presave($data);
		return !empty($data['trigger']);
	}

	static public function setTypes( $id, $types ) {
		if ( $types !== null ) {
			self::$_db->delete(Trigger::TYPES_TABLE, array('trigger_id' => $id));
			$inserts = array_map(function($type) use ($id) {
				return array('trigger_id' => $id, 'type_id' => $type);
			}, array_filter((array) $types));
			return self::$_db->inserts(Trigger::TYPES_TABLE, $inserts);
		}
	}

	public function evalNominality( $matches, Result $result ) {
		if ( trim($this->expect) === '' ) return null;

		$expect = $this->expect;
		$num = trim($expect, ':<>');

		if ( $expect[0] === '<' ) {
			return $matches < $num;
		}
		elseif ( $expect[0] === '>' ) {
			return $matches > $num;
		}
		elseif ( $expect[0] === ':' ) {
			return isset($result->triggers[$num]) && $result->triggers[$num]->amount == $matches;
		}

		return $matches == $num;
	}

	public function update( $data ) {
		$types = null;
		if ( is_array($data) ) {
			$types = @$data['type_ids'];
			unset($data['type_ids']);
		}

		$ok = parent::update($data);
		self::setTypes($this->id, $types);

		return $ok;
	}

	static public function insert( array $data ) {
		$types = @$data['type_ids'];
		unset($data['type_ids']);

		$id = parent::insert($data);
		self::setTypes($id, $types);

		return $id;
	}
}
