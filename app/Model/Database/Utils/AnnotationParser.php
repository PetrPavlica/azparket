<?php

namespace App\Model\Database\Utils;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;

final class AnnotationParser
{
    private Reader $reader;
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    private $classAnnotationArr = [];

    public function getClassAnnotationsEntity($class): array
    {
        if (isset($this->classAnnotationArr[$class])) {
            return $this->classAnnotationArr[$class];
        }
        $nextOrderNumber = $nextDetailOrderNumber = PHP_MAXPATHLEN - 1000;
        $arr = [];
        try {
            $r = new \ReflectionClass($class);
            foreach ($this->reader->getClassAnnotations($r) as $c) {
                if ($c instanceof Entity) {
                    $arr['repositoryClass'] = $c->repositoryClass;
                } else if ($c instanceof Table) {
                    $arr['name'] = $c->name;
                    $arr['schema'] = $c->schema;
                    $arr['indexes'] = $c->indexes;
                    $arr['uniqueConstraints'] = $c->uniqueConstraints;
                    $arr['options'] = $c->options;
                }
            }

            $sections = [
                'form' => [
                    'default' => 100
                ],
                'detail' => [
                    'default' => 100
                ]
            ];
            try {
                preg_match_all('/.*((?:FORM-SECTION|DETAIL-SECTION)).*/i', $r->getDocComment(), $matches);
                if (isset($matches[0])) {
                    foreach ($matches[0] as $k => $line) {
                        $line = trim($line, ' *');
                        $line = trim(substr($line, mb_strlen($matches[1][$k])));
                        [$section, $order] = explode('=', $line);
                        [$type, $sec] = explode('-', $matches[1][$k]);
                        $sections[mb_strtolower($type)][$section] = intval($order);
                    }
                }
            } catch (\Exception $ex) {}
            $arr['form-sections'] = $sections['form'];
            $arr['detail-sections'] = $sections['detail'];
            asort($arr['form-sections']);
            asort($arr['detail-sections']);

            $titles = [];
            try {
                preg_match_all('/.*((?:DETAIL-TITLE-SECTION)).*/i', $r->getDocComment(), $matches);
                if (isset($matches[0])) {
                    foreach ($matches[0] as $k => $line) {
                        $line = trim($line, ' *');
                        $line = trim(substr($line, mb_strlen($matches[1][$k])));
                        [$section, $title] = explode('=', $line);
                        $titles[$section] = $title;
                    }
                }
            } catch (\Exception $ex) {}
            $arr['detail-titles'] = $titles;

            foreach ($r->getProperties() as $p) {
                $column = [];
                foreach ($this->reader->getPropertyAnnotations($p) as $a) {
                    if ($a instanceof Id) {
                        $column = array_merge($column, [
                            'primaryKey' => true
                        ]);
                    } else if ($a instanceof GeneratedValue) {
                        $column = array_merge($column, [
                            'generatedValue' => [
                                'strategy' => $a->strategy
                            ]
                        ]);
                    } else if ($a instanceof Column) {
                        $column = array_merge($column, [
                            'name' => $a->name,
                            'type' => $a->type,
                            'length' => $a->length,
                            'precision' => $a->precision,
                            'scale' => $a->scale,
                            'unique' => $a->unique,
                            'nullable' => $a->nullable,
                            'options' => $a->options,
                            'columnDefinition' => $a->columnDefinition
                        ]);
                    } else if ($a instanceof OneToMany) {
                        $column = array_merge($column, [
                            'oneToMany' => [
                                'mappedBy' => $a->mappedBy,
                                'targetEntity' => $a->targetEntity,
                                'cascade' => $a->cascade,
                                'fetch' => $a->fetch,
                                'orphanRemoval' => $a->orphanRemoval,
                                'indexBy' => $a->indexBy
                            ]
                        ]);
                    } else if ($a instanceof ManyToOne) {
                        $column = array_merge($column, [
                            'manyToOne' => [
                                'targetEntity' => $a->targetEntity,
                                'cascade' => $a->cascade,
                                'fetch' => $a->fetch,
                                'inversedBy' => $a->inversedBy
                            ]
                        ]);
                    } else if ($a instanceof OneToOne) {
                        $column = array_merge($column, [
                            'oneToOne' => [
                                'targetEntity' => $a->targetEntity,
                                'mappedBy' => $a->mappedBy,
                                'inversedBy' => $a->inversedBy,
                                'cascade' => $a->cascade,
                                'fetch' => $a->fetch,
                                'orphanRemovel' => $a->orphanRemoval
                            ]
                        ]);
                    } else if ($a instanceof OrderBy) {
                        $column = array_merge($column, [
                            'orderBy' => $a->value
                        ]);
                    } else if ($a instanceof JoinColumn) {
                        $column = array_merge($column, [
                            'joinColumn' => [
                                'name' => $a->name,
                                'referencedColumnName' => $a->referencedColumnName,
                                'unique' => $a->unique,
                                'nullable' => $a->nullable,
                                'onDelete' => $a->onDelete,
                                'columnDefinition' => $a->columnDefinition,
                                'fieldName' => $a->fieldName
                            ]
                        ]);
                    }
                }
                preg_match_all('/.*((?:FORM|GRID|DETAIL)).*/', $p->getDocComment(), $matches);

                $forms = $grids = $details = [];
                if (isset($matches[0]) && count($matches[0])) {
                    foreach ($matches[0] as $k => $m) {
                        $line = trim($m, " \t\n\r\0\x0B*");
                        $type = mb_strtoupper($matches[1][$k]);
                        $subtype = 'default';
                        preg_match('/'.$type.'\\[.*?\]/is', $line, $typeMatch);
                        if (isset($typeMatch[0])) {
                            $subtype = str_replace([$type, '[', ']'], '', $typeMatch[0]);
                        }
                        $value = trim(str_replace('['.$subtype.']', '', substr($line, mb_strlen($type))));
                        if ($type == 'FORM') {
                            list($key, $data) = $this->getAnnotationValues($value);
                            $forms[$subtype][$key] = $data;
                        } else if ($type == 'GRID') {
                            list($key, $data) = $this->getAnnotationValues($value);
                            $grids[$subtype][$key] = $data;
                        } else if ($type == 'DETAIL') {
                            list($key, $data) = $this->getAnnotationValues($value);
                            $details[$subtype][$key] = $data;
                        }
                    }
                    if (isset($forms['default']) && !isset($forms['default']['section'])) {
                        $forms['default']['section'] = 'default';
                    }
                    if (isset($forms['default'])) {
                        if (isset($forms['default']['order'])) {
                            $forms['default']['order'] = intval($forms['default']['order']);
                        } else {
                            $forms['default']['order'] = $nextOrderNumber;
                            $nextOrderNumber++;
                        }
                    }
                    if (isset($details['default']) && !isset($details['default']['section'])) {
                        $details['default']['section'] = 'default';
                    }
                    if (isset($details['default'])) {
                        if (isset($details['default']['order'])) {
                            $details['default']['order'] = intval($details['default']['order']);
                        } else {
                            $details['default']['order'] = $nextDetailOrderNumber;
                            $nextDetailOrderNumber++;
                        }
                    }
                }

                $column['forms'] = $forms;
                $column['grids'] = $grids;
                $column['details'] = $details;

                $arr['properties'][$p->getName()] = $column;
            }
        } catch (\Exception $ex) {}

        $this->classAnnotationArr[$class] = $arr;

        return $arr;
    }

