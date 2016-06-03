<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TrunkSoftware\Component\Serializer\Mapping\Loader;

use Symfony\Component\Serializer\Mapping\Loader\LoaderInterface;

use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Serializer\Exception\MappingException;
#use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;

use TrunkSoftware\Component\Serializer\Mapping\AttributeMetadata;

/**
 * Loads XML mapping files.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class PhpLoader implements LoaderInterface
{
	public function loadClassMetadata(ClassMetadataInterface $classMetadata)
	{
		// only custom propel attribute metadata loader
		if( ! $classMetadata->getReflectionClass()->hasMethod('getAttributesMetadata') )
			return true;

		$attributesMetadata = $classMetadata->getAttributesMetadata();

		// load attribute meta data for a given propel class
		$attributes = $classMetadata->getReflectionClass()->getMethod('getAttributesMetadata')->invoke( null );
		// load callbacks for the given propel class
		$callbacks = $classMetadata->getReflectionClass()->hasMethod('getAttributeCallbacks') ? $classMetadata->getReflectionClass()->getMethod('getAttributeCallbacks')->invoke( null ) : NULL;

		foreach ($attributes as $attribute) {
			$attributeName = (string) $attribute['name'];

			if (isset($attributesMetadata[$attributeName])) {
				$attributeMetadata = $attributesMetadata[$attributeName];
			} else {
				$attributeMetadata = new AttributeMetadata($attributeName);
				$classMetadata->addAttributeMetadata($attributeMetadata);
			}

			foreach ($attribute['group'] as $name=>$group) {
				if(is_array($group) || $group instanceof \Traversable) {

				} else {

				}
				$attributeMetadata->addGroup((string) $group);
			}

			if(isset($attribute['convert_name'])) {
				$attributeMetadata->setConvertedName( $attribute['convert_name'] );
			}

			if(isset($attribute['flatten'])) {
				$attributeMetadata->setFlatten( $attribute['flatten'] );
			}

			// there really is only one callback
			// @TODO implement callback per group
			if( $callbacks && isset($callbacks[ $attributeName ]) ){
				$attributeMetadata->setCallback( $callbacks[ $attributeName ] );
			}
		}

		return true;
	}
}
