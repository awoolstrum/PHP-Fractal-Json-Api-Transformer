# PHP-Fractal-Json-Api-Transformer

This repository is a helper class  to assist developers implementing the [JSON API specifications](https://jsonapi.org/).  The JsonApi class found here extends the functionality of [PHP League's Fractal package](https://fractal.thephpleague.com/) (1) to use the same transformer class to transform the input resource to entity objects for the back end and (2) to support more complex relationships between resources.

# Installation

Right now, the class is a stand alone file that can be included and instantiated from anywhere in your code base.  To use the class, download the JsonApi.php, JsonApiEntityInterface.php and JsonApiTransformerInterface.php files and place the files in your project.    

In the future, as I continue to use this class in my projects and flesh out more features, I will add it to packagist.   

# Usage

## Simple example

You are on a development team, and the specification for the front end and back end teams are that all data will conform to the JSON API specification.  You are working on a project that involves coffee shop franchies, with entities for each franchise, regions within the franchise, and locations that may or may not be tied to regions but will always be tied to a franchise.  

A simple JSON input to edit a coffee shop may look something like this:

``` 
{
	"data": {
		"type": "location",
		"id": "1",
		"attributes": {
			"location": "Austin"
		},
		"relationships": {
			"region": {
				"data": {
					"type": "region",
					"id": "1"
				}
			}
		}
	},
	"included": [{
		"type": "region",
		"id": "1",
		"attributes": {
			"name": "Central Texas"
		}
	}]
}
```

In your controller or action class, you can autowire or include the JsonApi class, and automatically convert this coffee shop location JSON to a coffee shop entity object.

```
		$jsonApi = new JsonApi();
		$jsonApi->transformer('location', new CoffeeShopTransformer);
		$jsonApi->transformer('region', new RegionTransformer);
		$result = $jsonApi->transform($json);
```

The JsonApi class will map the JSON API resource type (ie, 'region', 'location') to the transformer that you define with the JsonApi::transformer method.  Your transformer should implement the JsonApiTransformerInterface, which will addd a load method to your transformer. This load method should instantiate the entity object.  How you do that is your discretion, so you can still utilize a Factory or any other pattern to create the class.  Because the coffee shop entity object also has a method called region (matching the resource type), the entity object type Region will be included as an item in a container in the Coffee Shop class.  

The final output may look like:

```
object(App\Entity\CoffeeShop)#27 (3) {
  ["id"]=>
  string(1) "1"
  ["location"]=>
  string(6) "Austin"
  ["region"]=>
  object(App\Entity\Region)#26 (2) {
    ["id"]=>
    string(1) "1"
    ["name"]=>
    string(13) "Central Texas"
  }
}
```

For more examples, check out the [index.php file in the examples directory](https://github.com/awoolstrum/PHP-Fractal-Json-Api-Transformer/blob/master/examples/index.php).  

## Checklist for complex relationships

1. Create resources (can be an item or a collection)
2. Convert to arrays
3. Pass the arrays to JsonApi::mergeResources
4. JSON encode the resulting array

## Checklist for converting JSON Input to Entity Objects

1. Implement the JsonApiTransformerInterface on your transformer
2. Implement the JsonApiEntityInterface on your entity object
3. Instantiate the JsonApi class in your action or controller class
4. Define the resource type to transformer mapping
5. Only if your entity has relationships, then make sure your entity object has a method name that matches the resource type of the relationship.
6. Call the jsonApi::transform class to retrieve your entity object.

# License

This code is original to the author and is intended to be free for commercial or non-commercial use with no restrictions.
