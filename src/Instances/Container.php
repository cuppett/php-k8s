<?php

namespace RenokiCo\PhpK8s\Instances;

class Container extends Instance
{
    /**
     * Set the image for the container.
     *
     * @return $this
     */
    public function setImage(string $image, string $tag = 'latest'): static
    {
        return $this->setAttribute('image', $image.':'.$tag);
    }

    /**
     * Add a new port to the container list.
     *
     * @return $this
     */
    public function addPort(int $containerPort, string $protocol = 'TCP', ?string $name = null): static
    {
        return $this->addToAttribute('ports', [
            'name' => $name,
            'protocol' => $protocol,
            'containerPort' => $containerPort,
        ]);
    }

    /**
     * Add a volume mount.
     *
     * @return $this
     */
    public function addMountedVolume(MountedVolume|array $volume): static
    {
        if ($volume instanceof MountedVolume) {
            $volume = $volume->toArray();
        }

        return $this->addToAttribute('volumeMounts', $volume);
    }

    /**
     * Batch-add multiple volume mounts.
     *
     * @return $this
     */
    public function addMountedVolumes(array $volumes): static
    {
        foreach ($volumes as $volume) {
            $this->addMountedVolume($volume);
        }

        return $this;
    }

    /**
     * Set the mounted volumes.
     *
     * @return $this
     */
    public function setMountedVolumes(array $volumes): static
    {
        foreach ($volumes as &$volume) {
            if ($volume instanceof MountedVolume) {
                $volume = $volume->toArray();
            }
        }

        return $this->setAttribute('volumeMounts', $volumes);
    }

    /**
     * Get the mounted volumes.
     */
    public function getMountedVolumes(bool $asInstance = true): array
    {
        $mountedVolumes = $this->getAttribute('volumeMounts', []);

        if ($asInstance) {
            foreach ($mountedVolumes as &$volume) {
                $volume = new MountedVolume($volume);
            }
        }

        return $mountedVolumes;
    }

    /**
     * Add an env variable by using a secret reference to the container.
     *
     * @return $this
     */
    public function addSecretKeyRef(string $name, string $secretName, string $key): static
    {
        return $this->addEnv($name, [
            'valueFrom' => [
                'secretKeyRef' => [
                    'name' => $secretName,
                    'key' => $key,
                ],
            ],
        ]);
    }

    /**
     * Add multiple secret references to the container.
     *
     * @return $this
     */
    public function addSecretKeyRefs(array $envsWithRefs): static
    {
        foreach ($envsWithRefs as $envName => $refs) {
            $this->addSecretKeyRef($envName, ...$refs);
        }

        return $this;
    }

    /**
     * Add an env variable by using a configmap reference to the container.
     *
     * @return $this
     */
    public function addConfigMapRef(string $name, string $cmName, string $key): static
    {
        return $this->addEnv($name, [
            'valueFrom' => [
                'configMapKeyRef' => [
                    'name' => $cmName,
                    'key' => $key,
                ],
            ],
        ]);
    }

    /**
     * Add multiple configmap references to the container.
     *
     * @return $this
     */
    public function addConfigMapRefs(array $envsWithRefs): static
    {
        foreach ($envsWithRefs as $envName => $refs) {
            $this->addConfigMapRef($envName, ...$refs);
        }

        return $this;
    }

    /**
     * Add an env variable by using a field reference to the container.
     *
     * @param  string  $cmName
     * @param  string  $key
     * @return $this
     */
    public function addFieldRef(string $name, string $fieldPath): static
    {
        return $this->addEnv($name, [
            'valueFrom' => [
                'fieldRef' => [
                    'fieldPath' => $fieldPath,
                ],
            ],
        ]);
    }

    /**
     * Add multiple field references to the container.
     *
     * @return $this
     */
    public function addFieldRefs(array $envsWithRefs): static
    {
        foreach ($envsWithRefs as $envName => $refs) {
            $this->addFieldRef($envName, ...$refs);
        }

        return $this;
    }

