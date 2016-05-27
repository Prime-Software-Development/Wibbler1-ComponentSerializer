<?php
namespace TrunkSoftware\Component\Serializer\NameConverter;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class PropelNameConverter implements NameConverterInterface
{
	private $attributeMetadata;
	private $context;

	public function setAttributeMetadata( AttributeMetadataInterface $attributesMetadata)
	{
		$this->attributeMetadata = array();

		if ( $converted_name = $attributesMetadata->getConvertedName() ) {
			$this->attributeMetadata[ $attributesMetadata->getName() ] = $converted_name;
		}

	}

	/**
	 * {@inheritdoc}
	 */
	public function normalize($propertyName)
	{
		if ( isset($this->attributeMetadata[ $propertyName ] ) ) {
			return $this->attributeMetadata[ $propertyName ];
		}

		return $propertyName;
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