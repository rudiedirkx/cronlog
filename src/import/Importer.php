<?php

namespace rdx\cronlog\import;

interface Importer {

	public function delete() : void;

	public function getFrom() : ?string;
	public function getTo() : ?string;
	public function getSubject() : ?string;
	public function getSentUtc() : ?int;
	public function getOutput() : ?string;

}
