<?php
namespace Celium\Commands;
use Celium\Command;

/**
 * Description of class
 * @author Kirill Zorin <zarincheg@gmail.com>
 */

class TestPipe extends Command {

	public function execute($input = null)
	{
		echo "Yes mr.Pipe!\n";
		return "Pipe command success";
	}
}