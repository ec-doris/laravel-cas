# Publishing to brownrl/laravel-cas

This document outlines the steps to publish this forked repository to your GitHub account.

## Repository Changes Made

✅ Updated `composer.json` package name from `ecphp/laravel-cas` to `brownrl/laravel-cas`
✅ Updated repository URL to `https://github.com/brownrl/laravel-cas`
✅ Updated README.md installation instructions
✅ Updated all source file headers to reference the new repository
✅ Updated LICENSE with fork attribution
✅ Added changelog entry documenting the fork
✅ Fixed `jsonSerialize()` method issue that was causing test failures
✅ All tests passing (5/5)

## Steps to Publish

### 1. Create the GitHub Repository

1. Go to https://github.com/brownrl
2. Click "New repository"
3. Name it `laravel-cas`
4. Set it to Public (if you want it publicly available)
5. **DO NOT** initialize with README, .gitignore, or license (since we already have these)
6. Click "Create repository"

### 2. Update Git Remote

From the `/Users/brownrl/Herd/laravel-cas` directory, run:

```bash
# Check current remote
git remote -v

# Update the remote origin to your new repository
git remote set-url origin https://github.com/brownrl/laravel-cas.git

# Verify the change
git remote -v
```

### 3. Commit and Push Changes

```bash
# Stage all changes
git add .

# Commit the fork and updates
git commit -m "Fork from ecphp/laravel-cas to brownrl/laravel-cas

- Updated package name and repository references
- Fixed jsonSerialize() method implementation
- Removed PHP conventions dependencies
- Updated documentation and license attribution"

# Push to your new repository
git push -u origin main
```

If the default branch is `master` instead of `main`, use:
```bash
git push -u origin master
```

### 4. Verify on GitHub

1. Go to https://github.com/brownrl/laravel-cas
2. Verify all files are present
3. Check that the README.md displays correctly
4. Verify the composer.json shows the correct package name

### 5. Test Installation

You can test that your package works by installing it in a test project:

```bash
composer require brownrl/laravel-cas
```

## Package Information

- **Package Name**: `brownrl/laravel-cas`
- **Repository**: https://github.com/brownrl/laravel-cas
- **Version**: 1.0.0 (suggested for first release)
- **License**: BSD-3-Clause
- **PHP Version**: >= 8.1
- **Laravel Versions**: ^9 || ^10 || ^11 || ^12

## Notes

- All original functionality is preserved
- Tests are passing
- The package is ready for use
- You may want to tag a release (e.g., v1.0.0) after pushing to make it available via Composer/Packagist