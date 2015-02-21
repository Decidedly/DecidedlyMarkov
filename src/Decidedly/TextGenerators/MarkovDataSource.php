<?php

namespace Decidedly\TextGenerators;

interface MarkovDataSource {

	public function addRelation($prefix, $suffix);
	public function getRandomPrefix();
	public function getSuffixesForPrefix($prefix);
	public function getNumRelations();
}