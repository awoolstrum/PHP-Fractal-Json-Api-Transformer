<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

/*
 * This class converts PHP League's Fractal JSON Api Serializer HTTP packets into more usable PHP objects.
 * See more here: https://fractal.thephpleague.com/serializers/
 */
use Exception;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Http\JsonApiTransformerInterface;
use App\Infrastructure\Http\JsonApiEntityInterface;
use const JSON_ERROR_NONE;
use function array_merge;
use function is_array;
use function json_decode;
use function json_last_error;
use function mb_substr;
use function trim;

final class JsonApi
{
	private $entityObjects;
	private $relationships;
	private $transformers;
	
    public function assertJson(string $input, $message = null) : array
    {
        if (mb_substr($input, 0, 1) !== '{') {
            throw new Exception($message ?? 'The input was not a valid JSON string.');
        }
        $json = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception($message ?? 'The JSON was not formatted properly.');
        }
        return $json;
    }
	
    public function transform(string $jsonInput) 
    {
        $arr = $this->assertJson(trim($jsonInput));
		
		if (isset($arr['included'])) {
			$this->retrieveIncludes($arr['included']);			
		}
		
        $baseObject = $this->retrieveDocument($arr['data']);
		
		$this->loadRelationships();
		
		return $baseObject;
    }

	/*
	 * The Fractal library has an unresolved condition when one item can have one or many relationships
	 * to a second and third item, and the second item can also have one or many relationships to the 
	 * third item.  For example, imagine you had coffee shop locations, which may or may not be grouped
	 * into regions.  Each region and each coffee shop is part of a particular franchise, but each coffee shop
	 * may or may not be part of a region grouping in that franchise, and each region needs to know its 
	 * relationships to coffee shops. 
	 */ 
    public function mergeCollections(array $base, array $supplement) : array
    {
        if (! is_array($supplement['data'])) {
            return $base;
        }
        if (! is_array($base['included'])) {
            return $base;
        }

        foreach ($base['included'] as $index => $includedResource) {
            foreach ($supplement['data'] as $supplementIndex => $supplementResource) {
                if ($includedResource['type'] !== $supplementResource['type']
                    || $includedResource['id'] !== $supplementResource['id']
                ) {
                    continue;
                }

                if (isset($supplementResource['relationships'])) {
                    $base['included'][$index]['relationships'] = $supplementResource['relationships'];
                }

                $base['included'][$index]['attributes'] = array_merge(
                    $supplementResource['attributes'],
                    $includedResource['attributes']
                );
            }
        }

        return $base;
    }

    public function request(Request $request)
    {
        return $this->transform($request->getContent());
    }
	
	public function transformer(string $resourceType, JsonApiTransformerInterface $transformer) {
		$this->transformers[$resourceType] = $transformer;
	}

	protected function mapRelationships($type, $id, $relationships) 
	{
		if (empty($relationships)) {
			return;
		}
		
		foreach ($relationships as $resourceType => $data) {
			if (isset($relationships[$resourceType]['data']['type'])) {
				// there is a 1:1 relationship defined. 
				$this->setRelationships(
					$type,
					$id, 
					$relationships[$resourceType]['data']['type'],
					$relationships[$resourceType]['data']['id']
					);
				continue;
			} else {
				foreach ($data as $values) {
					foreach($values as $link){
						$this->setRelationships($type,$id, $link['type'], $link['id']);
					}
				}
			}
		}
	}
	
	protected function setRelationships($type, $id, $relatedType, $relatedId)
	{
		$this->relationships[] = [
			'type' => $type,
			'id' => $id,
			'relatedType' => $relatedType,
			'relatedId' => $relatedId,
		];
	}
	
	protected function extract(array $document) : JsonApiEntityInterface
	{
        $results = [];
		
        if (isset($document['type'])) {
            $results['type'] = $document['type'];
        }
		
        if (isset($document['id'])) {
            $results['id'] = $document['id'];
        }
		
        if (isset($document['attributes']) && ! empty($document['attributes'])) {
            foreach ($document['attributes'] as $k => $v) {
                // we have a nested document
                $results[$k] = is_array($v)
                    ? $this->retrieveDocument($v)
                    : $v;
            }
        }
		
		return $this->load($results);		
	}
	
	protected function load($flattenedArray) 
	{
		if (! isset($this->transformers[$flattenedArray['type']])) {
			throw new Exception("You need to define the transformer for this type of fractal resource type 
				for the JSON API class to automatically load your entity objects.");
		}
		
		if (! method_exists($this->transformers[$flattenedArray['type']], 'load')) {
			throw new Exception("Your transformer needs a load method.");
		}
		
		return $this->transformers[$flattenedArray['type']]->load($flattenedArray);		
	}
	
    protected function retrieveDocument($data) 
    {
        if (! $this->isCollection($data)) {
            return $this->retrieveItem($data);
        }
        return $this->retrieveCollection($data);
    }
	
    protected function isCollection(array $arr) : bool
    {
        if (isset($arr[0])) {
            return true;
        }
        return false;
    }

    protected function retrieveCollection(array $collection) : array
    {
        $results = [];
        foreach ($collection as $i => $item) {
            $results[$i] = $this->retrieveItem($item);
        }
        return $results;
    }
	
	protected function retrieveIncludes($included)
	{
		if (empty($included)) {
			return;
		}
		
		foreach($included as $item){
			$this->retrieveItem($item);
		}
	}

    protected function retrieveItem(array $item) : JsonApiEntityInterface
    {
        if (empty($item)) {
            return null;
        }
		
		$this->entityObjects[$item['type']][$item['id']] = $this->extract($item);
		
		if (isset($item['relationships'])) {
			$this->mapRelationships($item['type'], $item['id'], $item['relationships']);
		}
		
		return $this->entityObjects[$item['type']][$item['id']];
    }
	
	protected function loadRelationships() 
	{
		if (empty($this->relationships)) {
			return;
		}
		foreach ($this->relationships as $r) {
			if (isset($this->entityObjects[$r['type']][$r['id']]) 
				&& isset($this->entityObjects[$r['relatedType']][$r['relatedId']]) 
			) {
				$methodName = $r['relatedType'];
				
				if (! method_exists($this->entityObjects[$r['type']][$r['id']], $methodName)) {
					throw new Exception("The resource type of the relationship object must match
						the method name to load the related object into the entity.");
				}
				
				$this->entityObjects[$r['type']][$r['id']]->{$methodName}(
					$this->entityObjects[$r['relatedType']][$r['relatedId']]
					);
					
			}
		}
	}
}
