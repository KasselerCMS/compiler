<?php

namespace Kasseler\Component\Compiler;

class Compiler
{
    private $function;
    private $types = ['assoc_array', 'array', 'string'];

    public function __construct($function)
    {
        $this->function = $function;
    }

    private function decode($string)
    {
        $result = json_decode($string, true);

        return json_last_error() == JSON_ERROR_NONE ? $result : false;
    }

    private function isFunction($string)
    {
        return preg_match_all('/^'.$this->function.'\(.+\)$/', $string);
    }

    private function getType($string)
    {
        foreach ($this->types as $type) {
            if (stripos($string, $type) !== false) {
                return $type;
            }
        }

        return false;
    }

    private function replaceParameters(&$string, &$parameters, $replace, $name)
    {
        if (!empty($replace)) {
            $stringArray = [];
            $start = 0;
            foreach ($replace as $num) {
                $parameters[$name][] = $name == 'string'
                    ? substr($string, $num[0]+1, $num[1]-$num[0]-1)
                    : substr($string, $num[0], $num[1]-$num[0]+1)
                ;
                $stringArray[] = substr($string, $start, $num[0]-$start);
                $start = $num[1]+1;
            }
            if (strlen($string) > $start) {
                $stringArray[] = substr($string, $start, strlen($string)-$start);
            }
            $count = count($stringArray);
            if ($count > 0) {
                $string = $count > 1 ? implode('%:'.strtoupper($name).':%', $stringArray) : '%:'.strtoupper($name).':%';
            }
        }
    }

    private function compileParameters($string)
    {
        $parameters =  [];
        $arrayTypes = [
            'assoc_array'   => ['open' => '{', 'close' => '}'],
            'array'         => ['open' => '[', 'close' => ']'],
        ];

        foreach ($arrayTypes as $name => $tag) {
            $replace = $this->searchArrays($string, $tag['open'], $tag['close']);
            $this->replaceParameters($string, $parameters, $replace, $name);
        }

        $replacer = function($var, $value) {
            return $var !== false && $var !== null
                ? $var
                : $value;
        };

        $this->replaceParameters($string, $parameters, $this->searchString($string), 'string');
        $result = preg_split('/[\s,]+/', $string);
        foreach($result as &$v) {
            $replaced = false;
            while (is_string($v) && ($type = $this->getType($v))) {
                if ($type) {
                    $v = preg_replace_callback("/%:{$type}:%/i", function() use ($parameters, $type) {
                        return $parameters[$type][0];
                    }, $v);

                    array_shift($parameters[$type]);
                }
                $v = $replacer($this->decode($v), $v);
                $replaced = true;
            }
            if (empty($type) && !$replaced) {
                $v = $replacer($this->decode($v), $v);
            }
        }

        return $result;
    }

    private function searchString($string)
    {
        $quotes = ['"', "'"];
        $strings = [];
        $open = false;
        $start = 0;
        $openSymbol = '';
        for ($i = 0; $i < strlen($string); $i++) {
            if ($string[$i] == $openSymbol && $open && $string[$i-1] != '\\') {
                $open = false;
                $strings[] = [$start, $i, $openSymbol];
                continue;
            }
            if (in_array($string[$i], $quotes) && !$open) {
                $openSymbol = $string[$i];
                $open = true;
                $start = $i;
            }
        }

        return $strings;
    }

    private function searchArrays($string, $openSymbol, $closeSymbol)
    {
        $objects = [];
        $open = $start = -1;
        for ($i = 0; $i < strlen($string); $i++) {
            if ($string[$i] == $openSymbol) {
                $start = $open == -1 ? $i : $start;
                $open++;
            }
            if ($string[$i] == $closeSymbol && $open > -1) {
                $open--;
                if ($open == -1) {
                    $objects[] = [$start, $i];
                    $open = -1;
                }
            }
        }

        return $objects;
    }

    private function getData($string)
    {
        if($this->isFunction($string)){
            $function = json_decode(
                preg_replace_callback('/^(.+?)\((.*)\)$/i', function($match) {
                    return json_encode(['type' => 'function', 'name' => $match[1], 'attributes' => $match[2]]);
                },
                    $string
                ),
                true
            );
            $function['attributes'] = $this->compileParameters($function['attributes']);

            return $function;
        } else {
            if (($var = $this->decode($string))) {
                $type = gettype($var);

                $return = [
                    'type' => $type == 'double' ? 'float' : $type,
                    'data' => $var,
                ];
            }

            return isset($return) ? $return : [
                'type' => 'string',
                'data' => $string,
            ];
        }
    }

    public function run($string)
    {
        $data = $this->getData($string);
        if ($data['type'] != 'function') {
            return $data['data'];
        }
        if (function_exists($data['name'])) {
            return call_user_func_array($data['name'], $data['attributes']);
        }

        return false;
    }
}
