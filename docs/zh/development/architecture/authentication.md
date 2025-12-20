# Authentication Architecture

How PHP K8s handles Kubernetes authentication.

## Supported Methods

- **Kubeconfig** - Load from kubeconfig files
- **Bearer Token** - Service account tokens
- **Client Certificates** - x509 client certs
- **Basic Auth** - Username/password (deprecated)
- **In-Cluster** - Automatic when running in pods

## Implementation

Authentication credentials are attached to HTTP and WebSocket requests via headers or client certificates.

## Security

- SSL/TLS verification by default
- Secure credential storage
- No credentials in logs

## See Also

- [Authentication Guide](/guide/getting-started/authentication) - Usage guide

---

*Authentication architecture documentation for cuppett/php-k8s fork*
