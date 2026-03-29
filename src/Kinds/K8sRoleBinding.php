<?php

namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;
use RenokiCo\PhpK8s\Contracts\Watchable;
use RenokiCo\PhpK8s\Traits\Resource\HasSubjects;

class K8sRoleBinding extends K8sResource implements InteractsWithK8sCluster, Watchable
{
    use HasSubjects;

    /**
     * The resource Kind parameter.
     */
    protected static ?string $kind = 'RoleBinding';

    /**
     * Wether the resource has a namespace.
     */
    protected static bool $namespaceable = true;

    /**
     * The default version for the resource.
     */
    protected static string $defaultVersion = 'rbac.authorization.k8s.io/v1';

    /**
     * Attach a Role/ClusterRole to the binding.
     *
     * @return $this
     */
    public function setRole(K8sRole $role, string $apiGroup = 'rbac.authorization.k8s.io')
    {
        return $this->setAttribute('roleRef', [
            'apiGroup' => $apiGroup,
            'kind' => $role::getKind(),
            'name' => $role->getName(),
        ]);
    }

    /**
     * Get the roleRef attribute.
     *
     * @return array|null
     */
    public function getRole()
    {
        return $this->getAttribute('roleRef');
    }
}
