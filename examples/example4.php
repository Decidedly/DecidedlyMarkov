<?php

require '../vendor/autoload.php';

class Example4 {
	public function run() {
		$markov = new \Decidedly\TextGenerators\SimpleMarkovGenerator(2);
		$markov->verbose = true;
		$text = file_get_contents("alice.txt");
		$markov->parseText($text);

		$blacklist = array('alice');
		$string = $markov->generateText(140, 5, true, $blacklist);
		echo $string . "\n";
	}
}

$app = new Example4();
$app->run();