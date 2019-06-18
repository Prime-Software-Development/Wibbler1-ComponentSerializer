<?php
namespace Trunk\Component\Serializer\NameConverter;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class PropelNameConverter implements NameConverterInterface
{
	private $attributeMetadata;
	private $context;

	/**
	 * {@inheritdoc}
	 */
	public function normalize($attributesMetadata)
	{
		if( $attributesMetadata->getConvertedName() )
			return $attributesMetadata->getConvertedName();

		return $attributesMetadata->getName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function denormalize($propertyName)
	{
		/*$camelCasedName = preg_replace_callback('/(^|_|\.)+(.)/', function ($match) {
			return ('.' === $match[1] ? '_' : '').strtoupper($match[2]);
		}, $propertyName);

		if ($this->lowerCamelCase) {
			$camelCasedName = lcfirst($camelCasedName);
		}

		if (null === $this->attributes || in_array($camelCasedName, $this->attributes)) {
			return $this->lowerCamelCase ? lcfirst($camelCasedName) : $camelCasedName;
		}*/

		return $propertyName;
	}
}