    public function getAnnotationValues($value): array
    {
        $key = $val = null;
        $index = strpos($value, '=');
        if ($index !== false) {
            $key = mb_substr($value, 0, $index);
            $val = trim(mb_substr($value, $index + 1), " \t\n\r\0\x0B");
            $firstChar = mb_substr($val, 0, 1);
            if ($firstChar === '"' || $firstChar === "'") {
                $val = mb_substr($val, 1);
            }
            $lastChar = mb_substr($val, mb_strlen($val) - 1, 1);
            if ($lastChar === '"' || $lastChar === "'") {
                $val = mb_substr($val, 0, mb_strlen($val) - 1);
            }

            if ($key == 'filter') {
                if (mb_substr($val, 0, 13) == 'single-entity') {
                    $newVal = [
                        'type' => 'single-entity',
                        'column' => null,
                        'order' => null
                    ];

                    preg_match_all('/\\[.*?\]/is', mb_substr($val, 13), $matches);
                    if (isset($matches[0][0])) {
                        $newVal['column'] = trim(str_replace(['[', ']'], '', $matches[0][0]), " \t\n\r\0\x0B'");
                    }
                    if (isset($matches[0][1])) {
                        $parseVal = trim($matches[0][1], " \t\n\r\0\x0B'");
                        if (strpos($parseVal, '=>') === false) {
                            $parseVal = str_replace(['>', '|'], ['=>', ','], $parseVal);
                        }
                        $newVal['order'] = $this->getArrayValues($parseVal);
                    }

                    $val = $newVal;
                } else if (mb_substr($val, 0, 6) == 'single') {
                    $val = [
                        'type' => 'single'
                    ];
                } else if (mb_substr($val, 0, 5) == 'range') {
                    $val = [
                        'type' => 'range'
                    ];
                } else if (mb_substr($val, 0, 10) == 'date-range') {
                    $val = [
                        'type' => 'date-range'
                    ];
                } else if (mb_substr($val, 0, 4) == 'date') {
                    $val = [
                        'type' => 'date'
                    ];
                } else if (mb_substr($val, 0, 18) == 'multiselect-entity') {
                    $newVal = [
                        'type' => 'multiselect-entity',
                        'column' => null,
                        'order' => null
                    ];

                    preg_match_all('/\\[.*?\]/is', mb_substr($val, 18), $matches);
                    if (isset($matches[0][0])) {
                        $newVal['column'] = trim(str_replace(['[', ']'], '', $matches[0][0]), " \t\n\r\0\x0B'");
                    }
                    if (isset($matches[0][1])) {
                        $parseVal = trim($matches[0][1], " \t\n\r\0\x0B'");
                        if (strpos($parseVal, '=>') === false) {
                            $parseVal = str_replace(['>', '|'], ['=>', ','], $parseVal);
                        }
                        $newVal['order'] = $this->getArrayValues($parseVal);
                    }

                    $val = $newVal;
                } else if (mb_substr($val, 0, 34) == 'multicolumnname-multiselect-entity') {
                    $newVal = [
                        'type' => 'multicolumnname-multiselect-entity',
                        'column' => null,
                        'order' => null
                    ];

                    preg_match_all('/\\[.*?\]/is', mb_substr($val, 34), $matches);
                    if (isset($matches[0][0])) {
                        $newVal['column'] = trim(str_replace(['[', ']'], '', $matches[0][0]), " \t\n\r\0\x0B'");
                    }
                    if (isset($matches[0][1])) {
                        $parseVal = trim($matches[0][1], " \t\n\r\0\x0B'");
                        if (strpos($parseVal, '=>') === false) {
                            $parseVal = str_replace(['>', '|'], ['=>', ','], $parseVal);
                        }
                        $newVal['order'] = $this->getArrayValues($parseVal);
                    }

                    $val = $newVal;
                } elseif (mb_substr($val, 0, 13) === 'select-entity') {
                    $newVal = [
                        'type' => 'select-entity',
                        'column' => null,
                        'order' => null
                    ];

                    preg_match_all('/\\[.*?\]/is', mb_substr($val, 13), $matches);
                    if (isset($matches[0][0])) {
                        $newVal['column'] = trim(str_replace(['[', ']'], '', $matches[0][0]), " \t\n\r\0\x0B'");
                    }
                    if (isset($matches[0][1])) {
                        $parseVal = trim($matches[0][1], " \t\n\r\0\x0B'");
                        if (strpos($parseVal, '=>') === false) {
                            $parseVal = str_replace(['>', '|'], ['=>', ','], $parseVal);
                        }
                        $newVal['order'] = $this->getArrayValues($parseVal);
                    }

                    $val = $newVal;
                } elseif (mb_substr($val, 0, 6) === 'select') {
                    $newVal = [
                        'type' => 'select',
                        'values' => []
                    ];
                    $parseVal = trim(str_replace(['#', '(', ')'], ['', '', ''], mb_substr($val, 6)));
                    if (strpos($parseVal, '=>') === false) {
                        $parseVal = str_replace(['>', '|'], ['=>', ','], $parseVal);
                    }
                    $newVal['values'] = $this->getArrayValues($parseVal);
                    $val = $newVal;
                }
            } else if (in_array($key, ['replacement', 'data-own'])) {
                if (mb_substr($value, 0, mb_strlen($key)) == $key) {
                    $val = [];
                    preg_match_all('/\\[.*?\]/is', mb_substr($value, mb_strlen($key)), $matches);
                    if (isset($matches[0][0])) {
                        $parseVal = trim($matches[0][0], " \t\n\r\0\x0B'");
                        if (strpos($parseVal, '=>') === false) {
                            $parseVal = str_replace(['>', '|'], ['=>', ','], $parseVal);
                        }
                        $val = $this->getArrayValues($parseVal);
                    }
                }
            } else if ($key == 'data-entity') {
                $newVal = [
                    'entity' => null,
                    'column' => null,
                    'findBy' => [],
                    'order' => null
                ];

                $index = strpos($val, '[');
                if ($index !== false) {
                    $entity = mb_substr($val, 0, $index);
                } else {
                    $entity = $val;
                }
                $newVal['entity'] = stripos($entity, 'App\\') !== false ?: 'App\Model\Database\Entity\\' . $entity;

                preg_match_all('/\[.*?\]/is', $val, $matches);
                if (isset($matches[0][0])) {
                    $newVal['column'] = trim(str_replace(['[', ']'], '', $matches[0][0]), " \t\n\r\0\x0B'");
                }
                if (isset($matches[0][1])) {
                    $parseVal = trim($matches[0][1], " \t\n\r\0\x0B'");
                    if (strpos($parseVal, '=>') === false) {
                        $parseVal = str_replace(['>', '|'], ['=>', ','], $parseVal);
                    }
                    $newVal['findBy'] = $this->getArrayValues($parseVal) ?: [];
                }
                if (isset($matches[0][2])) {
                    $parseVal = trim($matches[0][2], " \t\n\r\0\x0B'");
                    if (strpos($parseVal, '=>') === false) {
                        $parseVal = str_replace(['>', '|'], ['=>', ','], $parseVal);
                    }
                    $newVal['order'] = $this->getArrayValues($parseVal);
                }

                $val = $newVal;
            } else if ($key == 'data-entity-values') {
                $newVal = [
                    'entity' => null,
                    'column' => null,
                    'findBy' => [],
                    'order' => null
                ];

                $index = strpos($val, '[');
                if ($index !== false) {
                    $entity = mb_substr($val, 0, $index);
                } else {
                    $entity = $val;
                }
                $newVal['entity'] = stripos($entity, 'App\\') !== false ?: 'App\Model\Database\Entity\\' . $entity;

                preg_match_all('/\[.*?\]/is', $val, $matches);
                if (isset($matches[0][0])) {
                    $newVal['column'] = trim(str_replace(['[', ']'], '', $matches[0][0]), " \t\n\r\0\x0B'");
                }
                if (isset($matches[0][1])) {
                    $parseVal = trim($matches[0][1], " \t\n\r\0\x0B'");
                    if (strpos($parseVal, '=>') === false) {
                        $parseVal = str_replace(['>', '|'], ['=>', ','], $parseVal);
                    }
                    $newVal['findBy'] = $this->getArrayValues($parseVal) ?: [];
                }
                if (isset($matches[0][2])) {
                    $parseVal = trim($matches[0][2], " \t\n\r\0\x0B'");
                    if (strpos($parseVal, '=>') === false) {
                        $parseVal = str_replace(['>', '|'], ['=>', ','], $parseVal);
                    }
                    $newVal['order'] = $this->getArrayValues($parseVal);
                }

                $val = $newVal;
            } else if ($key == 'multiselect-entity') {
                $newVal = [
                    'entity' => null,
                    'source' => null,
                    'target' => null
                ];

                $index = strpos($val, '[');
                if ($index !== false) {
                    $entity = mb_substr($val, 0, $index);
                } else {
                    $entity = $val;
                }
                $newVal['entity'] = stripos($entity, 'App\\') !== false ?: 'App\Model\Database\Entity\\' . $entity;

                preg_match_all('/\[.*?\]/is', $val, $matches);

                if (isset($matches[0][0])) {
                    $newVal['source'] = trim(str_replace(['[', ']'], '', $matches[0][0]), " \t\n\r\0\x0B'");
                }

                if (isset($matches[0][1])) {
                    $newVal['target'] = trim(str_replace(['[', ']'], '', $matches[0][1]), " \t\n\r\0\x0B'");
                }

                $val = $newVal;
            }
        }

        return [$key, $val];
    }

