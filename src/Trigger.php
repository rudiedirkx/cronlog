<?php

namespace rdx\cronlog;

class Trigger extends Model {
	public const TYPES_TABLE = 'triggers_types';

	public static $_table = 'triggers';

	protected function get_js_regex() : bool {
		return $this->regex[0] === '/';
	}

	protected function relate_type_ids() {
		return $this->to_many_scalar('type_id', Trigger::TYPES_TABLE, 'trigger_id');
	}

	protected function get_pretty_expect() : string {
		$expect = $this->expect;
		$num = trim($expect, ':<>');

		if ( $expect === '' ) {
			return '';
		}
		if ( $expect[0] === '<' ) {
			return "Must be less than $num";
		}
		elseif ( $expect[0] === '>' ) {
			return "Must be greater than $num";
		}
		elseif ( $expect[0] === ':' ) {
			$other = Trigger::find((int) $num);
			return "Must equal '{$other->description}'";
		}

		return "Must equal $num";
	}

	static public function presave( array &$data ) : void {
		parent::presave($data);
	}

	static public function validate( array $data ) : bool {
		self::presave($data);
		return !empty($data['description']);
	}

	/**
	 * @param ?list<int> $types
	 */
	static public function setTypes( int $id, ?array $types ) : ?bool {
		if ( $types !== null ) {
			self::$_db->delete(Trigger::TYPES_TABLE, array('trigger_id' => $id));
			$inserts = array_map(function(int $type) use ($id) : array {
				return array('trigger_id' => $id, 'type_id' => $type);
			}, array_filter((array) $types));
			return self::$_db->inserts(Trigger::TYPES_TABLE, $inserts);
		}
		return null;
	}

	public function evalNominality( int $matches, Result $result ) : ?bool {
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
		elseif ( $expect[0] === '!' ) {
			return isset($result->triggers[$num]) && $result->triggers[$num]->amount != $matches;
		}

		return $matches == $num;
	}

	public function update( array $data ) : true {
		$types = $data['type_ids'] ?? null;
		unset($data['type_ids']);

		parent::update($data);
		self::setTypes($this->id, $types);

		return true;
	}

	static public function insert( array $data ) : true {
		$types = $data['type_ids'] ?? null;
		unset($data['type_ids']);

		parent::insert($data);
		self::setTypes(self::$_db->insert_id(), $types);

		return true;
	}
}
