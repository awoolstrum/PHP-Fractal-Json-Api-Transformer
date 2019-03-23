<?php

declare(strict_types=1);

namespace App\Entity;

use awoolstrum\JsonApi\JsonApiEntityInterface;

class CoffeeShop implements JsonApiEntityInterface
{
	
	public $id;
	public $location;
	public $region;
	
	public function __construct($id, $location, ?Region $region){
		$this->id = $id;
		$this->location = $location;
		$this->region = $region;
	}
	
	public function region(Region $region)
	{
		$this->region = $region;
	}
}

