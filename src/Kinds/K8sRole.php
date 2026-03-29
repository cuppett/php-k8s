<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasRules;

class K8sRole extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasRules;

    /**
     * The resource Kind parameter.
     */
    protected static ?string $kind = 'Role';

    /**
     * Wether the resource has a namespace.
     */
    protected static bool $namespaceable = true;

    /**
     * The default version for the resource.
     */
    protected static string $defaultVersion = 'rbac.authorization.k8s.io/v1';
}
