# Documentation System

This document explains how the documentation system works and how to maintain it as features are added.

## Overview

The documentation is built with **VitePress** and deployed to **php-k8s.cuppett.dev** via AWS S3 + CloudFront.

- **Framework**: VitePress 1.6.4
- **Source**: `docs/` directory (markdown files)
- **Config**: `docs/.vitepress/config.mjs`
- **Build Output**: `docs/.vitepress/dist/`
- **Deployment**: Automated via GitHub Actions

## Quick Start

### Local Development

```bash
# Install dependencies (first time only)
npm install

# Start dev server with hot reload
npm run docs:dev

# Opens at http://localhost:5173
```

### Build for Production

```bash
npm run docs:build

# Output in docs/.vitepress/dist/
```

### Preview Production Build

```bash
npm run docs:preview
```

## Documentation Structure

```
docs/
├── .vitepress/
│   ├── config.mjs          # VitePress configuration (navigation, sidebar, theme)
│   └── theme/              # Custom theme (if needed)
│
├── _templates/             # Templates for new pages
│   ├── resource-template.md
│   └── example-template.md
│
├── index.md                # Homepage
│
├── getting-started/        # Installation, quickstart, auth, config
├── guide/                  # User guides (CRUD, YAML, watch, exec, patch, etc.)
├── resources/              # All 33+ Kubernetes resources organized by category
├── api-reference/          # KubernetesCluster, K8sResource, traits, contracts, enums
├── examples/               # Practical examples (CRUD, deployments, autoscaling, etc.)
├── architecture/           # Architecture deep-dives
├── migration/              # Migration guides (upstream to fork, PHP 8.2+, versions)
├── development/            # Development guides (setup, testing, contributing)
├── integrations/           # Laravel, CI/CD
├── troubleshooting/        # Common errors, auth issues, debugging
└── project/                # History, fork differences, license, roadmap
```

## Adding New Features - Documentation Workflow

### 1. Implement the Feature

Add your feature to `src/`:

```php
// Example: New resource type
// src/Kinds/K8sNewResource.php
class K8sNewResource extends K8sResource { }
```

### 2. Add Factory Method

```php
// src/Traits/InitializesResources.php
public static function newResource($cluster = null, array $attributes = [])
{
    return new K8sNewResource($cluster, $attributes);
}
```

### 3. Write Tests

```php
// tests/NewResourceTest.php
public function test_new_resource_api_interaction() { }
```

### 4. Generate Documentation Stub

```bash
php scripts/generate-resource-doc.php K8sNewResource category
```

This creates `docs/resources/category/newresource.md` with a template.

### 5. Edit Documentation

Fill in the template with:
- Description
- Usage examples
- Methods
- Complete working example

### 6. Update Sidebar

Edit `docs/.vitepress/config.mjs` and add to the appropriate sidebar section:

```javascript
{
  text: 'Category',
  items: [
    { text: 'NewResource', link: '/resources/category/newresource' }
  ]
}
```

Or run `php scripts/update-sidebar.php` for suggestions.

### 7. Test Documentation

```bash
# Start dev server
npm run docs:dev

# Navigate to your new page
# Verify all links work
# Check code examples render correctly
```

### 8. Build and Verify

```bash
# Build for production
npm run docs:build

# Check for errors
```

### 9. Commit Together

```bash
git add src/Kinds/K8sNewResource.php
git add src/Traits/InitializesResources.php
git add tests/NewResourceTest.php
git add docs/resources/category/newresource.md
git add docs/.vitepress/config.mjs
git commit -m "Add NewResource with documentation"
```

### 10. Automatic Deployment

When merged to `main`, GitHub Actions automatically:
1. Builds documentation
2. Deploys to S3
3. Invalidates CloudFront cache
4. Live at php-k8s.cuppett.dev within minutes

## Helper Scripts

Located in `scripts/`:

### Check Documentation Coverage

```bash
php scripts/check-documentation.php
```

Verifies all resource classes have documentation. Run before releases.

### Generate Resource Documentation

```bash
php scripts/generate-resource-doc.php K8sClassName category
```

Creates documentation stub from template with class metadata.

### Update Sidebar Configuration

```bash
php scripts/update-sidebar.php
```

Scans docs directory and suggests sidebar configuration.

## Documentation Templates

Located in `docs/_templates/`:

- **resource-template.md** - Template for resource documentation
- **example-template.md** - Template for example documentation

Use these as starting points for new documentation.

## Maintaining Documentation Quality

### Before Every Commit

- [ ] Documentation builds without errors
- [ ] Code examples are tested and work
- [ ] All internal links are valid
- [ ] Attribution footer present on adapted pages

### Before Every Release

- [ ] Run `php scripts/check-documentation.php`
- [ ] All new features documented
- [ ] Migration guide updated if breaking changes
- [ ] Changelog updated
- [ ] Build succeeds: `npm run docs:build`

### Monthly Maintenance

- Review GitHub issues for documentation requests
- Update outdated examples
- Add examples for frequently asked scenarios
- Improve clarity based on user feedback

## Automated Deployment

### GitHub Actions Workflow

`.github/workflows/docs-deploy.yml`:

