<!doctype html>
<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta charset="utf-8" />
<title>Cronlog</title>
<style>
body {
	font-family: sans-serif;
}
table {
	border-collapse: collapse;
}
th, td {
	padding: 6px 9px;
	border: solid 1px #ddd;
}
th:not([align]) {
	text-align: left;
}
tbody tr:nth-child(odd) {
	background-color: #eee;
}
tbody tr.even-section:nth-child(odd) {
	background-color: hsl(39, 100%, 90%);
}
tbody tr.even-section:nth-child(even) {
	background-color: hsl(60, 100%, 90%);
}
tr.next-section > * {
	border-top: solid 3px black;
}
input.o {
	width: 2em;
	text-align: center;
}
input.regex {
	font-family: monospace;
	width: 20em;
}
input.expect {
	width: 2em;
	text-align: center;
}
input.color {
	width: 4em;
}

.output {
	font-family: monospace;
	white-space: pre-wrap;
}
.output strong {
	color: #c00;
	background: #eee;
}
</style>
</head>

<body>

<p class="main-menu">
	<a href="index.php">Index</a> |
	<a href="cron.php">Execute</a> |
	<a href="results.php">All results</a>
</p>
