<?php

namespace App\Model\Database\Utils;

use App\Model\Database\Entity\AbstractEntity;
use Nette\Utils\Strings;

class EntityData
{
    private AnnotationParser $parser;

    public function __construct(AnnotationParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param AbstractEntity $entity
     * @param array|object $data
     * @return AbstractEntity|null
     * @throws \Exception
     */
    public function set(AbstractEntity $entity, $data): ?object
    {
        if (!$entity instanceof AbstractEntity) {
            throw new \Exception("Object isn't instance of \\App\\Module\\Database\\Entity\\BaseEntity.");
        }

        $classAnnotation = $this->parser->getClassAnnotationsEntity(get_class($entity));

        foreach ($data as $key => $value) {
            if (isset($classAnnotation['properties'][$key])) {
                $annotation = $classAnnotation['properties'][$key];
                if (isset($annotation['type']) && $annotation['type'] == 'date') {
                    $value = date_create_from_format('j. n. Y', $value);
                    if (!$value)
                        $value = NULL;
                }
                if (isset($annotation['type']) && $annotation['type'] == 'datetime') {
                    if (!$value instanceof \DateTime) {
                        $value = date_create_from_format('j. n. Y H:i', $value);
                        if (!$value)
                            $value = NULL;
                    }
                }
                if (isset($annotation['type']) && $annotation['type'] == 'time') {
                    if (!$value instanceof \DateTime) {
                        $value = date_create_from_format('H:i', $value);
                        if (!$value)
                            $value = null;
                    }
                }
                if (isset($annotation['type']) && $annotation['type'] == 'float') {
                    if (empty($value)) {
                        if (isset($annotation['nullable']) && $annotation['nullable']) {
                            $value = null;
                        } else {
                            $value = 0;
                        }
                    }
                }

                $entity->$key = $value;
            }
        }

        return $entity;
    }

    /**
     * @param AbstractEntity $entity
     * @return array|null
     * @throws \Exception
     */
    public function get(AbstractEntity $entity): ?array
    {
        $data = [];

        $classAnnotation = $this->parser->getClassAnnotationsEntity(get_class($entity));
        foreach ($classAnnotation['properties'] as $propertyname => $annotation) {
            if($propertyname == 'lazyPropertiesNames' || $propertyname == 'lazyPropertiesDefaults') {
                continue;
            }
            if (isset($annotation['oneToMany'])) {
                if (isset($annotation['forms']['default']['multiselect-entity']['target'])) {
                    $list = [];
                    $target = $annotation['forms']['default']['multiselect-entity']['target'];
                    if ($entity->$propertyname) {
                        foreach ($entity->$propertyname as $collection) {
                            if ($collection->$target) {
                                $list[] = $collection->$target->id;
                            }
                        }
                    }
                    $data[$propertyname] = $list;
                }
                continue;
            }
            if (is_object($entity->$propertyname)) {
                $reflectionProperty = new \ReflectionClass(get_class($entity->$propertyname));
            }
            if (is_object($entity->$propertyname) && stripos(get_class($entity->$propertyname), 'DateTime') !== false) {
                if (isset($annotation['type']) && $annotation['type'] == 'datetime') {
                    $data[$propertyname] = $entity->$propertyname->format('j. n. Y H:i');
                } elseif (isset($annotation['type']) && $annotation['type'] == 'date') {
                    $data[$propertyname] = $entity->$propertyname->format('j. n. Y');
                } elseif (isset($annotation['type']) && $annotation['type'] == 'time') {
                    $data[$propertyname] = $entity->$propertyname->format('H:i');
                } else {
                    $data[$propertyname] = $entity->$propertyname->format('j. n. Y');
                }
            } else if (is_object($entity->$propertyname)
                && isset($reflectionProperty) && $reflectionProperty->hasProperty('id')
            ) {
                $data[$propertyname] = $entity->$propertyname->id;
            } else if (is_array($entity->$propertyname)) {
                $list = [];
                foreach ($entity->$propertyname as $collection) {
                    $list[] = $collection->id;
                }
                $data[$propertyname] = $list;
            } else {
                $data[$propertyname] = $entity->$propertyname;
            }
        }

        return $data;
    }
}