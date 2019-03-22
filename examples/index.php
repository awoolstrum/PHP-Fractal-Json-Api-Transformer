<?php
declare(strict_types=1);

namespace App\Infrastructure\Http;

require('bootstrap.php');

use App\Infrastructure\Http\Responder;
use App\Infrastructure\Http\CoffeeShop;
use App\Infrastructure\Http\Franchise;
use App\Infrastructure\Http\Region;
use App\Infrastructure\Http\CoffeeShopTransformer;
use App\Infrastructure\Http\FranchiseTransformer;
use App\Infrastructure\Http\RegionTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class ExamplesOfUsage extends Responder {
		
	public function prepareSimpleRelationship()
	{
		$centralTexas = new Region(1, "Central Texas");
		$coffeeShop = new CoffeeShop(1, "Austin", $centralTexas);				
		$resource = new Item($coffeeShop, new CoffeeShopTransformer, 'location');
		return $this->fractal->createData($resource)->toJson();
	}
	
	public function prepareComplexRelationships()
	{
		$franchise = new Franchise(1, "CenTexBucks, LLC");

		$centralTexas = new Region(1, "Central Texas");
		$coffeeShops = [
			new CoffeeShop(1, "Austin", $centralTexas),
			new CoffeeShop(2, "Round Rock", null)
			];

		$regions = [
			$centralTexas,
			new Region(2, "North Texas"),
			new Region(3, "East Texas")
			];
			
		$franchise->locations($coffeeShops);
		$franchise->regions($regions);
				
		$resource = new Item($franchise, new FranchiseTransformer, 'franchise');
		$franchiseJson = $this->fractal->createData($resource)->toJson();
		$locationsResource = new Collection($coffeeShops, new CoffeeShopTransformer, 'location');
		$locationsJson = $this->fractal->createData($locationsResource)->toJson();

		$jsonApi = new JsonApi();
		$newResource = $jsonApi->mergeCollections(json_decode($franchiseJson, true), json_decode($locationsJson, true));

		return json_encode($newResource);
	}
	
	public function parseJsonApi()
	{
		$json = $this->prepareSimpleRelationship();
//		$json = $this->prepareComplexRelationships();
		echo $json;

		$jsonApi = new JsonApi();
		$jsonApi->transformer('franchise', new FranchiseTransformer);
		$jsonApi->transformer('location', new CoffeeShopTransformer);
		$jsonApi->transformer('region', new RegionTransformer);
		$result = $jsonApi->transform($json);
		echo "<pre>";
		var_dump($result);
		echo "</pre>";
	}
}

$test = new ExamplesOfUsage;
$test->parseJsonApi();