# WordPress to MVC Migration Roadmap

## 📋 Overview

This document outlines the phased migration strategy from a WordPress-based system to a standalone MVC architecture while maintaining backward compatibility.

**Timeline:** Q3 2026 → Q1 2027  
**Goal:** Reduce dependencies on WordPress core while preserving existing functionality  
**Risk Level:** Low (parallel systems approach)

---

## 🎯 Phase 1: Foundation & Setup (Q3 2026)

### Objectives
- Establish MVC framework structure
- Create base classes and interfaces
- Set up dependency injection
- Build database abstraction layer
- Configure routing system

### 1.1 Create Project Structure

```
app/
├── Config/
│   ├── Container.php              # Service container
│   ├── Database.php               # Database configuration
│   └── Services.php               # Service registration
├── Core/
│   ├── Application.php            # Main app class
│   ├── Controller.php             # Base controller
│   ├── Model.php                  # Base model
│   ├── View.php                   # View renderer
│   ├── Router.php                 # URL routing
│   ├── Request.php                # Request handler
│   └── Response.php               # Response handler
├── Exceptions/
│   ├── NotFoundException.php
│   ├── UnauthorizedException.php
│   └── ValidationException.php
├── Middleware/
│   ├── AuthenticationMiddleware.php
│   ├── AuthorizationMiddleware.php
│   └── ValidationMiddleware.php
├── Services/
│   └── (services go here)
└── Modules/
    └── (modules go here)

public/
├── index.php                      # Single entry point
├── api.php                        # API entry point
└── assets/
    ├── css/
    ├── js/
    └── images/

config/
├── app.php                        # App configuration
├── database.php                   # Database config
├── cache.php                      # Cache config
└── services.php                   # Service configuration

storage/
├── logs/
├── uploads/
└── cache/

tests/
├── Unit/
├── Integration/
└── fixtures/

bootstrap.php                      # Application bootstrap
composer.json                      # Dependencies
```

### 1.2 Core Framework Implementation

#### Config/Container.php (Dependency Injection)

```php
<?php
namespace Lesarge\Config;

class Container
{
    private array $services = [];
    private array $singletons = [];

    public function register(string $name, callable $definition): void
    {
        $this->services[$name] = $definition;
    }

    public function singleton(string $name, callable $definition): void
    {
        $this->singletons[$name] = $definition;
    }

    public function get(string $name): mixed
    {
        if (isset($this->singletons[$name])) {
            if (!isset($this->singletons[$name]['instance'])) {
                $this->singletons[$name]['instance'] = $this->resolve($name);
            }
            return $this->singletons[$name]['instance'];
        }

        return $this->resolve($name);
    }

    private function resolve(string $name): mixed
    {
        $definition = $this->services[$name] ?? $this->singletons[$name] ?? null;
        if (!$definition) {
            throw new \Exception("Service not found: $name");
        }

        return $definition($this);
    }
}
```

#### Core/Controller.php (Base Controller)

```php
<?php
namespace Lesarge\Core;

use Lesarge\Config\Container;

abstract class Controller
{
    protected Container $container;
    protected array $data = [];
    protected string $view = '';

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Render HTML view
     */
    protected function render(string $view, array $data = []): Response
    {
        $this->view = $view;
        $this->data = array_merge($this->data, $data);
        
        return new Response(
            $this->renderView($view, $data),
            200,
            ['Content-Type' => 'text/html']
        );
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $status = 200): Response
    {
        return new Response(
            json_encode($data),
            $status,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Authorize action
     */
    protected function authorize($capability): void
    {
        // WordPress capability check
        if (!current_user_can($capability)) {
            throw new UnauthorizedException("Unauthorized action: $capability");
        }
    }

    /**
     * Render view file
     */
    private function renderView(string $view, array $data): string
    {
        $path = app_path("Modules/{$this->module}/Views/{$view}.php");
        
        if (!file_exists($path)) {
            throw new NotFoundException("View not found: $view");
        }

        extract($data);
        ob_start();
        require $path;
        return ob_get_clean();
    }
}
```

#### Core/Model.php (Base Model)

