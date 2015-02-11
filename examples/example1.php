<?php

require '../vendor/autoload.php';

class Example1 {
	public function run() {
		$taco  = new \Decidedly\Taco;
		$markov = new \Decidedly\TextGenerators\SimpleMarkovGenerator();
		$text = file_get_contents("alice.txt");
		$markov->parseText($text);
		
		$string = $markov->generateText(140);
		echo $string . "\n";
	}
}

$app = new Example1();
$app->run();