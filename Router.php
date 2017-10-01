<?php
	class Router {
		private $methods = ['get', 'post'];
		private $routes = [];
		private $guards = [];
		private $namespace;
		private $lastNamespace;
		private $patterns = [
			'<all>' => '([^/]+)',
			'<int>' => '([0-9]+)',
			'<string>' => '([a-zA-Z]+)',
			'<char>' => '([a-zA-Z0-9_]+)',
			'<url>' => '([a-zA-Z0-9_-]+)',
			'<*>' => '(.*)'
		];

		public function __construct(){}

		public function __call($method, $args){
			if(count($args) !== 2)
				return;

			if(isset($this -> namespace)){
				$args[0] = $this -> namespace.$args[0];
			}

			if(in_array($method, $this -> methods))
				$this -> addRoute($method, $args[0], $args[1]);
		}

		public function group($prefix, $callback){
			if(is_callable($callback)){
				$prefix = $this -> namespace .= $prefix;
				$this -> lastNamespace = $prefix;
				call_user_func($callback, $this);
				$this -> namespace = $this -> namespacePrev($this -> namespace);
			}

			return $this;
		}

		public function guard($callback){
			array_push($this -> guards, [
				'uri' => $this -> lastNamespace,
				'callback' => $callback
			]);
		}

		public function run(){
			$routes = [];
			foreach($this -> routes as $route){
				if($route['method'] == $_SERVER['REQUEST_METHOD'])
					if($route['uri'] == $_SERVER['REQUEST_URI']){
						$this -> loadRoute($route);
						return;
					}
					array_push($routes, $route);
			}

			$searches = array_keys($this -> patterns);
			$replaces = array_values($this -> patterns);

			foreach($routes as $route){
				$uri = str_replace($searches ,$replaces, $route['uri']);
				if(preg_match('#^' . $uri . '$#', $_SERVER['REQUEST_URI'], $matched)){
					unset($matched[0]);
					$this -> loadRoute($route, $matched);
					return;
				}
			}

			echo '404';
		}

		private function addRoute($method, $route, $args){
			array_push($this -> routes, [
				'method' => strtoupper($method),
				'uri' => $route,
				'callback' => $args
			]);
		}

		private function loadRoute($route, $args = []){
			$access = true;
			foreach($this -> guards as $guard){
				if($guard['uri'] == $this -> namespacePrev($route['uri']))
					$access = call_user_func($guard['callback']);
			}
			if($access){
				if(is_array($route['callback']))
					$this -> loadClass($route['callback'][0], $route['callback'][1], $args);
				elseif(is_callable($route['callback']))
					call_user_func_array($route['callback'], $args);
			} else {
				echo 'access denied';
			}
		}

		private function loadClass($class, $method, $args){
			require_once($class.'.php');
			$class = new $class;
			call_user_func_array([$class, $method], $args);
		}

		private function namespacePrev($namespace){
			$namespace = explode('/', $namespace);
			array_pop($namespace);
			return implode('/', $namespace);
		}
	}
?>
