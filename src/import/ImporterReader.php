<?php

namespace rdx\cronlog\import;

interface ImporterReader {

	public function read( Importer $importer ) : void;

}
