<?php
namespace TrunkSoftware\Component\Serializer\Mapping;

use Symfony\Component\Serializer\Mapping\AttributeMetadata as BaseAttributeMetadata;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;

class AttributeMetadata extends BaseAttributeMetadata {

	public $callback;

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

		foreach ($attributeMetadata->getCallbacks() as $callback) {
			$this->addCallback($callback);
		}
	}

	public function setCallback( $callback )
	{
		$this->callback = $callback;

		return $this;
	}
}