    public function getArrayValues($value)
    {
        try {
            $arr = null;
            eval("\$arr = $value;");
            return $arr;
        } catch (\ParseError $ex) {}

        return null;
    }

    public function getClassAnnotations($class, $prefix)
    {
        $r = new \ReflectionClass($class);
        preg_match_all('#' . $prefix . '(.*?)\n#s', $r->getDocComment(), $i);
        $annotation = [];
        if ($i[1]) {
            foreach ($i[1] as $a) {
                $ann = self::cleanAnnotation($a);
                $annotation[$ann[0]] = $ann[1];
            }
        }
        return $annotation;
    }

    /**
     * Get comments from property of class
     * @param mixed $class define of php class
     * $param string $prefix prefix of anotation
     * @return array of nameproperty => array(comments)
     */
    public function getClassPropertyAnnotations($class, $prefix)
    {
        $r = new \ReflectionClass($class);
        $property = $r->getProperties();
        $annotations = [];
        foreach ($property as $item) {
            $i = NULL;
            preg_match_all('#' . $prefix . '(.*?)\n#s', $item->getDocComment(), $i);
            $annotations[$item->name] = $i[1];
        }
        return $annotations;
    }

    /**
     * Get comments from method of class
     * @param mixed $class define of php class
     * $param string $prefix prefix of anotation
     * @return array of nameproperty => array(comments)
     */
    public function getMethodPropertyAnnotations($class, $function, $prefix)
    {
        $r = new \ReflectionClass($class);
        $property = NULL;
        preg_match_all('#' . $prefix . '(.*?)\n#s', $r->getMethod($function)->getDocComment(), $property);
        return $property[1];
    }

