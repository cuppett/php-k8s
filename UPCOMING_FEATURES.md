# Upcoming Features

This document tracks planned Kubernetes resource implementations for future releases of php-k8s.

## Current Status

**Implemented:** 36 core Kubernetes resources
**Planned:** 9 additional stable resources

## Recently Implemented (December 2025)

### Storage & CSI Resources ✅

All storage and CSI resources are now implemented:

**Core API Resources** (in `src/Kinds/`):
- ✅ **CSIDriver** (`storage.k8s.io/v1`) - CSI driver information and capabilities
- ✅ **CSINode** (`storage.k8s.io/v1`) - CSI drivers installed on nodes
- ✅ **VolumeAttributesClass** (`storage.k8s.io/v1`) - Mutable volume attributes (K8s 1.34+)

**CRD Examples** (in `tests/Kinds/` - reference implementations):
- ✅ **VolumeSnapshot** (`snapshot.storage.k8s.io/v1`) - Point-in-time volume snapshots
- ✅ **VolumeSnapshotClass** (`snapshot.storage.k8s.io/v1`) - Snapshot class definitions
- ✅ **VolumeSnapshotContent** (`snapshot.storage.k8s.io/v1`) - Snapshot content bindings

**Benefits:**
- Backup & restore workflows with volume snapshots
- CSI driver discovery and management
- Dynamic volume attribute modification (IOPS, throughput)
- Production-ready storage operations

## Priority 1: Missing Core Resources

Widely used resources surprisingly absent from the library.

### Endpoints (`v1`)
- **Status:** GA (Stable)
- **Namespace:** Yes
- **Purpose:** Service endpoint information (predecessor to EndpointSlice)
- **Use Case:** Service discovery, load balancing
- **Note:** Library has EndpointSlice but not Endpoints (still widely used)

## Priority 2: Dynamic Resource Allocation (Kubernetes 1.34)

Enable efficient GPU and specialized hardware management for AI/ML workloads.

### ResourceClaim (`resource.k8s.io/v1`)
- **Status:** GA in Kubernetes 1.34 (August 2025)
- **Namespace:** Yes
- **Purpose:** Claim dynamically allocated resources (GPUs, FPGAs, specialized hardware)
- **Use Case:** Request GPUs for AI/ML pods, manage specialized hardware
- **Impact:** Critical for AI/ML workloads, 20-35% cost reduction via sharing

### DeviceClass (`resource.k8s.io/v1`)
- **Status:** GA in Kubernetes 1.34
- **Namespace:** No (cluster-scoped)
- **Purpose:** Define classes of devices (like StorageClass for storage)
- **Use Case:** Categorize GPU types, define device selection criteria

### ResourceSlice (`resource.k8s.io/v1`)
- **Status:** GA in Kubernetes 1.34
- **Namespace:** No (cluster-scoped)
- **Purpose:** Describes available resources on nodes for DRA
- **Use Case:** Resource inventory, capacity planning

### ResourceClaimTemplate (`resource.k8s.io/v1`)
- **Status:** GA in Kubernetes 1.34
- **Namespace:** Yes
- **Purpose:** Templates for creating ResourceClaims (like PVC templates in StatefulSets)
- **Use Case:** Automate resource claim creation for workloads

## Priority 3: Coordination & Runtime

Important for building Kubernetes operators and controllers.

### Lease (`coordination.k8s.io/v1`)
- **Status:** GA (Stable)
- **Namespace:** Yes
- **Purpose:** Distributed locks for leader election and node heartbeats
- **Use Case:** Kubernetes operators, controller leader election, cluster coordination

### RuntimeClass (`node.k8s.io/v1`)
- **Status:** GA (Stable)
- **Namespace:** No (cluster-scoped)
- **Purpose:** Select container runtime configuration (gVisor, Kata Containers, etc.)
- **Use Case:** Security isolation, specialized runtime requirements

## Priority 4: API Extensions

Advanced use cases for platform builders.

### CustomResourceDefinition (`apiextensions.k8s.io/v1`)
- **Status:** GA (Stable)
- **Namespace:** No (cluster-scoped)
- **Purpose:** Define custom resources programmatically
- **Use Case:** Create CRDs via API, validate CRD definitions
- **Note:** Library already supports CRDs via macro system, this adds native resource support

### APIService (`apiregistration.k8s.io/v1`)
- **Status:** GA (Stable)
- **Namespace:** No (cluster-scoped)
- **Purpose:** Represents aggregated API servers
- **Use Case:** API aggregation layer, custom API servers

## Implementation Timeline

These features will be implemented based on:

1. **User demand** - Which features are most requested
2. **Kubernetes adoption** - How widely the features are used in production
3. **Stability** - Preference for GA (stable) features over beta/alpha
4. **Dependencies** - Some features build on others

Current priorities based on production use:

1. **Missing core resources** - Endpoints for backward compatibility
2. **DRA resources** - For AI/ML workloads (requires K8s 1.34+)
3. **Coordination resources** - For operator development
4. **API extensions** - For platform builders

## Benefits of Planned Resources

### DRA Resources
- **GPU Sharing:** Multiple pods sharing same GPU (cost savings)
- **Resource Efficiency:** Better utilization of specialized hardware
- **AI/ML Support:** First-class support for GPU workloads

### Coordination Resources
- **Operator Development:** Build robust controllers with leader election
- **High Availability:** Distributed lock management

### Missing Core Resources
- **Feature Completeness:** Fill gaps in API coverage
- **Backward Compatibility:** Support legacy resources still in use

### API Extensions
- **Platform Building:** Programmatic CRD management
- **API Aggregation:** Custom API server integration

## How to Request Features

If you need one of these resources implemented sooner:

1. **Open an Issue:** Describe your use case on GitHub
2. **Contribute:** Submit a PR following the patterns in existing resources
3. **Sponsor:** Prioritize development through GitHub Sponsors

## Stay Updated

Watch the repository for updates on feature implementation:

- [GitHub Issues](https://github.com/cuppett/php-k8s/issues)
- [GitHub Releases](https://github.com/cuppett/php-k8s/releases)
- [Documentation](https://php-k8s.cuppett.dev)

## Resources

- [Kubernetes API Reference](https://kubernetes.io/docs/reference/kubernetes-api/)
- [Kubernetes 1.34 Release Notes](https://kubernetes.io/blog/2025/08/27/kubernetes-v1-34-release/)
- [Dynamic Resource Allocation](https://kubernetes.io/docs/concepts/scheduling-eviction/dynamic-resource-allocation/)
- [Volume Snapshots](https://kubernetes.io/docs/concepts/storage/volume-snapshots/)
