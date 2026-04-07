<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method:string, regex:string, params:array<int,string>, handler:mixed, path:string, middlewares:array}> */
    private array $routes = [];

    /**
     * Example basePath on XAMPP:
     *   /cityevents/public
     */
    private string $basePath = "";

    public function __construct(?string $basePath = null)
    {
        if ($basePath !== null) {
            $this->setBasePath($basePath);
            return;
        }

        // Auto-detect from SCRIPT_NAME, e.g. /cityevents/public/index.php -> /cityevents/public
        $detected = rtrim(
            str_replace(
                "\\",
                "/",
                (string) dirname($_SERVER["SCRIPT_NAME"] ?? ""),
            ),
            "/",
        );
        $this->basePath = $detected === "/" ? "" : $detected;
    }

    public function setBasePath(string $basePath): void
    {
        $basePath = str_replace("\\", "/", $basePath);
        $basePath = rtrim($basePath, "/");
        $this->basePath = $basePath === "/" ? "" : $basePath;
    }

    public function get(
        string $path,
        mixed $handler,
        array $middlewares = [],
    ): void
    {
        $this->add("GET", $path, $handler, $middlewares);
    }

    public function post(
        string $path,
        mixed $handler,
        array $middlewares = [],
    ): void
    {
        $this->add("POST", $path, $handler, $middlewares);
    }

    public function add(
        string $method,
        string $path,
        mixed $handler,
        array $middlewares = [],
    ): void
    {
        $path = $this->normalizeRoutePath($path);
        [$regex, $params] = $this->compile($path);

        $this->routes[] = [
            "method" => strtoupper($method),
            "path" => $path,
            "regex" => $regex,
            "params" => $params,
            "handler" => $handler,
            "middlewares" => $middlewares,
        ];
    }

    /**
     * Dispatch current request if args not provided.
     */
    public function dispatch(?string $method = null, ?string $uri = null): void
    {
        $method = strtoupper($method ?? ($_SERVER["REQUEST_METHOD"] ?? "GET"));
        $uri = $uri ?? ($_SERVER["REQUEST_URI"] ?? "/");

        $path = $this->normalizeRequestPath($uri);

        foreach ($this->routes as $r) {
            if ($r["method"] !== $method) {
                continue;
            }

            if (preg_match($r["regex"], $path, $m)) {
                $assoc = [];
                $positional = [];

                foreach ($r["params"] as $p) {
                    $assoc[$p] = $m[$p] ?? null;
                    $positional[] = $m[$p] ?? null;
                }

                if (!$this->runMiddlewares($r["middlewares"], $assoc)) {
                    return;
                }

                $this->invoke($r["handler"], $positional, $assoc);
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found: " . htmlspecialchars($path, ENT_QUOTES, "UTF-8");
    }

    // ----------------------------
    // Internals
    // ----------------------------

    private function normalizeRoutePath(string $path): string
    {
        $path = "/" . ltrim($path, "/");
        return $path === "/" ? "/" : rtrim($path, "/");
    }

    private function normalizeRequestPath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? "/";

        // Strip basePath (e.g. /cityevents/public).
        // Use case-insensitive match to avoid 404s when URL casing differs.
        if (
            $this->basePath !== "" &&
            stripos($path, $this->basePath) === 0
        ) {
            $path = substr($path, strlen($this->basePath));
        }

        // Also strip index.php if it's explicitly in the URL
        if (str_starts_with($path, "/index.php")) {
            $path = substr($path, 10);
        }

        // normalize
        $path = "/" . ltrim($path, "/");
        if ($path !== "/") {
            $path = rtrim($path, "/");
        }

        return $path;
    }

    /**
     * /events/{id} -> #^/events/(?P<id>[^/]+)$#
     * /events/{id:\d+} -> #^/events/(?P<id>\d+)$#
     */
    private function compile(string $path): array
    {
        $params = [];

        $regex = preg_replace_callback(
            "/\{([a-zA-Z_][a-zA-Z0-9_]*)(:([^}]+))?\}/",
            function (array $m) use (&$params): string {
                $name = $m[1];
                $params[] = $name;
                $pattern = $m[3] ?? "[^/]+";
                return "(?P<" . $name . ">" . $pattern . ")";
            },
            $path,
        );

        // IMPORTANT: do NOT rtrim "/" into empty string
        if ($regex !== "/" && str_ends_with($regex, "/")) {
            $regex = rtrim($regex, "/");
        }

        return ["#^" . $regex . '$#', $params];
    }

    private function invoke(
        mixed $handler,
        array $positionalArgs,
        array $assocArgs,
    ): void {
        // 1) callable (closure, function, [obj,'method'], etc.)
        if (is_callable($handler)) {
            $this->callSmart($handler, $positionalArgs, $assocArgs);
            return;
        }

        // 2) "Controller@method"
        if (is_string($handler) && str_contains($handler, "@")) {
            [$controller, $method] = explode("@", $handler, 2);

            $class = $this->resolveControllerClass($controller);

            $obj = new $class();

            if (!method_exists($obj, $method)) {
                throw new \RuntimeException(
                    "Controller method not found: {$class}::{$method}()",
                );
            }

            $this->callSmart([$obj, $method], $positionalArgs, $assocArgs);
            return;
        }

        // 3) [ClassOrObject, 'method']
        if (is_array($handler) && count($handler) === 2) {
            [$classOrObj, $method] = $handler;

            if (is_string($classOrObj)) {
                $class = $this->resolveControllerClass($classOrObj);
                $obj = new $class();
                if (!method_exists($obj, $method)) {
                    throw new \RuntimeException(
                        "Controller method not found: {$class}::{$method}()",
                    );
                }
                $this->callSmart([$obj, $method], $positionalArgs, $assocArgs);
                return;
            }

            if (is_object($classOrObj)) {
                if (!method_exists($classOrObj, (string) $method)) {
                    throw new \RuntimeException(
                        "Method not found on handler object",
                    );
                }
                $this->callSmart(
                    [$classOrObj, $method],
                    $positionalArgs,
                    $assocArgs,
                );
                return;
            }
        }

        throw new \RuntimeException("Invalid route handler");
    }

    /**
     * Resolves controller name to an actual class.
     * Supports:
     *  - Fully qualified names: App\Controllers\HomeController
     *  - Legacy global controllers: HomeController (loads src/Controllers/HomeController.php)
     *  - Namespaced controllers: App\Controllers\HomeController (if you later add namespaces)
     */
    private function resolveControllerClass(string $controller): string
    {
        // If fully qualified, just trust it
        if (str_contains($controller, "\\")) {
            if (!class_exists($controller)) {
                throw new \RuntimeException(
                    "Controller class not found: {$controller}",
                );
            }
            return $controller;
        }

        // 1) Try namespaced first (future-proof)
        $namespaced = "App\\Controllers\\" . $controller;
        if (class_exists($namespaced)) {
            return $namespaced;
        }

        // 2) Try legacy global controller class; load file if needed
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $controller)) {
            throw new \RuntimeException(
                "Invalid controller name: {$controller}",
            );
        }

        $legacyFile = __DIR__ . "/../Controllers/" . $controller . ".php";
        if (is_file($legacyFile)) {
            require_once $legacyFile;
        }

        if (class_exists($controller)) {
            return $controller;
        }

        throw new \RuntimeException(
            "Controller class not found: {$namespaced} or {$controller}",
        );
    }

    /**
     * Avoids fatal "too many arguments" by checking arity:
     *  - 0 params -> call with ()
     *  - 1 param typed array -> pass associative array of route params
     *  - 1 param otherwise -> pass single route value (e.g. /events/{id})
     *  - 2+       -> pass positional params
     */
    private function callSmart(
        callable $cb,
        array $positionalArgs,
        array $assocArgs,
    ): void {
        try {
            $ref = is_array($cb)
                ? new \ReflectionMethod($cb[0], (string) $cb[1])
                : new \ReflectionFunction(\Closure::fromCallable($cb));

            $n = $ref->getNumberOfParameters();

            if ($n === 0) {
                call_user_func($cb);
                return;
            }

            if ($n === 1) {
                $param = $ref->getParameters()[0];
                if ($this->reflectionParamAcceptsArray($param)) {
                    call_user_func($cb, $assocArgs);
                    return;
                }

                $value = $positionalArgs[0] ?? reset($assocArgs);
                call_user_func(
                    $cb,
                    $this->castRouteValueForParameter($param, $value),
                );

                return;
            }

            call_user_func_array($cb, $positionalArgs);
        } catch (\ReflectionException) {
            // fallback: try positional
            call_user_func_array($cb, $positionalArgs);
        }
    }

    private function reflectionParamAcceptsArray(\ReflectionParameter $param): bool
    {
        $type = $param->getType();
        if ($type === null) {
            return false;
        }
        if ($type instanceof \ReflectionNamedType) {
            return $type->getName() === "array";
        }
        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $u) {
                if ($u instanceof \ReflectionNamedType && $u->getName() === "array") {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function castRouteValueForParameter(
        \ReflectionParameter $param,
        $value,
    ) {
        $type = $param->getType();
        if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
            if ($type instanceof \ReflectionNamedType) {
                $name = $type->getName();
                if ($name === "int") {
                    return (int) $value;
                }
                if ($name === "float") {
                    return (float) $value;
                }
                if ($name === "string") {
                    return (string) $value;
                }
                if ($name === "bool") {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }
            }

            return $value;
        }

        return $value;
    }

    private function runMiddlewares(array $middlewares, array $assocArgs): bool
    {
        foreach ($middlewares as $middleware) {
            if (is_callable($middleware)) {
                $result = $middleware($assocArgs);
                if ($result === false) {
                    return false;
                }
                continue;
            }

            if (is_object($middleware) && method_exists($middleware, "handle")) {
                $result = $middleware->handle($assocArgs);
                if ($result === false) {
                    return false;
                }
                continue;
            }

            throw new \RuntimeException("Invalid middleware handler");
        }

        return true;
    }
}
