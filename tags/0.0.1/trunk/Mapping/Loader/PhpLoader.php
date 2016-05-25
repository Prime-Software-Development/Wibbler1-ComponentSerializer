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
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;

/**
 * Loads XML mapping files.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class PhpLoader implements LoaderInterface
{
    /**
     * An array of {@class \SimpleXMLElement} instances.
     *
     * @var \SimpleXMLElement[]|null
     */
    private $classes;

	private $object;

	public function __construct($object)
	{
		if (!is_object($object)) {
			throw new MappingException(sprintf('Mapping value passed in is not an object'));
		}

		if (!method_exists($object,'getAttributesMetadata')) {
			throw new MappingException(sprintf('Mapping value passed in does not contain metadata'));
		}

		$this->object = $object;
	}

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(ClassMetadataInterface $classMetadata)
    {
        $attributesMetadata = $classMetadata->getAttributesMetadata();

	    foreach ($object = $this->object->getAttributesMetadata() as $attribute) {
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
