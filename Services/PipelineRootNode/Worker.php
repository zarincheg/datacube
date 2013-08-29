<?php
namespace Services\PipelineRootNode;
use Communication\Pipeline;

/**
 * @author Kirill Zorin aka Zarin <zarincheg@gmail.com>
 *
 */
class Worker extends \Services\Worker {
	/**
	 * @var \Communication\Pipeline
	 */
	protected $node;
	/**
	 * @var array Results of command execution
	 */
	protected $results;

	public function __construct($name) {
		$this->node = new Pipeline($name);
		parent::__construct($name);
	}

	public function process(\GearmanJob $job) {
		if(!parent::process($job))
			return true;

		foreach($this->workload->commands as $command) {
			$name = $command['name'];
			$this->logger->info('Prepare command: '.$name);

			$command = \CommandRegistry::get($name);
			$this->results[$name] = $command->execute($command['params']);
		}

		try {
			$this->saveResults();
		} catch(\MongoException $e) {
			$this->logger->fatal('Failed to save the results of data processing. Worker: '.__CLASS__);
		}

		/**
		 * @note Выбираем все ключи запросов дочерних узлов и шлем уведомления о завершении обработки
		 */
		$index = $this->node->getIndexByParent($this->workload->key);

		while($index->hasNext()) {
			$keys = $index->getNext();
			$notify = ['request_key' => $keys['request_key'], 'data_key' => $this->workload->key];
			$this->node->notify(json_encode($notify));
			$this->logger->info("Notification sent. Child request key: ". $keys['request_key']);
		}

		$this->logger->info("Task complete. Request key: ". $this->workload->key);

		return true;
	}

	/**
	 * Method for handle data saving logic. Can be redefined in child classes.
	 */
	protected function saveResults() {
		$this->node->saveData($this->workload->key, ['data' => $this->results]);
	}
}