<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Infrastructure\Http\JsonApiEntityInterface;

class Region implements JsonApiEntityInterface
{
	
	public $id;
	public $name;
	
	public function __construct($id, $name){
		$this->id = $id;
		$this->name = $name;
	}
}

