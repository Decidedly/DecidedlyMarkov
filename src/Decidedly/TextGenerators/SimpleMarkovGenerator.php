<?php

namespace Decidedly\TextGenerators;

class SimpleMarkovGenerator {
	
	// These are characters that fall in between words
	var $delim = " \r\n\t()[]\"";
	var $relations = array();
	var $chain = array();
	var $sentenceEndingPunctuation = ".!";
	var $verbose = false;

	// The number of words that we use as a key when parsing
	var $prefixLength;

	function __construct($prefixLength = 1, $verbose = false) {
		$this->prefixLength = $prefixLength;
		$this->verbose = $verbose;
	}

	public function parseText($text) {
		$thisWord = strtok($text, $this->delim);
		$lastWords = array();

		while ($thisWord !== false) {
			
			if(count($lastWords) == $this->prefixLength) {
				$lastWord = implode(' ', $lastWords);
				$this->addWordTransitionToChain($lastWord, $thisWord);
			}
			
			// Move the current word so that we can use it next time.
			$lastWords[] = $thisWord;
			while(count($lastWords) > $this->prefixLength) {
				array_shift($lastWords);
			}
		 
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
		}
	}


	public function generateText($characterCount, $minWordCount = null, $rejectInevitablePhrases = false) {
		$string = '';
		$attempts = 0;
		
		while(empty($string) && $attempts < 100) {
			$phraseObject = $this->generatePhrase($characterCount);
			$rejectPhrase = false;
			$attempts++;

			if($rejectInevitablePhrases && $phraseObject->phraseIsInevitable) {
				$rejectPhrase = true;
				if($this->verbose) {
					echo "Rejecting the inevitable phrase: {$phraseObject->text}\n";
				}
			}

			if($phraseObject->wordCount < $minWordCount) {
				if($this->verbose) {
					echo "Rejecting this phrase because its too short: {$phraseObject->text}\n";
				}

				$rejectPhrase = true;
			}

			if(!$rejectPhrase) {
				if($string == '')
					$string = $phraseObject->text;
				else 
					$string .= $phraseObject->text;
			}
		}

		return $string;
	}

	public function generatePhrase($maxCharacterCount = 140) {
		$string = "";
		$wordCount = 0;
		$lastWords = array();

		$currentWord = array_rand($this->relations);
		$phraseIsInevitable = true;
		
		while(strlen($string . " " . $currentWord) < $maxCharacterCount) {
			if(strlen($string) > 0) {
				$string .= " " . $currentWord;
			} else {
				$string = $currentWord;
			}

			// Tokenize our current word and add it to the last words array
			// We tokenize because at the start of a string, we will be dealing
			// with multiple words 
			$currentWordArray = explode(" ", $currentWord);
			$wordCount += count($currentWordArray);
			foreach($currentWordArray as $token) {
				$lastWords[] = $token;
			}

			while(count($lastWords) > $this->prefixLength) {
				array_shift($lastWords);
			}
				

			$currentWord = implode(" ", $lastWords);

			if(isset($this->relations[$currentWord]) && is_array($this->relations[$currentWord])) {
				if(count($this->relations[$currentWord]) > 1) {
					$phraseIsInevitable = false;
				} 
				$currentWord = $this->getRandomWeightedElement($this->relations[$currentWord]);
			} else {
				// We've reached a dead-end
				break;
			}
		}

		return (object) array('text'=>$string, 'phraseIsInevitable'=>$phraseIsInevitable, 'wordCount'=>$wordCount);
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