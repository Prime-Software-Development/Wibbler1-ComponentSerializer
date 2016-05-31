<?php
namespace TrunkSoftware\Component\Serializer\Mapping;

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
		$this->flatten = false;
		$this->converted_name = false;
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

	public function setConvertedName( $name )
	{
		$this->converted_name = $name;
	}

	public function getConvertedName()
	{
		return $this->converted_name;
	}

	public function setCallback( $callback )
	{
		$this->callback = $callback;

		return $this;
	}

	public function getCallback()
	{
		return $this->callback;
	}
}