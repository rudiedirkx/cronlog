<?php

return array(
	'version' => 6,
	'tables' => array(
		'types' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'type',
				'description',
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
		'results' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'origin',
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
