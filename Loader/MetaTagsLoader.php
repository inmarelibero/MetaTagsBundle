<?php

namespace Copiaincolla\MetaTagsBundle\Loader;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Doctrine\ORM\EntityManager;

/**
 * MetaTags loader.
 */
class MetaTagsLoader
{
    protected $router;
    protected $config;
    protected $em;
    
    protected $dynamic_routes_default_params;
    
    /**
     * constructor
     * 
     * @param Router $router
     * @param array $config
     * @param EntityManager $em
     */
    public function __construct(Router $router, array $config = array(), EntityManager $em)
    {
        $this->router    = $router;
        $this->config    = $config;
        $this->em        = $em;
        
        // set defaults parameters for all routes if specified
        $this->dynamic_routes_default_params = (array_key_exists('dynamic_routes_default_params', $this->config)) ? $this->config['dynamic_routes_default_params'] : array();
    }
    
    /**
     * Get all urls
     * 
     * Return an associative array of (relative) urls organized by route name
     * es: $output = array(
     *         'homepage' => array([url]),
     *         'products' => array([url], [url], [url], [url], ...),
     *         ...
     * )
     * 
     * User will be able to set meta tags for each url
     * 
     * @return array
     */
    public function getUrls()
    {
        // associative array [route name] => [array urls] to be returned
        $output = array();
        
        // iterate on selected routes to generate urls
        foreach ($this->router->getRouteCollection()->all() as $name => $route) {
            
            // array of urls generated by route $name
            $output[$name] = array();
            
            // route needs datas from database
            if (array_key_exists($name, $this->config['dynamic_routes'])) {
                
                // load objects from repository
                if (array_key_exists('repository', $this->config['dynamic_routes'][$name])) {
                    $repository = $this->config['dynamic_routes'][$name]['repository'];
                    
                    // data fetched from database
                    $data = $this->em->getRepository($repository)->findAll();
                    
                    // generate a url for each object
                    foreach ($data as $obj) {
                        $preparedRoute = $this->prepareDynamicUrl($name, $route, $obj, $this->config['dynamic_routes'][$name]);
                        
                        if ($preparedRoute) {
                            $output[$name][] = $preparedRoute;
                        }
                    }
                }
            
            // route does not need variables to be loaded by objects in database
            } else {
                $preparedRoute = $this->prepareUrl($name, $route);
                
                if ($preparedRoute) {
                    $output[$name][] = $preparedRoute;
                }
            }
            
            
        }
        
        /*
        foreach ($output as $route) {
            var_dump($route);
        } die();
        */
        
        return $output;
    }
    
    /**
     * Generate a url fetching the route variables from the object $obj
     * 
     * @param string $name route name
     * @param Route $route Route object
     * @param mixed $obj object fetched from database
     * @param array $dynamicRouteArray array from bundle config
     */
    private function prepareDynamicUrl($name, $route, $obj, $dynamicRouteArray)
    {
        // route parameters
        $routeParameters = array();
        
        // set route variables fetching the value from $obj property or method
        if (array_key_exists('object_params', $dynamicRouteArray)) {
            
            foreach ($dynamicRouteArray['object_params'] as $k => $param) {
                
                // get the value from $obj by accessing the variable name or calling a method
                if (isset($obj->$param)) {
                    $routeParameters[$k] = $obj->$param;
                } else if (method_exists($obj, $param)) {
                    $routeParameters[$k] = call_user_func_array(array($obj, $param), array());
                } else if (method_exists($obj, 'get'.$param)) {
                    $routeParameters[$k] = call_user_func_array(array($obj, 'get'.$param), array());
                } else if (method_exists($obj, 'is'.$param)) {
                    $routeParameters[$k] = call_user_func_array(array($obj, 'is'.$param), array());
                }
            }
            
        }
        
        // process [route_name]['params']
        foreach ($dynamicRouteArray['params'] as $k => $param) {
            $routeParameters[$k] = $dynamicRouteArray['params'][$k];
        }
        
        // return the url
        return $this->prepareUrl($name, $route, $routeParameters);
    }
    
    /**
     * Generate a url
     * 
     * If route has some variables, try to read $defaultVariables, then bundle config
     * 
     * @param string $name route name
     * @param Route $route Route object
     * @param array $defaultVariables array of [routa variable] => [value]
     */
    private function prepareUrl($name, $route, $defaultVariables = array())
    {
        // get compiled route
        $compiledRoute = $route->compile();
        
        // variables required to generate the route
        $variables = $compiledRoute->getVariables();
        
        // route parameters
        $routeParameters = array();
        
        // set variables value
        foreach ($variables as $variable) {
            
            // read the variable valude from $defaultVariables
            if (array_key_exists($variable, $defaultVariables)) {
                $routeParameters[$variable] = $defaultVariables[$variable];
            
            // search the $variable key in bundle configuration: dynamic_routes_default_params
            } else if (array_key_exists($variable, $this->dynamic_routes_default_params)) {
                $routeParameters[$variable] = $this->dynamic_routes_default_params[$variable];
            }
            
        }
        
        // try to generate the route
        try {
            return $this->router->generate($name, $routeParameters);
        } catch (\Exception $e) {}
        
        return null;
    }
}
