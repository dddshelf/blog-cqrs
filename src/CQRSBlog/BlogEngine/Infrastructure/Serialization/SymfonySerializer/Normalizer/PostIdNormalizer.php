<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Serialization\SymfonySerializer\Normalizer;

use CQRSBlog\BlogEngine\DomainModel\PostId;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

final class PostIdNormalizer
    extends SerializerAwareNormalizer
    implements NormalizerInterface, DenormalizerInterface
{
    /**
     * Denormalizes data back into an object of the given class
     *
     * @param mixed $data data to restore
     * @param string $class the expected class to instantiate
     * @param string $format format the given data was extracted from
     * @param array $context options available to the denormalizer
     *
     * @return object
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return PostId::fromString($data['serialized_post_id']);
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer
     *
     * @param mixed $data Data to denormalize from.
     * @param string $type The class to which the data should be denormalized.
     * @param string $format The format being deserialized from.
     *
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            is_array($data)
            && isset($data['serialized_post_id'])
            && Uuid::isValid($data['serialized_post_id'])
        ;
    }

    /**
     * Normalizes an object into a set of arrays/scalars
     *
     * @param object $object object to normalize
     * @param string $format format the normalization result will be encoded as
     * @param array $context Context options for the normalizer
     *
     * @return array|scalar
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return ['serialized_post_id' => (string) $object];
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer
     *
     * @param mixed $data Data to normalize.
     * @param string $format The format being (de-)serialized from or into.
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PostId  ;
    }
}