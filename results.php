<?php

use rdx\cronlog\data\Result;
use rdx\cronlog\data\Type;

require 'inc.bootstrap.php';

$type = Type::find(@$_GET['type']);

$results = $type ? $type->results : Result::all('1 ORDER BY sent DESC');

if ( isset($_GET['recollate']) ) {
	foreach ( $results as $result ) {
		$result->collate();
	}

	$query = $type ? "?type={$type->id}" : '';
	return do_redirect('results.php' . $query);
}

include 'tpl.header.php';

$ids = array_flip(array_values($db->select_fields(Result::$_table, 'id', '1 ORDER BY sent DESC')));

?>

<h2>
	Results
	<? if ($type): ?>
		for <em><?= html($type->description) ?></em>
	<? endif ?>
	(<?= count($results) ?>)
</h2>

<table>
	<thead>
		<tr>
			<th align="right">#</th>
			<? if (!$type): ?>
				<th>Type</th>
			<? endif ?>
			<th>Subject</th>
			<th>Origin</th>
			<th>Date/time</th>
			<th align="center">?</th>
			<th>Size</th>
			<? if ($type): ?>
				<? foreach ($type->triggers as $trigger): ?>
					<th style="color: <?= html($trigger->color) ?>" title="<?= html($trigger->regex) ?>"><?= html($trigger->description) ?></th>
				<? endforeach ?>
			<? endif ?>
			<th><a href="?type=<?= @$type->id ?>&recollate">Recollate</a></th>
			<th>/day</th>
		</tr>
	</thead>
	<tbody>
		<? $prevDate = $prevBatch = null;
		$batch = 1;
		foreach ($results as $result):
			$newSection = $prevDate && substr($result->sent, 0, 10) != $prevDate;
			$prevDate = substr($result->sent, 0, 10);
			$newBatch = $prevBatch && $result->batch != $prevBatch;
			$prevBatch = $result->batch;
			$batch += $newBatch;
			?>
			<tr class="<?= $newSection ? 'next-section' : '' ?> <?= $batch % 2 == 0 ? 'even-section' : 'odd-section' ?>">
				<th align="right"><?= $ids[$result->id]+1 ?></th>
				<? if (!$type): ?>
					<td><a href="?type=<?= $result->type_id ?>"><?= html($result->type->description) ?></a></td>
				<? endif ?>
				<td><?= html($result->relevant_subject) ?></td>
				<td><?= html($result->server ?: '?') ?></td>
				<td><a title="Batch: <?= date('Y-m-d H:i:s', $result->batch) ?>" href="result.php?id=<?= $result->id ?>"><?= get_datetime($result->sent) ?></a></td>
				<td align="center">
					<? if ($result->nominal !== null): ?>
						<img src="<?= $result->nominal ? 'yes' : 'no' ?>.gif" />
					<? endif ?>
				</td>
				<td><?= number_format($result->output_size, 0) ?></td>
				<? if ($type): ?>
					<? foreach ($type->triggers as $trigger):
						$amount = $result->triggeredAmount($trigger->id);
						?>
						<td <? if ($amount > 0): ?>style="font-weight: bold; color: <?= html($trigger->color) ?>"<? endif ?>>
							<?= $amount ?>
						</td>
					<? endforeach ?>
				<? endif ?>
				<td><a href="result.php?id=<?= $result->id ?>&recollate&goto=results.php?type=<?= $type->id ?>">recollate</a></td>
			</tr>
		<? endforeach ?>
	</tbody>
</table>

<script>
(function() {
	var sectionFirst = null;
	var prev = null;
	var sectionSize = 0;
	var cell;
	[].forEach.call(document.querySelectorAll('tbody tr'), function(tr) {
		if (!sectionFirst) sectionFirst = tr;

		if (tr.classList.contains('next-section')) {
			cell = sectionFirst.insertCell(sectionFirst.cells.length);
			cell.textContent = sectionSize;
			if (prev != sectionFirst) {
				cell = prev.insertCell(prev.cells.length);
				cell.textContent = sectionSize;
			}
			sectionSize = 0;
			sectionFirst = tr;
		}

		sectionSize++;
		prev = tr;
	});
})();
</script>
