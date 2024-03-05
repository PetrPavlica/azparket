<?php

namespace App\Model;

use App\Model\Database\Entity\AbstractEntity;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\EntityData;
use Nette;
use Nette\Application\UI;
use App\Model\Database\Utils\AnnotationParser;
use Nette\Caching\Storage;
use Nette\ComponentModel\Component;

class DoctrineFormGenerator
{
    use Nette\SmartObject;

    /** @var EntityManager */
    private $em;

    /** @var ACLForm */
    private $form;

    /** @var ACLMapper */
    private $mapper;

    /** @var Storage */
    private $storage;

    /** @var AnnotationParser */
    private $parser;

    /** @var EntityData */
    private $ed;

    /** presenter where write message and do redirect */
    private $presenter;

    /** default values for components */
    private $defaultsValues;

    public function __construct(EntityManager $em, ACLMapper $mapper, Storage $storage, AnnotationParser $parser, EntityData $entityData)
    {
        $this->em = $em;
        $this->mapper = $mapper;
        $this->storage = $storage;
        $this->parser = $parser;
        $this->ed = $entityData;
    }

    /**
     * Generate form by doctrine annotation. Prepare all form whit save/update method.
     * @param string $class
     * @param Nette\Security\User $user
     * @param object $control
     * @param string $function
     * @param string $captionSubmit
     * @return ACLForm form whit emelements from class
     * @throws \Exception
     */
    public function generateFormByAnnotation($class, $user, $control, $function, $keyForm = 'default', $captionSubmit = 'Uložit', $captionSubmitSave = 'Uložit a pokračovat')
    {
        $presenter = $control->getPresenter();
        $this->form = new ACLForm;
        $this->form->setScope($user, get_class($control), $function, $this->mapper);
        $this->form->setTranslator($presenter->translator);
        $this->presenter = $presenter;
        $this->form->class = $class;

        $classAnnotations = $this->parser->getClassAnnotationsEntity($class);

        $this->form->sections = array_keys($classAnnotations['form-sections']);

        uasort($classAnnotations['properties'], function($it1, $it2) {
            $a = $it1['forms']['default']['order'] ?? PHP_MAXPATHLEN;
            $b = $it2['forms']['default']['order'] ?? PHP_MAXPATHLEN;
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });

        $annotations = $classAnnotations['properties'];

        $sectionProperties = [];

        foreach ($annotations as $name => $annotation) {
            // if property dont have annotation - dont create component
            if (count($annotation['forms']) == 0) {
                continue;
            }

            $defaultAnnotation = $annotation['forms']['default'];
            if (array_key_exists($keyForm, $annotation['forms'])) {
                $defaultAnnotation = array_merge($defaultAnnotation, $annotation['forms'][$keyForm]);
            }

            $defaultAnnotation['doctrine'] = $annotation;

            $section = $defaultAnnotation['section'] ?? 'default';
            if (!isset($sectionProperties[$section])) {
                $sectionProperties[$section] = [];
            }

            $sectionProperties[$section][] = $name;

            $this->createAnnotationComponent($name, $defaultAnnotation);
        }

        $this->form->sectionProperties = $sectionProperties;

        $this->form->addSubmitAcl('send', $captionSubmit);
        $this->form->addSubmit('sendSave', $captionSubmitSave);
        $this->form->onError[] = function(UI\Form $form) use ($presenter) {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $presenter->flashMessage($e, 'error');
                }
            }
        };
        $this->form->onSuccess[] = [$this, 'processForm'];
        if ($this->defaultsValues)
            $this->form->setDefaults($this->defaultsValues);
        return $this->form;
    }

    /**
     * Generate form whithout doctrine annotation.
     * @param string $class
     * @param Nette\Security\User $user
     * @param object $presenter
     * @param string $function
     * @param string $captionSubmit
     * @return ACLForm form
     * @throws \Exception
     */
    public function generateFormWithoutAnnotation($class, $user, $presenter, $function)
    {
        $this->form = new ACLForm;
        $this->form->setScope($user, get_class($presenter), $function, $this->mapper);
        $this->presenter = $presenter;
        $this->form->class = $class;

        $this->form->onSuccess[] = [$this, 'processForm'];
        if ($this->defaultsValues)
            $this->form->setDefaults($this->defaultsValues);
        return $this->form;
    }

    /**
     * Generate form by doctrine annotation. Prepare all form whit save/update method.
     * @param string $class
     * @param Nette\Security\User $user
     * @param object $presenter
     * @param string $function
     * @param string $captionSubmit
     * @return ACLForm form whit emelements from class
     * @throws \Exception
     */
    public function generateFormByAnnotationWithoutACL($class, $user, $presenter, $function, $keyForm = 'default', $captionSubmit = 'Uložit', $captionSubmitSave = 'Uložit a pokračovat')
    {
        $this->form = new ACLForm();
        $this->form->setScope($user, get_class($presenter), $function, $this->mapper);
        $this->form->setTranslator($presenter->translator);
        $this->presenter = $presenter;
        $this->form->class = $class;

        // prepare key for cash  - připravené cashe, ale kvůli rychlosti se to asi zatím nevyplatí
        /* $ent = explode("\\", $class);
          $pres = explode("\\", get_class($presenter));
          $key = end($ent) . '-' . end($pres) . '-form';

          //cash annotations - read
          $annotations = $this->storage->read($key);
          if ($annotations == NULL) { // if null, create and cash
          $annotations = AnnotationParser::getClassPropertyAnnotations($class, self::PREFIX);
          $this->storage->write($key, $annotations, []);
          } */
        $classAnnotations = $this->parser->getClassAnnotationsEntity($class);

        $annotations = $classAnnotations['properties'];

        foreach ($annotations as $name => $annotation) {
            // if property dont have annotation - dont create component
            if (count($annotation['forms']) == 0) {
                continue;
            }

            $defaultAnnotation = $annotation['forms']['default'];
            if (array_key_exists($keyForm, $annotation['forms'])) {
                $defaultAnnotation = array_merge($defaultAnnotation, $annotation['forms'][$keyForm]);
            }

            $defaultAnnotation['doctrine'] = $annotation;

            $this->createAnnotationComponent($name, $defaultAnnotation, false);
        }

        $this->form->addSubmitAcl('send', $captionSubmit);
        $this->form->addSubmit('sendSave', $captionSubmitSave);
        $this->form->onSuccess[] = [$this, 'processForm'];
        if ($this->defaultsValues)
            $this->form->setDefaults($this->defaultsValues);
        return $this->form;
    }

    /**
     * Function for handler onSuccess form
     * @param ACLForm|UI\Form $form
     * @param Nette\Utils\ArrayHash $values
     * @return AbstractEntity|null
     * @throws \Exception
     */
    public function processForm($form, $values, $enforce = false)
    {
        $values2 = $this->presenter->request->getPost();
        // if exist another onSuccess on form - return this and stop it
        if (count($form->onSuccess) > 1 && $enforce == false)
            return null;
        $id = "";
        if (isset($values['id'])) {
            $id = $values['id'];
            unset($values['id']);
        }
        if (isset($values['ID'])) {
            $id = $values['ID'];
            unset($values['ID']);
        }
        if (isset($values['Id'])) {
            $id = $values['Id'];
            unset($values['Id']);
        }
        if (isset($values['iD'])) {
            $id = $values['iD'];
            unset($values['iD']);
        }

        try {
            $this->em->beginTransaction();
            $entity = null;
            if (empty($id)) {
                $entity = new $form->class();
                $this->em->persist($entity);
            } else {
                $entity = $this->em->getRepository($form->class)->find($id);
                if (!$entity) {
                    throw new \Exception('Cannot find row with ID: '.$id);
                }
            }

            //Save foreign entity - need find it
            if (isset($form->arrayForeignEntity)) {
                foreach ($form->arrayForeignEntity as $name => $value) {
                    //if entity exist in array for N:N save - save information to arrayNNForeignEntity and unset post value, continue and save as last
                    if (isset($form->arrayNNForeignEntity[$name])) {
                        $form->arrayNNForeignEntity[$name]['value'] = $values[$name];
                        $form->arrayNNForeignEntity[$name]['foreign-entity'] = $value;
                        unset($values[$name]);
                        continue;
                    }
                    if (isset($values[$name]) && $values[$name]) {
                        $a = $this->em->getRepository($value)->find($values[$name]);
                        // Check if exist foreign entity - if not, dont save.

                        if ($a) {
                            $values[$name] = $a;
                        } else {
                            unset($values[$name]);
                        }
                    } elseif (isset($values[$name]) && !$values[$name]) { //If value not set, set entity cell to NULL
                        $values[$name] = NULL;
                    }
                }
            }

            $valuesBefore = clone $values;

            if (is_array($form->filesPath)) {
                foreach ($form->filesPath as $name => $value) {
                    if (isset($values[$name]) && $values[$name]->isOk()) {
                        if ($entity && $entity->$name && file_exists($entity->$name)) {
                            @unlink($entity->$name);
                        }
                    }
                    unset($valuesBefore[$name]);
                    if (isset($values2[$name.'_delete'])) {
                        if ($entity && $entity->$name && file_exists($entity->$name)) {
                            @unlink($entity->$name);
                            $success = @rmdir($form->filesPath[$name].$entity->id);
                            if ($success) {
                                @rmdir($form->filesPath[$name]);
                            }
                        }
                        $valuesBefore[$name] = null;
                    }
                }
            }

            $entity = $this->ed->set($entity, $valuesBefore);
            $this->em->flush($entity);

            if (is_array($form->filesPath)) {
                foreach ($form->filesPath as $name => $value) {
                    $prefix = $form->filesPrefix[$name] ?? '';
                    if (isset($values[$name]) && $values[$name]->isOk()) {
                        $filename = $form->filesPath[$name].$entity->id.'/'.$prefix.$values[$name]->getSanitizedName();
                        $values[$name]->move($filename);
                        $values[$name] = $filename;
                    } else {
                        unset($values[$name]);
                    }
                }
            }

            $entity = $this->ed->set($entity, $values);

            // Save foreign entity whit N:N relationship
            if (isset($form->arrayNNForeignEntity)) {
                foreach ($form->arrayNNForeignEntity as $name => $value) {
                    $existIds = [];

                    if (is_array($value['value']) && count($value['value'])) {
                        foreach ($value['value'] as $item) {
                            $ent = $this->em->getRepository($value['entity'])->findOneBy([$value['this'] => $entity->id, $value['foreign'] => $item]);
                            if (!$ent) {
                                $entityForeign = new $value['entity'];
                                $foreignEntity = $this->em->getRepository($value['foreign-entity'])->find($item);
                                $entityForeign->{$value['this']} = $entity;
                                $entityForeign->{$value['foreign']} = $foreignEntity;
                                $this->em->persist($entityForeign);
                                $this->em->flush($entityForeign);
                                $existIds[] = $entityForeign->id;
                            } else {
                                $existIds[] = $ent->id;
                            }
                        }
                    }

                    if (count($existIds)) {
                        $this->em->createQuery('DELETE '.$value['entity'].' e WHERE e.'.$value['this'].' = :id and e.id NOT IN (:ids)')
                            ->execute([
                                'id' => $entity->id,
                                'ids' => $existIds
                            ]);
                    } else {
                        $this->em->createQuery('DELETE '.$value['entity'].' e WHERE e.'.$value['this'].' = :id')
                            ->execute([
                                'id' => $entity->id
                            ]);
                    }
                }
            }
            $this->em->commit();
        } catch (\Exception $e) {
            throw $e;
            // Check Integrity constraint viloadin - duplicate entry
            if (strpos($e, 'SQLSTATE[23000]')) {
                $n = explode("'", $e->getMessage());
                $this->presenter->flashMessage('Formulář se nepodařilo uložit - hodnota "' . $n[3] . '" není jedinečná - jiný záznam již má tuto hodnotu!', 'warning');
                return null;
            }
            \Tracy\Debugger::log($e);
            if (isset($this->form->messageEr)) {
                $this->presenter->flashMessage($this->form->messageEr[0], $this->form->messageEr[1]);
            } else {
                throw $e;
            }
            return null;
        }
        if (isset($this->form->messageOk)) {
            $this->presenter->flashMessage($this->form->messageOk[0], $this->form->messageOk[1]);
        }

        if ($form->isRedirect) {
            if ($form->target && !isset($values2['sendSave'])) {
                if ($form->targetPar) {
                    $this->presenter->redirect($form->target, $form->targetPar);
                } else {
                    $this->presenter->redirect($form->target);
                }
            } else {
                $this->presenter->redirect('this');
            }
        }

        return $entity;
    }

    /**
     * Create form component by doctrine annotation
     * @param string $name of property
     * @param array $annotation annotations of property
     * @throws \Exception
     */
    public function createAnnotationComponent($name, $annotation, $acl = true)
    {
        if (!isset($annotation['type'])) {
            return;
        }
        // create form component by doctrine annotation specification
        switch ($annotation['type']) {
            case 'text':
                if ($acl) {
                    $component = $this->form->addTextAcl($name, $annotation['title'] ?? '', $annotation['doctrine']['length'] ?? null, $annotation['doctrine']['length'] ?? null);
                } else {
                    $component = $this->form->addText($name, $annotation['title'] ?? '', $annotation['doctrine']['length'] ?? null, $annotation['doctrine']['length'] ?? null);
                }
                break;
            case 'select':
                if ($acl) {
                    $component = $this->form->addSelectAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addSelect($name, $annotation['title'] ?? '');
                }
                break;
            case 'multiselect':
                if ($acl) {
                    $component = $this->form->addMultiSelectAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addMultiSelect($name, $annotation['title'] ?? '');
                }
                break;
            case 'checkbox':
                if ($acl) {
                    $component = $this->form->addCheckboxAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addCheckbox($name, $annotation['title'] ?? '');
                }
                break;
            case 'checkboxlist':
                if ($acl) {
                    $component = $this->form->addCheckboxListAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addCheckboxList($name, $annotation['title'] ?? '');
                }
                break;
            case 'email':
                if ($acl) {
                    $component = $this->form->addEmailAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addEmail($name, $annotation['title'] ?? '');
                }
                break;
            case 'hidden': //hidden dont acl map
                $component = $this->form->addHidden($name, $annotation['title'] ?? '');
                break;
            case 'image':
                if ($acl) {
                    $component = $this->form->addImageAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addImageButton($name, $annotation['title'] ?? '');
                }
                break;
            case 'integer':
                if ($acl) {
                    $component = $this->form->addIntegerAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addText($name, $annotation['title'] ?? '');
                    $component->addRule(UI\Form::PATTERN, 'Prosím zadávejte pouze celá čísla', '[0-9]*');
                }
                break;
            case 'number':
            case 'float':
                if ($acl) {
                    $component = $this->form->addNumberAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addText($name, $annotation['title'] ?? '');
                    $component->addRule(UI\Form::FLOAT, 'Zadejte platné číslo!');
                }
                break;
            case 'password':
                if ($acl) {
                    $component = $this->form->addPasswordAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addPassword($name, $annotation['title'] ?? '');
                }
                break;
            case 'textarea':
                if ($acl) {
                    $component = $this->form->addTextAreaAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addTextArea($name, $annotation['title'] ?? '');
                }
                break;
            case 'upload':
                if ($acl) {
                    $component = $this->form->addUploadAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addUpload($name, $annotation['title'] ?? '');
                }
                break;
            case 'editor':
                if ($acl) {
                    $component = $this->form->addEditorAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addTextArea($name, $annotation['title'] ?? '');
                    $component->setHtmlAttribute('class', 'ckEditor');
                }
                break;
            case 'date':
                if ($acl) {
                    $component = $this->form->addTextAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addText($name, $annotation['title'] ?? '');
                }
                if ($component) {
                    $component->setHtmlAttribute('data-provide', 'datepicker');
                    $component->setHtmlAttribute('data-date-orientation', 'bottom');
                    $component->setHtmlAttribute('data-date-format', 'd. m. yyyy');
                    $component->setHtmlAttribute('data-date-today-highlight', 'true');
                    $component->setHtmlAttribute('data-date-autoclose', 'true');
                    $component->setHtmlAttribute('data-date-language', 'cs');
                    $component->setHtmlAttribute('autocomplete', 'off');
                    $component->setRequired(false);
                    $component->addRule(UI\Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011', '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})');
                }
                break;
            case 'datetime':
                if ($acl) {
                    $component = $this->form->addTextAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addText($name, $annotation['title'] ?? '');
                }
                if ($component) {
                    $component->setRequired(false);
                    $component->addRule(UI\Form::PATTERN, 'Datum a čas musí být ve formátu 15. 10. 2011 15:20 (pozor na mezery)', '(^(0?[1-9]|[12][0-9]|3[01]). (0?[1-9]|1[0-2]). \d\d\d\d ([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9])');
                }
                break;
            case 'time':
                if ($acl) {
                    $component = $this->form->addTextAcl($name, $annotation['title'] ?? '');
                } else {
                    $component = $this->form->addText($name, $annotation['title'] ?? '');
                }
                if ($component) {
                    $component->setRequired(false);
                    $component->addRule(UI\Form::PATTERN, 'Čas musí být ve formátu "15:20"', '(^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9])');
                }
                break;
            case 'autocomplete':
                if ($acl) {
                    $component = $this->form->addHiddenAcl($name);
                } else {
                    $component = $this->form->addHidden($name);
                }
                if ($component) {
                    $component->setHtmlAttribute('class', 'autocomplete-input');
                    $component->setHtmlAttribute('data-toggle', 'completer');
                    $component->setHtmlAttribute('autocomplete', 'true');
                    $component->setHtmlAttribute('title', $annotation['title'] ?? '');
                    if (isset($annotation['autocomplete-data-source'])) {
                        $component->setHtmlAttribute('data-source', $annotation['autocomplete-data-source']);
                    }
                    if (isset($annotation['autocomplete-data-send'])) {
                        $component->setHtmlAttribute('data-send', $annotation['autocomplete-data-send']);
                    }
                    if (isset($annotation['autocomplete-data-success'])) {
                        $component->setHtmlAttribute('data-success', $annotation['autocomplete-data-success']);
                    }
                }
                break;

            case 'multiupload':
                /* TODO multiupload */
                break;
            case 'radio':
            case 'radiolist':
                $component = $this->form->addRadioList($name, $annotation['title'] ?? '', $annotation['data-own']);
                break;
            default:
                throw new \Exception('Unknow type of input - Doctrine-Form annotation. Type: ' . $annotation['type']);
        }
        $this->addOtherProperties($name, $component, $annotation);
    }

    /**
     * @param $nameEl
     * @param $component
     * @param $componentInfo
     * @throws \Exception
     */
    protected function addOtherProperties(string $nameEl, Nette\Forms\Control $component, array $componentInfo)
    {
        if ($component == NULL)
            return;

        if (isset($componentInfo['doctrine']) && isset($componentInfo['doctrine']['type'])) {
            if ($componentInfo['doctrine']['type'] == 'string' && $componentInfo['doctrine']['nullable'] == false) {
                $component->setRequired('Toto pole je povinné.');
            }
        }

        foreach ($componentInfo as $name => $value) {
            switch ($name) {
                case 'required':
                    if ($value == 'false' || $value == '0') {
                        $value = false;
                    }
                    $component->setRequired($value);
                    break;
                case 'prompt':
                    $component->setPrompt($value);
                    break;
                case 'disabled':
                    $component->setDisabled();
                    break;
                case 'default-value':
                    $this->defaultsValues[$nameEl] = $value;
                    break;
                case strpos($name, 'attr-'):
                case strpos($name, 'attribute-'):
                    $n = str_replace(['attribute-', 'attr-'], '', $name);
                    if (isset($component->control->attrs[$n]))
                        $value .= ' ' . $component->control->attrs[$n];
                    $component->setHtmlAttribute($n, $value);
                    break;
                case strpos($name, 'rule'):
                    $this->addRuleAnnotation($name, $value, $component);
                    break;
                case strpos($name, 'data-'):
                    $n = str_replace("data-", "", $name);
                    if ($n == 'entity') {
                        if ($componentInfo['type'] !== 'hidden') { // its allow to add data-entity for hidden field. but in hidden field you dont fill component
                            $items = $this->em->getRepository($value['entity'])->findBy($value['findBy'], $value['order']);
                            $arr = [];
                            $idName = 'id';
                            foreach ($items as $item) {
                                $val = $value['column'];
                                $arr[$item->$idName] = $item->$val;
                            }
                            $component->setItems($arr);
                        }
                        $this->form->arrayForeignEntity[$nameEl] = $value['entity'];
                    } else if ($n == 'entity-values') {
                        if ($componentInfo['type'] !== 'hidden') { // its allow to add data-entity for hidden field. but in hidden field you dont fill component
                            $findBy = $value['findBy'];
                            $orderBy = $value['order'];
                            $items = $this->em->getRepository($value['entity'])->findBy($findBy, $orderBy);
                            $properties = AnnotationParser::getPropertiesOfClass(new $value['entity']);
                            $arr = [];
                            $idVal = 'id';
                            foreach ($items as $item) {
                                if (isset($item->isHidden) && $item->isHidden == 1)
                                    continue;
                                $resVal = $value['column'];

                                foreach ($properties as $prop) {
                                    if ($prop == 'parent') {
                                        // nothing
                                    } elseif (strpos($resVal, $prop)) {
                                        if (is_array($item->$prop)) {
                                            throw new \Exception('Error in doctrine annotation - FORM data-entity-values=' . $value['column'] . ' - entity property: ' . $prop . ' is foreign key - you cannot use foreign key to this annotation');
                                        }
                                        $resVal = str_replace("$" . $prop . "$", $item->$prop, $resVal);
                                    }
                                }
                                $arr[$item->$idVal] = $resVal;
                            }
                            $component->setItems($arr);
                        }
                        $this->form->arrayForeignEntity[$nameEl] = $value['entity'];
                    } else if ($n == 'own') {
                        $component->setItems($componentInfo['data-own']);
                    }
                    break;
                case 'multiselect-entity':
                    if ($value['entity'] && $value['source'] && $value['target']) {
                        $this->form->arrayNNForeignEntity[$nameEl] = [
                            'entity' => $value['entity'],
                            'this' => $value['source'],
                            'foreign' => $value['target']
                        ];
                    }
                    break;
                case 'autocomplete-entity':
                    $this->form->arrayForeignEntity[$nameEl] = stripos($value, 'App\\') !== false ? $value : 'App\Model\Database\Entity\\' . $value;
                    break;
                case 'currency':
                    $component->setHtmlAttribute('currency', $value);
                    break;
                case 'dir':
                    $this->form->filesPath[$nameEl] = trim($value, '/').'/';
                    break;
                case 'prefix':
                    $this->form->filesPrefix[$nameEl] = $value;
                    break;
                case 'column':
                    $this->form->propertiesColumn[$nameEl] = $value;
                    break;
            }
        }
    }

    /**
     * Function addRule by annotations
     * @param string $name type of rule format: rule-{type or rule}
     * @param string $value text + arg to rule
     * @param Component $component component from form
     * @throws \Exception
     */
    protected function addRuleAnnotation($name, $value, Component $component)
    {
        $n = trim(str_replace("rule-", "", $name), " \t\n\r\0\x0B*'");
        $arg = NULL;
        if (strpos($value, "#[") != FALSE) {
            $item = trim(substr($value, strpos($value, "#[") + 2));
            $arg = $this->parser->createAndCleanArg($item);
            $value = trim(substr($value, 0, strpos($value, "#[")), " \t\n\r\0\x0B*'");
        }

        //@TODO udělat pattern addRule na PSČ, Telefon, IČ, Rodné číslo,
        switch ($n) {
            case 'integer':
                $component->addRule(UI\Form::PATTERN, $value, '[0-9]*');
                break;
            case 'range':
                $component->addRule(UI\Form::RANGE, $value, $arg);
                break;
            case 'min_length':
                $component->addRule(UI\Form::MIN_LENGTH, $value, $arg);
                break;
            case 'max_length':
                $component->addRule(UI\Form::MAX_LENGTH, $value, $arg);
                break;
            case 'email':
                $component->addRule(UI\Form::EMAIL, $value, $arg);
            break;
            case 'phone':
                $component->addRule(
                    UI\Form::PATTERN,
                    $value,
                    '^((\+[0-9]{1,3}|[(][0-9]{1,3}[)])[ -])?[^- ][- 0-9]{3,16}[^- ]$'
                );
                break;
            case 'integer':
                $component->addRule(UI\Form::PATTERN, $value, '[0-9]*');
            case 'length':
                $component->addRule(UI\Form::LENGTH, $value, $arg);
                break;
            case 'equal':  //TODO udělat equal na druhé políčko - např hesla
                $component->addRule(UI\Form::EQUAL, $value, $arg);
                break;
            case 'url':
                $component->addRule(UI\Form::URL, $value, $arg);
                break;
            case 'numeric':
            case 'number':
                $component->addRule(UI\Form::NUMERIC, $value, $arg);
                break;
            case 'float':
                $component->addRule(UI\Form::FLOAT, $value, $arg);
                break;
            case 'min':
                $component->addRule(UI\Form::MIN, $value, $arg);
                break;
            case 'max':
                $component->addRule(UI\Form::MAX, $value, $arg);
                break;
            case 'psc':
                //$component->addRule(Form::PATTERN, $value, '([0-9]\s*){5}');
                $component->addRule(UI\Form::PATTERN, $value, '([0-9]\s*){5}');
                break;
            default:
                throw new \Exception('Unknow Doctrine-Form annotation for addRule: ' . $name . ' = ' . $value);
        }
    }

}