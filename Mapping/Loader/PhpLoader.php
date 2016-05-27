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

		$attributes = $classMetadata->getReflectionClass()->getMethod('getAttributesMetadata')->invoke( null );

		foreach ($attributes as $attribute) {
			$attributeName = (string) $attribute['name'];

			if (isset($attributesMetadata[$attributeName])) {
				$attributeMetadata = $attributesMetadata[$attributeName];
			} else {
				$attributeMetadata = new AttributeMetadata($attributeName);
				$classMetadata->addAttributeMetadata($attributeMetadata);
			}

			foreach ($attribute['group'] as $group) {
				$attributeMetadata->addGroup((string) $group);
			}
		}

		return true;
	}
}
