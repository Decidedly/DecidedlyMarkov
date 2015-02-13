<?php

require '../vendor/autoload.php';

class Example1 {
	public function run() {
		$markov = new \Decidedly\TextGenerators\SimpleMarkovGenerator(2);
		$text = file_get_contents("alice.txt");
		$markov->parseText($text);

		$string = $markov->generateText(140, 5, true);
		echo $string . "\n";
	}
}

$app = new Example1();
$app->run();