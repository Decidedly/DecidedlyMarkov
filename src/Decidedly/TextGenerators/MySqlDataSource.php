<?php

namespace Decidedly\TextGenerators;

class MySqlDataSource implements MarkovDataSource {

	// Database handle
	var $dbh;
	// Relations Table
	var $tablePrefix;

	public function __construct($hostname, $username, $password, $database, $tablePrefix = '') {
		$this->tablePrefix = $tablePrefix;
		$dsn = "mysql:host={$hostname};dbname={$database}";

		$options = array(
		    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
		); 
		try {
			$this->dbh = new \PDO($dsn, $username, $password, $options);
			$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		} catch(\PDOException $e) {
			throw new \Exception("Unable to connect to database \"{$database}\".");
		}
	}

	public function addRelation($prefix, $suffix) {
		$suffixes = $this->getSuffixesForPrefix($prefix);

		if($suffixes == null) {
			$this->addRelationHelper($prefix, $suffix, 1);
		} else {
			if(!isset($suffixes[$suffix])) {
				$this->addRelationHelper($prefix, $suffix, 1);
			} else {
				$currentCount = $suffixes[$suffix];
				$this->addRelationHelper($prefix, $suffix, $currentCount + 1);
			}
		}
	}

	public function addRelationHelper($prefix, $suffix, $count) {
		$prefixId = $this->prefixSelect($prefix);
		if(empty($prefixId)) {
			$prefixId = $this->prefixInsert($prefix);
		}
		
		$suffixId = $this->suffixSelect($suffix);
		if(empty($suffixId)) {
			$suffixId = $this->suffixInsert($suffix);
		}

		$this->relationReplace($prefixId, $suffixId, $count);
	}

	public function prefixSelect($prefix) {
		$sql = "SELECT id FROM {$this->tablePrefix}prefix WHERE `text` = :prefix";
		$q = $this->dbh->prepare($sql);
		$q->execute(array(':prefix'=>$prefix));
		$row = $q->fetch();
		if(!empty($row))
			return $row['id'];
		else 
			return null;
	}

	public function prefixInsert($prefix) {
		$sql = "INSERT INTO {$this->tablePrefix}prefix (`text`) VALUES (:prefix)";
		$q = $this->dbh->prepare($sql);
		$q->execute(array(':prefix'=>$prefix));
		return $this->dbh->lastInsertId();
	}

	private function suffixSelect($suffix) {
		$sql = "SELECT id FROM {$this->tablePrefix}suffix WHERE `text` = :suffix";
		$q = $this->dbh->prepare($sql);
		$q->execute(array(':suffix'=>$suffix));
		$row = $q->fetch();
		if(!empty($row))
			return $row['id'];
		else 
			return null;
	}

	private function suffixInsert($suffix) {
		$sql = "INSERT INTO {$this->tablePrefix}suffix (`text`) VALUES (:suffix)";
		$q = $this->dbh->prepare($sql);
		$q->execute(array(':suffix'=>$suffix));
		return $this->dbh->lastInsertId();
	}

	private function relationSelect($prefixId, $suffixId) {
		$sql = "SELECT id FROM {$this->tablePrefix}relations WHERE `prefix_id` = :prefixId AND `suffix_id` = :suffixId";
		$q = $this->dbh->prepare($sql);
		$q->execute(array(':prefix_id'=>$prefixId, ':suffix_id'=>$suffixId));
		$row = $q->fetch();
		return $row;
	}

	private function relationReplace($prefixId, $suffixId, $count) {
		$sql = "REPLACE INTO {$this->tablePrefix}relations (`prefix_id`,`suffix_id`, `count`) VALUES (:prefixId,:suffixId,:count)";
		$q = $this->dbh->prepare($sql);
		return $q->execute(array(
			':prefixId'=>$prefixId,
			':suffixId'=>$suffixId,
			':count'=>$count
		));
	}

	public function getRandomPrefix() {
		$numPrefixes = $this->getNumPrefixes();
		$randomNum = rand(1, $numPrefixes);

		$sql = "SELECT `text` FROM {$this->tablePrefix}prefix LIMIT {$randomNum}, 1";
		$q = $this->dbh->query($sql);
		return $q->fetchColumn();
	}

	public function getSuffixesForPrefix($prefix) {
		$sql = "SELECT suffix.text, relations.count FROM {$this->tablePrefix}prefix as `prefix`
	INNER JOIN {$this->tablePrefix}relations AS `relations` ON `prefix`.`id` =  `relations`.prefix_id
	INNER JOIN {$this->tablePrefix}suffix AS `suffix` ON relations.suffix_id = suffix.id
	WHERE prefix.text = :prefix";

		$q = $this->dbh->prepare($sql);
		$q->execute(array(':prefix'=>$prefix));
		$results = $q->fetchAll(\PDO::FETCH_ASSOC);

		$newArray = array();
		foreach($results as $subArray) {
			$newArray[$subArray['text']] = $subArray['count'];
		}

		if(count($newArray) > 0)
			return $newArray;
		else
			return null;
	}
	public function getNumRelations() {
		$sql = "SELECT count(*) FROM {$this->tablePrefix}relations";
		$q = $this->dbh->prepare($sql);
		$q->execute();
		return $q->fetchColumn();
	}

	public function getNumPrefixes() {
		$sql = "SELECT count(id) FROM {$this->tablePrefix}prefix";
		$q = $this->dbh->prepare($sql);
		$q->execute();
		return $q->fetchColumn();
	}
}