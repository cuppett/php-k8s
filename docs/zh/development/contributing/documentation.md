# Documentation Maintenance

Guide for maintaining and updating the documentation as the codebase evolves.

## Documentation Workflow

### Adding a New Feature

When adding new features to PHP K8s, follow this documentation workflow:

```
1. Implement feature in src/
2. Write tests in tests/
3. Document in docs/
4. Build and verify locally
5. Commit code + docs together
6. CI automatically deploys docs
```

### Documentation Types

| Type | Location | When to Update |
|------|----------|----------------|
| API Reference | `docs/api-reference/` | When adding/changing public APIs |
| User Guide | `docs/guide/` | When adding major features |
| Examples | `docs/examples/` | When adding common use cases |
| Resource Docs | `docs/resources/` | When adding new resource types |
| Migration | `docs/migration/` | For breaking changes |

## Adding a New Resource

When adding support for a new Kubernetes resource:

### 1. Implement the Resource Class

```php
// src/Kinds/K8sNewResource.php
namespace RenokiCo\PhpK8s\Kinds;

use RenokiCo\PhpK8s\Contracts\InteractsWithK8sCluster;

class K8sNewResource extends K8sResource implements InteractsWithK8sCluster
{
    use HasSpec;
    use HasStatus;

    protected static $kind = 'NewResource';
    protected static $defaultVersion = 'v1';
    protected static $namespaceable = true;
}
```

### 2. Add Factory Method

```php
// In src/Traits/InitializesResources.php
public static function newResource($cluster = null, array $attributes = [])
{
    return new K8sNewResource($cluster, $attributes);
}
```

### 3. Create Documentation

Create `docs/resources/category/newresource.md`:

```markdown
# NewResource

Brief description of what NewResource does.

## Basic Usage

\`\`\`php
use RenokiCo\PhpK8s\K8s;

$resource = K8s::newResource($cluster)
    ->setName('my-resource')
    ->setNamespace('default')
    // Resource-specific configuration
    ->create();
\`\`\`

## Methods

### setSpecificMethod()

Description of what this method does.

\`\`\`php
$resource->setSpecificMethod($value);
\`\`\`

## Complete Example

[Full working example]

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
```

### 4. Update Sidebar Navigation

Edit `docs/.vitepress/config.mjs` and add to the appropriate sidebar section:

```javascript
{
  text: 'Category Name',
  collapsed: true,
  items: [
    // ... existing items
    { text: 'NewResource', link: '/resources/category/newresource' }
  ]
}
```

### 5. Build and Test

```bash
npm run docs:dev  # Preview locally
npm run docs:build  # Build for production
```

## Documentation Templates

### Resource Documentation Template

Save as `docs/_templates/resource-template.md`:

```markdown
# ResourceName

Brief description of the resource and its purpose in Kubernetes.

## API Version

- **Group**: `group.k8s.io` (or empty for core)
- **Version**: `v1`
- **Kind**: `ResourceName`
- **Namespaced**: Yes/No

## Basic Usage

\`\`\`php
use RenokiCo\PhpK8s\K8s;

$resource = K8s::resourceName($cluster)
    ->setName('my-resource')
    ->setNamespace('default')  # Remove for cluster-scoped
    ->setLabels(['app' => 'myapp'])
    ->create();
\`\`\`

## Common Operations

### Create

\`\`\`php
$resource->create();
\`\`\`

### Get

\`\`\`php
$resource = $cluster->getResourceNameByName('my-resource', 'namespace');
\`\`\`

### Update

\`\`\`php
$resource->setAttribute('spec.field', 'value')->update();
\`\`\`

### Delete

\`\`\`php
$resource->delete();
\`\`\`

## Resource-Specific Methods

Document methods unique to this resource type.

## Complete Example

Provide a complete, working example.

## See Also

- Related resource links
- Related guide links

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
```

### Example Documentation Template

Save as `docs/_templates/example-template.md`:

```markdown
# Example Title

Brief description of what this example demonstrates.

## Prerequisites

- What's needed to run this example
- Required cluster features
- Any special setup

## Complete Example

\`\`\`php
<?php

require 'vendor/autoload.php';

use RenokiCo\PhpK8s\K8s;
use RenokiCo\PhpK8s\KubernetesCluster;

$cluster = new KubernetesCluster('http://127.0.0.1:8080');

// Complete, runnable code here
\`\`\`

## Step-by-Step Explanation

### Step 1: [Description]

[Explanation]

### Step 2: [Description]

[Explanation]

## Running the Example

Instructions for running the example.

## Expected Output

What users should see when running the example.

## See Also

- Related examples
- Related guides

---

*Example for cuppett/php-k8s fork*
```

## Automated Documentation Updates

### Pre-Commit Hook

Create `.git/hooks/pre-commit`:

```bash
#!/bin/bash

# Check if docs need rebuilding
if git diff --cached --name-only | grep -q "^src/"; then
    echo "Code changes detected, checking documentation..."

    # List new classes added
    NEW_CLASSES=$(git diff --cached --name-only | grep "^src/Kinds/" | grep "\.php$")

    if [ ! -z "$NEW_CLASSES" ]; then
        echo ""
        echo "⚠️  New resource classes detected:"
        echo "$NEW_CLASSES"
        echo ""
        echo "Please ensure documentation is updated:"
        echo "  1. Add docs/resources/category/newresource.md"
        echo "  2. Update docs/.vitepress/config.mjs sidebar"
        echo "  3. Run: npm run docs:build"
        echo ""
        echo "Continue anyway? (y/n)"
        read -r response
        if [[ ! "$response" =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi
fi
```