    /**
     *
     * @param string $value
     * @return array
     */
    public function parseArray($value): array
    {
        $e = explode('|', $value);
        $array = [];
        foreach ($e as $item) {
            $item = str_replace("]", "", $item);
            $item = str_replace('[', "", $item);
            $item = str_replace("'", "", $item);
            $item = str_replace('"', "", $item);
            $item = trim($item);
            $a = explode('>', $item);

            if (!isset($a[1]))
                $array[] = trim($a[0]);
            else
                $array[trim($a[0])] = trim($a[1]);
        }
        return $array;
    }

    /**
     * Helper method for clean annotation text
     * @param string $a annotation
     * @return array $a clean annotation
     */
    public function cleanAnnotation($a, $explode = '='): array
    {
        $a = explode($explode, $a);
        $a[0] = str_replace("'", "", $a[0]);
        $a[0] = str_replace('"', "", $a[0]);
        $a[0] = trim($a[0]);
        $a[0] = strtolower($a[0]);
        $a[1] = str_replace("'", "", $a[1]);
        $a[1] = str_replace('"', "", $a[1]);
        $a[1] = trim($a[1]);
        return $a;
    }

    /**
     * Helper method for convert input string to array|string for array
     * @param string $item string from annotation - format [2, 3, ..], if only one value - return string, another array
     * @return array|string depend on entry
     */
    public function createAndCleanArg($item, $oneReturnString = true)
    {
        $arg = NULL;

        $item = str_replace("[", "", $item);
        $item = str_replace("]", "", $item);
        $item = str_replace("(", "", $item);
        $item = str_replace(")", "", $item);
        $item = str_replace("/", "", $item);
        $item = str_replace("\\", "", $item);
        $item = str_replace("'", "", $item);
        $item = str_replace('"', "", $item);

        $item = explode(',', $item);

        $arg = [];
        foreach ($item as $i) {
            $arg[] = trim($i);
        }

        if (count($arg) == 1 && $oneReturnString) {
            return $arg[0];
        }

        return $arg;
    }

