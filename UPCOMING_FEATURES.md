# Upcoming Features

This document tracks planned Kubernetes resource implementations for future releases of php-k8s.

## Current Status

**Implemented:** 33 core Kubernetes resources
**Planned:** 15+ additional stable resources

## Priority 1: Storage & CSI Resources

Essential for production backup/restore workflows.

### VolumeSnapshot (`snapshot.storage.k8s.io/v1`)
- **Status:** GA (Stable)
- **Namespace:** Yes
- **Purpose:** Create point-in-time snapshots of persistent volumes
- **Use Case:** Backup, disaster recovery, cloning volumes
- **Note:** Currently exists in `tests/Kinds/` as CRD example, should be promoted to core

### VolumeSnapshotClass (`snapshot.storage.k8s.io/v1`)
- **Status:** GA (Stable)
- **Namespace:** No (cluster-scoped)
- **Purpose:** Define how volume snapshots are created (like StorageClass for volumes)
- **Use Case:** Configure snapshot parameters, driver selection

### VolumeSnapshotContent (`snapshot.storage.k8s.io/v1`)
- **Status:** GA (Stable)
- **Namespace:** No (cluster-scoped)
- **Purpose:** Actual snapshot content (relationship: VolumeSnapshot → VolumeSnapshotContent, like PVC → PV)
- **Use Case:** Low-level snapshot management

### CSIDriver (`storage.k8s.io/v1`)
- **Status:** GA (Stable)
- **Namespace:** No (cluster-scoped)
- **Purpose:** Information about Container Storage Interface drivers in the cluster
- **Use Case:** Discover available storage drivers, driver capabilities

### CSINode (`storage.k8s.io/v1`)
- **Status:** GA (Stable)
- **Namespace:** No (cluster-scoped)
- **Purpose:** Information about CSI drivers installed on specific nodes
- **Use Case:** Node-level storage driver tracking

### VolumeAttributesClass (`storage.k8s.io/v1`)
- **Status:** GA in Kubernetes 1.34
- **Namespace:** No (cluster-scoped)
- **Purpose:** Mutable volume attributes for CSI volumes
- **Use Case:** Modify volume parameters without recreating (IOPS, throughput, etc.)

## Priority 2: Missing Core Resources

Widely used resources surprisingly absent from the library.

### Endpoints (`v1`)
- **Status:** GA (Stable)
- **Namespace:** Yes
- **Purpose:** Service endpoint information (predecessor to EndpointSlice)
- **Use Case:** Service discovery, load balancing
- **Note:** Library has EndpointSlice but not Endpoints (still widely used)

## Priority 3: Dynamic Resource Allocation (Kubernetes 1.34)

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

## Priority 4: Coordination & Runtime

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

## Priority 5: API Extensions

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
4. **Dependencies** - Some features build on others (e.g., VolumeSnapshot ecosystem)

Current priorities based on production use:

1. **Storage snapshots** (Weeks 3-4 of future work)
2. **Missing core resources** (Week 4)
3. **DRA resources** (Week 5) - For AI/ML workloads
4. **Coordination resources** (Week 6)
5. **API extensions** (Week 6)

## Benefits of Planned Resources

### Storage Resources
- **Backup & Restore:** Point-in-time snapshots for disaster recovery
- **Data Cloning:** Quickly clone volumes for testing/development
- **CSI Management:** Better visibility into storage drivers

### DRA Resources
- **GPU Sharing:** Multiple pods sharing same GPU (cost savings)
- **Resource Efficiency:** Better utilization of specialized hardware
- **AI/ML Support:** First-class support for GPU workloads

### Coordination Resources
- **Operator Development:** Build robust controllers with leader election
- **High Availability:** Distributed lock management

### Core Resources
- **Feature Completeness:** Fill gaps in API coverage
- **Backward Compatibility:** Support legacy resources still in use

## How to Request Features

If you need one of these resources implemented sooner:

1. **Open an Issue:** Describe your use case on GitHub
2. **Contribute:** Submit a PR following the patterns in existing resources
3. **Sponsor:** Prioritize development through GitHub Sponsors

## Stay Updated

Watch the repository for updates on feature implementation:

- [GitHub Issues](https://github.com/renoki-co/php-k8s/issues)
- [GitHub Releases](https://github.com/renoki-co/php-k8s/releases)
- [Documentation](https://rennokki.gitbook.io/php-k8s/)

## Resources

- [Kubernetes API Reference](https://kubernetes.io/docs/reference/kubernetes-api/)
- [Kubernetes 1.34 Release Notes](https://kubernetes.io/blog/2025/08/27/kubernetes-v1-34-release/)
- [Dynamic Resource Allocation](https://kubernetes.io/docs/concepts/scheduling-eviction/dynamic-resource-allocation/)
- [Volume Snapshots](https://kubernetes.io/docs/concepts/storage/volume-snapshots/)
