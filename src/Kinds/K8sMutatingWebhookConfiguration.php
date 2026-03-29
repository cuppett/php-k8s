<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasWebhooks;

class K8sMutatingWebhookConfiguration extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasWebhooks;

    /**
     * The resource Kind parameter.
     */
    protected static ?string $kind = 'MutatingWebhookConfiguration';

    /**
     * The default version for the resource.
     */
    protected static string $defaultVersion = 'admissionregistration.k8s.io/v1';

    /**
     * Wether the resource has a namespace.
     */
    protected static bool $namespaceable = false;
}
