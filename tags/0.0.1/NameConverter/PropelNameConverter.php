<?php
namespace TrunkSoftware\Component\Serializer\NameConverter;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class PropelNameConverter implements NameConverterInterface
{
    private $attributeMetadata;
    private $object;
	private $context;

	/**
	 * PropelNameConverter constructor.
	 *
	 * @param null  $object Object we are converting attributes for
	 * @param array $context
	 */
    public function __construct($object = null, array $context = array() )
    {
	    $this->setObject( $object, $context );
    }

	public function setObject( $object, array $context)
	{
		if( $object instanceof ActiveRecordInterface ) {
			$this->context = $context;
			$this->object = $object;
			$this->attributeMetadata = array();
			if ( $object ) {
				$attributeMetadata = $object->getAttributesMetadata();

				foreach ( $attributeMetadata as $meta ) {
					if ( isset( $meta[ 'convert_name' ] ) ) {
						$this->attributeMetadata[ $meta[ 'name' ] ] = $meta[ 'convert_name' ];
					}
				}
			}
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
