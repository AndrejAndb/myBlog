<?php
namespace myBlog\Router;

use Traversable,
    Zend\Stdlib\IteratorToArray,
    Zend\Stdlib\RequestDescription as Request,
    Zend\Mvc\Router\Exception;

/**
 * Literal route.
 *
 * @package    Zend_Mvc_Router
 * @subpackage Http
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @see        http://manuals.rubyonrails.com/read/chapter/65
 */
class Post implements \Zend\Mvc\Router\Http\Route
{
    protected $locator;
    
    public function setLocator(\Zend\Di\Locator $locator)
    {
        $this->locator = $locator;
        return $this;
    }
    
    public function getLocator()
    {
        return $this->locator;
    }
    public function __construct()
    {
    }
    public static function factory($options = array())
    {
        return new static();
    }

    public function match(Request $request, $pathOffset = null)
    {
        if (!method_exists($request, 'uri')) {
            return null;
        }

        $uri  = $request->uri();
        $path = $uri->getPath();
        $slug = null;

        if ($pathOffset !== null) {
            if ($pathOffset >= 0 && strlen($path) >= $pathOffset) {
                $slug = substr($path, $pathOffset);
            }
        } else {
            $slug = $path;
        }
        
        if ($slug == null) {
            return null;
        }
        
        $feed = $this->getLocator()->get('myBlog\Model\Feed');
        $id = $feed->getIdFromSlug($slug);
        if($id === null) {
            return null;
        }
        $defaults = array();
        $defaults['id'] = $id;
        return new \Zend\Mvc\Router\Http\RouteMatch($defaults, strlen($slug));
    }

    /**
     * assemble(): Defined by Route interface.
     *
     * @see    Route::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = array(), array $options = array())
    {
        if (!isset($params['slug'])) {
            return '/';
        }
        return $params['slug'];
    }
    
    /**
     * getAssembledParams(): defined by Route interface.
     * 
     * @see    Route::getAssembledParams
     * @return array
     */
    public function getAssembledParams()
    {
        return array();
    }
}
