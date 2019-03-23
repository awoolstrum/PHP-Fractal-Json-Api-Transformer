<?php

declare(strict_types=1);

namespace awoolstrum\JsonApi;

interface JsonApiTransformerInterface 
{
	// Can instantiate the class directly or call a classes' factory
	public function load(array $array);
}