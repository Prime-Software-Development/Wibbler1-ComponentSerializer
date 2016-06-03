<?php
namespace TrunkSoftware\Component\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\LogicException;

/**
 * Converts between objects and arrays using the PropertyAccess component.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class DateTimeNormalizer implements NormalizerInterface
{
	/*
	    const ATOM = 'Y-m-d\TH:i:sP';
	    const COOKIE = 'l, d-M-y H:i:s T';
	    const ISO8601 = 'Y-m-d\TH:i:sO';
	    const RFC822 = 'D, d M y H:i:s O';
	    const RFC850 = 'l, d-M-y H:i:s T';
	    const RFC1036 = 'D, d M y H:i:s O';
	    const RFC1123 = 'D, d M Y H:i:s O';
	    const RFC2822 = 'D, d M Y H:i:s O';
	    const RFC3339 = 'Y-m-d\TH:i:sP';
	    const RSS = 'D, d M Y H:i:s O';
	    const W3C = 'Y-m-d\TH:i:sP';
	 */
	protected $format = \DateTime::ISO8601;

	public function __construct( $format = null ) {
		if( $format ) {
			$this->format = $format;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsNormalization($data, $format = null)
	{
		// supports php \DateTime
		return $data instanceof \DateTime;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws CircularReferenceException
	 */
	public function normalize($object, $format = null, array $context = array())
	{
		return $object->format( $this->format );
	}

}
