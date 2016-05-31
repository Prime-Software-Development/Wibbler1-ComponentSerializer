<?php
namespace TrunkSoftware\Component\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;

use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;

use Propel\Runtime\Collection\ObjectCollection as PropelCollection;

/**
 * Converts between objects and arrays using the PropertyAccess component.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class PropelNormalizer extends AbstractNormalizer
{
	private $attributesCache = array();

	/**
	 * @var PropertyAccessorInterface
	 */
	protected $propertyAccessor;

	public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null, PropertyAccessorInterface $propertyAccessor = null)
	{
		parent::__construct($classMetadataFactory, $nameConverter);

		$this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsNormalization($data, $format = null)
	{
		// supports any propel 1.7 object
		return ($data instanceof ActiveRecordInterface) && method_exists($data,'getAttributesMetadata');
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws CircularReferenceException
	 */
	public function normalize($object, $format = null, array $context = array())
	{
		if (!isset($context['cache_key'])) {
			$context['cache_key'] = $this->getCacheKey($context);
		}
		if ($this->isCircularReference($object, $context)) {
			return $this->handleCircularReference($object);
		}

		$data = array();
		$attributes_meta = $this->getAttributes($object, $context);

		if ($this->nameConverter) {
			$this->nameConverter->setObject( $object, $context );
		}

		foreach ($attributes_meta as $attribute_meta) {
			$attribute = $attribute_meta->getName();

			if (in_array($attribute, $this->ignoredAttributes)) {
				continue;
			}

			$attributeValue = $this->propertyAccessor->getValue($object, $attribute);

			if (isset($this->callbacks[$attribute])) {
				$attributeValue = call_user_func($this->callbacks[$attribute], $attributeValue);
			}

			if (null !== $attributeValue && !is_scalar($attributeValue)) {
				if (!$this->serializer instanceof NormalizerInterface) {
					throw new LogicException(sprintf('Cannot normalize attribute "%s" because injected serializer is not a normalizer', $attribute));
				}

				$attributeValue = $this->serializer->normalize($attributeValue, $format, $context);
			}

			if ($this->nameConverter) {
				$attribute = $this->nameConverter->normalize($attribute);
			}


			// whether to merge child object properties into parent data array
			if( $attribute_meta->getFlatten() ) {
				$data = array_merge( $data, $attributeValue );
			} else{
				$data[$attribute] = $attributeValue;
			}
		}

		return $data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsDenormalization($data, $type, $format = null)
	{
		return class_exists($type);
	}

	/**
	 * {@inheritdoc}
	 */
	public function denormalize($data, $class, $format = null, array $context = array())
	{
		if (!isset($context['cache_key'])) {
			$context['cache_key'] = $this->getCacheKey($context);
		}
		$allowedAttributes = $this->getAllowedAttributes($class, $context, true);
		$normalizedData = $this->prepareForDenormalization($data);

		$reflectionClass = new \ReflectionClass($class);
		$object = $this->instantiateObject($normalizedData, $class, $context, $reflectionClass, $allowedAttributes);

		foreach ($normalizedData as $attribute => $value) {
			if ($this->nameConverter) {
				$attribute = $this->nameConverter->denormalize($attribute);
			}

			$allowed = $allowedAttributes === false || in_array($attribute, $allowedAttributes);
			$ignored = in_array($attribute, $this->ignoredAttributes);

			if ($allowed && !$ignored) {
				try {
					$this->propertyAccessor->setValue($object, $attribute, $value);
				} catch (NoSuchPropertyException $exception) {
					// Properties not found are ignored
				}
			}
		}

		return $object;
	}

	private function getCacheKey(array $context)
	{
		try {
			return md5(serialize($context));
		} catch (\Exception $exception) {
			// The context cannot be serialized, skip the cache
			return false;
		}
	}

	/**
	 * Gets and caches attributes for this class and context.
	 *
	 * @param object $object
	 * @param array  $context
	 *
	 * @return string[]
	 */
	public function getAttributes($object, array $context)
	{
		$class = get_class($object);
		$key = $class.'-'.$context['cache_key'];

		if (isset($this->attributesCache[$key])) {
			return $this->attributesCache[$key];
		}

		#$allowedAttributes = $this->getAllowedAttributes($object, $context, true);
		$allowedAttributes = $this->getAllowedAttributes($object, $context);

		if (false !== $allowedAttributes) {
			if ($context['cache_key']) {
				$this->attributesCache[$key] = $allowedAttributes;
			}

			return $allowedAttributes;
		}

		if (isset($this->attributesCache[$class])) {
			return $this->attributesCache[$class];
		}

		return $this->attributesCache[$class] = $this->extractAttributes($object);
	}

	/**
	 * Extracts attributes for this class and context.
	 *
	 * @param object $object
	 *
	 * @return string[]
	 */
	private function extractAttributes($object)
	{
		// If not using groups, detect manually
		$attributes = array();

		// methods
		$reflClass = new \ReflectionClass($object);
		foreach ($reflClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflMethod) {
			if (
				$reflMethod->getNumberOfRequiredParameters() !== 0 ||
				$reflMethod->isStatic() ||
				$reflMethod->isConstructor() ||
				$reflMethod->isDestructor()
			) {
				continue;
			}

			$name = $reflMethod->name;

			if (0 === strpos($name, 'get') || 0 === strpos($name, 'has')) {
				// getters and hassers
				$attributes[lcfirst(substr($name, 3))] = true;
			} elseif (strpos($name, 'is') === 0) {
				// issers
				$attributes[lcfirst(substr($name, 2))] = true;
			}
		}

		// properties
		foreach ($reflClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $reflProperty) {
			if ($reflProperty->isStatic()) {
				continue;
			}

			$attributes[$reflProperty->name] = true;
		}

		foreach($object->getVirtualColumns() as $virtualColumn ) {
			$attributes[$virtualColumn] = true;
		}

		#die( var_export( $attributes, true ) );

		return array_keys($attributes);
	}
}