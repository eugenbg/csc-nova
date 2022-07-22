<?php

namespace App\Http\Resources;

use ReflectionProperty;

abstract class AbstractResource
{
    public $types = [
        'bool',
        'int',
        'float',
        'string',
        'array'
    ];

    public function __construct($data = [])
    {
        foreach ($data as $key => $datum) {
            $this->initField($key, $datum);
        }
    }

    protected function initField($key, $payload)
    {
        if (property_exists($this, $key)) {
            $prop = new ReflectionProperty(get_class($this), $key);
            $comment = $prop->getDocComment();
            $type = $this->getTypeFromComment($comment);

            if (is_array($payload) && $type && !$this->isTypePrimitive($type)) {
                if (strpos($comment, '[]') !== false) {
                    $this->initArrayOfObjects($key, $payload, $comment);
                } else {
                    $this->initSingleObject($key, $payload, $comment);
                }
            } elseif($payload) {
                $this->$key = $payload;
            }
        }
    }

    public function toArray()
    {
        $result = [];
        $fields = array_keys(get_class_vars(static::class));
        foreach ($fields as $field) {
            if($field == 'types') { continue; }
            $value = $this->$field;
            if (is_array($value) && isset($value[0]) && is_object($value[0])) {
                $list = [];
                /** @var AbstractDTO $item */
                foreach ($value as $item) {
                    $list[] = $item->toArray();
                }
                $result[$field] = $list;
            } elseif (is_object($value)) {
                /** @var AbstractDTO $value */
                $result[$field] = $value->toArray();
            } else {
                $result[$field] = $value;
            }
        }

        return $result;
    }

    private function initArrayOfObjects($key, $data, $comment)
    {
        $this->$key = [];
        $type = $this->getTypeFromComment($comment);
        foreach ($data as $datum) {
            $this->$key[] = new $type($datum);
        }
    }

    private function initSingleObject($key, array $payload, string $comment)
    {
        $type = $this->getTypeFromComment($comment);
        $this->$key = new $type($payload);
    }

    private function getTypeFromComment($comment)
    {
        if(!$comment) { return null; }

        $type = str_replace('[]', '', trim(str_replace(['/**', '*/', '* @var', '@var'], '', $comment)));
        if (strpos($type, '\\') === false && !$this->isTypePrimitive($type)) {
            $type = '\\' . __NAMESPACE . '\\' . $type;
        }

        return $type;
    }

    private function isTypePrimitive(string $type)
    {
        return in_array($type, $this->types);
    }
}
