<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;

class K8sEvent extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    /**
     * The resource Kind parameter.
     */
    protected static ?string $kind = 'Event';

    /**
     * Wether the resource has a namespace.
     */
    protected static bool $namespaceable = true;

    /**
     * Attach the given resource to the event.
     *
     * @return $this
     */
    public function setResource(K8sResource $resource)
    {
        $object = [
            'apiVersion' => $resource->getApiVersion(),
            'kind' => $resource::getKind(),
            'name' => $resource->getName(),
            'namespace' => $resource->getNamespace(),
        ];

        if ($resourceVersion = $resource->getResourceVersion()) {
            $object['resourceVersion'] = $resourceVersion;
        }

        return $this->setAttribute('involvedObject', $object);
    }

    /**
     * Emit or update the event with the given name.
     *
     * @return K8sResource
     *
     * @throws KubernetesAPIException
     */
    public function emitOrUpdate(array $query = ['pretty' => 1])
    {
        return $this->createOrUpdate($query);
    }
}
