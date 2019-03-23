<?php
declare(strict_types=1);

namespace App\Infrastructure\Http;

require('bootstrap.php');

use App\Responder\Responder;
use App\Entity\CoffeeShop;
use App\Entity\Franchise;
use App\Entity\Region;
use App\Transformer\CoffeeShopTransformer;
use App\Transformer\FranchiseTransformer;
use App\Transformer\RegionTransformer;
use awoolstrum\JsonApi\JsonApi;
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
		$newResource = $jsonApi->mergeResources(json_decode($franchiseJson, true), json_decode($locationsJson, true));

		return json_encode($newResource);
	}
	
	public function transform(string $json)
	{
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
echo '<h1>Example of a 1:1 Relationship</h1>';
echo '<h2>JSON complying with JSON API specifications prepared using Fractal</h2>';
echo $test->prepareSimpleRelationship();
echo '<h2>Objects extracted, transformed and loaded from the JSON API string</h2>';
$test->transform($test->prepareSimpleRelationship());

echo '<h1>Example of Complex Many-to-Many-to-Many Relationships</h1>';
echo '<h2>JSON complying with JSON API specifications prepared using Fractal</h2>';
echo $test->prepareComplexRelationships();
echo '<h2>Objects extracted, transformed and loaded from the JSON API string.</h2>';
$test->transform($test->prepareComplexRelationships());