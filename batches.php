<?php

use rdx\cronlog\Result;

require 'inc.bootstrap.php';

$batches = $db->select_fields(Result::$_table, 'batch, count(1)', "batch <> '' GROUP BY batch ORDER BY batch DESC");

include 'tpl.header.php';

?>
<table border="1">
	<? foreach ($batches as $batch => $num): ?>
		<tr>
			<th><a href="results.php?batch=<?= $batch ?>"><?= date('Y-m-d H:i', $batch) ?></a></th>
			<td><?= $num ?></td>
		</tr>
	<? endforeach ?>
</table>
