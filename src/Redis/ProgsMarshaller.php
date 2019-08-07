<?php

declare(strict_types=1);

namespace App\Redis;

use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

class ProgsMarshaller extends DefaultMarshaller implements MarshallerInterface
{
    /**
     * {@inheritdoc}
     */
    public function marshall(array $values, ?array &$failed): array
    {
        $serialized = $failed = [];

        foreach ($values as $id => $value) {
            try {
                $serialized[$id] = $this->serialise($value);
            } catch (\Exception $e) {
                $failed[] = $id;
            }
        }

        return $serialized;
    }

    /**
     * {@inheritdoc}
     */
    public function unmarshall(string $value)
    {
        if ('b:0;' === $value) {
            return false;
        }

        if ('N;' === $value) {
            return null;
        }

        static $igbinaryNull;
        if ($value === ($igbinaryNull ?? $igbinaryNull = $this->serialise(null))) {
            return null;
        }

        $unserializeCallbackHandler = ini_set('unserialize_callback_func', __CLASS__ . '::handleUnserializeCallback');

        try {
            if (null !== $value = $this->unserialise($value)) {
                return $value;
            }
            throw new \DomainException(error_get_last() ? error_get_last()['message'] : 'Failed to unserialize values.');
        } catch (\Error $e) {
            throw new \ErrorException($e->getMessage(), $e->getCode(), E_ERROR, $e->getFile(), $e->getLine());
        } finally {
            ini_set('unserialize_callback_func', $unserializeCallbackHandler);
        }
    }

    /**
     * @internal
     */
    public static function handleUnserializeCallback($class)
    {
        throw new \DomainException('Class not found: ' . $class);
    }

    private function serialise($value)
    {
        return lzf_compress(igbinary_serialize($value));
    }

    private function unserialise($value)
    {
        return igbinary_unserialize(lzf_decompress($value));
    }
}
