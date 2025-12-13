# WebSocket Handling

How PHP K8s handles WebSocket connections for streaming operations.

## WebSocket Operations

PHP K8s uses WebSockets for:

- **Watch** - Stream resource changes
- **Exec** - Execute commands in containers
- **Attach** - Attach to running containers
- **Logs** - Stream container logs

## Implementation

Uses Ratchet Pawl library for WebSocket client functionality.

## Connection Management

- Automatic reconnection on failure
- Proper header forwarding (auth, etc.)
- SSL/TLS support

## See Also

- [Watching Resources](/guide/usage/watching-resources) - Watch API usage
- [Exec & Logs](/guide/usage/exec-logs) - Exec and logs usage

---

*WebSocket handling documentation for cuppett/php-k8s fork*