### Documentation Checklist Script

Create `scripts/doc-checklist.php`:

```php
<?php

/**
 * Verify documentation exists for all resources
 */

$srcKinds = glob(__DIR__ . '/../src/Kinds/K8s*.php');
$docsResources = glob(__DIR__ . '/../docs/resources/**/*.md');

$undocumented = [];

foreach ($srcKinds as $kind) {
    $className = basename($kind, '.php');
    $resourceName = strtolower(preg_replace('/^K8s/', '', $className));

    $hasDoc = false;
    foreach ($docsResources as $doc) {
        if (str_contains(strtolower($doc), $resourceName)) {
            $hasDoc = true;
            break;
        }
    }

    if (!$hasDoc) {
        $undocumented[] = $className;
    }
}

if (!empty($undocumented)) {
    echo "⚠️  Resources without documentation:\n";
    foreach ($undocumented as $class) {
        echo "  - {$class}\n";
    }
    exit(1);
}

echo "✓ All resources are documented\n";
exit(0);
```

Run before release:

```bash
php scripts/doc-checklist.php
```

## GitHub Actions Integration

The documentation is automatically built and deployed when changes are pushed to `main`:

```yaml
# .github/workflows/docs-deploy.yml (already created)
# Triggers on:
#  - Push to main (docs/** changes)
#  - Manual workflow dispatch

# Process:
#  1. Build documentation
#  2. Upload to S3
#  3. Invalidate CloudFront cache
```

### Testing Docs in PRs

Documentation builds are tested in pull requests but not deployed:

```yaml
# In .github/workflows/docs-deploy.yml
on:
  pull_request:
    paths: ['docs/**']
# Build job runs, deploy job is skipped for PRs
```

## Documentation Review Checklist

Before merging changes that affect documentation:

- [ ] All new resource types have documentation pages
- [ ] API reference updated for new methods
- [ ] Examples added for new features
- [ ] Migration guide updated for breaking changes
- [ ] Build succeeds: `npm run docs:build`
- [ ] No broken links
- [ ] Code examples are tested and work
- [ ] Attribution footer present on adapted pages

## Keeping Documentation Current

### Monthly Review

Schedule monthly documentation reviews:

1. **Check for outdated content**
   - Compare with current codebase
   - Update version numbers
   - Refresh examples

2. **Add missing examples**
   - Review GitHub issues for common questions
   - Add examples for frequently asked scenarios

3. **Update API reference**
   - Document new methods
   - Update method signatures

4. **Improve clarity**
   - Based on user feedback
   - Simplify complex explanations

### Using GitHub Issues

Label documentation requests:

```
Type: Documentation
Priority: High/Medium/Low
Category: Guide/Example/API Reference/Resource
```

Track with GitHub Projects.

## Semi-Automated Documentation

### Extract PHPDoc Comments

Create `scripts/generate-api-docs.php`:

```php
<?php

/**
 * Generate API documentation from PHPDoc comments
 */

use phpDocumentor\Reflection\DocBlockFactory;

$files = glob(__DIR__ . '/../src/Kinds/K8s*.php');

foreach ($files as $file) {
    $className = basename($file, '.php');

    // Parse PHP file
    $content = file_get_contents($file);

    // Extract PHPDoc comments
    // Generate markdown
    // Save to docs/api-reference/

    echo "Generated docs for {$className}\n";
}
```

This can help generate skeleton documentation from code comments.

## Documentation Style Guide

### Code Examples

- Use complete, runnable examples
- Include all necessary `use` statements
- Show error handling
- Add comments for clarity
- Test all examples before committing

### Language

- Use present tense ("creates" not "will create")
- Use active voice
- Be concise but clear
- Include "why" not just "how"

### Structure

Each page should have:

1. **Title** - Clear, descriptive
2. **Overview** - What this is and why you'd use it
3. **Basic Usage** - Simple, common case
4. **Advanced Usage** - Complex scenarios
5. **Complete Example** - Full working code
6. **See Also** - Related pages

### Attribution

All pages adapted from upstream must include:

```markdown
---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
```

New fork-only pages should include:

```markdown
---

*Documentation for cuppett/php-k8s fork*
```

## Future Improvements

### Potential Automation

1. **API Doc Generator** - Extract from PHPDoc comments
2. **Example Tester** - Automatically test code examples
3. **Link Checker** - Verify all internal/external links
4. **Search Analytics** - Track what users search for, add missing docs

### Version Documentation

For multiple versions:

```
docs/
├── v3/     # Current version
├── v4/     # Next major version
└── legacy/ # Older versions
```

Update VitePress config for version switcher.

## Contributing Documentation

Contributors should:

1. **Update docs with code changes** - Document new features immediately
2. **Add examples for common use cases**
3. **Fix typos and improve clarity**
4. **Test all code examples**

Documentation contributions are as valuable as code contributions!

## Getting Help

Questions about documentation:

- [Open an issue](https://github.com/cuppett/php-k8s/issues) with label `Type: Documentation`
- Check this guide
- Ask in [GitHub Discussions](https://github.com/cuppett/php-k8s/discussions)

## See Also

- [Contributing](/development/contributing/contributing) - General contribution guidelines
- [Development Setup](/development/contributing/setup) - Dev environment setup
- [Testing](/development/contributing/testing) - Testing guidelines

---

*Documentation maintenance guide for cuppett/php-k8s fork*
