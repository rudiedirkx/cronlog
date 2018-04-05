<?php

use rdx\cronlog\Result;

require 'inc.bootstrap.php';

include 'tpl.header.php';

$dates = $db->select_fields('results', "date(sent) day, concat(date(sent), ' (', count(1), ')') num", '1 GROUP BY day ORDER BY day DESC');

$date1 = $date2 = null;
if ( @$_GET['date1'] && @$_GET['date2'] ) {
	$date1 = Result::all('date(sent) = ? ORDER BY type_id, server_id', [$_GET['date1']]);
	$date2 = Result::all('date(sent) = ? ORDER BY type_id, server_id', [$_GET['date2']]);
	$types = array_unique(array_column(array_merge($date1, $date2), 'type_id'));
	sort($types, SORT_NUMERIC);
}

$typeFilter = function(array $list, $type) {
	return array_values(array_filter($list, function(Result $result) use ($type) {
		return $result->type_id == $type;
	}));
};

$typeClass = function(array $list1, array $list2) {
	if ( count($list1) != count($list2) ) {
		return 'different';
	}

	if ( array_column($list1, 'compare_info') == array_column($list2, 'compare_info') ) {
		return 'identical';
	}

	return 'equal';
};

?>
<style>
tbody.identical {
	color: green;
}
tbody.equal {
	color: blue;
}
tbody.different {
	color: red;
}
</style>

<form method="get" action>
	<p>
		Compare
		<select name="date1"><?= html_options($dates, @$_GET['date1']) ?></select>
		vs
		<select name="date2"><?= html_options($dates, @$_GET['date2']) ?></select>
		&nbsp;
		<button>Compare</button>
	</p>
</form>

<? if ($date1 || $date2 ): ?>
	<table border="1">
		<thead>
			<tr>
				<th><?= html($_GET['date1']) ?></th>
				<th><?= html($_GET['date2']) ?></th>
			</tr>
		</thead>
		<? foreach ($types as $type):
			$type1 = $typeFilter($date1, $type);
			$type2 = $typeFilter($date2, $type);
			$max = max(count($type1), count($type2));
			?>
			<tbody class="<?= $typeClass($type1, $type2) ?>">
				<tr>
					<th colspan="2" style="text-align: center"><?= html(($type1[0] ?? $type2[0])->type->description) ?></th>
				</tr>
				<? for ($i=0; $i < $max; $i++): ?>
					<tr>
						<td>
							<? if (isset($type1[$i])): ?>
								<?= $type1[$i]->compare_info ?>
							<? endif ?>
						</td>
						<td>
							<? if (isset($type2[$i])): ?>
								<?= $type2[$i]->compare_info ?>
							<? endif ?>
						</td>
					</tr>
				<? endfor ?>
			</tbody>
		<? endforeach ?>
	</table>
<? endif ?>
