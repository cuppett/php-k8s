<?php

namespace RenokiCo\PhpK8s\Kinds;

class K8sClusterRole extends K8sRole
{
    /**
     * The resource Kind parameter.
     */
    protected static ?string $kind = 'ClusterRole';

    /**
     * Wether the resource has a namespace.
     */
    protected static bool $namespaceable = false;
}
