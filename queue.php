<?php

use rdx\cronlog\UploadedLog;

require 'inc.bootstrap.php';

if ( isset($_POST['deletes']) ) {
	UploadedLog::deleteAll([
		'id' => $_POST['deletes'],
	]);
	return do_redirect();
}

$logs = UploadedLog::all('1=1');

include 'tpl.header.php';

?>
<h1>Ready-to-import queue (<?= count($logs) ?>)</h1>
<form method="post" action>
	<table border="1">
		<thead>
			<tr>
				<th></th>
				<th>Received</th>
				<th>From</th>
				<th>Subject</th>
			</tr>
		</thead>
		<? foreach ($logs as $log): ?>
			<tbody class="striping">
				<tr>
					<td><input type="checkbox" name="deletes[]" value="<?= $log->id ?>"></td>
					<td nowrap><?= html($log->created_on) ?></td>
					<td><?= html($log->from) ?></td>
					<td onclick="this.parentNode.nextElementSibling.hidden = !this.parentNode.nextElementSibling.hidden">
						<?= html($log->subject) ?>
					</td>
				</tr>
				<tr hidden>
					<td colspan="4" class="output"><?= html($log->body) ?></td>
				</tr>
			</tbody>
		<? endforeach ?>
	</table>
	<p><button>Delete selected</button></p>
</form>
