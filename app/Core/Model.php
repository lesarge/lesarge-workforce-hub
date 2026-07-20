<?php
/**
 * Base Model Class
 * Provides common database operations
 */

namespace Lesarge\Core;

abstract class Model
{
    protected static string $table = '';
    protected array $attributes = [];
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
        global $wpdb;
        $sql = "SELECT * FROM " . $wpdb->prefix . static::$table;
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        return array_map(fn($row) => new static($row), $results ?: []);
    }

    /**
     * Find by ID
     */
    public static function find(int $id): ?self
    {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT * FROM " . $wpdb->prefix . static::$table . " WHERE id = %d",
            $id
        );
        
        $result = $wpdb->get_row($sql, ARRAY_A);
        return $result ? new static($result) : null;
    }

    /**
     * Create new record
     */
    public static function create(array $attributes): self
    {
        $instance = new static($attributes);
        $instance->save();
        return $instance;
    }

    /**
     * Save model to database
     */
    public function save(): bool
    {
        global $wpdb;
        
        if (isset($this->attributes['id'])) {
            return $this->update();
        }

        $result = $wpdb->insert(
            $wpdb->prefix . static::$table,
            $this->attributes
        );

        if ($result) {
            $this->attributes['id'] = $wpdb->insert_id;
        }

        return (bool) $result;
    }

    /**
     * Update existing record
     */
    public function update(): bool
    {
        global $wpdb;
        $id = $this->attributes['id'];
        
        $data = $this->attributes;
        unset($data['id']);

        return (bool) $wpdb->update(
            $wpdb->prefix . static::$table,
            $data,
            ['id' => $id]
        );
    }

    /**
     * Delete record
     */
    public function delete(): bool
    {
        global $wpdb;
        return (bool) $wpdb->delete(
            $wpdb->prefix . static::$table,
            ['id' => $this->attributes['id'] ?? null]
        );
    }

    /**
     * Fill model with attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (empty($this->fillable) || in_array($key, $this->fillable)) {
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

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
