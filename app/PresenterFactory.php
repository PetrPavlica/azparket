<?php

declare(strict_types=1);

namespace App;

class PresenterFactory extends \Nette\Application\PresenterFactory
{
    /**
     * Generates and checks presenter class name.
     * @throws \Nette\Application\InvalidPresenterException
     */
    public function getPresenterClass(string &$name): string
    {
        $class = parent::getPresenterClass($name);

        $classNew = str_replace('App\Presenters', 'App\Presenters\Custom', $class);

        if (class_exists($classNew)) {
            $class = $classNew;
        }

        $reflection = new \ReflectionClass($class);
        $class = $reflection->getName();

        if (!$reflection->implementsInterface(\Nette\Application\IPresenter::class)) {
            throw new \Nette\Application\InvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
        } elseif ($reflection->isAbstract()) {
            throw new \Nette\Application\InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
        }

        return $class;
    }

}
