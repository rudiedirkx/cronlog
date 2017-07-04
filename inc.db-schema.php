<?php

return array(
	'version' => 9,
	'tables' => array(
		'types' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'type',
				'description',
				'to_regex',
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
				'from',
				'server_id' => array('type' => 'int'),
				'to',
				'type_id' => array('type' => 'int'),
				'subject',
				'output',
			),
		),
		'results_types' => array(
			'columns' => array(
				'type_id' => array('type' => 'int'),
				'result_id' => array('type' => 'int'),
			),
			'indexes' => array(
				'type_result' => array(
					'unique' => true,
					'columns' => array('type_id', 'result_id'),
				),
			),
		),
	),
);
