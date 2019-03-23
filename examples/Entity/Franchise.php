<?php

declare(strict_types=1);

namespace App\Entity;

use awoolstrum\JsonApi\JsonApiEntityInterface;

class Franchise implements JsonApiEntityInterface
{
	
	public $id;
	public $name;
	public $regions;
	public $locations;
	
	public function __construct($id, $name){
		$this->id = $id;
		$this->name = $name;
	}
	
	public function region(Region $region)
	{
		$this->regions[] = $region;
	}
	
	public function location(CoffeeShop $coffeeShop)
	{
		$this->locations[] = $coffeeShop;
	}
	
	public function regions(array $regions)
	{
		$this->regions = $regions;
	}
	
	public function locations(array $locations)
	{
		$this->locations = $locations;
	}
}

