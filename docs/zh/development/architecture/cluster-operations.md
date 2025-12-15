# Cluster Operations

How resources interact with the Kubernetes API server.

## HTTP Client

PHP K8s uses Guzzle for HTTP communication with the Kubernetes API.

## API Requests

Resources make RESTful API calls:

- **GET** - Retrieve resources
- **POST** - Create resources
- **PUT** - Update resources (replace)
- **PATCH** - Partial updates
- **DELETE** - Delete resources

## WebSocket Operations

For streaming operations (watch, exec, logs), PHP K8s uses WebSocket connections via Ratchet Pawl.

## Error Handling

API errors throw `KubernetesAPIException` with status code and response details.

## See Also

- [KubernetesCluster](/development/api-reference/kubernetes-cluster) - Cluster connection class

---

*Cluster operations documentation for cuppett/php-k8s fork*
