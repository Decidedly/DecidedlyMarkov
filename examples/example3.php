<?php

/**
 * This example demonstrates using MySQL as a data source
*/

require '../vendor/autoload.php';

class Example3 {
	public function run() {
		$dataSource = new \Decidedly\TextGenerators\MySqlDataSource(
			'localhost',
			'decidedly_markov',
			'pQ9SjCRN6KadCUvV',
			'markov_generators',
			'dhilowitz_'
		);

		$markov = new \Decidedly\TextGenerators\SimpleMarkovGenerator(2, true, $dataSource);
		$text = file_get_contents("alice.txt");
		$markov->parseText($text);
		$string = $markov->generateText(140, 5, false);
		echo $string . "\n";
	}
}

$app = new Example3();
$app->run();