<?php

namespace PHPixie\Route\Resolvers\Resolver;

class Group implements \PHPixie\Route\Resolvers\Resolver,
                       \PHPixie\Route\Resolvers\Registry
{
    protected $resolverBuilder;
    protected $resolversConfig;
    
    protected $names;
    protected $resolverMap;
    
    public function __construct($resolverBuilder, $configData)
    {
        $this->resolverBuilder = $resolverBuilder;
        $this->resolversConfig = $configData->slice('resolvers');
    }
    
    public function names()
    {
        $this->requireRouteNames();
        return $this->names;
    }
    
    public function get($name)
    {
        $this->requireRouteNames();
        

        if(!array_key_exists($name, $this->resolverMap)) {
            throw new \PHPixie\Route\Exception\Route();
        }
        
        if($this->resolverMap[$name] === null) {
            $configData = $this->resolversConfig->slice($name);
            $this->resolverMap[$name] = $this->resolverBuilder->buildFromConfig($configData);
        }
        
        return $this->resolverMap[$name];
    }
    
    public function match($segment)
    {
        $match = null;
        
        foreach($this->names() as $name) {
            $resolver = $this->get($name);
            $match = $resolver->match($segment);
            if($match !== null) {
                $match->prependResolverPath($name);
                break;
            }
        }
        
        return $match;
    }
    
    public function generate($match, $withHost = false)
    {
        $name  = $match->popResolverPath();
        $resolver = $this->get($name);
        return $resolver->generate($match, $withHost);
    }
    
    protected function requireRouteNames()
    {
        if($this->names === null) {
            $this->names = $this->resolversConfig->keys();
            $this->resolverMap = array_fill_keys($this->names, null);
        }
    }
}