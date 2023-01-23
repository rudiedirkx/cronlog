<?php

namespace rdx\cronlog;

use DateTime;

class Result extends Model {

	const RESULT_TIMING_MARGIN_MINS = 55;
	const OUTPUT_DATE_REGEX = '(\w+ \w+ \d+ \d\d+:\d\d+:\d\d+ \w+ \d{4}|\w+ \d+ \w+ \d{4} \d\d:\d\d:\d\d (AM|PM) \w+)';

	public static $_table = 'results';

	public function init() {
		if ( $this->nominal !== null ) {
			$this->nominal = (bool) $this->nominal;
		}
	}

	protected function get_compare_info() {
		$subject = $this->relevant_subject ? " ({$this->relevant_subject})" : '';
		return "{$this->sent_time}{$subject}";
	}

	protected function get_sent_utc() {
		return strtotime($this->sent);
	}

	protected function get_sent_time() {
		return date('H:i', $this->sent_utc);
	}

	protected function get_relevant_subject() {
		return $this->subject_subject;
	}

	protected function get_subject_subject() {
		if ( $this->type->subject_regex && preg_match($this->type->subject_regex, $this->subject, $match) ) {
			if ( isset($match[1]) ) {
				return $match[1];
			}
		}
	}

	protected function get_generic_subject() {
		return trim(preg_replace('#^Cron\s+<.+?>\s+#i', '', $this->subject));
	}

	protected function relate_type() {
		return $this->to_one(Type::class, 'type_id');
	}

	protected function relate_server() {
		return $this->to_one(Server::class, 'server_id');
	}

	protected function relate_triggers() {
		return $this->to_many(ResultTrigger::class, 'result_id')->key('trigger_id');
	}

	public function sentTimeAlmostMatches( self $result ) {
		$a = date('H:i', strtotime('+12 hours', $this->sent_utc));
		$b = date('H:i', strtotime('+12 hours', $result->sent_utc));
		if ($a == $b) {
			return true;
		}

		$diff = (new DateTime($a))->diff(new DateTime($b), true);
		$mins = $diff->h * 60 + $diff->i;
		return $mins <= self::RESULT_TIMING_MARGIN_MINS;
	}

	public function delete() {
		ResultTrigger::deleteAll(array('result_id' => $this->id));
		return parent::delete();
	}

	public function retype() {
		$type = Type::findBySubject($this->subject);
		if ( $type ) {
			$this->update(['type_id' => $type->id]);
			$this->type = $type;
			return true;
		}

		return false;
	}

	public function collate() {
		$none = [0, 0, 0];
		if ( !$this->type ) return $none;
		// if ( !$this->type->triggers ) return $none;
		if ( !$this->output ) return $none;

		self::$_db->begin();

		$timing = 0;
		if ($utc1 = self::parseStartDate($this->output)) {
			if ($utc2 = self::parseEndDate($this->output)) {
				$timing = max(1, $utc2 - $utc1);
			}
		}

		ResultTrigger::deleteAll(array('result_id' => $this->id));
		$this->triggers = [];

		$inserts = array();
		$nominals = [0, 0];
		foreach ( $this->type->triggers as $trigger ) {
			$matches = preg_match_all($trigger->regex, $this->output);
			$nominal = $trigger->evalNominality($matches, $this);
			if ( $nominal !== null ) {
				$nominals[(int) $nominal]++;
			}

			$inserts[] = array(
				'trigger_id' => $trigger->id,
				'result_id' => $this->id,
				'amount' => $matches,
				'nominal' => $nominal === null ? null : (int) $nominal,
			);
			$this->triggers[$trigger->id] = (object) end($inserts);
		}
		ResultTrigger::insertAll($inserts);

		$nominal = !$nominals[0] && !$nominals[1] ? null : (int) ($nominals[0] == 0);

		$update = [
			'nominal' => $nominal,
			'timing' => $timing,
		];

		if ( $server = Server::findByFrom($this->from) ) {
			$update['server_id'] = $server->id;
		}

		if ( count($this->type->triggers) && $nominal === 1 && $this->type->handling_delete ) {
			$update['output'] = '';
		}

		$this->update($update);

		self::$_db->commit();

		return [count($inserts), $nominals[1], $nominals[0]];
	}

	public function triggered( $triggerId ) {
		$triggers = $this->triggers;
		if ( isset($triggers[$triggerId]) )  {
			$trigger = $triggers[$triggerId];
			return [$trigger->amount, $trigger->nominal];
		}

		return ['?', false];
	}

	static protected function parseStartDate(string $output) : int {
		return self::parseTimingDate($output, '^' . self::OUTPUT_DATE_REGEX);
	}

	static protected function parseEndDate(string $output) : int {
		return self::parseTimingDate($output, self::OUTPUT_DATE_REGEX . '$');
	}

	static protected function parseTimingDate(string $output, string $regex) : int {
		if (preg_match("#$regex#", $output, $match)) {
			$utc = strtotime($match[1]);
			if ($utc && $utc > strtotime('2001-01-01')) {
				return $utc;
			}
		}

		return 0;
	}

}
