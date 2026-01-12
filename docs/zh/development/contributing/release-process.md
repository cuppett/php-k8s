# Release Process

Guidelines for releasing new versions of PHP K8s.

## Version Numbering

Follows [Semantic Versioning](https://semver.org/):

- **MAJOR** - Breaking changes
- **MINOR** - New features (backward compatible)
- **PATCH** - Bug fixes (backward compatible)

## Release Checklist

1. Update changelog
2. Run full test suite
3. Run static analysis
4. Tag release
5. Publish to Packagist (automatic)

## Tagging

```bash
git tag -a v3.x.x -m "Release v3.x.x"
git push origin v3.x.x
```

---

*Release process documentation for cuppett/php-k8s fork*
