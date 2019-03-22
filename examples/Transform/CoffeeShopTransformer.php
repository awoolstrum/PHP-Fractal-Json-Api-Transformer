<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use League\Fractal\TransformerAbstract;
use App\Infrastructure\Http\CoffeeShop;
use App\Infrastructure\Http\RegionTransformer;

final class CoffeeShopTransformer extends TransformerAbstract implements JsonApiTransformerInterface
{
	protected $availableIncludes = [
		'region'
	];
	
	public function transform(CoffeeShop $coffeeShop)
	{
		return [
			'id' => (int) $coffeeShop->id,
			'location' => $coffeeShop->location
		];
	}
	
	public function load(array $v)
	{
		return new CoffeeShop($v['id'], $v['location'], $v['region']);
	}

	public function includeRegion(CoffeeShop $coffeeShop) 
	{
		$region = $coffeeShop->region;
		
		if ($region === NULL) {
			return;
		}
		
		return $this->item($region, new RegionTransformer, 'region');
	}
}