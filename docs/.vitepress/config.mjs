import {defineConfig} from 'vitepress'

export default defineConfig({
    title: 'PHP K8s',
    description: 'PHP client for Kubernetes clusters',

    head: [
        ['meta', {name: 'theme-color', content: '#306ce8'}],
        ['meta', {name: 'og:type', content: 'website'}],
        ['meta', {name: 'og:site_name', content: 'PHP K8s'}],
    ],

    // 🔥 核心：多语言配置
    locales: {
        // 英文（默认语言，root标识）
        root: {
            label: 'English',
            lang: 'en-US',
            link: '/en/', // 英文首页入口
            title: 'PHP K8s',
            description: 'PHP client for Kubernetes clusters'
        },
        // 中文
        zh: {
            label: '简体中文',
            lang: 'zh-CN',
            link: '/zh/', // 中文首页入口
            title: 'PHP K8s',
            description: 'Kubernetes 集群的 PHP 客户端'
        }
    },

    // 🔥 多语言主题配置（按语言差异化）
    themeConfig: {
        // 语言切换器（自动显示在导航栏右侧）
        localeLinks: {
            items: [
                {locale: 'root', label: 'English'},
                {locale: 'zh', label: '简体中文'}
            ]
        },

        // 共享配置（所有语言通用）
        socialLinks: [{icon: 'github', link: 'https://github.com/cuppett/php-k8s'}],
        search: {provider: 'local'},
        editLink: {
            pattern: 'https://github.com/cuppett/php-k8s/edit/main/docs/:path',
            text: 'Edit this page on GitHub'
        },
        footer: {
            message: 'Released under the Apache-2.0 License. Originally from <a href="https://github.com/renoki-co/php-k8s">renoki-co/php-k8s</a>.',
            copyright: 'Copyright © 2020-present Alex Renoki, Stephen Cuppett'
        },
        lastUpdated: {
            text: 'Last Updated'
        }
    },

    locales: {
        root: {
            themeConfig: {
                nav: [
                    {text: 'Home', link: '/'},
                    {text: 'Guide', link: '/guide/getting-started/installation'},
                    {text: 'Resources', link: '/resources/base-resource'},
                    {text: 'Development', link: '/development/api-reference/kubernetes-cluster'},
                    {text: 'About', link: '/project/history'},
                    {text: 'GitHub', link: 'https://github.com/cuppett/php-k8s'}
                ],

                sidebar: {
                    '/guide/': [
                        {
                            text: 'Getting Started',
                            items: [
                                {text: 'Installation', link: '/guide/getting-started/installation'},
                                {text: 'Quick Start', link: '/guide/getting-started/quickstart'},
                                {text: 'Authentication', link: '/guide/getting-started/authentication'},
                                {text: 'Configuration', link: '/guide/getting-started/configuration'}
                            ]
                        },
                        {
                            text: 'Advanced Authentication',
                            collapsed: true,
                            items: [
                                {text: 'Exec Credential Plugin', link: '/guide/authentication/exec-credential'},
                                {text: 'AWS EKS', link: '/guide/authentication/eks'},
                                {text: 'OpenShift', link: '/guide/authentication/openshift'},
                                {
                                    text: 'ServiceAccount TokenRequest',
                                    link: '/guide/authentication/service-account-token'
                                }
                            ]
                        },
                        {
                            text: 'Usage',
                            items: [
                                {text: 'Cluster Interaction', link: '/guide/usage/cluster-interaction'},
                                {text: 'CRUD Operations', link: '/guide/usage/crud-operations'},
                                {text: 'Import from YAML', link: '/guide/usage/yaml-import'},
                                {text: 'Watching Resources', link: '/guide/usage/watching-resources'},
                                {text: 'Exec & Logs', link: '/guide/usage/exec-logs'},
                                {text: 'Patching Resources', link: '/guide/usage/patching'},
                                {text: 'Server Side Apply', link: '/guide/usage/server-side-apply'},
                                {text: 'Scaling', link: '/guide/usage/scaling'},
                                {text: 'Custom Resources (CRDs)', link: '/guide/usage/custom-resources'}
                            ]
                        }
                    ],
                    '/resources/': [
                        {
                            text: 'Resources',
                            items: [{text: 'Base Resource', link: '/resources/base-resource'}]
                        },
                        {
                            text: 'Cluster Resources',
                            items: [
                                {text: 'Namespace', link: '/resources/cluster/namespace'},
                                {text: 'Node', link: '/resources/cluster/node'},
                                {text: 'Event', link: '/resources/cluster/event'}
                            ]
                        },
                        {
                            text: 'Workloads',
                            collapsed: true,
                            items: [
                                {text: 'Pod', link: '/resources/workloads/pod'},
                                {text: 'Deployment', link: '/resources/workloads/deployment'},
                                {text: 'StatefulSet', link: '/resources/workloads/statefulset'},
                                {text: 'DaemonSet', link: '/resources/workloads/daemonset'},
                                {text: 'Job', link: '/resources/workloads/job'},
                                {text: 'CronJob', link: '/resources/workloads/cronjob'},
                                {text: 'ReplicaSet', link: '/resources/workloads/replicaset'}
                            ]
                        },
                        {
                            text: 'Configuration',
                            collapsed: true,
                            items: [
                                {text: 'ConfigMap', link: '/resources/configuration/configmap'},
                                {text: 'Secret', link: '/resources/configuration/secret'}
                            ]
                        },
                        {
                            text: 'Storage',
                            collapsed: true,
                            items: [
                                {text: 'PersistentVolume', link: '/resources/storage/persistentvolume'},
                                {text: 'PersistentVolumeClaim', link: '/resources/storage/persistentvolumeclaim'},
                                {text: 'StorageClass', link: '/resources/storage/storageclass'},
                                {text: 'CSIDriver', link: '/resources/storage/csidriver'},
                                {text: 'CSINode', link: '/resources/storage/csinode'},
                                {text: 'VolumeAttributesClass', link: '/resources/storage/volumeattributesclass'}
                            ]
                        },
                        {
                            text: 'Networking',
                            collapsed: true,
                            items: [
                                {text: 'Service', link: '/resources/networking/service'},
                                {text: 'Ingress', link: '/resources/networking/ingress'},
                                {text: 'NetworkPolicy', link: '/resources/networking/networkpolicy'},
                                {text: 'EndpointSlice', link: '/resources/networking/endpointslice'}
                            ]
                        },
                        {
                            text: 'Autoscaling',
                            collapsed: true,
                            items: [
                                {
                                    text: 'HorizontalPodAutoscaler',
                                    link: '/resources/autoscaling/horizontalpodautoscaler'
                                },
                                {text: 'VerticalPodAutoscaler', link: '/resources/autoscaling/verticalpodautoscaler'}
                            ]
                        },
                        {
                            text: 'Policy',
                            collapsed: true,
                            items: [
                                {text: 'ResourceQuota', link: '/resources/policy/resourcequota'},
                                {text: 'LimitRange', link: '/resources/policy/limitrange'},
                                {text: 'PodDisruptionBudget', link: '/resources/policy/poddisruptionbudget'},
                                {text: 'PriorityClass', link: '/resources/policy/priorityclass'}
                            ]
                        },
                        {
                            text: 'RBAC',
                            collapsed: true,
                            items: [
                                {text: 'ServiceAccount', link: '/resources/rbac/serviceaccount'},
                                {text: 'Role', link: '/resources/rbac/role'},
                                {text: 'ClusterRole', link: '/resources/rbac/clusterrole'},
                                {text: 'RoleBinding', link: '/resources/rbac/rolebinding'},
                                {text: 'ClusterRoleBinding', link: '/resources/rbac/clusterrolebinding'}
                            ]
                        },
                        {
                            text: 'Webhooks',
                            collapsed: true,
                            items: [
                                {
                                    text: 'ValidatingWebhookConfiguration',
                                    link: '/resources/webhooks/validatingwebhookconfiguration'
                                },
                                {
                                    text: 'MutatingWebhookConfiguration',
                                    link: '/resources/webhooks/mutatingwebhookconfiguration'
                                }
                            ]
                        }
                    ],
                    '/development/': [
                        {
                            text: 'API Reference',
                            items: [
                                {text: 'KubernetesCluster', link: '/development/api-reference/kubernetes-cluster'},
                                {text: 'K8sResource', link: '/development/api-reference/k8s-resource'},
                                {text: 'K8s Facade', link: '/development/api-reference/k8s-facade'}
                            ]
                        },
                        {
                            text: 'Traits',
                            collapsed: true,
                            items: [
                                {text: 'Resource Traits', link: '/development/api-reference/traits/resource-traits'},
                                {text: 'Cluster Traits', link: '/development/api-reference/traits/cluster-traits'}
                            ]
                        },
                        {
                            text: 'Contracts',
                            collapsed: true,
                            items: [{text: 'Interfaces', link: '/development/api-reference/contracts/interfaces'}]
                        },
                        {
                            text: 'Enums',
                            collapsed: true,
                            items: [{text: 'Enumerations', link: '/development/api-reference/enums/enumerations'}]
                        },
                        {
                            text: 'Instances',
                            collapsed: true,
                            items: [
                                {text: 'Container', link: '/development/api-reference/instances/container'},
                                {text: 'Affinity', link: '/development/api-reference/instances/affinity'},
                                {text: 'Probe', link: '/development/api-reference/instances/probe'},
                                {text: 'Volume', link: '/development/api-reference/instances/volume'}
                            ]
                        },
                        {
                            text: 'Patches',
                            items: [
                                {text: 'JSON Patch', link: '/development/api-reference/patches/json-patch'},
                                {text: 'JSON Merge Patch', link: '/development/api-reference/patches/json-merge-patch'},
                                {
                                    text: 'Server Side Apply',
                                    link: '/development/api-reference/patches/server-side-apply'
                                }
                            ]
                        },
                        {
                            text: 'Architecture',
                            collapsed: true,
                            items: [
                                {text: 'Resource Model', link: '/development/architecture/resource-model'},
                                {text: 'Trait Composition', link: '/development/architecture/trait-composition'},
                                {text: 'Cluster Operations', link: '/development/architecture/cluster-operations'},
                                {text: 'WebSocket Handling', link: '/development/architecture/websocket-handling'},
                                {text: 'Authentication', link: '/development/architecture/authentication'},
                                {text: 'State Tracking', link: '/development/architecture/state-tracking'},
                                {text: 'Extensibility', link: '/development/architecture/extensibility'}
                            ]
                        },
                        {
                            text: 'Contributing',
                            collapsed: true,
                            items: [
                                {text: 'Setup', link: '/development/contributing/setup'},
                                {text: 'Testing', link: '/development/contributing/testing'},
                                {text: 'Minikube Setup', link: '/development/contributing/minikube-setup'},
                                {text: 'Adding Resources', link: '/development/contributing/adding-resources'},
                                {text: 'Documentation', link: '/development/contributing/documentation'},
                                {text: 'Contributing', link: '/development/contributing/contributing'},
                                {text: 'Release Process', link: '/development/contributing/release-process'}
                            ]
                        },
                        {
                            text: 'Migration',
                            collapsed: true,
                            items: [
                                {text: 'Upstream to Fork', link: '/development/migration/upstream-to-fork'},
                                {text: 'PHP 8.2+ Modernization', link: '/development/migration/php-82-modernization'},
                                {text: 'Version Upgrades', link: '/development/migration/version-upgrades'},
                                {text: 'Breaking Changes', link: '/development/migration/breaking-changes'}
                            ]
                        },
                        {
                            text: 'Integrations',
                            collapsed: true,
                            items: [
                                {text: 'Laravel', link: '/development/integrations/laravel'},
                                {text: 'CI/CD', link: '/development/integrations/ci-cd'}
                            ]
                        },
                        {
                            text: 'Troubleshooting',
                            collapsed: true,
                            items: [
                                {text: 'Common Errors', link: '/troubleshooting/common-errors'},
                                {text: 'Authentication Issues', link: '/troubleshooting/authentication-issues'},
                                {text: 'Connection Problems', link: '/troubleshooting/connection-problems'},
                                {text: 'Debugging', link: '/troubleshooting/debugging'}
                            ]
                        }
                    ],
                    '/project/': [
                        {
                            text: 'About',
                            items: [
                                {text: 'History', link: '/project/history'},
                                {text: 'Fork Differences', link: '/project/fork-differences'},
                                {text: 'Upstream Attribution', link: '/project/upstream-attribution'},
                                {text: 'Roadmap', link: '/project/roadmap'},
                                {text: 'Changelog', link: '/project/changelog'},
                                {text: 'License', link: '/project/license'}
                            ]
                        }
                    ]
                },
                editLink: {
                    pattern: 'https://github.com/cuppett/php-k8s/edit/main/docs/en/:path',
                    text: 'Edit this page on GitHub'
                },
                lastUpdated: {
                    text: 'Last Updated'
                }
            }
        },

        // 中文配置（导航/侧边栏文本汉化）
        zh: {
            themeConfig: {
                nav: [
                    {text: '首页', link: '/zh/'},
                    {text: '指南', link: '/zh/guide/getting-started/installation'},
                    {text: '资源', link: '/zh/resources/base-resource'},
                    {text: '开发', link: '/zh/development/api-reference/kubernetes-cluster'},
                    {text: '关于', link: '/zh/project/history'},
                    {text: 'GitHub', link: 'https://github.com/cuppett/php-k8s'}
                ],
                sidebar: {
                    '/zh/guide/': [
                        {
                            text: '快速入门',
                            items: [
                                {text: '安装', link: '/zh/guide/getting-started/installation'},
                                {text: '快速开始', link: '/zh/guide/getting-started/quickstart'},
                                {text: '认证', link: '/zh/guide/getting-started/authentication'},
                                {text: '配置', link: '/zh/guide/getting-started/configuration'}
                            ]
                        },
                        {
                            text: '高级认证',
                            collapsed: true,
                            items: [
                                {text: 'Exec 凭证插件', link: '/zh/guide/authentication/exec-credential'},
                                {text: 'AWS EKS', link: '/zh/guide/authentication/eks'},
                                {text: 'OpenShift', link: '/zh/guide/authentication/openshift'},
                                {
                                    text: 'ServiceAccount TokenRequest',
                                    link: '/zh/guide/authentication/service-account-token'
                                }
                            ]
                        },
                        {
                            text: '使用方法',
                            items: [
                                {text: '集群交互', link: '/zh/guide/usage/cluster-interaction'},
                                {text: 'CRUD 操作', link: '/zh/guide/usage/crud-operations'},
                                {text: '从 YAML 导入', link: '/zh/guide/usage/yaml-import'},
                                {text: '监听资源', link: '/zh/guide/usage/watching-resources'},
                                {text: '执行 & 日志', link: '/zh/guide/usage/exec-logs'},
                                {text: '补丁更新资源', link: '/zh/guide/usage/patching'},
                                {text: '服务端应用', link: '/zh/guide/usage/server-side-apply'},
                                {text: '扩缩容', link: '/zh/guide/usage/scaling'},
                                {text: '自定义资源 (CRDs)', link: '/zh/guide/usage/custom-resources'}
                            ]
                        }
                    ],
                    '/zh/resources/': [
                        {
                            text: '资源',
                            items: [{text: '基础资源', link: '/zh/resources/base-resource'}]
                        },
                        {
                            text: '集群资源',
                            items: [
                                {text: '命名空间', link: '/zh/resources/cluster/namespace'},
                                {text: '节点', link: '/zh/resources/cluster/node'},
                                {text: '事件', link: '/zh/resources/cluster/event'}
                            ]
                        },
                        {
                            text: '工作负载',
                            collapsed: true,
                            items: [
                                {text: 'Pod', link: '/zh/resources/workloads/pod'},
                                {text: 'Deployment', link: '/zh/resources/workloads/deployment'},
                                {text: 'StatefulSet', link: '/zh/resources/workloads/statefulset'},
                                {text: 'DaemonSet', link: '/zh/resources/workloads/daemonset'},
                                {text: 'Job', link: '/zh/resources/workloads/job'},
                                {text: 'CronJob', link: '/zh/resources/workloads/cronjob'},
                                {text: 'ReplicaSet', link: '/zh/resources/workloads/replicaset'}
                            ]
                        },
                        {
                            text: '配置',
                            collapsed: true,
                            items: [
                                {text: '配置映射', link: '/zh/resources/configuration/configmap'},
                                {text: '密钥', link: '/zh/resources/configuration/secret'}
                            ]
                        },
                        {
                            text: '存储',
                            collapsed: true,
                            items: [
                                {text: '持久化卷', link: '/zh/resources/storage/persistentvolume'},
                                {text: '持久化卷声明', link: '/zh/resources/storage/persistentvolumeclaim'},
                                {text: '存储类', link: '/zh/resources/storage/storageclass'},
                                {text: 'CSIDriver', link: '/zh/resources/storage/csidriver'},
                                {text: 'CSINode', link: '/zh/resources/storage/csinode'},
                                {text: '卷属性类', link: '/zh/resources/storage/volumeattributesclass'}
                            ]
                        },
                        {
                            text: '网络',
                            collapsed: true,
                            items: [
                                {text: '服务', link: '/zh/resources/networking/service'},
                                {text: 'Ingress', link: '/zh/resources/networking/ingress'},
                                {text: '网络策略', link: '/zh/resources/networking/networkpolicy'},
                                {text: '端点切片', link: '/zh/resources/networking/endpointslice'}
                            ]
                        },
                        {
                            text: '自动扩缩容',
                            collapsed: true,
                            items: [
                                {text: '水平 Pod 自动扩缩', link: '/zh/resources/autoscaling/horizontalpodautoscaler'},
                                {text: '垂直 Pod 自动扩缩', link: '/zh/resources/autoscaling/verticalpodautoscaler'}
                            ]
                        },
                        {
                            text: '策略',
                            collapsed: true,
                            items: [
                                {text: '资源配额', link: '/zh/resources/policy/resourcequota'},
                                {text: '限制范围', link: '/zh/resources/policy/limitrange'},
                                {text: 'Pod 中断预算', link: '/zh/resources/policy/poddisruptionbudget'},
                                {text: '优先级类', link: '/zh/resources/policy/priorityclass'}
                            ]
                        },
                        {
                            text: 'RBAC',
                            collapsed: true,
                            items: [
                                {text: '服务账户', link: '/zh/resources/rbac/serviceaccount'},
                                {text: '角色', link: '/zh/resources/rbac/role'},
                                {text: '集群角色', link: '/zh/resources/rbac/clusterrole'},
                                {text: '角色绑定', link: '/zh/resources/rbac/rolebinding'},
                                {text: '集群角色绑定', link: '/zh/resources/rbac/clusterrolebinding'}
                            ]
                        },
                        {
                            text: 'Webhooks',
                            collapsed: true,
                            items: [
                                {
                                    text: '验证 Webhook 配置',
                                    link: '/zh/resources/webhooks/validatingwebhookconfiguration'
                                },
                                {text: '变异 Webhook 配置', link: '/zh/resources/webhooks/mutatingwebhookconfiguration'}
                            ]
                        }
                    ],
                    '/zh/development/': [
                        {
                            text: 'API 参考',
                            items: [
                                {text: 'KubernetesCluster', link: '/zh/development/api-reference/kubernetes-cluster'},
                                {text: 'K8sResource', link: '/zh/development/api-reference/k8s-resource'},
                                {text: 'K8s 外观模式', link: '/zh/development/api-reference/k8s-facade'}
                            ]
                        },
                        {
                            text: '特性',
                            collapsed: true,
                            items: [
                                {text: '资源特性', link: '/zh/development/api-reference/traits/resource-traits'},
                                {text: '集群特性', link: '/zh/development/api-reference/traits/cluster-traits'}
                            ]
                        },
                        {
                            text: '契约',
                            collapsed: true,
                            items: [{text: '接口', link: '/zh/development/api-reference/contracts/interfaces'}]
                        },
                        {
                            text: '枚举',
                            collapsed: true,
                            items: [{text: '枚举类型', link: '/zh/development/api-reference/enums/enumerations'}]
                        },
                        {
                            text: '实例',
                            collapsed: true,
                            items: [
                                {text: '容器', link: '/zh/development/api-reference/instances/container'},
                                {text: '亲和性', link: '/zh/development/api-reference/instances/affinity'},
                                {text: '探针', link: '/zh/development/api-reference/instances/probe'},
                                {text: '卷', link: '/zh/development/api-reference/instances/volume'}
                            ]
                        },
                        {
                            text: '补丁',
                            items: [
                                {text: 'JSON 补丁', link: '/zh/development/api-reference/patches/json-patch'},
                                {text: 'JSON 合并补丁', link: '/zh/development/api-reference/patches/json-merge-patch'},
                                {text: '服务端应用', link: '/zh/development/api-reference/patches/server-side-apply'}
                            ]
                        },
                        {
                            text: '架构',
                            collapsed: true,
                            items: [
                                {text: '资源模型', link: '/zh/development/architecture/resource-model'},
                                {text: '特性组合', link: '/zh/development/architecture/trait-composition'},
                                {text: '集群操作', link: '/zh/development/architecture/cluster-operations'},
                                {text: 'WebSocket 处理', link: '/zh/development/architecture/websocket-handling'},
                                {text: '认证', link: '/zh/development/architecture/authentication'},
                                {text: '状态跟踪', link: '/zh/development/architecture/state-tracking'},
                                {text: '可扩展性', link: '/zh/development/architecture/extensibility'}
                            ]
                        },
                        {
                            text: '贡献指南',
                            collapsed: true,
                            items: [
                                {text: '环境搭建', link: '/zh/development/contributing/setup'},
                                {text: '测试', link: '/zh/development/contributing/testing'},
                                {text: 'Minikube 搭建', link: '/zh/development/contributing/minikube-setup'},
                                {text: '添加资源', link: '/zh/development/contributing/adding-resources'},
                                {text: '文档', link: '/zh/development/contributing/documentation'},
                                {text: '贡献流程', link: '/zh/development/contributing/contributing'},
                                {text: '发布流程', link: '/zh/development/contributing/release-process'}
                            ]
                        },
                        {
                            text: '迁移',
                            collapsed: true,
                            items: [
                                {text: '上游到分支', link: '/zh/development/migration/upstream-to-fork'},
                                {text: 'PHP 8.2+ 现代化', link: '/zh/development/migration/php-82-modernization'},
                                {text: '版本升级', link: '/zh/development/migration/version-upgrades'},
                                {text: '破坏性变更', link: '/zh/development/migration/breaking-changes'}
                            ]
                        },
                        {
                            text: '集成',
                            collapsed: true,
                            items: [
                                {text: 'Laravel', link: '/zh/development/integrations/laravel'},
                                {text: 'CI/CD', link: '/zh/development/integrations/ci-cd'}
                            ]
                        },
                        {
                            text: '故障排除',
                            collapsed: true,
                            items: [
                                {text: '常见错误', link: '/zh/troubleshooting/common-errors'},
                                {text: '认证问题', link: '/zh/troubleshooting/authentication-issues'},
                                {text: '连接问题', link: '/zh/troubleshooting/connection-problems'},
                                {text: '调试', link: '/zh/troubleshooting/debugging'}
                            ]
                        }
                    ],
                    '/zh/project/': [
                        {
                            text: '关于',
                            items: [
                                {text: '历史', link: '/zh/project/history'},
                                {text: '分支差异', link: '/zh/project/fork-differences'},
                                {text: '上游归属', link: '/zh/project/upstream-attribution'},
                                {text: '路线图', link: '/zh/project/roadmap'},
                                {text: '更新日志', link: '/zh/project/changelog'},
                                {text: '许可证', link: '/zh/project/license'}
                            ]
                        }
                    ]
                },
                editLink: {
                    pattern: 'https://github.com/cuppett/php-k8s/edit/main/docs/zh/:path',
                    text: '在 GitHub 上编辑此页面'
                },
                lastUpdated: {
                    text: '最后更新时间'
                }
            }
        }
    },

    // 原有通用配置保留
    markdown: {
        theme: {light: 'github-light', dark: 'github-dark'},
        lineNumbers: true,
        config: (md) => {
            // Custom markdown plugins can be added here
        }
    },
    cleanUrls: true,
    lastUpdated: true,
    sitemap: {hostname: 'https://php-k8s.cuppett.dev'},
    ignoreDeadLinks: true
})
