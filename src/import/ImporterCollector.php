<?php

namespace rdx\cronlog\import;

interface ImporterCollector {

	public function collect( ImporterReader $reader ) : void;

}
