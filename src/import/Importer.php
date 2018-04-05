<?php

namespace rdx\cronlog\import;

interface Importer {
	public function delete();

	public function getFrom();
	public function getTo();
	public function getSubject();
	public function getSentUtc();
	public function getOutput();
}