    /**
     * Add an env variable to the container.
     *
     * @return $this
     */
    public function addEnv(string $name, mixed $value): static
    {
        // If a valuFrom is encountered, add it under valueFrom instead.
        if (is_array($value) && array_key_exists('valueFrom', $value)) {
            return $this->addToAttribute('env', ['name' => $name, 'valueFrom' => $value['valueFrom']]);
        }

        return $this->addToAttribute('env', ['name' => $name, 'value' => $value]);
    }

    /**
     * Batch-add a list of envs.
     *
     * @return $this
     */
    public function addEnvs(array $envs): static
    {
        foreach ($envs as $name => $value) {
            $this->addEnv($name, $value);
        }

        return $this;
    }

    /**
     * Set the environments.
     *
     * @return $this
     */
    public function setEnv(array $envs): static
    {
        $envs = collect($envs)->map(function ($value, $name) {
            // If a valuFrom is encountered, add it under valueFrom instead.
            if (is_array($value) && array_key_exists('valueFrom', $value)) {
                return ['name' => $name, 'valueFrom' => $value['valueFrom']];
            }

            return ['name' => $name, 'value' => $value];
        })->values()->toArray();

        return $this->setAttribute('env', $envs);
    }

    /**
     * Requests minimum memory for the container.
     *
     * @return $this
     */
    public function minMemory(int $size, string $measure = 'Gi'): static
    {
        return $this->setAttribute('resources.requests.memory', $size.$measure);
    }

    /**
     * Get the minimum memory amount.
     */
    public function getMinMemory(): ?string
    {
        return $this->getAttribute('resources.requests.memory', null);
    }

    /**
     * Requests minimum CPU for the container.
     *
     * @return $this
     */
    public function minCpu(string $size): static
    {
        return $this->setAttribute('resources.requests.cpu', $size);
    }

    /**
     * Get the minimum CPU amount.
     */
    public function getMinCpu(): ?string
    {
        return $this->getAttribute('resources.requests.cpu', null);
    }

    /**
     * Sets max memory for the container.
     *
     * @return $this
     */
    public function maxMemory(int $size, string $measure = 'Gi'): static
    {
        return $this->setAttribute('resources.limits.memory', $size.$measure);
    }

    /**
     * Get the max memory amount.
     */
    public function getMaxMemory(): ?string
    {
        return $this->getAttribute('resources.limits.memory', null);
    }

    /**
     * Sets max CPU for the container.
     *
     * @return $this
     */
    public function maxCpu(string $size): static
    {
        return $this->setAttribute('resources.limits.cpu', $size);
    }

    /**
     * Get the max CPU amount.
     */
    public function getMaxCpu(): ?string
    {
        return $this->getAttribute('resources.limits.cpu', null);
    }

    /**
     * Set the readiness probe for the container.
     *
     * @return $this
     */
    public function setReadinessProbe(Probe $probe): static
    {
        return $this->setAttribute('readinessProbe', $probe->toArray());
    }

    /**
     * Get the readiness probe.
     */
    public function getReadinessProbe(bool $asInstance = true): Probe|array|null
    {
        $probe = $this->getAttribute('readinessProbe', null);

        if (! $probe) {
            return null;
        }

        return $asInstance ? new Probe($probe) : $probe;
    }

    /**
     * Set the liveness probe for the container.
     *
     * @return $this
     */
    public function setLivenessProbe(Probe $probe): static
    {
        return $this->setAttribute('livenessProbe', $probe->toArray());
    }

    /**
     * Get the liveness probe.
     */
    public function getLivenessProbe(bool $asInstance = true): Probe|array|null
    {
        $probe = $this->getAttribute('livenessProbe', null);

        if (! $probe) {
            return null;
        }

        return $asInstance ? new Probe($probe) : $probe;
    }

    /**
     * Set the startup probe for the container.
     *
     * @return $this
     */
    public function setStartupProbe(Probe $probe): static
    {
        return $this->setAttribute('startupProbe', $probe->toArray());
    }

    /**
     * Get the startup probe.
     */
    public function getStartupProbe(bool $asInstance = true): Probe|array|null
    {
        $probe = $this->getAttribute('startupProbe', null);

        if (! $probe) {
            return null;
        }

        return $asInstance ? new Probe($probe) : $probe;
    }

    /**
     * Check if the container is ready.
     */
    public function isReady(): bool
    {
        return $this->getAttribute('ready', false);
    }
}