```php
<?php
namespace Lesarge\Core;

use Lesarge\Config\Database;

abstract class Model
{
    protected static string $table = '';
    protected array $attributes = [];
    protected array $casts = [];
    protected array $fillable = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Get all records
     */
    public static function all(): array
    {
        $db = Database::getInstance();
        $results = $db->select('SELECT * FROM ' . static::$table);
        return array_map(fn($row) => new static($row), $results);
    }

    /**
     * Get by ID
     */
    public static function find(int $id): ?static
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM ' . static::$table . ' WHERE id = ?');
        $stmt->execute([$id]);
        
        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return new static($row);
        }
        return null;
    }

    /**
     * Create new record
     */
    public static function create(array $attributes): static
    {
        $instance = new static($attributes);
        $instance->save();
        return $instance;
    }

    /**
     * Save to database
     */
    public function save(): bool
    {
        $db = Database::getInstance();
        
        if (isset($this->attributes['id'])) {
            return $this->update();
        }

        $columns = implode(',', array_keys($this->attributes));
        $placeholders = implode(',', array_fill(0, count($this->attributes), '?'));
        
        $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute(array_values($this->attributes));
        
        if ($result) {
            $this->attributes['id'] = $db->lastInsertId();
        }
        
        return $result;
    }

    /**
     * Update record
     */
    public function update(): bool
    {
        $db = Database::getInstance();
        $id = $this->attributes['id'];
        
        $sets = array_map(fn($k) => "$k = ?", array_keys($this->attributes));
        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $sets) . " WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute(array_merge(array_values($this->attributes), [$id]));
    }

    /**
     * Fill model with attributes
     */
    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable) || empty($this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Get attribute
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Set attribute
     */
    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }
}
```

#### Core/Router.php (URL Routing)

```php
<?php
namespace Lesarge\Core;

class Router
{
    private array $routes = ['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => []];
    private string $prefix = '';

    public function group(array $options, callable $callback): void
    {
        $this->prefix = $options['prefix'] ?? '';
        $callback($this);
        $this->prefix = '';
    }

    public function get(string $path, string $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, string $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, string $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, string $handler): void
    {
        $fullPath = $this->prefix . $path;
        $this->routes[$method][$fullPath] = $handler;
    }

    public function dispatch(string $method, string $path): Response
    {
        $method = strtoupper($method);
        
        foreach ($this->routes[$method] as $routePath => $handler) {
            if ($this->matches($routePath, $path, $params)) {
                return $this->call($handler, $params);
            }
        }

        throw new NotFoundException("Route not found: $method $path");
    }

    private function matches(string $pattern, string $path, &$params = []): bool
    {
        $pattern = preg_replace('/:[^\/]+/', '([^/]+)', $pattern);
        $pattern = "/^" . str_replace('/', '\/', $pattern) . "$/";
        
        if (preg_match($pattern, $path, $matches)) {
            array_shift($matches);
            $params = $matches;
            return true;
        }
        
        return false;
    }

    private function call(string $handler, array $params): Response
    {
        [$class, $method] = explode('@', $handler);
        $instance = new $class(app('container'));
        
        return $instance->$method(...$params);
    }
}
```

### 1.3 Database Abstraction Layer

```php
<?php
namespace Lesarge\Config;

class Database
{
    private static ?\PDO $instance = null;

    public static function getInstance(): \PDO
    {
        if (self::$instance === null) {
            self::$instance = new \PDO(
                sprintf(
                    'mysql:host=%s;dbname=%s;charset=utf8mb4',
                    getenv('DB_HOST'),
                    getenv('DB_NAME')
                ),
                getenv('DB_USER'),
                getenv('DB_PASSWORD'),
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        }

        return self::$instance;
    }

    public static function prepare(string $sql): \PDOStatement
    {
        return self::getInstance()->prepare($sql);
    }

    public static function select(string $sql, array $params = []): array
    {
        $stmt = self::prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function insert(string $sql, array $params = []): int
    {
        $stmt = self::prepare($sql);
        $stmt->execute($params);
        return self::getInstance()->lastInsertId();
    }

    public static function update(string $sql, array $params = []): int
    {
        $stmt = self::prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function delete(string $sql, array $params = []): int
    {
        $stmt = self::prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
```

### 1.4 Service Container Setup

