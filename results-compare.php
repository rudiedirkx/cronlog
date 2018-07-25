<?php

use rdx\cronlog\Result;
use rdx\cronlog\Type;

require 'inc.bootstrap.php';

include 'tpl.header.php';

$dates = $db->select_fields('results', "date(sent) day, concat(date(sent), ' (', count(1), ')') num", '1 GROUP BY day ORDER BY day DESC');

$date1 = $date2 = [];
if ( @$_GET['date1'] && @$_GET['date2'] ) {
	$date1 = Result::all('date(sent) = ? ORDER BY type_id, server_id', [$_GET['date1']]);
	$date2 = Result::all('date(sent) = ? ORDER BY type_id, server_id', [$_GET['date2']]);
}

$types = Type::all('1 ORDER BY description');

$groups = [];
foreach ( $date1 as $result ) {
	$groups[$result->type_id][$result->relevant_subject][] = [$result];
}
foreach ( $date2 as $result ) {
	foreach ( $groups[$result->type_id][$result->relevant_subject] ?? [] as $n => $group ) {
		if ( count($group) == 1 && $result->sentTimeAlmostMatches($group[0]) ) {
			$groups[$result->type_id][$result->relevant_subject][$n][1] = $result;
			continue 2;
		}
	}

	$groups[$result->type_id][$result->relevant_subject][] = [1 => $result];
}

uksort($groups, function($a, $b) use ($types) {
	return array_search($a, array_keys($types)) - array_search($b, array_keys($types));
});
foreach ( $groups as $typeId => $typeGroup ) {
	$regroup = [];
	foreach ( $typeGroup as $subject => $subjectGroup ) {
		$regroup = array_merge($regroup, array_values($subjectGroup));
	}
	usort($regroup, function($a, $b) {
		$a = reset($a);
		$b = reset($b);
		return strcmp($a->sent_time, $b->sent_time) ?: strcasecmp($a->relevant_subject, $b->relevant_subject);
	});
	$groups[$typeId] = $regroup;
}

$timeGroupIdentical = function(array $timeGroup) {
	return count($timeGroup) == 2;
};

$typeGroupIdentical = function(array $typeGroup) use ($timeGroupIdentical) {
	return count($typeGroup) == count(array_filter($typeGroup, $timeGroupIdentical));
};

$allGroupsIdentical = function(array $groups) use ($typeGroupIdentical) {
	return count($groups) == count(array_filter($groups, $typeGroupIdentical));
};

?>
<style>
thead a {
	color: inherit;
}

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
	?>
	<table border="1" class="<?= $allGroupsIdentical($groups) ? 'identical' : 'different' ?>">
		<thead>
			<tr>
				<th>
					<a href="results.php?date=<?= html($_GET['date1']) ?>"><?= html($_GET['date1']) ?></a>
					(<?= count($date1) ?>)
				</th>
				<th>
					<a href="results.php?date=<?= html($_GET['date2']) ?>"><?= html($_GET['date2']) ?></a>
					(<?= count($date2) ?>)
				</th>
			</tr>
		</thead>
		<? foreach ($groups as $typeId => $typeGroup): ?>
			<tbody class="<?= $typeGroupIdentical($typeGroup) ? 'identical' : 'different' ?>">
				<tr>
					<th colspan="2" style="text-align: center"><?= html($types[$typeId]->description) ?></th>
				</tr>
				<? foreach ($typeGroup as $subject => $timeGroup): ?>
					<tr class="<?= $timeGroupIdentical($timeGroup) ? 'identical' : 'different' ?>">
						<td>
							<? if (isset($timeGroup[0])): ?>
								<?= $timeGroup[0]->compare_info ?>
							<? endif ?>
						</td>
						<td>
							<? if (isset($timeGroup[1])): ?>
								<?= $timeGroup[1]->compare_info ?>
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
