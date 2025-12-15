# Minikube Setup

Detailed Minikube configuration for PHP K8s development.

## Installation

```bash
# Install Minikube
# See https://minikube.sigs.k8s.io/docs/start/

# Start cluster
minikube start --kubernetes-version=v1.33.1
```

## Enable Addons

```bash
minikube addons enable volumesnapshots
minikube addons enable csi-hostpath-driver
minikube addons enable metrics-server
```

## Install VPA

```bash
git clone https://github.com/kubernetes/autoscaler.git /tmp/autoscaler
kubectl apply -f /tmp/autoscaler/vertical-pod-autoscaler/deploy/vpa-v1-crd-gen.yaml
kubectl apply -f /tmp/autoscaler/vertical-pod-autoscaler/deploy/recommender-deployment.yaml
kubectl apply -f /tmp/autoscaler/vertical-pod-autoscaler/deploy/updater-deployment.yaml
kubectl apply -f /tmp/autoscaler/vertical-pod-autoscaler/deploy/admission-controller-deployment.yaml
```

## Install CRDs

```bash
# Sealed Secrets
kubectl apply -f https://raw.githubusercontent.com/bitnami-labs/sealed-secrets/main/helm/sealed-secrets/crds/bitnami.com_sealedsecrets.yaml

# Gateway API
kubectl apply -f https://github.com/kubernetes-sigs/gateway-api/releases/download/v1.3.0/standard-install.yaml
```

## Expose API

```bash
kubectl proxy --port=8080 --reject-paths="^/non-existent-path" &
```

## Verify

```bash
curl -s http://127.0.0.1:8080/version
```

## See Also

- [Development Setup](/development/contributing/setup) - Complete development setup

---

*Minikube setup guide for cuppett/php-k8s fork*
