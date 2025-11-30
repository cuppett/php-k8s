# Project History

This page documents the history of the `cuppett/php-k8s` fork and its relationship with the upstream `renoki-co/php-k8s` project.

## Timeline

### 2020: Upstream Project Created

The original `renoki-co/php-k8s` project was created by [Alex Renoki](https://github.com/rennokki), providing a PHP-native way to interact with Kubernetes clusters.

**Key Features:**
- PHP-based Kubernetes client
- Support for core Kubernetes resources
- YAML import/export
- Watch API implementation
- Laravel integration

### 2021-2023: Growth and Maturity

The upstream project grew to support:
- Multiple Kubernetes versions
- Expanded resource types
- Improved authentication methods
- WebSocket operations (exec, attach, logs)
- JSON Patch support

### 2024: Fork Created

The `cuppett/php-k8s` fork was created to:

1. **Continue Active Development** - Ensure ongoing maintenance and support
2. **Modernize for PHP 8.2+** - Leverage new language features
3. **Enhance Type Safety** - Add enums and strict type hints
4. **Expand Documentation** - Provide comprehensive guides and examples

### Current State

Both projects continue to exist:

- **Upstream (`renoki-co/php-k8s`)** - Original project by Alex Renoki
- **Fork (`cuppett/php-k8s`)** - PHP 8.2+ modernized version by Stephen Cuppett

## Rationale for Fork

### Why Fork Instead of Contributing Upstream?

The decision to fork was made after considering several factors:

1. **PHP Version Requirements** - Breaking changes for PHP 8.2+ minimum
2. **Enum Migration** - Significant API changes from strings to enums
3. **Maintenance Velocity** - Need for faster iteration and updates
4. **Modern Features** - Full adoption of PHP 8.2+ features
5. **Community Needs** - Specific requirements for active Kubernetes users

### Strategic Independence

The fork provides:

- **Decoupled Release Cycle** - Independent versioning and releases
- **Modern PHP Focus** - Commitment to latest PHP versions
- **Documentation Ownership** - Self-hosted comprehensive docs
- **Community Control** - Direct responsiveness to user needs

## Relationship with Upstream

This fork maintains a respectful relationship with upstream:

### What We Preserve

- ✅ Original architecture and design patterns
- ✅ Core API structure
- ✅ Apache-2.0 License
- ✅ Attribution to original author
- ✅ Compatible method names (where possible)

### What We Enhance

- 🚀 PHP 8.2+ modern syntax
- 🚀 Type-safe enums
- 🚀 Enhanced documentation
- 🚀 Additional resource types
- 🚀 Active maintenance

### Contribution Philosophy

Where appropriate, improvements that benefit both projects may be contributed back upstream, ensuring the broader PHP Kubernetes community benefits.

## Technical Evolution

### PHP Version Support Evolution

```
Upstream:    PHP 7.4+ → 8.0+ → 8.1+
                               ↓
Fork:                        8.2+ → 8.3+ → 8.4+
```

### Feature Adoption Timeline

| Feature | Upstream | Fork |
|---------|----------|------|
| PHP 8.0 Features | ✅ | ✅ |
| PHP 8.1 Features | ✅ | ✅ |
| PHP 8.2 Features | Partial | ✅ Full |
| Enums | ❌ | ✅ |
| Readonly Properties | ❌ | ✅ |
| Match Expressions | Limited | ✅ Extensive |

## Governance

### Upstream Project

- Maintained by: Alex Renoki and contributors
- Repository: https://github.com/renoki-co/php-k8s
- License: Apache-2.0

### Fork Project

- Maintained by: Stephen Cuppett and contributors
- Repository: https://github.com/cuppett/php-k8s
- License: Apache-2.0 (inherited)
- Documentation: https://php-k8s.cuppett.dev

## Community

### User Base

- **Upstream Users** - Those using PHP 8.0-8.1 or preferring established stability
- **Fork Users** - Those using PHP 8.2+ and wanting modern features

Both communities are valued and supported.

### Communication Channels

- **Fork Issues**: https://github.com/cuppett/php-k8s/issues
- **Fork Discussions**: https://github.com/cuppett/php-k8s/discussions
- **Upstream Issues**: https://github.com/renoki-co/php-k8s/issues

## Looking Forward

### Fork Roadmap

1. **Maintain PHP 8.2+ Support** - Keep current with latest PHP versions
2. **Expand Resource Coverage** - Support new Kubernetes resources
3. **Enhance Documentation** - Continuous improvement of guides
4. **Community Engagement** - Responsive to user feedback
5. **Performance Optimization** - Leverage PHP 8.2+ performance gains

### Long-Term Vision

The fork aims to:

- Serve as the go-to Kubernetes client for modern PHP applications
- Maintain compatibility where possible with upstream
- Provide exceptional documentation and developer experience
- Build a vibrant community around PHP and Kubernetes

## Credits and Acknowledgments

### Original Creator

**Alex Renoki** ([@rennokki](https://github.com/rennokki))
- Created the original php-k8s library
- Established the architecture and patterns
- Built the foundation we build upon

### Fork Maintainer

**Stephen Cuppett** ([@cuppett](https://github.com/cuppett))
- Fork maintenance and modernization
- Documentation overhaul
- PHP 8.2+ enhancements

### Contributors

Both projects benefit from community contributions. See:
- [Upstream Contributors](https://github.com/renoki-co/php-k8s/graphs/contributors)
- [Fork Contributors](https://github.com/cuppett/php-k8s/graphs/contributors)

## Frequently Asked Questions

### Will the fork merge back with upstream?

The fork maintains its own trajectory focused on PHP 8.2+ and modern features. However, improvements that benefit both projects may be contributed upstream.

### Can I migrate from upstream to fork?

Yes! See the [Upstream to Fork Migration Guide](/migration/upstream-to-fork) for detailed instructions.

### Which should I use?

See [Fork Differences](/project/fork-differences) for a comparison to help decide.

### How can I contribute?

Both projects welcome contributions:
- **Fork**: https://github.com/cuppett/php-k8s/blob/main/CONTRIBUTING.md
- **Upstream**: https://github.com/renoki-co/php-k8s

## License

Both the upstream project and this fork are released under the Apache-2.0 License, ensuring open-source freedom and compatibility.

---

This fork exists to serve the PHP and Kubernetes communities with modern, well-documented tooling. We're grateful to stand on the shoulders of the excellent upstream work.
