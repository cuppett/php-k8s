<?php

namespace RenokiCo\PhpK8s\Traits;

use Closure;
use RenokiCo\PhpK8s\Contracts\Attachable;
use RenokiCo\PhpK8s\Contracts\Executable;
use RenokiCo\PhpK8s\Contracts\Loggable;
use RenokiCo\PhpK8s\Contracts\Scalable;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Enums\Operation;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Exceptions\KubernetesAttachException;
use RenokiCo\PhpK8s\Exceptions\KubernetesExecException;
use RenokiCo\PhpK8s\Exceptions\KubernetesLogsException;
use RenokiCo\PhpK8s\Exceptions\KubernetesScalingException;
use RenokiCo\PhpK8s\Exceptions\KubernetesWatchException;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Kinds\K8sScale;
use RenokiCo\PhpK8s\KubernetesCluster;
use RenokiCo\PhpK8s\Patches\JsonMergePatch;
use RenokiCo\PhpK8s\Patches\JsonPatch;
use RenokiCo\PhpK8s\ResourcesList;

trait RunsClusterOperations
{
    use Resource\HasAttributes;
    use Resource\HasNamespace;

    /**
     * The cluster instance that
     * binds to the cluster API.
     */
    protected ?KubernetesCluster $cluster = null;

    /**
     * Specify the cluster to attach to.
     *
     * @return $this
     */
    public function onCluster(KubernetesCluster $cluster): static
    {
        $this->cluster = $cluster;

        return $this;
    }

    /**
     * Get the resource version of the resource.
     */
    public function getResourceVersion(): ?string
    {
        return $this->getAttribute('metadata.resourceVersion', null);
    }

    /**
     * Get the resource UID.
     */
    public function getResourceUid(): ?string
    {
        return $this->getAttribute('metadata.uid', null);
    }

    /**
     * Get the identifier for the current resource.
     */
    public function getIdentifier(): mixed
    {
        return $this->getAttribute('metadata.name', null);
    }

    /**
     * Make a call to the cluster to get a fresh instance.
     *
     * @return $this
     */
    public function refresh(array $query = ['pretty' => 1]): static
    {
        return $this->syncWith($this->get($query)->toArray());
    }

    /**
     * Make a call to the cluster to get fresh original values.
     *
     * @return $this
     */
    public function refreshOriginal(array $query = ['pretty' => 1]): static
    {
        return $this->syncOriginalWith($this->get($query)->toArray());
    }

    /**
     * Make sure to sync the resource version with the original.
     *
     * @return $this
     */
    public function refreshResourceVersion(): static
    {
        $this->setAttribute(
            'metadata.resourceVersion',
            $this->original['metadata']['resourceVersion']
        );

        return $this;
    }

    /**
     * Create or update the resource, wether the resource exists
     * or not within the cluster.
     *
     * @return $this
     */
    public function syncWithCluster(array $query = ['pretty' => 1]): K8sResource
    {
        try {
            return $this->get($query);
        } catch (KubernetesAPIException $e) {
            return $this->create($query);
        }
    }

    /**
     * Create or update the app based on existence.
     *
     * @return $this
     */
    public function createOrUpdate(array $query = ['pretty' => 1]): K8sResource
    {
        if ($this->exists($query)) {
            $this->update($query);

            return $this;
        }

        return $this->create($query);
    }

