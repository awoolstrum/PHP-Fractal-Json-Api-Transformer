<?php

declare(strict_types=1);

namespace App\Transformer;

use League\Fractal\TransformerAbstract;
use App\Entity\Region;
use awoolstrum\JsonApi\JsonApiTransformerInterface;

final class RegionTransformer extends TransformerAbstract implements JsonApiTransformerInterface
{
	protected $availableIncludes = [
		'locations'
	];
	
	public function transform(Region $region)
	{
		return [
			'id' => (int) $region->id,
			'name' => $region->name
		];
	}
	
	public function load(array $v)
	{
		return new Region($v['id'], $v['name']);
	}
}