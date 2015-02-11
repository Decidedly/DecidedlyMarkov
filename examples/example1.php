<?php

require 'vendor/autoload.php';

class Markov1 {
	public function run() {
		$taco  = new \Decidedly\Taco;
		$markov = new \Decidedly\TextGenerators\SimpleMarkovGenerator();
		$text = file_get_contents("alice.txt");
		$markov->parseText($text);
		print_r($markov->relations);
		$string = $markov->generateText(140);
		echo $string . "\n";
	}
}

$app = new Markov1();
$app->run();