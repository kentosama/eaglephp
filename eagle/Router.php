<?php

namespace Eagle;

class Router
{

    static $scopes = [];

    public function __contruct()
    {

    }

    public static function reverse(string $url): string
    {
        $request = new Request;
        $query = []; 
        $url = rtrim($url, '/');
        
        parse_str($url, $query);

        
        
        $result = '/';
        
        if(isset($query['/prefix']))
        {
            if(!empty($query['/prefix']))
            $result .= $query['/prefix'] . DS;

            unset($query['/prefix']);
        }

        if(isset($query['/controller']))
        {
            $query['controller'] = $query['/controller'];
            unset($query['/controller']);
        }

        if(isset($query['/action']))
        {
            $query['action'] = $query['/action'];
            unset($query['/action']);
        }

        if(isset($query['controller']))
        {
            if(!empty($query['controller']))
            $result .= $query['controller'];

            if(!empty($query['action']))
            $result .= DS;

            unset($query['controller']);
        }


        if(isset($query['action']))
        {
            if(!empty($query['action']))
            $result .= $query['action'];

            unset($query['action']);
        }

       
        if (!empty($query)) 
        {
            $items = [];
            foreach ($query as $key => $value) {
                if (!empty($value))
                    $items[] = str_replace('/', '', $key) . '=' . $value;
            }

            if (!empty($items))
                $result .= '/?' . implode('&', $items);
        }

       

        $result = str_replace('//', '/', $result);

        return $result;
    }

    private static function array_diff(array $one, array $two): bool
    {
        $count = 0;

        foreach($one as $key_one => $value_one)
        {
            foreach($two as $key_two => $value_two)
            {
                if($key_one === $key_two)
                {
                    if($value_one !== $value_two)
                    $count++;
                }
            }
        }
        
        return ($count > 0);
    }

    public static function parse($url, bool $array = FALSE)
    {
        
        if(is_array($url))
        {
            $prefix = '/';
            if(isset($url['prefix'])) $prefix .= $url['prefix'];
            
            if(isset(static::$scopes[$prefix]))
            {
            
                foreach(static::$scopes[$prefix]['items'] as $item)
                {
                    $diff = static::array_diff($item['url'], $url);

                    if(!$diff)
                    {
                        if(!$array)
                        {
                            if(isset($item['pass']))
                            {
                                foreach($item['pass'] as $field)
                                {
                                    if(isset($url[$field]))
                                    $item['path'] = str_replace(':'.$field, $url[$field], $item['path']);
                                }
                            }

                            
                            return $item['path'];
                        }
                        
                        return $item['url'];
                    }
                }
            }
        }
        else if(is_string($url))
        {
            $url = rtrim($url, '/');
            foreach(static::$scopes as $scope)
            {
                foreach($scope['items'] as $item)
                {
                    if($item['path'] !== $url)
                    {
                        if (isset($item['pass'])) 
                        {

                            $a = explode('/', $url);
                            $b = explode('/', $item['path']);
                            $index = 0;
                            
                            foreach ($b as $key => $value) {
                                
                                
                                if (!empty($value)) 
                                {
                                    if (isset($value[0])) {
                                        if ($value[0] === ':') {
                                            $item['path'] = str_replace($value, $a[$index], $item['path']);
                                            $item['url']['pass'][] = $a[$index];
                                        }
                                    }
                                }

                                $index++;
                                
                                if(!isset($a[$index]))
                                break; 
                            }
                        }
                    }
                
                    if($url === $item['path']) 
                    return $item['url'];    
                   
                }
            }
        }

        

        return $url;
    }

    public static function scope(string $path, ?array $params = [], callable $callback)
    {
        $items = $callback();
        $prefix = $path === '/' ? FALSE : str_replace('/', '', $path);
        foreach($items as $key => $value)
        {
            $items[$key]['url']['prefix'] = $prefix;
        }

        static::$scopes[$path] = ['params' => $params, 'items' => $items];
    }
}