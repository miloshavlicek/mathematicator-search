<?php

declare(strict_types=1);

namespace Mathematicator\Search;


use Mathematicator\Engine\EngineSingleResult;
use Mathematicator\Engine\Helper\Terminal;
use Nette\Utils\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;
use Tracy\Dumper;

class Console extends Command
{

	/** @var Search */
	private $search;


	/**
	 * @param Search $search
	 */
	public function __construct(Search $search)
	{
		parent::__construct();
		$this->search = $search;
	}


	protected function configure(): void
	{
		$this->setName('app:search')
			->setDescription('Search by computational knowledge engine.');
	}


	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		try {
			echo ' __  __       _   _                          _   _           _                ' . "\n";
			echo '|  \/  | __ _| |_| |__   ___ _ __ ___   __ _| |_(_) ___ __ _| |_ ___  _ __    ' . "\n";
			echo '| |\/| |/ _` | __| \'_ \ / _ \ \'_ ` _ \ / _` | __| |/ __/ _` | __/ _ \| \'__|' . "\n";
			echo '| |  | | (_| | |_| | | |  __/ | | | | | (_| | |_| | (_| (_| | || (_) | |      ' . "\n";
			echo '|_|  |_|\__,_|\__|_| |_|\___|_| |_| |_|\__,_|\__|_|\___\__,_|\__\___/|_|      ' . "\n";
			echo '                                                        Terminal edition      ' . "\n";

			while (true) {
				$query = Terminal::ask('Query');

				if ($query !== null) {
					$result = $this->search->search($query);

					if (\is_array($result)) {
						foreach ($result as $resultItem) {
							if ($resultItem instanceof EngineSingleResult) {
								$this->render($resultItem);
							}
							echo "\n\n\n---------------------\n\n\n";
						}
					} elseif ($result instanceof EngineSingleResult) {
						$this->render($result);
					}
				}
			}
		} catch (\Throwable $e) {
			$output->writeLn('<error>' . $e->getMessage() . '</error>');
			echo "\n\n";
			Terminal::code($e->getFile(), $e->getLine());

			if (class_exists('Tracy\Dumper') && class_exists('Tracy\Debugger')) {
				echo Dumper::toTerminal(Debugger::log($e));
			}

			return 1;
		}
	}


	/**
	 * @param EngineSingleResult $result
	 */
	private function render(EngineSingleResult $result): void
	{
		if ($result->getInterpret() !== null) {
			echo 'In[1] = ' . $result->getInterpret()->getText();
		}

		foreach ($result->getBoxes() as $box) {
			echo "\n\n\n";
			echo '   ' . $box->getTitle() . "\n";
			echo '---' . str_repeat('-', Strings::length($box->getTitle())) . "---\n\n";
			echo $box->getText();
		}
	}
}
