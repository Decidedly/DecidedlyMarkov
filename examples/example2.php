<?php

require '../vendor/autoload.php';

class Example2 {
	public function run() {
		$markov = new \Decidedly\TextGenerators\SimpleMarkovGenerator(2);
		$text = file_get_contents("alice.txt");
		$markov->parseText($text);

		$string = $markov->generateText(140, 5, true);
		echo $string . "\n";
	}
}

$app = new Example2();
$app->run();