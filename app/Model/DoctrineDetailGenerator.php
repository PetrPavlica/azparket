<?php

namespace App\Model;

use App\Model\Database\Entity\AbstractEntity;
use App\Model\Database\Utils\AnnotationParser;
use Nette;

class DoctrineDetailGenerator
{
    use Nette\SmartObject;

    /** @var AnnotationParser */
    private $parser;

    /** @var array */
    private $sections;

    /** @var array */
    private $sectionProperties;

    /** @var array */
    private $titles;

    /** @var array */
    private $properties;

    public function __construct(AnnotationParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Generate form by doctrine annotation. Prepare all form whit save/update method.
     * @param AbstractEntity $entity
     * @param string $keyForm
     * @return DoctrineDetailGenerator
     * @throws \Exception
     */
    public function generateDetailByAnnotation(AbstractEntity $entity, $keyForm = 'default')
    {
        $classAnnotations = $this->parser->getClassAnnotationsEntity(get_class($entity));

        $this->sections = array_keys($classAnnotations['detail-sections']);
        $this->titles = $classAnnotations['detail-titles'];

        uasort($classAnnotations['properties'], function($it1, $it2) {
            $a = $it1['details']['default']['order'] ?? PHP_MAXPATHLEN;
            $b = $it2['details']['default']['order'] ?? PHP_MAXPATHLEN;
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });

        $annotations = $classAnnotations['properties'];

        $sectionProperties = [];

        foreach ($annotations as $name => $annotation) {
            // if property dont have annotation - dont create component
            if (count($annotation['details']) == 0) {
                continue;
            }

            $defaultAnnotation = $annotation['details']['default'];
            if (array_key_exists($keyForm, $annotation['details'])) {
                $defaultAnnotation = array_merge($defaultAnnotation, $annotation['details'][$keyForm]);
            }

            $section = $defaultAnnotation['section'] ?? 'default';
            if (!isset($sectionProperties[$section])) {
                $sectionProperties[$section] = [];
            }

            $property = [
                'value' => ''
            ];

            $value = '';

            if (isset($defaultAnnotation['title'])) {
                $property['title'] = $defaultAnnotation['title'];
            }

            if (isset($defaultAnnotation['data-own'])) {
                $value = $defaultAnnotation['data-own'][$entity->$name] ?? null;
            } else if (isset($annotation['manyToOne']) && isset($defaultAnnotation['entity-value'])) {
                $value = $defaultAnnotation['entity-value'];
                preg_match_all('/\$.*?\$/', $value, $matches);
                foreach ($matches[0] as $m) {
                    $c = str_replace('$', '', $m);
                    $v = $entity->$name->$c;
                    $value = trim(str_replace($m, $v, $value), " \t\n\r\0\x0B*,");
                }
            } else if (isset($annotation['type'])) {
                switch ($annotation['type']) {
                    case 'text':
                    case 'string':
                    case 'integer':
                    case 'float':
                        $value = $entity->$name;
                        break;
                    case 'date':
                        $value = $entity->$name ? $entity->$name->format('j. n. Y') : '';
                        break;
                    case 'datetime':
                        $value = $entity->$name ? $entity->$name->format('j. n. Y H:i') : '';
                        break;
                }
            } else if (isset($annotation['manyToOne']) && isset($defaultAnnotation['entity-column'])) {
                $col = $defaultAnnotation['entity-column'];
                $value = $entity->$name->$col;
            } else if (isset($annotation['oneToMany']) && isset($defaultAnnotation['entity-value'])) {
                $value = $defaultAnnotation['entity-value'];
                $values = [];
                if ($entity->$name) {
                    preg_match_all('/\$.*?\$/', $value, $matches);
                    foreach ($entity->$name as $v) {
                        $value = $defaultAnnotation['entity-value'];
                        foreach ($matches[0] as $m) {
                            $c = str_replace('$', '', $m);
                            $vv = $v->$c;
                            $value = trim(str_replace($m, $vv, $value), " \t\n\r\0\x0B*,");
                        }
                        $values[] = $value;
                    }
                }
                $value = implode('<br>', $values);
            } else if (isset($annotation['oneToMany']) && isset($defaultAnnotation['entity-join-column'])) {
                $joinCol = $defaultAnnotation['entity-join-column'];
                $col = $defaultAnnotation['entity-column'];
                $values = [];
                if ($entity->$name) {
                    foreach ($entity->$name as $v) {
                        $values[] = $v->$joinCol->$col;
                    }
                }
                $value = implode(', ', $values);
            }

            $property['value'] = $value;

            $this->properties[$name] = $property;
            $sectionProperties[$section][] = $name;
        }

        $this->sectionProperties = $sectionProperties;

        return $this;
    }

    public function getSections(): array
    {
        return $this->sections;
    }

    public function getSectionProperty($section): ?array
    {
        return $this->sectionProperties[$section] ?? null;
    }

    public function getSectionTitle($section)
    {
        return $this->titles[$section] ?? null;
    }

    public function existProperty($property): bool
    {
        return isset($this->properties[$property]);
    }

    public function getPropertyTitle($property): ?string
    {
        return $this->properties[$property]['title'] ?? null;
    }

    public function getPropertyValue($property): ?string
    {
        return $this->properties[$property]['value'] ?? null;
    }
}