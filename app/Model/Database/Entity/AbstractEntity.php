<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use Nette\Utils\Strings;

class AbstractEntity
{
    /**
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        if ($data !== null) {
            $this->data($data);
        }
    }

    public function __set(string $name, $value)
    {
        $this->$name = $value;
    }

    public function __get(string $name)
    {
        return $this->$name;
    }

    public function __call($name, $args) {
        if (strlen($name) > 3) {
            $op = substr($name, 0, 3);
            $prop = Strings::firstLower($name[ 3 ] . substr($name, 4));
            if ($op === 'set') {
                try {
                    $this->$prop = $args[0];
                } catch (\Exception $ex) {
                    throw new \Exception('Unknown database entity property: ' . $prop);
                }
            } else if ($op === 'get') {
                try {
                    return $args[0];
                } catch (\Exception $ex) {
                    throw new \Exception('Unknown database entity property: ' . $prop);
                }
            }
        }
    }
}