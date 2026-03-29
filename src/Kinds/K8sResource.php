<?php

namespace RenokiCo\PhpK8s\Kinds;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Exceptions\KubernetesLogsException;
use RenokiCo\PhpK8s\Exceptions\KubernetesWatchException;
use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\Traits\Resource\HasAnnotations;
use RenokiCo\PhpK8s\Traits\Resource\HasAttributes;
use RenokiCo\PhpK8s\Traits\Resource\HasEvents;
use RenokiCo\PhpK8s\Traits\Resource\HasFinalizers;
use RenokiCo\PhpK8s\Traits\Resource\HasKind;
use RenokiCo\PhpK8s\Traits\Resource\HasLabels;
use RenokiCo\PhpK8s\Traits\Resource\HasName;
use RenokiCo\PhpK8s\Traits\Resource\HasNamespace;
use RenokiCo\PhpK8s\Traits\Resource\HasOwnerReferences;
use RenokiCo\PhpK8s\Traits\Resource\HasVersion;
use RenokiCo\PhpK8s\Traits\RunsClusterOperations;

class K8sResource implements Arrayable, Jsonable
{
    use HasAnnotations;
    use HasAttributes;
    use HasEvents;
    use HasFinalizers;
    use HasKind;
    use HasLabels;
    use HasName;
    use HasNamespace;
    use HasOwnerReferences;
    use HasVersion;
    use RunsClusterOperations;

    /**
     * Create a new resource.
     *
     * @return void
     */
    public function __construct(?KubernetesCluster $cluster = null, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->original = $attributes;

        if ($cluster instanceof KubernetesCluster) {
            $this->onCluster($cluster);
        }
    }

    /**
     * Register the current resource in macros.
     */
    public static function register(?string $name = null): void
    {
        K8s::registerCrd(static::class, $name);
    }

    /**
     * This method should be used only for CRDs.
     * It returns an internal macro name to help transition from YAML to resource
     * when importing YAML.
     */
    public static function getUniqueCrdMacro(?string $kind = null, ?string $defaultVersion = null): string
    {
        $kind = $kind ?: static::getKind();
        $defaultVersion = $defaultVersion ?: static::getDefaultVersion();

        return Str::of($kind.explode('/', $defaultVersion)[0])->camel()->slug();
    }

    /**
     * Get the plural resource name.
     */
    public static function getPlural(): string
    {
        return strtolower(Str::plural(static::getKind()));
    }

    /**
     * Check if the current resource exists.
     */
    public function exists(array $query = ['pretty' => 1]): bool
    {
        try {
            $this->get($query);
        } catch (KubernetesAPIException $e) {
            if ($e->getCode() === 404) {
                return false;
            }

            throw $e;
        }

        return true;
    }

    /**
     * Get a resource by name.
     */
    public function getByName(string $name, array $query = ['pretty' => 1]): K8sResource
    {
        return $this->whereName($name)->get($query);
    }

    /**
     * Get the instance as an array.
     * Optionally, you can specify the Kind attribute to replace.
     */
    #[\Override]
    public function toArray(?string $kind = null): array
    {
        $attributes = $this->attributes;

        // Make sure to also include the namespace.
        if (static::$namespaceable) {
            Arr::set($attributes, 'metadata.namespace', $this->getNamespace());
        }

        $instanceToArray = array_merge($attributes, [
            'kind' => $kind ?: $this::getKind(),
            'apiVersion' => $this->getApiVersion(),
        ]);

        ksort($instanceToArray);

        return $instanceToArray;
    }

    /**
     * Convert the object to its JSON representation.
     * Optionally, you can specify the Kind attribute to replace.
     *
     * @param  int  $options
     */
    #[\Override]
    public function toJson($options = 0, ?string $kind = null): string|false
    {
        return json_encode($this->toArray($kind), $options);
    }

    /**
     * Convert the object to its JSON representation, but
     * escaping [] for {}. Optionally, you can specify
     * the Kind attribute to replace.
     *
     * @return string
     */
    public function toJsonPayload(?string $kind = null): string|false
    {
        $attributes = $this->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES, $kind);

        $attributes = str_replace(': []', ': {}', $attributes);

        $attributes = str_replace('"allowedTopologies": {}', '"allowedTopologies": []', $attributes);
        $attributes = str_replace('"mountOptions": {}', '"mountOptions": []', $attributes);
        $attributes = str_replace('"accessModes": {}', '"accessModes": []', $attributes);

        return $attributes;
    }

    /**
     * Watch the specific resource by name.
     *
     *
     * @throws KubernetesWatchException
     */
    public function watchByName(string $name, Closure $callback, array $query = ['pretty' => 1]): mixed
    {
        return $this->whereName($name)->watch($callback, $query);
    }

    /**
     * Get logs for a specific container.
     *
     *
     * @throws KubernetesLogsException
     * @throws KubernetesAPIException
     */
    public function containerLogs(string $container, array $query = ['pretty' => 1]): string
    {
        return $this->logs(array_merge($query, ['container' => $container]));
    }

    /**
     * Watch the specific resource by name.
     *
     * @param  Closure  $callback
     *
     * @throws KubernetesLogsException
     * @throws KubernetesAPIException
     */
    public function logsByName(string $name, array $query = ['pretty' => 1]): string
    {
        return $this->whereName($name)->logs($query);
    }

    /**
     * Watch the specific resource by name.
     *
     * @param  Closure  $callback
     *
     * @throws KubernetesLogsException
     * @throws KubernetesAPIException
     */
    public function containerLogsByName(string $name, string $container, array $query = ['pretty' => 1]): string
    {
        return $this->whereName($name)->containerLogs($container, $query);
    }

    /**
     * Watch the specific resource's container logs until the closure returns true or false.
     *
     *
     * @throws KubernetesWatchException
     * @throws KubernetesLogsException
     */
    public function watchContainerLogs(string $container, Closure $callback, array $query = ['pretty' => 1]): mixed
    {
        return $this->watchLogs($callback, array_merge($query, ['container' => $container]));
    }

    /**
     * Watch the specific resource's logs by name.
     *
     *
     * @throws KubernetesWatchException
     * @throws KubernetesLogsException
     */
    public function watchLogsByName(string $name, Closure $callback, array $query = ['pretty' => 1]): mixed
    {
        return $this->whereName($name)->watchLogs($callback, $query);
    }

    /**
     * Watch the specific resource's container logs by names.
     *
     *
     * @throws KubernetesWatchException
     * @throws KubernetesLogsException
     */
    public function watchContainerLogsByName(string $name, string $container, Closure $callback, array $query = ['pretty' => 1]): mixed
    {
        return $this->whereName($name)->watchContainerLogs($container, $callback, $query);
    }
}