```php
<?php
// bootstrap.php

use Lesarge\Config\Container;
use Lesarge\Config\Database;
use Lesarge\Core\Router;

$container = new Container();

// Register core services
$container->singleton('container', fn() => $container);

$container->singleton('database', function() {
    return Database::getInstance();
});

$container->singleton('router', function($c) {
    return new Router();
});

// Register module services
require 'config/services.php';

// Global helper
function app($name = null) {
    global $container;
    return $name ? $container->get($name) : $container;
}

return $container;
```

### 1.5 Public Entry Point

```php
<?php
// public/index.php

define('APP_PATH', dirname(__DIR__));

// Load WordPress for compatibility
require APP_PATH . '/wp-load.php';

// Load MVC app
$container = require APP_PATH . '/bootstrap.php';

// Route the request
$router = app('router');
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {
    $response = $router->dispatch($method, $path);
    $response->send();
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
```

### Deliverables for Phase 1

- [ ] MVC directory structure created
- [ ] Container/DI implementation complete
- [ ] Base Controller, Model, Router classes
- [ ] Database abstraction layer
- [ ] Bootstrap file
- [ ] Unit tests for core framework
- [ ] Documentation of architecture

---

## 🏢 Phase 2: Module Migration (Q4 2026)

### 2.1 Migrate HR CRM Module

**Scope:**
- Employee directory
- Departments
- Contacts
- Activity logs

**Structure:**
```
app/Modules/HrCrm/
├── Controllers/
│   ├── EmployeeController.php
│   ├── DepartmentController.php
│   └── ContactController.php
├── Models/
│   ├── Employee.php
│   ├── Department.php
│   └── Contact.php
├── Services/
│   ├── EmployeeService.php
│   └── ContactService.php
├── Migrations/
│   └── create_employees_table.php
└── Routes/
    └── web.php
```

**Database Schema:**
```sql
CREATE TABLE employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    department_id INT,
    position VARCHAR(100),
    hire_date DATE,
    status ENUM('active', 'inactive', 'on_leave'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    manager_id INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2.2 REST API Endpoints

```
GET    /api/employees              # List all
GET    /api/employees/:id          # Get one
POST   /api/employees              # Create
PUT    /api/employees/:id          # Update
DELETE /api/employees/:id          # Delete

GET    /api/departments            # List departments
GET    /api/departments/:id        # Get department
```

### 2.3 Migration Approach

1. Create MVC models/controllers
2. Duplicate data retrieval (WordPress + MVC)
3. Add feature flags for routing
4. Gradually switch users to new UI
5. Archive old WordPress code

---

## 🔌 Phase 3: API & Integration (Q1 2027)

### 3.1 REST API v2

Standardized endpoints with proper HTTP methods

### 3.2 Authentication

JWT or OAuth2 for external clients

### 3.3 Mobile API

Optimized endpoints for mobile apps

---

## 📊 Success Metrics

- [ ] 100% of new code follows MVC pattern
- [ ] ≥80% test coverage
- [ ] API response time <200ms
- [ ] Zero breaking changes
- [ ] Security audit passed
- [ ] Performance improved 30%+

---

## 🔄 Backward Compatibility Strategy

- WordPress plugin adapter layer
- Shared database schema
- Gradual data migration
- Feature flags for routing

---

## 📅 Timeline

| Phase | Start | End | Duration | Focus |
|-------|-------|-----|----------|-------|
| 1 | Jul 2026 | Aug 2026 | 4 weeks | Framework & foundation |
| 2 | Sep 2026 | Dec 2026 | 12 weeks | Module migration |
| 3 | Jan 2027 | Mar 2027 | 12 weeks | API & optimization |

---

## 👥 Team Assignments

- **Architecture Lead:** Framework & core infrastructure
- **Module Leads:** Individual module migration
- **DevOps Lead:** Deployment & infrastructure
- **QA Lead:** Testing & validation

---

## 🚨 Risk Mitigation

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|-----------|
| Data loss | Low | High | Full backups before each phase |
| Performance issues | Medium | High | Load testing at milestones |
| Breaking changes | Medium | High | Comprehensive integration tests |
| Security vulnerabilities | Medium | High | Security audit after phase 1 |
| Team adoption | Medium | Medium | Training & documentation |

---

## 📚 References

- [MVC Architecture Pattern](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)
- [REST API Best Practices](https://restfulapi.net/)
- [Dependency Injection Pattern](https://www.php-fig.org/psr/psr-11/)
- [PHP Standards](https://www.php-fig.org/)
