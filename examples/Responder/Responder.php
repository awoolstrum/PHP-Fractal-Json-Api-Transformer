<?php

declare(strict_types=1);

namespace App\Responder;

use League\Fractal;
use League\Fractal\Manager;
use League\Fractal\Serializer\JsonApiSerializer;

class Responder {
	
	protected $fractal;
	protected $jsonApi;
	
	public function __construct() {
		$this->fractal = new Manager();
		$this->fractal->setSerializer(new JsonApiSerializer());
		$this->fractal->parseIncludes(['region', 'regions', 'locations']);	
	}
	
}