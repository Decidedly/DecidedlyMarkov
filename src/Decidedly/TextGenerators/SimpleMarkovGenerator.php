<?php

namespace Decidedly\TextGenerators;

class SimpleMarkovGenerator {
	// var $delim = " \r\n\t,.!?:;()[]";
	var $delim = " \r\n\t()[]";
	var $relations = array();
	var $chain = array();

	function __constructor() {

	}

	public function parseText($text) {
		$thisWord = strtok($text, $this->delim);

		while ($thisWord !== false) {
			$thisWord = $thisWord;
			
			if(!empty($lastWord)) {
				$this->addWordTransitionToChain($lastWord, $thisWord);
			}
			
			// Move the current word so that we can use it next time.
			$lastWord = $thisWord;
		    // echo "$thisWord\n";
		  // Get next word
		  $thisWord = strtok($this->delim);
		}
	}

	private function addWordTransitionToChain($firstWord, $secondWord) {
		
		if(!isset($this->relations[$firstWord])) {
			$this->relations[$firstWord] = array($secondWord=>1);
		} else {
			if(!isset($this->relations[$firstWord][$secondWord])) {
				$this->relations[$firstWord][$secondWord] = 1;
			} else {
				$this->relations[$firstWord][$secondWord]++;
			}
			$this->calculateWordProbabilities($firstWord);
		}
	}
	

	private function calculateWordProbabilities($word) {
		$totalCount = 0;
		foreach ($this->relations[$word] as $secondWord => $count) {
			$totalCount += $count;
		}

		foreach ($this->relations[$word] as $secondWord => $count) {
			$this->chain[$word][$secondWord] = ($count / $totalCount);
		}		
	}

	public function generateText($characterCount) {
		$string = "";

		$currentWord = array_rand($this->relations);
		
		while(strlen($string . " " . $currentWord) < $characterCount) {
			if(strlen($string > 0)) {
				$string .= " " . $currentWord;
			} else {
				$string = $currentWord;
			}

			if(isset($this->relations[$currentWord]) && is_array($this->relations[$currentWord])) {
				$currentWord = $this->getRandomWeightedElement($this->relations[$currentWord]);
			} else {
				if(strlen($string . ".") < $characterCount) {
					$string .= ".";
				}
				$currentWord = array_rand($this->relations);
			}
		}

		if(strlen($string . ".") < $characterCount) {
			$string .= ".";
		}

		return $string;
	}

	/**
   * getRandomWeightedElement()
   * Utility function for getting random values with weighting.
   * Pass in an associative array, such as array('A'=>5, 'B'=>45, 'C'=>50)
   * An array like this means that "A" has a 5% chance of being selected, "B" 45%, and "C" 50%.
   * The return value is the array key, A, B, or C in this case.  Note that the values assigned
   * do not have to be percentages.  The values are simply relative to each other.  If one value
   * weight was 2, and the other weight of 1, the value with the weight of 2 has about a 66%
   * chance of being selected.  Also note that weights should be integers.
   *
   * @url http://stackoverflow.com/a/11872928/479540
   * @param array $weightedValues
   */
  function getRandomWeightedElement(array $weightedValues) {
    $rand = mt_rand(1, (int) array_sum($weightedValues));

    foreach ($weightedValues as $key => $value) {
      $rand -= $value;
      if ($rand <= 0) {
        return $key;
      }
    }
  }
}