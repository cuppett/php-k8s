<?php

namespace RenokiCo\PhpK8s\Kinds;

class K8sClusterRoleBinding extends K8sRoleBinding
{
    /**
     * The resource Kind parameter.
     */
    protected static ?string $kind = 'ClusterRoleBinding';

    /**
     * Wether the resource has a namespace.
     */
    protected static bool $namespaceable = false;
}
