# Development Setup

Set up your local development environment for contributing to PHP K8s.

## Prerequisites

- PHP 8.2 or higher (8.3+ recommended)
- Composer
- Git
- Kubernetes cluster (Minikube recommended for development)
- kubectl

## Clone the Repository

```bash
git clone https://github.com/cuppett/php-k8s.git
cd php-k8s
```

## Install Dependencies

```bash
composer install
```

## Local Kubernetes Cluster

### Start Minikube

```bash
# Start with specific Kubernetes version
minikube start --kubernetes-version=v1.33.1

# Enable required addons
minikube addons enable volumesnapshots
minikube addons enable csi-hostpath-driver
minikube addons enable metrics-server
```

### Install VPA (Vertical Pod Autoscaler)

```bash
git clone https://github.com/kubernetes/autoscaler.git /tmp/autoscaler
kubectl apply -f /tmp/autoscaler/vertical-pod-autoscaler/deploy/vpa-v1-crd-gen.yaml
kubectl apply -f /tmp/autoscaler/vertical-pod-autoscaler/deploy/recommender-deployment.yaml
kubectl apply -f /tmp/autoscaler/vertical-pod-autoscaler/deploy/updater-deployment.yaml
kubectl apply -f /tmp/autoscaler/vertical-pod-autoscaler/deploy/admission-controller-deployment.yaml
```

### Install Additional CRDs

```bash
# Sealed Secrets
kubectl apply -f https://raw.githubusercontent.com/bitnami-labs/sealed-secrets/main/helm/sealed-secrets/crds/bitnami.com_sealedsecrets.yaml

# Gateway API
kubectl apply -f https://github.com/kubernetes-sigs/gateway-api/releases/download/v1.3.0/standard-install.yaml
```

### Expose API Server

```bash
# Start kubectl proxy
kubectl proxy --port=8080 --reject-paths="^/non-existent-path" &

# Verify connectivity
curl -s http://127.0.0.1:8080/version
```

## Running Tests

### All Tests

```bash
vendor/bin/phpunit
```

### Unit Tests Only

```bash
vendor/bin/phpunit --filter Test$
```

### Specific Test File

```bash
vendor/bin/phpunit tests/PodTest.php
```

### Specific Test Method

```bash
vendor/bin/phpunit tests/PodTest.php --filter test_pod_build
```

### Integration Tests (Requires Cluster)

```bash
CI=true vendor/bin/phpunit
```

## Static Analysis

```bash
# Run Psalm
vendor/bin/psalm

# Fix issues automatically (when possible)
vendor/bin/psalm --alter --issues=all
```

## Code Style

StyleCI handles formatting in CI. Follow existing code patterns:

- PSR-12 coding standard
- Type hints on all parameters and return types
- Use PHP 8.2+ features (enums, readonly, match)

## Project Structure

```
php-k8s/
├── src/
│   ├── Kinds/           # Resource classes (Pod, Deployment, etc.)
│   ├── Traits/          # Reusable traits
│   ├── Contracts/       # Interfaces
│   ├── Enums/           # Enumerations (PodPhase, ServiceType, etc.)
│   ├── Instances/       # Helper classes (Container, Volume, etc.)
│   ├── Patches/         # JSON Patch implementations
│   └── KubernetesCluster.php
├── tests/
│   ├── Kinds/           # Test-only CRD classes
│   ├── *Test.php        # Test files
│   └── TestCase.php     # Base test class
├── docs/                # VitePress documentation
└── examples/            # Usage examples
```

## Making Changes

1. Create a feature branch
2. Make your changes
3. Write/update tests
4. Run tests: `vendor/bin/phpunit`
5. Run static analysis: `vendor/bin/psalm`
6. Commit with descriptive message
7. Push and create pull request

## Testing Workflow

Tests expect the Kubernetes API accessible at `http://127.0.0.1:8080`:

```php
// tests/TestCase.php
protected function setUp(): void
{
    parent::setUp();

    $this->cluster = new KubernetesCluster('http://127.0.0.1:8080');
}
```

### Test Structure

```php
public function test_pod_api_interaction()
{
    $this->runCreationTests();
    $this->runGetAllTests();
    $this->runGetTests();
    $this->runUpdateTests();
    $this->runDeletionTests();
}
```

## Debugging

### Enable Debug Mode

```php
$cluster = new KubernetesCluster('http://127.0.0.1:8080', [
    'debug' => true,
]);
```

### Check Cluster State

```bash
kubectl get pods --all-namespaces
kubectl get deployments --all-namespaces
kubectl describe pod <pod-name>
```

## Documentation

Documentation is built with VitePress:

```bash
# Install Node dependencies
npm install

# Start dev server
npm run docs:dev

# Build documentation
npm run docs:build
```

## Common Issues

### SSL Verification Errors

Tests disable SSL verification by default for local development.

### Port Already in Use

Kill existing kubectl proxy:

```bash
pkill -f "kubectl proxy"
kubectl proxy --port=8080 &
```

### Test Failures

Ensure cluster is running and accessible:

```bash
curl http://127.0.0.1:8080/version
```

## See Also

- [Testing](/development/contributing/testing) - Testing guidelines
- [Contributing](/development/contributing/contributing) - Contribution guidelines
- [Minikube Setup](/development/contributing/minikube-setup) - Detailed Minikube configuration

---

*Development setup guide for cuppett/php-k8s fork*
