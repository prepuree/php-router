<?php
	class Router {
		private $methods = ['get', 'post', 'put', 'delete'];
		private $routes = [];
		private $lastGroupNamespace;
		private $namespace;
		private $patterns = [
			'<all>' => '([^/]+)',
			'<int>' => '([0-9]+)',
			'<string>' => '([a-zA-Z]+)',
			'<char>' => '([a-zA-Z0-9_]+)',
			'<url>' => '([a-zA-Z0-9_-]+)',
			'<*>' => '(.*)'
    ];
      
		public function __call($method, $args) {
			if(isset($this -> namespace))
				$args[0] = $this -> namespace.$args[0];
			if(in_array($method, $this -> methods))
				$this -> addRoute($method, $args[0], $args[1]);
    }
    
		public function group($suffix, $callback) {
			if(is_callable($callback)) {
				$this -> lastGroupNamespace = $this -> namespace .= $suffix;
				call_user_func($callback, $this);
				$this -> namespace = explode($suffix, $this -> namespace)[0];
      }

			return $this;
    }
    
		public function guard($callback) {
			foreach($this -> routes as $index => $route) {
				if(strstr($route['url'], $this -> lastGroupNamespace))
					array_push($this -> routes[$index]['guards'], $callback);
			}

			$this -> lastGroupNamespace = $this -> namespace;
    }
    
		public function run() {
			$routes = [];
			foreach($this -> routes as $route) {
				if($route['method'] == $_SERVER['REQUEST_METHOD'])
					if($route['url'] == $_SERVER['REQUEST_URI']) {
						$this -> loadRoute($route);
						return;
					}
					array_push($routes, $route);
      }
      
			$searches = array_keys($this -> patterns);
			$replaces = array_values($this -> patterns);
			foreach($routes as $route) {
        $url = str_replace($searches ,$replaces, $route['url']);
				if(preg_match('#^' . $url . '$#', $_SERVER['REQUEST_URI'], $matched)) {
					unset($matched[0]);
					$this -> loadRoute($route, $matched);
					return;
				}
      }
      
			http_response_code(404);
    }
    
		private function addRoute($method, $url, $callback) {
			array_push($this -> routes, [
				'method' => strtoupper($method),
				'url' => $url,
				'callback' => $callback,
				'guards' => []
			]);
    }

		private function loadRoute($route, $args = []) {
			$access = true;
			foreach($route['guards'] as $guard) {
				if(is_callable($guard)) {
					$access = call_user_func($guard);
				} else if(is_bool($guard)) {
					$access = $guard;
				} else if(is_string($guard)) {
					$access = $this -> loadClass($guard);
				} else {
					$access = false;
				}

				if($access === false) break;
			}

			if($access) {
        if(is_string($route['callback']))
					$this -> loadClass($route['callback'], $args);
				elseif(is_callable($route['callback']))
					call_user_func_array($route['callback'], $args);
			} else {
				http_response_code(403);
			}
    }

		private function loadClass($callback, $args = []) {
      $attrs = explode('::', $callback);
      $classNamespace = str_replace('/', '\\', $attrs[0]);
      $method = $attrs[1];
      $class = new $classNamespace;

      call_user_func_array([$class, $method], $args);
    }
	}
?>