import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: 'PHP K8s',
  description: 'PHP client for Kubernetes clusters',

  head: [
    ['meta', { name: 'theme-color', content: '#306ce8' }],
    ['meta', { name: 'og:type', content: 'website' }],
    ['meta', { name: 'og:site_name', content: 'PHP K8s' }],
  ],

  themeConfig: {
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Guide', link: '/getting-started/installation' },
      { text: 'Resources', link: '/resources/base-resource' },
      { text: 'API Reference', link: '/api-reference/kubernetes-cluster' },
      { text: 'Examples', link: '/examples/basic-crud' },
      { text: 'GitHub', link: 'https://github.com/cuppett/php-k8s' }
    ],

    sidebar: {
      '/getting-started/': [
        {
          text: 'Getting Started',
          items: [
            { text: 'Installation', link: '/getting-started/installation' },
            { text: 'Quick Start', link: '/getting-started/quickstart' },
            { text: 'Authentication', link: '/getting-started/authentication' },
            { text: 'Configuration', link: '/getting-started/configuration' }
          ]
        },
        {
          text: 'Advanced Authentication',
          collapsed: true,
          items: [
            { text: 'Exec Credential Plugin', link: '/authentication/exec-credential' },
            { text: 'AWS EKS', link: '/authentication/eks' },
            { text: 'OpenShift', link: '/authentication/openshift' },
            { text: 'ServiceAccount TokenRequest', link: '/authentication/service-account-token' }
          ]
        }
      ],

      '/guide/': [
        {
          text: 'User Guide',
          items: [
            { text: 'Cluster Interaction', link: '/guide/cluster-interaction' },
            { text: 'CRUD Operations', link: '/guide/crud-operations' },
            { text: 'Import from YAML', link: '/guide/yaml-import' },
            { text: 'Watching Resources', link: '/guide/watching-resources' },
            { text: 'Exec & Logs', link: '/guide/exec-logs' },
            { text: 'Patching Resources', link: '/guide/patching' },
            { text: 'Server Side Apply', link: '/guide/server-side-apply' },
            { text: 'Scaling', link: '/guide/scaling' },
            { text: 'Custom Resources (CRDs)', link: '/guide/custom-resources' }
          ]
        }
      ],

      '/resources/': [
        {
          text: 'Resources',
          items: [
            { text: 'Base Resource', link: '/resources/base-resource' }
          ]
        },
        {
          text: 'Cluster Resources',
          items: [
            { text: 'Namespace', link: '/resources/cluster/namespace' },
            { text: 'Node', link: '/resources/cluster/node' },
            { text: 'Event', link: '/resources/cluster/event' }
          ]
        },
        {
          text: 'Workloads',
          collapsed: true,
          items: [
            { text: 'Pod', link: '/resources/workloads/pod' },
            { text: 'Deployment', link: '/resources/workloads/deployment' },
            { text: 'StatefulSet', link: '/resources/workloads/statefulset' },
            { text: 'DaemonSet', link: '/resources/workloads/daemonset' },
            { text: 'Job', link: '/resources/workloads/job' },
            { text: 'CronJob', link: '/resources/workloads/cronjob' },
            { text: 'ReplicaSet', link: '/resources/workloads/replicaset' }
          ]
        },
        {
          text: 'Configuration',
          collapsed: true,
          items: [
            { text: 'ConfigMap', link: '/resources/configuration/configmap' },
            { text: 'Secret', link: '/resources/configuration/secret' }
          ]
        },
        {
          text: 'Storage',
          collapsed: true,
          items: [
            { text: 'PersistentVolume', link: '/resources/storage/persistentvolume' },
            { text: 'PersistentVolumeClaim', link: '/resources/storage/persistentvolumeclaim' },
            { text: 'StorageClass', link: '/resources/storage/storageclass' },
            { text: 'CSIDriver', link: '/resources/storage/csidriver' },
            { text: 'CSINode', link: '/resources/storage/csinode' },
            { text: 'VolumeAttributesClass', link: '/resources/storage/volumeattributesclass' }
          ]
        },
        {
          text: 'Networking',
          collapsed: true,
          items: [
            { text: 'Service', link: '/resources/networking/service' },
            { text: 'Ingress', link: '/resources/networking/ingress' },
            { text: 'NetworkPolicy', link: '/resources/networking/networkpolicy' },
            { text: 'EndpointSlice', link: '/resources/networking/endpointslice' }
          ]
        },
        {
          text: 'Autoscaling',
          collapsed: true,
          items: [
            { text: 'HorizontalPodAutoscaler', link: '/resources/autoscaling/horizontalpodautoscaler' },
            { text: 'VerticalPodAutoscaler', link: '/resources/autoscaling/verticalpodautoscaler' }
          ]
        },
        {
          text: 'Policy',
          collapsed: true,
          items: [
            { text: 'ResourceQuota', link: '/resources/policy/resourcequota' },
            { text: 'LimitRange', link: '/resources/policy/limitrange' },
            { text: 'PodDisruptionBudget', link: '/resources/policy/poddisruptionbudget' },
            { text: 'PriorityClass', link: '/resources/policy/priorityclass' }
          ]
        },
        {
          text: 'RBAC',
          collapsed: true,
          items: [
            { text: 'ServiceAccount', link: '/resources/rbac/serviceaccount' },
            { text: 'Role', link: '/resources/rbac/role' },
            { text: 'ClusterRole', link: '/resources/rbac/clusterrole' },
            { text: 'RoleBinding', link: '/resources/rbac/rolebinding' },
            { text: 'ClusterRoleBinding', link: '/resources/rbac/clusterrolebinding' }
          ]
        },
        {
          text: 'Webhooks',
          collapsed: true,
          items: [
            { text: 'ValidatingWebhookConfiguration', link: '/resources/webhooks/validatingwebhookconfiguration' },
            { text: 'MutatingWebhookConfiguration', link: '/resources/webhooks/mutatingwebhookconfiguration' }
          ]
        }
      ],

      '/api-reference/': [
        {
          text: 'Core Classes',
          items: [
            { text: 'KubernetesCluster', link: '/api-reference/kubernetes-cluster' },
            { text: 'K8sResource', link: '/api-reference/k8s-resource' },
            { text: 'K8s Facade', link: '/api-reference/k8s-facade' }
          ]
        },
        {
          text: 'Traits',
          collapsed: true,
          items: [
            { text: 'Resource Traits', link: '/api-reference/traits/resource-traits' },
            { text: 'Cluster Traits', link: '/api-reference/traits/cluster-traits' }
          ]
        },
        {
          text: 'Contracts',
          collapsed: true,
          items: [
            { text: 'Interfaces', link: '/api-reference/contracts/interfaces' }
          ]
        },
        {
          text: 'Enums',
          collapsed: true,
          items: [
            { text: 'Enumerations', link: '/api-reference/enums/enumerations' }
          ]
        },
        {
          text: 'Instances',
          collapsed: true,
          items: [
            { text: 'Container', link: '/api-reference/instances/container' },
            { text: 'Affinity', link: '/api-reference/instances/affinity' },
            { text: 'Probe', link: '/api-reference/instances/probe' },
            { text: 'Volume', link: '/api-reference/instances/volume' }
          ]
        },
        {
          text: 'Patches',
          items: [
            { text: 'JSON Patch', link: '/api-reference/patches/json-patch' },
            { text: 'JSON Merge Patch', link: '/api-reference/patches/json-merge-patch' },
            { text: 'Server Side Apply', link: '/api-reference/patches/server-side-apply' }
          ]
        }
      ],

      '/examples/': [
        {
          text: 'Examples',
          items: [
            { text: 'Basic CRUD', link: '/examples/basic-crud' },
            { text: 'Deployment Management', link: '/examples/deployment-management' },
            { text: 'Pod Operations', link: '/examples/pod-operations' },
            { text: 'ConfigMap & Secrets', link: '/examples/configmap-secrets' },
            { text: 'Autoscaling', link: '/examples/autoscaling' },
            { text: 'RBAC Setup', link: '/examples/rbac-setup' },
            { text: 'Storage Management', link: '/examples/storage-management' },
            { text: 'Networking', link: '/examples/networking' },
            { text: 'Patching', link: '/examples/patching' },
            { text: 'Custom Resources', link: '/examples/custom-resources' },
            { text: 'Advanced Patterns', link: '/examples/advanced-patterns' }
          ]
        }
      ],

      '/architecture/': [
        {
          text: 'Architecture',
          items: [
            { text: 'Resource Model', link: '/architecture/resource-model' },
            { text: 'Trait Composition', link: '/architecture/trait-composition' },
            { text: 'Cluster Operations', link: '/architecture/cluster-operations' },
            { text: 'WebSocket Handling', link: '/architecture/websocket-handling' },
            { text: 'Authentication', link: '/architecture/authentication' },
            { text: 'State Tracking', link: '/architecture/state-tracking' },
            { text: 'Extensibility', link: '/architecture/extensibility' }
          ]
        }
      ],

      '/migration/': [
        {
          text: 'Migration Guides',
          items: [
            { text: 'Upstream to Fork', link: '/migration/upstream-to-fork' },
            { text: 'PHP 8.2+ Modernization', link: '/migration/php-82-modernization' },
            { text: 'Version Upgrades', link: '/migration/version-upgrades' },
            { text: 'Breaking Changes', link: '/migration/breaking-changes' }
          ]
        }
      ],

      '/development/': [
        {
          text: 'Development',
          items: [
            { text: 'Setup', link: '/development/setup' },
            { text: 'Testing', link: '/development/testing' },
            { text: 'Minikube Setup', link: '/development/minikube-setup' },
            { text: 'Adding Resources', link: '/development/adding-resources' },
            { text: 'Documentation', link: '/development/documentation' },
            { text: 'Contributing', link: '/development/contributing' },
            { text: 'Release Process', link: '/development/release-process' }
          ]
        }
      ],

      '/integrations/': [
        {
          text: 'Integrations',
          items: [
            { text: 'Laravel', link: '/integrations/laravel' },
            { text: 'CI/CD', link: '/integrations/ci-cd' }
          ]
        }
      ],

      '/authentication/': [
        {
          text: 'Authentication',
          items: [
            { text: 'Basic Authentication', link: '/getting-started/authentication' },
            { text: 'Exec Credential Plugin', link: '/authentication/exec-credential' },
            { text: 'AWS EKS', link: '/authentication/eks' },
            { text: 'OpenShift', link: '/authentication/openshift' },
            { text: 'ServiceAccount TokenRequest', link: '/authentication/service-account-token' }
          ]
        }
      ],

      '/troubleshooting/': [
        {
          text: 'Troubleshooting',
          items: [
            { text: 'Common Errors', link: '/troubleshooting/common-errors' },
            { text: 'Authentication Issues', link: '/troubleshooting/authentication-issues' },
            { text: 'Connection Problems', link: '/troubleshooting/connection-problems' },
            { text: 'Debugging', link: '/troubleshooting/debugging' }
          ]
        }
      ],

      '/project/': [
        {
          text: 'Project Information',
          items: [
            { text: 'History', link: '/project/history' },
            { text: 'Fork Differences', link: '/project/fork-differences' },
            { text: 'Upstream Attribution', link: '/project/upstream-attribution' },
            { text: 'Roadmap', link: '/project/roadmap' },
            { text: 'Changelog', link: '/project/changelog' },
            { text: 'License', link: '/project/license' }
          ]
        }
      ]
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/cuppett/php-k8s' }
    ],

    search: {
      provider: 'local'
    },

    editLink: {
      pattern: 'https://github.com/cuppett/php-k8s/edit/main/docs/:path',
      text: 'Edit this page on GitHub'
    },

    footer: {
      message: 'Released under the Apache-2.0 License. Originally from <a href="https://github.com/renoki-co/php-k8s">renoki-co/php-k8s</a>.',
      copyright: 'Copyright © 2020-present Alex Renoki, Stephen Cuppett'
    }
  },

  markdown: {
    theme: {
      light: 'github-light',
      dark: 'github-dark'
    },
    lineNumbers: true,
    config: (md) => {
      // Custom markdown plugins can be added here
    }
  },

  cleanUrls: true,

  lastUpdated: true,

  sitemap: {
    hostname: 'https://php-k8s.cuppett.dev'
  },

  ignoreDeadLinks: true
})
