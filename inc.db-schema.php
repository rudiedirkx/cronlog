<?php

return array(
	'version' => 15,
	'tables' => array(
		'types' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'type',
				'description',
				'to_regex',
				'subject_regex',
			),
			'indexes' => array(
				'type' => array(
					'unique' => true,
					'columns' => array('type'),
				),
			),
		),
		'triggers' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'type_id' => array('type' => 'int'),
				'trigger',
				'description',
				'regex',
				'color',
				'o' => array('type' => 'int', 'default' => 0),
			),
			'indexes' => array(
				'trigger' => array(
					'unique' => true,
					'columns' => array('trigger'),
				),
			),
		),
		'servers' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'name',
				'from_regex',
			),
		),
		'results' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'sent' => array('type' => 'datetime'),
				'from',
				'server_id' => array('type' => 'int'),
				'to',
				'type_id' => array('type' => 'int'),
				'subject',
				'output',
			),
		),
		'results_triggers' => array(
			'columns' => array(
				'trigger_id' => array('type' => 'int'),
				'result_id' => array('type' => 'int'),
				'amount' => array('type' => 'int'),
			),
			'indexes' => array(
				'trigger_result' => array(
					'unique' => true,
					'columns' => array('trigger_id', 'result_id'),
				),
			),
		),
	),
);
