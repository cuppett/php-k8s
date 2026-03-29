<?php

namespace RenokiCo\PhpK8s\Test\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Traits\Resource\HasSelector;
use RenokiCo\PhpK8s\Traits\Resource\HasSpec;
use RenokiCo\PhpK8s\Traits\Resource\HasStatus;
use RenokiCo\PhpK8s\Traits\Resource\HasStatusConditions;
use RenokiCo\PhpK8s\Traits\Resource\HasTemplate;

class SealedSecret extends K8sResource implements InteractsWithK8sCluster
{
    use HasSelector;
    use HasSpec;
    use HasStatus;
    use HasStatusConditions;
    use HasTemplate;

    /**
     * The resource Kind parameter.
     */
    protected static ?string $kind = 'SealedSecret';

    /**
     * The default version for the resource.
     */
    protected static string $defaultVersion = 'bitnami.com/v1alpha1';

    /**
     * Wether the resource has a namespace.
     */
    protected static bool $namespaceable = true;
}
