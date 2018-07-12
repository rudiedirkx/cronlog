<?php

use rdx\cronlog\Result;
use rdx\cronlog\Server;
use rdx\cronlog\Type;

require 'inc.bootstrap.php';

$type = Type::find(@$_GET['type']);
$server = Server::find(@$_GET['server']);
$date = @$_GET['date'];

$anominal = !empty($_GET['anominal']);
$resultsProp = $anominal ? 'anominal_results' : 'results';
$resultsSql = ($anominal ? "nominal = '0'" : '1') . ' AND ' . ($date ? $db->replaceholders("date(sent) = ?", [$date]) : '1');

$results = $type ? $type->$resultsProp : ($server ? $server->$resultsProp : Result::all("$resultsSql ORDER BY sent DESC LIMIT 1000"));

if ( isset($_GET['recollate']) ) {
	foreach ( $results as $result ) {
		$result->retype() && $result->collate();
	}

	$query = $type ? "?type={$type->id}" : ($server ? "?server={$server->id}" : '');
	return do_redirect('results.php' . $query);
}

include 'tpl.header.php';

$ids = array_flip(array_values($db->select_fields(Result::$_table, 'id', '1 ORDER BY sent DESC')));

?>

<h2>
	<?= $anominal ? 'Anominal results' : 'Results' ?>
	<? if ($type): ?>
		for <em><?= html($type) ?></em>
		(<?= count($results) ?>)
	<? elseif ($server): ?>
		for <em><?= html($server) ?></em>
		(<?= count($results) ?>)
	<? elseif ($date): ?>
		for <em><?= html($date) ?></em>
		(<?= count($results) ?>)
	<? else: ?>
		(<?= count($results) ?> / <?= count($ids) ?>)
	<? endif ?>
</h2>

<p>
	<a href="?type=<?= @$type->id ?>&server=<?= @$server->id ?>&anominal=<?= (int) !$anominal ?>"><?= $anominal ? 'All' : 'Only anominal' ?></a>
</p>

<table>
	<thead>
		<tr>
			<th align="right">#</th>
			<? if (!$type): ?>
				<th>Type</th>
			<? endif ?>
			<th>Subject</th>
			<? if (!$server): ?>
				<th>Server</th>
			<? endif ?>
			<th>Date/time</th>
			<th align="center">?</th>
			<th>Size</th>
			<? if ($type): ?>
				<? foreach ($type->triggers as $trigger): ?>
					<th style="color: <?= html($trigger->color) ?>" title="<?= html($trigger->regex) ?>"><?= html($trigger->description) ?></th>
				<? endforeach ?>
			<? endif ?>
			<th><a href="?type=<?= @$type->id ?>&server=<?= @$server->id ?>&recollate">Recollate</a></th>
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
				<td><code><?= html($result->relevant_subject) ?></code></td>
				<? if (!$server): ?>
					<td><a href="?server=<?= $result->server_id ?>"><?= html($result->server ?: '?') ?></a></td>
				<? endif ?>
				<td><a title="Batch: <?= date('Y-m-d H:i:s', $result->batch) ?>" href="result.php?id=<?= $result->id ?>"><?= get_datetime($result->sent) ?></a></td>
				<td align="center">
					<? if ($result->nominal === true): ?>
						<img src="yes.gif" title="Meets all the expected values" />
					<? elseif ($result->nominal === false): ?>
						<img src="warning.png" title="Does NOT meet all the expected values!" />
					<? endif ?>
				</td>
				<td style="<?= strlen($result->output) == 0 ? 'text-decoration: line-through' : '' ?>">
					<?= number_format($result->output_size, 0) ?>
				</td>
				<? if ($type): ?>
					<? foreach ($type->triggers as $trigger):
						list($amount, $nominal) = $result->triggered($trigger->id);
						?>
						<td <? if ($amount > 0): ?>style="font-weight: bold; color: <?= html($trigger->color) ?>"<? endif ?>>
							<?= $amount ?>
						</td>
					<? endforeach ?>
				<? endif ?>
				<td><a href="result.php?id=<?= $result->id ?>&recollate&goto=results.php%3Ftype=<?= @$type->id ?>%26server=<?= @$server->id ?>">recollate</a></td>
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
			cell.innerHTML = `<a href="results-compare.php">${sectionSize}</a>`;
			if (prev != sectionFirst) {
				cell = prev.insertCell(prev.cells.length);
				cell.innerHTML = `<a href="results-compare.php">${sectionSize}</a>`;
			}
			sectionSize = 0;
			sectionFirst = tr;
		}

		sectionSize++;
		prev = tr;
	});
})();
</script>

<?php

include 'tpl.footer.php';