    /**
     * Get a list with all resources.
     *
     *
     * @throws KubernetesAPIException
     */
    public function all(array $query = ['pretty' => 1]): ResourcesList
    {
        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::GET,
                $this->allResourcesPath(),
                $this->toJsonPayload(),
                $query
            );
    }

    /**
     * Get a list with all resources from all namespaces.
     *
     *
     * @throws KubernetesAPIException
     */
    public function allNamespaces(array $query = ['pretty' => 1]): ResourcesList
    {
        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::GET,
                $this->allResourcesPath(false),
                $this->toJsonPayload(),
                $query
            );
    }

    /**
     * Get a fresh instance from the cluster.
     *
     *
     * @throws KubernetesAPIException
     */
    public function get(array $query = ['pretty' => 1]): K8sResource
    {
        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::GET,
                $this->resourcePath(),
                $this->toJsonPayload(),
                $query
            );
    }

    /**
     * Create the resource.
     *
     *
     * @throws KubernetesAPIException
     */
    public function create(array $query = ['pretty' => 1]): K8sResource
    {
        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::CREATE,
                $this->allResourcesPath(),
                $this->toJsonPayload(),
                $query
            );
    }

    /**
     * Update the resource.
     *
     *
     * @throws KubernetesAPIException
     */
    public function update(array $query = ['pretty' => 1]): bool
    {
        $this->refreshOriginal();
        $this->refreshResourceVersion();

        // If it didn't change, no way to trigger the change.
        if (! $this->hasChanged()) {
            return true;
        }

        $instance = $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::REPLACE,
                $this->resourcePath(),
                $this->toJsonPayload(),
                $query
            );

        $this->syncWith($instance->toArray());

        return true;
    }

    /**
     * Delete the resource.
     *
     * @param  null|int  $gracePeriod
     *
     * @throws KubernetesAPIException
     */
    public function delete(array $query = ['pretty' => 1], $gracePeriod = null, string $propagationPolicy = 'Foreground'): bool
    {
        if (! $this->isSynced()) {
            return true;
        }

        $this->setAttribute('preconditions', [
            'resourceVersion' => $this->getResourceVersion(),
            'uid' => $this->getResourceUid(),
            'propagationPolicy' => $propagationPolicy,
            'gracePeriodSeconds' => $gracePeriod,
        ]);

        $this->refresh();

        $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::DELETE,
                $this->resourcePath(),
                $this->toJsonPayload('DeleteOptions'),
                $query
            );

        $this->synced = false;

        return true;
    }

    /**
     * Apply the resource using server-side apply.
     *
     * @return $this
     *
     * @throws KubernetesAPIException
     */
    public function apply(string $fieldManager, bool $force = false, array $query = ['pretty' => 1]): static
    {
        $query = array_merge($query, [
            'fieldManager' => $fieldManager,
        ]);

        if ($force) {
            $query['force'] = 'true';
        }

        $instance = $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::APPLY,
                $this->resourcePath(),
                $this->toJsonPayload(),
                $query
            );

        $this->syncWith($instance->toArray());

        return $this;
    }

    /**
     * Apply JSON Patch (RFC 6902) operations to the resource.
     *
     * @return $this
     *
     * @throws KubernetesAPIException
     */
    public function jsonPatch(JsonPatch|array $patch, array $query = ['pretty' => 1]): static
    {
        if (is_array($patch)) {
            $payload = json_encode($patch);
        } else {
            $payload = $patch->toJson();
        }

        $instance = $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::JSON_PATCH,
                $this->resourcePath(),
                $payload,
                $query
            );

        $this->syncWith($instance->toArray());

        return $this;
    }

    /**
     * Apply JSON Merge Patch (RFC 7396) to the resource.
     *
     * @return $this
     *
     * @throws KubernetesAPIException
     */
    public function jsonMergePatch(JsonMergePatch|array $patch, array $query = ['pretty' => 1]): static
    {
        if (is_array($patch)) {
            $payload = json_encode($patch);
        } else {
            $payload = $patch->toJson();
        }

        $instance = $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::JSON_MERGE_PATCH,
                $this->resourcePath(),
                $payload,
                $query
            );

        $this->syncWith($instance->toArray());

        return $this;
    }

    /**
     * Watch the resources list until the closure returns true or false.
     *
     *
     * @throws KubernetesWatchException
     */
    public function watchAll(Closure $callback, array $query = ['pretty' => 1]): mixed
    {
        if (! $this instanceof Watchable) {
            throw new KubernetesWatchException(
                'The resource '.get_class($this).' does not support watch actions.'
            );
        }

        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::WATCH,
                $this->allResourcesWatchPath(),
                $callback,
                $query
            );
    }

    /**
     * Watch the specific resource until the closure returns true or false.
     *
     *
     * @throws KubernetesWatchException
     */
    public function watch(Closure $callback, array $query = ['pretty' => 1]): mixed
    {
        if (! $this instanceof Watchable) {
            throw new KubernetesWatchException(
                'The resource '.get_class($this).' does not support watch actions.'
            );
        }

        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::WATCH,
                $this->resourceWatchPath(),
                $callback,
                $query
            );
    }

    /**
     * Get a specific resource's logs.
     *
     *
     * @throws KubernetesLogsException
     * @throws KubernetesAPIException
     */
    public function logs(array $query = ['pretty' => 1]): string
    {
        if (! $this instanceof Loggable) {
            throw new KubernetesLogsException(
                'The resource '.get_class($this).' does not support logs.'
            );
        }

        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::LOG,
                $this->resourceLogPath(),
                $this->toJsonPayload(),
                $query
            );
    }

    /**
     * Watch the specific resource's logs until the closure returns true or false.
     *
     *
     * @throws KubernetesWatchException
     * @throws KubernetesLogsException
     */
    public function watchLogs(Closure $callback, array $query = ['pretty' => 1]): mixed
    {
        if (! $this instanceof Loggable) {
            throw new KubernetesWatchException(
                'The resource '.get_class($this).' does not support logs.'
            );
        }

        if (! $this instanceof Watchable) {
            throw new KubernetesLogsException(
                'The resource '.get_class($this).' does not support watch actions.'
            );
        }

        // Ensure the ?follow=1 query exists to trigger the watch.
        $query = array_merge($query, ['follow' => 1]);

        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::WATCH_LOGS,
                $this->resourceLogPath(),
                $callback,
                $query
            );
    }

    /**
     * Get a specific resource scaling data.
     *
     *
     * @throws KubernetesScalingException
     * @throws KubernetesAPIException
     */
    public function scaler(): K8sScale
    {
        if (! $this instanceof Scalable) {
            throw new KubernetesScalingException(
                'The resource '.get_class($this).' does not support scaling.'
            );
        }

        $scaler = $this->cluster
            ->setResourceClass(K8sScale::class)
            ->runOperation(
                Operation::GET,
                $this->resourceScalePath(),
                $this->toJsonPayload(),
                ['pretty' => 1]
            );

        $scaler->setScalableResource($this);

        return $scaler;
    }

    /**
     * Exec a command on the current resource.
     *
     * @throws KubernetesExecException
     * @throws KubernetesAPIException
     */
    public function exec(
        string|array $command,
        ?string $container = null,
        array $query = ['pretty' => 1, 'stdin' => 1, 'stdout' => 1, 'stderr' => 1, 'tty' => 1]
    ): string|array {
        if (! $this instanceof Executable) {
            throw new KubernetesExecException(
                'The resource '.get_class($this).' does not support exec commands.'
            );
        }

        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::EXEC,
                $this->resourceExecPath(),
                '',
                ['command' => array_map('urlencode', $command), 'container' => $container] + $query
            );
    }

    /**
     * Attach to the current resource.
     *
     *
     * @throws KubernetesAttachException
     * @throws KubernetesAPIException
     */
    public function attach(
        ?Closure $callback = null,
        ?string $container = null,
        array $query = ['pretty' => 1, 'stdin' => 1, 'stdout' => 1, 'stderr' => 1, 'tty' => 1]
    ): string|array {
        if (! $this instanceof Attachable) {
            throw new KubernetesAttachException(
                'The resource '.get_class($this).' does not support attach commands.'
            );
        }

        return $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::ATTACH,
                $this->resourceAttachPath(),
                $callback,
                ['container' => $container] + $query
            );
    }

    /**
     * Get the path, prefixed by '/', that points to the resources list.
     */
    public function allResourcesPath(bool $withNamespace = true): string
    {
        return "{$this->getApiPathPrefix($withNamespace)}/".static::getPlural();
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource.
     */
    public function resourcePath(): string
    {
        return "{$this->getApiPathPrefix()}/".static::getPlural()."/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource watch.
     */
    public function allResourcesWatchPath(): string
    {
        return "{$this->getApiPathPrefix(false)}/watch/".static::getPlural();
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to watch.
     */
    public function resourceWatchPath(): string
    {
        return "{$this->getApiPathPrefix(true, 'watch')}/".static::getPlural()."/{$this->getIdentifier()}";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource scale.
     */
    public function resourceScalePath(): string
    {
        return "{$this->getApiPathPrefix()}/".static::getPlural()."/{$this->getIdentifier()}/scale";
    }

    /**
     * Get the path, prefixed by '/', that points to the resource status.
     */
    public function resourceStatusPath(): string
    {
        return "{$this->getApiPathPrefix()}/".static::getPlural()."/{$this->getIdentifier()}/status";
    }

    /**
     * Update the status subresource.
     */
    public function updateStatus(array $query = ['pretty' => 1]): self
    {
        $this->refreshOriginal();
        $this->refreshResourceVersion();

        return $this->syncWith(
            $this->cluster->runOperation(
                Operation::REPLACE,
                $this->resourceStatusPath(),
                $this->toJsonPayload(),
                $query
            )
        );
    }

    /**
     * JSON Patch (RFC 6902) the status subresource.
     */
    public function jsonPatchStatus(JsonPatch|array $patch, array $query = ['pretty' => 1]): self
    {
        if (! $patch instanceof JsonPatch) {
            $patch = new JsonPatch($patch);
        }

        $instance = $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::JSON_PATCH,
                $this->resourceStatusPath(),
                $patch->toJson(),
                $query
            );

        $this->syncWith($instance->toArray());

        return $this;
    }

    /**
     * JSON Merge Patch (RFC 7396) the status subresource.
     */
    public function jsonMergePatchStatus(JsonMergePatch|array $patch, array $query = ['pretty' => 1]): self
    {
        if (! $patch instanceof JsonMergePatch) {
            $patch = new JsonMergePatch($patch);
        }

        $instance = $this->cluster
            ->setResourceClass(get_class($this))
            ->runOperation(
                Operation::JSON_MERGE_PATCH,
                $this->resourceStatusPath(),
                $patch->toJson(),
                $query
            );

        $this->syncWith($instance->toArray());

        return $this;
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to log.
     */
    public function resourceLogPath(): string
    {
        return "{$this->getApiPathPrefix()}/".static::getPlural()."/{$this->getIdentifier()}/log";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to exec.
     */
    public function resourceExecPath(): string
    {
        return "{$this->getApiPathPrefix()}/".static::getPlural()."/{$this->getIdentifier()}/exec";
    }

    /**
     * Get the path, prefixed by '/', that points to the specific resource to attach.
     */
    public function resourceAttachPath(): string
    {
        return "{$this->getApiPathPrefix()}/".static::getPlural()."/{$this->getIdentifier()}/attach";
    }

    /**
     * Get the prefix path for the resource.
     */
    protected function getApiPathPrefix(bool $withNamespace = true, ?string $preNamespaceAction = null): string
    {
        $version = $this->getApiVersion();

        $path = $version === 'v1' ? '/api/v1' : "/apis/{$version}";

        if ($preNamespaceAction) {
            $path .= "/{$preNamespaceAction}";
        }

        if ($withNamespace && static::$namespaceable) {
            $path .= "/namespaces/{$this->getNamespace()}";
        }

        return $path;
    }
}
