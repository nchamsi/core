<?php

declare(strict_types=1);

namespace Bolt\Entity\Field;

use Bolt\Configuration\Content\ContentType;
use Bolt\Entity\Field;
use Bolt\Entity\FieldInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ImagelistField extends Field implements FieldInterface
{
    public const TYPE = 'imagelist';

    /**
     * Returns the value, as is in the database. Useful for processing, like
     * editing in the backend, where the results are to be serialised
     */
    public function getRawValue(): array
    {
        return (array) parent::getValue() ?: [];
    }

    /**
     * Returns the value, where the contained fields are "hydrated" as actual
     * Image Fields. For example, for iterating in the frontend
     */
    public function getValue(): array
    {
        $result = [];

        foreach ($this->getRawValue() as $key => $image) {
            $imageField = new ImageField();
            $imageField->setName((string) $key);
            $imageField->setValue($image);
            array_push($result, $imageField);
        }

        return $result;
    }

    public function getDefaultValue(): array
    {
        $result = [];

        $values = parent::getDefaultValue();

        if ($values !== null) {
            /** @var ContentType $image */
            foreach (parent::getDefaultValue() as $key => $image) {
                $image = $image->toArray();
                $imageField = new ImageField();
                $imageField->setName((string) $key);
                $imageField->setValue($image);
                $result[] = $imageField;
            }
        }

        return $result;
    }

    /**
     * Returns the value, where the contained Image fields are seperately
     * casted to arrays, including the "extras"
     */
    public function getJsonValue()
    {
        if ($this->isNew()) {
            $values = $this->getDefaultValue();
        } else {
            $values = $this->getValue();
        }

        return json_encode(array_map(function (ImageField $i) {
            return $i->getValue();
        }, $values));
    }
}
