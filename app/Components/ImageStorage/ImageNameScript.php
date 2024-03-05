<?php

/**
 * @copyright   Copyright (c) 2016 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\ImageStorage;

use Nette;

class ImageNameScript
{

    use Nette\SmartObject;

    const PATTERN = '/__file__(\.(\d+)x(\d+)(crop(\d+)x(\d+)x(\d+)x(\d+))?\.(\w+))?(\.q(\d+))?\.([^\.]+)$/';

    /**
     * Identifier
     * @var string
     */
    public $identifier;

    /**
     * Original Identifier in form:
     * 	namespace/sha1_file[0..1]/img_name.suffix
     *
     * @var string
     */
    public $original;

    /**
     * @var string
     */
    public $namespace;

    /**
     * @var string
     */
    public $prefix;

    /**
     * @var string
     */
    public $name;

    /**
     * [width, height]
     * @var array
     */
    public $size = [];

    /**
     * @var int
     */
    public $flag;

    /**
     * @var int
     */
    public $quality;

    /**
     * @var string
     */
    public $extension;

    /**
     * @var array [$offset_left, $offset_top, $width, $height]
     */
    public $crop = [];


    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }


    public static function fromIdentifier($identifier)
    {
        return self::fromName($identifier);
    }


    public static function fromName($name)
    {

        $pattern = str_replace('__file__', '(.*?)', self::PATTERN);
        $pathParts = preg_split('/(\/|\\\\)/', $name);

        $path = '';
        for ($i = 0; $i < count($pathParts) - 1; $i++) {
            $path .= $pathParts[$i] . '/';
        }
        $path = substr($path, 0, strlen($path) - 1);
        $filename = end($pathParts);
        
        preg_match($pattern, $filename, $matches);

        $script = new static($name);
        $script->original = $name;
        $script->namespace = $path;
        $script->prefix = '';
        $script->name = $matches[1];
        $script->size = [(int)$matches[3], (int)$matches[4]];
        $script->flag = $matches[10];
        $script->quality = $matches[12];
        $script->extension = end($matches);

        if ($matches[6] && $matches[7] && $matches[8] && $matches[9]) {
            $script->crop = [(int)$matches[6], (int)$matches[7], (int)$matches[8], (int)$matches[9]];
        }

        return $script;
    }


    public function setSize($size)
    {
        $this->size = $size;
    }


    public function setcrop($crop)
    {
        $this->crop = $crop;
    }


    public function setFlag($flag)
    {
        $this->flag = $flag;
    }


    public function setQuality($quality)
    {
        $this->quality = $quality;
    }


    public function getIdentifier()
    {
        $identifier = implode('/', [$this->namespace, $this->prefix, $this->name]);

        if ($this->size) {
            $identifier .= '.' . $this->size[0] . 'x' . $this->size[1];

            if (sizeof($this->crop)) {
                $identifier .= "crop{$this->crop[0]}x{$this->crop[1]}x{$this->crop[2]}x{$this->crop[3]}";
            }

            $identifier .= '.' . $this->flag;

            if ($this->quality) {
                $identifier .= '.q' . $this->quality;
            }
        }

        $identifier .= '.' . $this->extension;

        return $identifier;
    }


    public function hasCrop()
    {
        if (!sizeof($this->crop)) {
            return FALSE;
        }

        return TRUE;
    }


    public function toQuery()
    {
        if ($this->size && $this->size[0] && $this->size[1]) {
            $params_dir = $this->size[0] . 'x' . $this->size[1];

            if (sizeof($this->crop)) {
                $params_dir .= "crop{$this->crop[0]}x{$this->crop[1]}x{$this->crop[2]}x{$this->crop[3]}";
            }

            $params_dir .= '.' . $this->flag;

            if ($this->quality) {
                $params_dir .= '.q' . $this->quality;
            }
        } else {
            return $this->original;
        }

        return implode('/', [
            $this->namespace,
            $this->prefix,
            $params_dir,
            "{$this->name}.{$this->extension}?_image_storage"
        ]);
    }

}
