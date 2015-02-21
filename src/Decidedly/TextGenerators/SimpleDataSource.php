<?php

namespace Decidedly\TextGenerators;

class SimpleDataSource implements MarkovDataSource {
	var $storageFileName;
	var $numRelations;

	// The data itself
	var $relations = array();

	public function __construct($storageFileName) {
		$this->storageFileName = $storageFileName;
		$this->numRelations = 0;
		$this->loadData($storageFileName, false);
	}

	public function addRelation($firstWord, $secondWord) {
		
		if(!isset($this->relations[$firstWord])) {
			$this->relations[$firstWord] = array($secondWord=>1);
		} else {
			if(!isset($this->relations[$firstWord][$secondWord])) {
				$this->relations[$firstWord][$secondWord] = 1;
			} else {
				$this->relations[$firstWord][$secondWord]++;
			}
		}

		$this->numRelations++;
	}

	public function getRandomPrefix() {
		return array_rand($this->relations);
	}

	public function getSuffixesForPrefix($prefix) {
		return isset($this->relations[$prefix]) ? $this->relations[$prefix] : null;
	}

	public function getNumRelations() {
		return $this->numRelations;
	}

	public function loadData($failSilently = true) {
		// Load config
		if(!file_exists($this->storageFileName)) {
			if($failSilently) {
				return;
			}
			throw new \Exception("The Markov relations file {$storageFileName} does not exist.");
		}

		$dataJson = file_get_contents($this->storageFileName);
		$data = json_decode($dataJson, true);
		if($data == null) {
			if($failSilently) {
				return;
			}
			throw new \Exception("The Markov relation file is not a valid JSON file.");
		} 


		if(isset($data['relations']) 
			&& count($data['relations']) > 0) {
			$this->relations = $data['relations'];
		}

		$this->numRelations = count($this->relations);
	}

	public function saveData() {
		if($this->numRelations == 0) {
			return;
		}

		$dataObject = array('relations' => $this->relations);
		
		$success = file_put_contents($this->storageFileName, json_encode($dataObject, JSON_PRETTY_PRINT));

		if(!$success) {
			throw new \Exception("Unable to save to memory file {$this->storageFileName}.");
		}
	}
}