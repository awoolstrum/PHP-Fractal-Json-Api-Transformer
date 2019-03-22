<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use League\Fractal\TransformerAbstract;
use App\Infrastructure\Http\Franchise;
use App\Infrastructure\Http\CoffeeShop;
use App\Infrastructure\Http\RegionTransformer;
use App\Infrastructure\Http\CoffeeShopTransformer;

final class FranchiseTransformer extends TransformerAbstract implements JsonApiTransformerInterface
{
	protected $availableIncludes = [
		'regions',
		'locations'
	];
	
	public function transform(Franchise $franchise)
	{
		return [
			'id' => (int) $franchise->id,
			'name' => $franchise->name
		];
	}
	
	public function load(array $v)
	{
		return new Franchise($v['id'], $v['name']);
	}

	public function includeRegions(Franchise $franchise) 
	{
		$region = $franchise->regions;
		
		if ($region === NULL) {
			return;
		}
		
		return $this->collection($region, new RegionTransformer, 'region');
	}
	
	public function includeLocations(Franchise $franchise) 
	{
		$locations = $franchise->locations;
		
		if ($locations === NULL) {
			return;
		}
		
		return $this->collection($locations, new CoffeeShopTransformer, 'location');
	}
}
