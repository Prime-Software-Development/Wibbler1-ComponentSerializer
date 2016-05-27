<?php
namespace TrunkSoftware\Component\Serializer\Mapping;

use Symfony\Component\Serializer\Mapping\AttributeMetadata as BaseAttributeMetadata;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;

class AttributeMetadata extends BaseAttributeMetadata {

	public $callback;

	public $flatten;

	/**
	 * {@inheritdoc}
	 */
	public function __construct($name)
	{
		$this->name = $name;
		$this->flatten = false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function merge(AttributeMetadataInterface $attributeMetadata)
	{
		foreach ($attributeMetadata->getGroups() as $group) {
			$this->addGroup($group);
		}

		foreach ($attributeMetadata->getCallbacks() as $callback) {
			$this->addCallback($callback);
		}
	}

	// if this attribute is an array/object
	//whether to merge it into parent object array
	public function setFlatten( $flatten = false )
	{
		$this->flatten = $flatten;
	}

	public function getFlatten()
	{
		return $this->flatten;
	}

	public function setCallback( $callback )
	{
		$this->callback = $callback;

		return $this;
	}
}