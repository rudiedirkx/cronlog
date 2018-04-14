<?php

use rdx\cronlog\Result;
use rdx\cronlog\Type;

require 'inc.bootstrap.php';

include 'tpl.header.php';

$dates = $db->select_fields('results', "date(sent) day, concat(date(sent), ' (', count(1), ')') num", '1 GROUP BY day ORDER BY day DESC');

$date1 = $date2 = null;
if ( @$_GET['date1'] && @$_GET['date2'] ) {
	$date1 = Result::all('date(sent) = ? ORDER BY type_id, server_id', [$_GET['date1']]);
	$date2 = Result::all('date(sent) = ? ORDER BY type_id, server_id', [$_GET['date2']]);
	$typeIds = array_unique(array_column(array_merge($date1, $date2), 'type_id'));
	$types = Type::all('id IN (?) ORDER BY description', [$typeIds]);
}

$typeFilter = function(array $list, $type) {
	return array_values(array_filter($list, function(Result $result) use ($type) {
		return $result->type_id == $type;
	}));
};

$typeClass = function(array $list1, array $list2) {
	if ( array_column($list1, 'compare_info') == array_column($list2, 'compare_info') ) {
		return 'identical';
	}

	return 'different';
};

$keyByUnique = function(array $list) {
	return array_reduce($list, function($list, Result $result) {
		return $list + [$result->compare_info => $result];
	}, []);
};

?>
<style>
table.identical > thead,
tbody.identical > tr:first-child,
tbody tr.identical {
	color: green;
}
table.different > thead,
tbody.different > tr:first-child,
tbody tr.different {
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
		<button id="prev-date">&lt;</button>
	</p>
</form>

<? if ($date1 || $date2 ):
	$keyByUnique($date1);
	$keyByUnique($date2);
	$totalClass = in_array('different', array_map(function(Type $type) use ($typeFilter, $typeClass, $date1, $date2) {
		$type1 = $typeFilter($date1, $type->id);
		$type2 = $typeFilter($date2, $type->id);
		return $typeClass($type1, $type2);
	}, $types)) ? 'different' : 'identical';
	?>
	<table border="1" class="<?= $totalClass ?>">
		<thead>
			<tr>
				<th><?= html($_GET['date1']) ?> (<?= count($date1) ?>)</th>
				<th><?= html($_GET['date2']) ?> (<?= count($date2) ?>)</th>
			</tr>
		</thead>
		<? foreach ($types as $type):
			$type1 = $typeFilter($date1, $type->id);
			$type2 = $typeFilter($date2, $type->id);
			$keyed1 = $keyByUnique($type1);
			$keyed2 = $keyByUnique($type2);
			$keys = array_keys($keyed1 + $keyed2);
			natcasesort($keys);
			?>
			<tbody class="<?= $typeClass($type1, $type2) ?>">
				<tr>
					<th colspan="2" style="text-align: center"><?= html(($type1[0] ?? $type2[0])->type->description) ?></th>
				</tr>
				<? foreach ($keys as $key): ?>
					<tr class="<?= isset($keyed1[$key], $keyed2[$key]) ? 'identical' : 'different' ?>">
						<td>
							<? if (isset($keyed1[$key])): ?>
								<?= $keyed1[$key]->compare_info ?>
							<? endif ?>
						</td>
						<td>
							<? if (isset($keyed2[$key])): ?>
								<?= $keyed2[$key]->compare_info ?>
							<? endif ?>
						</td>
					</tr>
				<? endforeach ?>
			</tbody>
		<? endforeach ?>
	</table>
<? endif ?>

<script>
document.querySelector('#prev-date').addEventListener('click', function(e) {
	const selects = this.form.querySelectorAll('select');
	selects[0].selectedIndex++;
	selects[1].selectedIndex++;
});
</script>