- **Triggers**:
  - Push to `main` (docs/** changes)
  - Pull requests (build only, no deploy)
  - Manual workflow dispatch

- **Jobs**:
  1. **Build** - Installs deps, builds VitePress
  2. **Deploy** - Syncs to S3, invalidates CloudFront (main branch only)

### Required Secrets

Set in GitHub repository settings:

- `AWS_DEPLOY_ROLE_ARN` - IAM role for OIDC authentication
- `AWS_ACCOUNT_ID` - AWS account ID (for bucket naming)
- `CLOUDFRONT_DISTRIBUTION_ID` - CloudFront distribution ID

### Deployment Process

```
Code merged to main
    ↓
GitHub Actions triggered
    ↓
Build docs (npm run docs:build)
    ↓
Upload to S3 bucket
    ↓
Invalidate CloudFront cache
    ↓
Live at php-k8s.cuppett.dev (30-60 seconds)
```

## Documentation Best Practices

### Code Examples

✅ **Do:**
- Use complete, runnable examples
- Include all `use` statements
- Show error handling
- Test examples before committing
- Use modern PHP 8.2+ syntax
- Demonstrate enum usage

❌ **Don't:**
- Use pseudo-code
- Omit necessary imports
- Show examples that don't work
- Use outdated syntax

### Structure

Every resource page should have:

1. **Title and description**
2. **API version info** (kind, version, namespaced)
3. **Basic usage** example
4. **Common operations** (CRUD)
5. **Resource-specific methods**
6. **Complete example**
7. **See also** links
8. **Attribution footer**

### Attribution

Pages adapted from upstream:
```markdown
---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
```

New fork-only pages:
```markdown
---

*Documentation for cuppett/php-k8s fork*
```

## VitePress Configuration

### Navigation (`docs/.vitepress/config.mjs`)

**Top Navigation:**
```javascript
nav: [
  { text: 'Home', link: '/' },
  { text: 'Guide', link: '/getting-started/installation' },
  { text: 'API Reference', link: '/api-reference/kubernetes-cluster' },
  { text: 'Examples', link: '/examples/basic-crud' },
  { text: 'GitHub', link: 'https://github.com/cuppett/php-k8s' }
]
```

**Sidebar:**
Organized by section with collapsible groups. Add new pages to the appropriate section.

### Search

Local search is enabled (no external service required):

```javascript
search: {
  provider: 'local'
}
```

### Theme

- Dark/light theme toggle
- GitHub social link
- Edit on GitHub links
- Footer with attribution

## Common Documentation Tasks

### Adding a New Guide Page

1. Create `docs/guide/new-guide.md`
2. Add to sidebar in config.mjs:
   ```javascript
   '/guide/': [
     {
       text: 'User Guide',
       items: [
         // ...
         { text: 'New Guide', link: '/guide/new-guide' }
       ]
     }
   ]
   ```
3. Build and verify: `npm run docs:dev`

### Adding a New Example

1. Create `docs/examples/new-example.md`
2. Use template: `docs/_templates/example-template.md`
3. Add to sidebar examples section
4. Test the example code actually works
5. Build and verify

### Updating Existing Documentation

1. Edit the markdown file directly
2. Build and preview: `npm run docs:dev`
3. Commit changes
4. Documentation auto-deploys on merge to main

### Adding API Reference

For new classes/traits/enums:

1. Create `docs/api-reference/category/name.md`
2. Document all public methods
3. Include code examples
4. Link from related pages

## Troubleshooting

### Build Fails

```bash
npm run docs:build
```

Common issues:
- **Syntax errors in markdown** - Fix markdown syntax
- **Dead links** - Ensure all linked pages exist (or set `ignoreDeadLinks: true`)
- **Invalid frontmatter** - Check YAML frontmatter at top of files

### Links Don't Work

VitePress uses clean URLs:

✅ **Correct:**
```markdown
[Link](/guide/crud-operations)
```

❌ **Incorrect:**
```markdown
[Link](/guide/crud-operations.md)
[Link](/guide/crud-operations.html)
```

### Images Missing

Place images in `docs/public/`:

```markdown
![Logo](/logo.png)  # Serves from docs/public/logo.png
```

## Future Enhancements

### Potential Automations

1. **PHPDoc Extraction** - Generate API docs from code comments
2. **Example Testing** - Automatically test code examples in CI
3. **Link Validation** - Check all links are valid
4. **Search Analytics** - Track what users search for, identify gaps
5. **Version Documentation** - Support multiple documentation versions

### Roadmap

- Automated API reference generation from PHPDoc
- Interactive code examples (try in browser)
- Video tutorials
- Localization (i18n)

## Getting Help

Questions about documentation:

- Check this README
- See [Documentation Maintenance](/development/documentation)
- Open issue with label `Type: Documentation`

## Quick Reference

```bash
# Local dev server
npm run docs:dev

# Build for production
npm run docs:build

# Check documentation coverage
php scripts/check-documentation.php

# Generate resource doc
php scripts/generate-resource-doc.php K8sClassName category

# Update sidebar suggestions
php scripts/update-sidebar.php
```

---

**Key Principle**: Documentation should evolve with the code. When you add a feature, document it immediately in the same commit.