    /**
     * Return all properties of class
     * @param string $class
     */
    static function getPropertiesOfClass($class): array
    {
        $reflect = new \ReflectionClass($class);
        $props = $reflect->getProperties();
        $arr = [];
        foreach ($props as $item) {
            $arr[] = $item->name;
        }
        return $arr;
    }

    /**
     * Return OneToMany properties of class
     * @param string $class
     * @return array
     * @throws \ReflectionException
     */
    public function getOneToManyPropertiesOfClass($class): array
    {
        $properties = $this->getClassPropertyAnnotations($class, '@ORM\\\OneToMany');
        $items = [];
        if ($properties) {
            foreach ($properties as $k => $p) {
                if (isset($p[0])) {
                    $anotation = explode(',', trim($p[0]));
                    foreach ($anotation as $a) {
                        $objects = explode('=', $a);
                        $objects[0] = trim(str_replace(['"', '(', ')', ' '], '', $objects[0]));
                        $objects[1] = trim(str_replace(['"', '(', ')', ' '], '', $objects[1]));
                        if (in_array($objects[0], ['targetEntity', 'mappedBy'])) {
                            if ($objects[0] == 'targetEntity') {
                                $arr = explode('\\', $class);
                                $objects[1] = implode('\\', array_slice($arr, 0, count($arr) - 1)) . '\\' . $objects[1];
                            }
                            $items[$k][$objects[0]] = $objects[1];
                        }
                    }
                }
            }
        }
        return $items;
    }
}