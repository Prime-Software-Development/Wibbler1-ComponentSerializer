<?php
namespace Trunk\Component\Serializer\Mapping;

use Symfony\Component\Serializer\Mapping\AttributeMetadata as BaseAttributeMetadata;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;

class AttributeMetadata extends BaseAttributeMetadata {

	public $callback;

	public $flatten;

	public $converted_name;

	/**
	 * {@inheritdoc}
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function merge(AttributeMetadataInterface $attributeMetadata)
	{
		foreach ($attributeMetadata->getGroups() as $group) {
			$this->addGroup($group);
		}
	}
}
