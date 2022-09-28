<?php

use rdx\cronlog\Result;
use rdx\cronlog\Server;
use rdx\cronlog\Type;

require 'inc.bootstrap.php';

$type = Type::find(@$_GET['type']);
$server = Server::find(@$_GET['server']);
$date = @$_GET['date'];
$batch = @$_GET['batch'];
$anominal = !empty($_GET['anominal']);

$conditions = [];
$type and $conditions['type_id'] = $type->id;
$server and $conditions['server_id'] = $server->id;
$date and $conditions[] = $db->replaceholders('date(sent) = ?', $date);
$batch and $conditions['batch'] = $batch;
$anominal and $conditions['nominal'] = '0';
$conditionsSql = count($conditions) ? $db->stringifyConditions($conditions) : '1';

$results = Result::all("$conditionsSql ORDER BY sent DESC LIMIT 500");
$totalResults = Result::count($conditionsSql);

if ( ($_GET['recollate'] ?? '') === 'all' ) {
	foreach ( $results as $result ) {
		$result->retype() && $result->collate();
	}

	return do_redirect('?' . http_build_query(array_diff_key($_GET, ['recollate' => 1])));
}

include 'tpl.header.php';

// Result::eager('type', $results);
// Result::eager('server', $results);
Result::eager('triggers', $results);

$ids = array_flip(array_values($db->select_fields(Result::$_table, 'id', '1 ORDER BY sent DESC')));

$typesOptions = Type::all('1 ORDER BY description');
$serversOptions = Server::all('1 ORDER BY name');
$datesOptions = $db->select_fields('results', 'date(sent)', '1 GROUP BY date(sent) ORDER BY date(sent) DESC');
$batchesOptions = array_map(function($utc) {
	return date('Y-m-d H:i', $utc);
}, $db->select_fields('results', 'batch', 'batch IS NOT NULL GROUP BY batch ORDER BY batch DESC'));

?>

<h2>
	Results
	(<?= count($results) ?> / <?= $totalResults != count($results) && $totalResults != count($ids) ? "$totalResults / " : '' ?> <?= count($ids) ?>)
</h2>

<form action onchange="this.submit()">
	<p>
		<select name="type"><?= html_options($typesOptions, $_GET['type'] ?? null, '-- Type') ?></select>
		<select name="server"><?= html_options($serversOptions, $_GET['server'] ?? null, '-- Server') ?></select>
		<select name="batch"><?= html_options($batchesOptions, $_GET['batch'] ?? null, '-- Batch') ?></select>
		<select name="date"><?= html_options($datesOptions, $_GET['date'] ?? null, '-- Date') ?></select>
		<select name="anominal"><?= html_options(['1' => 'Only anominal'], $anominal, '-- Nominality') ?></select>
	</p>
</form>

<table>
	<thead>
		<tr>
			<th align="right">#</th>
			<? if (!$type): ?>
				<th>Type</th>
			<? endif ?>
			<th nowrap>Subject</th>
			<? if (!$server): ?>
				<th nowrap>Server</th>
			<? endif ?>
			<th nowrap>Date/time</th>
			<th align="center">?</th>
			<th>Size</th>
			<? if ($type): ?>
				<? foreach ($type->triggers as $trigger): ?>
					<th style="color: <?= html($trigger->color) ?>" title="<?= html($trigger->regex) ?><?= strlen($trigger->expect) ? " - $trigger->expect" : '' ?>">
						<?= html($trigger->description) ?>
					</th>
				<? endforeach ?>
			<? endif ?>
			<th><a href="?<?= http_build_query($_GET) ?>&recollate=all">Recollate</a></th>
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
			<tr class="<?= $newSection ? 'next-section' : '' ?> <?= $batch % 2 == 0 ? 'even-section' : 'odd-section' ?>" data-date="<?= $prevDate ?>">
				<th align="right"><?= $ids[$result->id]+1 ?></th>
				<? if (!$type): ?>
					<td><a href="?type=<?= $result->type_id ?>"><?= html($result->type->description) ?></a></td>
				<? endif ?>
				<td nowrap title="<?= html($result->subject) ?>"><code><?= html($result->relevant_subject) ?></code></td>
				<? if (!$server): ?>
					<td nowrap title="<?= html($result->from) ?>"><a href="?server=<?= $result->server_id ?>"><?= html($result->server ?: '?') ?></a></td>
				<? endif ?>
				<td nowrap><a title="Batch: <?= date('Y-m-d H:i:s', $result->batch) ?>" href="result.php?id=<?= $result->id ?>"><?= get_datetime($result->sent) ?></a></td>
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
				<td><a href="result.php?id=<?= $result->id ?>&recollate&goto=result.php?id=<?= $result->id ?>">recollate</a></td>
			</tr>
		<? endforeach ?>
	</tbody>
</table>

<script>
(function() {
	function markSection(firstRow, i, arr) {
		var lastRow = arr[i+1] && arr[i+1].previousElementSibling || firstRow.parentNode.lastElementChild;
		var size = lastRow.rowIndex - firstRow.rowIndex + 1;
		makeCell(firstRow, size);
		size > 1 && makeCell(lastRow, size);
	}

	function makeCell(row, size) {
		var date = row.dataset.date;
		var cell = row.insertCell();
		cell.innerHTML = `<a href="results-compare.php?date1=${date}">${size}</a>`;
	}

	var firstRows = document.querySelectorAll('thead + tbody > tr:first-child, tr.next-section');
	[].forEach.call(firstRows, markSection);
})();
</script>

<?php

include 'tpl.footer.php';
