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
      { text: 'Guide', link: '/guide/getting-started/installation' },
      { text: 'Resources', link: '/resources/base-resource' },
      { text: 'Development', link: '/development/api-reference/kubernetes-cluster' },
      { text: 'About', link: '/project/history' },
      { text: 'GitHub', link: 'https://github.com/cuppett/php-k8s' }
    ],

    sidebar: {
      '/guide/': [
        {
          text: 'Getting Started',
          items: [
            { text: 'Installation', link: '/guide/getting-started/installation' },
            { text: 'Quick Start', link: '/guide/getting-started/quickstart' },
            { text: 'Authentication', link: '/guide/getting-started/authentication' },
            { text: 'Configuration', link: '/guide/getting-started/configuration' }
          ]
        },
        {
          text: 'Advanced Authentication',
          collapsed: true,
          items: [
            { text: 'Exec Credential Plugin', link: '/guide/authentication/exec-credential' },
            { text: 'AWS EKS', link: '/guide/authentication/eks' },
            { text: 'OpenShift', link: '/guide/authentication/openshift' },
            { text: 'ServiceAccount TokenRequest', link: '/guide/authentication/service-account-token' }
          ]
        },
        {
          text: 'Usage',
          items: [
            { text: 'Cluster Interaction', link: '/guide/usage/cluster-interaction' },
            { text: 'CRUD Operations', link: '/guide/usage/crud-operations' },
            { text: 'Import from YAML', link: '/guide/usage/yaml-import' },
            { text: 'Watching Resources', link: '/guide/usage/watching-resources' },
            { text: 'Exec & Logs', link: '/guide/usage/exec-logs' },
            { text: 'Patching Resources', link: '/guide/usage/patching' },
            { text: 'Server Side Apply', link: '/guide/usage/server-side-apply' },
            { text: 'Scaling', link: '/guide/usage/scaling' },
            { text: 'Custom Resources (CRDs)', link: '/guide/usage/custom-resources' }
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

      '/development/': [
        {
          text: 'API Reference',
          items: [
            { text: 'KubernetesCluster', link: '/development/api-reference/kubernetes-cluster' },
            { text: 'K8sResource', link: '/development/api-reference/k8s-resource' },
            { text: 'K8s Facade', link: '/development/api-reference/k8s-facade' }
          ]
        },
        {
          text: 'Traits',
          collapsed: true,
          items: [
            { text: 'Resource Traits', link: '/development/api-reference/traits/resource-traits' },
            { text: 'Cluster Traits', link: '/development/api-reference/traits/cluster-traits' }
          ]
        },
        {
          text: 'Contracts',
          collapsed: true,
          items: [
            { text: 'Interfaces', link: '/development/api-reference/contracts/interfaces' }
          ]
        },
        {
          text: 'Enums',
          collapsed: true,
          items: [
            { text: 'Enumerations', link: '/development/api-reference/enums/enumerations' }
          ]
        },
        {
          text: 'Instances',
          collapsed: true,
          items: [
            { text: 'Container', link: '/development/api-reference/instances/container' },
            { text: 'Affinity', link: '/development/api-reference/instances/affinity' },
            { text: 'Probe', link: '/development/api-reference/instances/probe' },
            { text: 'Volume', link: '/development/api-reference/instances/volume' }
          ]
        },
        {
          text: 'Patches',
          items: [
            { text: 'JSON Patch', link: '/development/api-reference/patches/json-patch' },
            { text: 'JSON Merge Patch', link: '/development/api-reference/patches/json-merge-patch' },
            { text: 'Server Side Apply', link: '/development/api-reference/patches/server-side-apply' }
          ]
        },
        {
          text: 'Architecture',
          collapsed: true,
          items: [
            { text: 'Resource Model', link: '/development/architecture/resource-model' },
            { text: 'Trait Composition', link: '/development/architecture/trait-composition' },
            { text: 'Cluster Operations', link: '/development/architecture/cluster-operations' },
            { text: 'WebSocket Handling', link: '/development/architecture/websocket-handling' },
            { text: 'Authentication', link: '/development/architecture/authentication' },
            { text: 'State Tracking', link: '/development/architecture/state-tracking' },
            { text: 'Extensibility', link: '/development/architecture/extensibility' }
          ]
        },
        {
          text: 'Contributing',
          collapsed: true,
          items: [
            { text: 'Setup', link: '/development/contributing/setup' },
            { text: 'Testing', link: '/development/contributing/testing' },
            { text: 'Minikube Setup', link: '/development/contributing/minikube-setup' },
            { text: 'Adding Resources', link: '/development/contributing/adding-resources' },
            { text: 'Documentation', link: '/development/contributing/documentation' },
            { text: 'Contributing', link: '/development/contributing/contributing' },
            { text: 'Release Process', link: '/development/contributing/release-process' }
          ]
        },
        {
          text: 'Migration',
          collapsed: true,
          items: [
            { text: 'Upstream to Fork', link: '/development/migration/upstream-to-fork' },
            { text: 'PHP 8.2+ Modernization', link: '/development/migration/php-82-modernization' },
            { text: 'Version Upgrades', link: '/development/migration/version-upgrades' },
            { text: 'Breaking Changes', link: '/development/migration/breaking-changes' }
          ]
        },
        {
          text: 'Integrations',
          collapsed: true,
          items: [
            { text: 'Laravel', link: '/development/integrations/laravel' },
            { text: 'CI/CD', link: '/development/integrations/ci-cd' }
          ]
        },
        {
          text: 'Troubleshooting',
          collapsed: true,
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
          text: 'About',
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
