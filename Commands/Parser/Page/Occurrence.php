<?php
namespace Commands\Parser\Page;

class Occurrence extends \Command {
	public function execute() {
		$parser = \Parsers\Page::instance($this->page);
		return $parser->occurrence();
	}
}