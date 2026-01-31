# GitHub Actions Setup Guide

This document explains the CI/CD pipeline configuration and what you need to set up for GitHub Actions to work properly.

---

## 📁 Files Created

```
.github/
├── workflows/
│   ├── ci.yml              # Main CI/CD pipeline
│   └── scheduled.yml       # Scheduled tasks (backup, cleanup)
├── dependabot.yml          # Automated dependency updates
├── PULL_REQUEST_TEMPLATE.md
└── ISSUE_TEMPLATE/
    ├── bug_report.md
    └── feature_request.md
```

---

## 🔄 CI/CD Pipeline Overview

### Workflow Triggers

| Branch | Event | Action |
|--------|-------|--------|
| `main` | Push | Run tests → Deploy to **Production** |
| `develop` | Push | Run tests → Deploy to **Staging** |
| `main`, `develop` | Pull Request | Run tests only (no deployment) |

### Pipeline Jobs

```
┌─────────────────────────────────────────────────────────────────┐
│                        CI/CD PIPELINE                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│   ┌─────────┐   ┌─────────┐   ┌──────────┐   ┌──────────┐     │
│   │  TEST   │   │  PINT   │   │ FRONTEND │   │ SECURITY │     │
│   │ PHP 8.1 │   │  Code   │   │  Build   │   │  Audit   │     │
│   │ PHP 8.2 │   │  Style  │   │  Assets  │   │          │     │
│   │ PHP 8.3 │   │         │   │          │   │          │     │
│   └────┬────┘   └────┬────┘   └────┬─────┘   └────┬─────┘     │
│        │             │             │              │            │
│        └─────────────┴─────────────┴──────────────┘            │
│                            │                                    │
│                            ▼                                    │
│              ┌─────────────────────────────┐                   │
│              │      ALL JOBS PASSED?       │                   │
│              └─────────────┬───────────────┘                   │
│                            │                                    │
│              ┌─────────────┴───────────────┐                   │
│              │                             │                    │
│              ▼                             ▼                    │
│   ┌─────────────────────┐     ┌─────────────────────┐         │
│   │  DEPLOY STAGING     │     │  DEPLOY PRODUCTION  │         │
│   │  (develop branch)   │     │  (main branch)      │         │
│   └─────────────────────┘     └─────────────────────┘         │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## ⚙️ Required Setup

### 1. GitHub Repository Secrets

Go to: **Repository → Settings → Secrets and variables → Actions → New repository secret**

Add these secrets:

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `STAGING_SSH_USER` | SSH username for staging server | `deploy` |
| `STAGING_SSH_HOST` | Staging server IP/hostname | `staging.example.com` |
| `STAGING_SSH_KEY` | Private SSH key for staging | `-----BEGIN RSA...` |
| `PROD_SSH_USER` | SSH username for production | `deploy` |
| `PROD_SSH_HOST` | Production server IP/hostname | `app.example.com` |
| `PROD_SSH_KEY` | Private SSH key for production | `-----BEGIN RSA...` |

### 2. GitHub Environments

Go to: **Repository → Settings → Environments**

Create two environments:

#### Staging Environment
- Name: `staging`
- Protection rules: None (auto-deploy)

#### Production Environment
- Name: `production`
- Protection rules:
  - ✅ Required reviewers (add yourself)
  - ✅ Wait timer: 5 minutes (optional)

### 3. Branch Protection Rules

Go to: **Repository → Settings → Branches → Add rule**

#### For `main` branch:
- ✅ Require a pull request before merging
- ✅ Require status checks to pass before merging
  - Select: `test`, `pint`, `frontend`, `security`
- ✅ Require branches to be up to date before merging
- ✅ Include administrators

#### For `develop` branch:
- ✅ Require status checks to pass before merging
  - Select: `test`, `pint`, `frontend`, `security`

---

## 📋 What Each Job Does

### 1. Test Job (`test`)

```yaml
Purpose: Run PHPUnit tests on multiple PHP versions
```

**What it does:**
- Sets up MySQL 8.0 database
- Installs PHP 8.1, 8.2, and 8.3
- Installs Composer dependencies
- Runs database migrations
- Executes `php artisan test --parallel`

**Requirements:**
- Your `.env.example` file must exist
- Tests must be in `tests/` directory

### 2. Pint Job (`pint`)

```yaml
Purpose: Check code style using Laravel Pint
```

**What it does:**
- Runs `vendor/bin/pint --test`
- Fails if code doesn't follow PSR-12 standards

**To fix locally:**
```bash
# Check for issues
vendor/bin/pint --test

# Auto-fix issues
vendor/bin/pint
```

### 3. Frontend Job (`frontend`)

```yaml
Purpose: Build frontend assets
```

**What it does:**
- Installs Node.js 20
- Runs `npm ci`
- Runs `npm run build`
- Uploads build artifacts for deployment

**Requirements:**
- Valid `package.json` and `package-lock.json`
- Vite configuration in `vite.config.js`

### 4. Security Job (`security`)

```yaml
Purpose: Check for known vulnerabilities
```

**What it does:**
- Runs `composer audit`
- Fails if vulnerabilities found

**To check locally:**
```bash
composer audit
```

### 5. Deploy Jobs

```yaml
Purpose: Deploy to staging/production servers
```

**Current placeholder commands - you need to customize:**

```yaml
# Option 1: SSH deployment
- name: Deploy via SSH
  run: |
    ssh ${{ secrets.PROD_SSH_USER }}@${{ secrets.PROD_SSH_HOST }} << 'EOF'
      cd /var/www/production
      git pull origin main
      composer install --no-dev --optimize-autoloader
      php artisan migrate --force
      php artisan config:cache
      php artisan route:cache
      php artisan view:cache
      php artisan queue:restart
    EOF

# Option 2: Laravel Envoy
- name: Deploy with Envoy
  run: php vendor/bin/envoy run deploy

# Option 3: Using a deployment service (Forge, Envoyer, etc.)
- name: Trigger deployment
  run: curl -X POST ${{ secrets.DEPLOY_WEBHOOK_URL }}
```

---

## 🗓️ Scheduled Tasks

### Daily Tasks (2:00 AM UTC)
- Database backup
- Security vulnerability check
- Check for outdated packages

### Weekly Tasks (Sundays)
- Cleanup old notifications (30+ days)
- Generate weekly reports (optional)

**To change schedule:**
```yaml
# cron format: minute hour day month weekday
schedule:
  - cron: '0 2 * * *'    # Daily at 2 AM
  - cron: '0 2 * * 0'    # Sundays at 2 AM
  - cron: '0 */6 * * *'  # Every 6 hours
```

---

## 🤖 Dependabot Configuration

Automatically creates PRs for:

| Package Manager | Schedule | Day |
|-----------------|----------|-----|
| Composer (PHP) | Weekly | Monday |
| NPM (JavaScript) | Weekly | Monday |
| GitHub Actions | Weekly | Monday |

**Ignored updates:**
- Laravel framework major versions (manual upgrade required)

---

## 🚀 How to Use

### Development Workflow

```bash
# 1. Create feature branch from develop
git checkout develop
git pull
git checkout -b feature/my-feature

# 2. Make changes and commit
git add .
git commit -m "feat: add new feature"

# 3. Push and create PR to develop
git push -u origin feature/my-feature
# Create PR on GitHub → triggers CI

# 4. After PR merged to develop
# → Auto-deploys to staging

# 5. Create PR from develop to main
# → After approval, deploys to production
```

### Manual Workflow Trigger

You can manually trigger workflows:

1. Go to **Actions** tab
2. Select workflow
3. Click **Run workflow**
4. Select branch and click **Run**

---

## 🔧 Customization

### Add More PHP Versions

```yaml
strategy:
  matrix:
    php: ['8.1', '8.2', '8.3', '8.4']  # Add 8.4
```

### Add PostgreSQL Instead of MySQL

```yaml
services:
  postgres:
    image: postgres:15
    env:
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: password
      POSTGRES_DB: ticket_manage_test
    ports:
      - 5432:5432
```

### Add Redis for Queue Testing

```yaml
services:
  redis:
    image: redis:7
    ports:
      - 6379:6379
```

### Add Code Coverage

```yaml
- name: Run tests with coverage
  run: php artisan test --coverage --min=80
```

---

## ❓ Troubleshooting

### Tests Failing

```bash
# Run locally first
php artisan test

# Check database connection
php artisan migrate:status
```

### Pint Failing

```bash
# Fix code style locally
vendor/bin/pint

# Commit the fixes
git add . && git commit -m "style: fix code formatting"
```

### Frontend Build Failing

```bash
# Check for errors locally
npm run build

# Clear cache and reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Deployment Failing

1. Check SSH keys are correct
2. Verify server has correct permissions
3. Check deployment logs in Actions tab

---

## 📊 Status Badges

Add these to your README.md:

```markdown
![CI](https://github.com/YOUR_USERNAME/ticket_manage/workflows/CI%2FCD%20Pipeline/badge.svg)
![Security](https://github.com/YOUR_USERNAME/ticket_manage/workflows/Scheduled%20Tasks/badge.svg)
```

---

## 📚 References

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Laravel Pint](https://laravel.com/docs/pint)
- [Dependabot Configuration](https://docs.github.com/en/code-security/dependabot)

---

## ✅ Checklist Before First Run

- [ ] Push code to GitHub repository
- [ ] Add required secrets (SSH keys, hosts)
- [ ] Create `staging` and `production` environments
- [ ] Set up branch protection rules
- [ ] Verify `.env.example` exists
- [ ] Ensure tests pass locally
- [ ] Configure deployment scripts for your server

---

**Created:** January 2026  
**Last Updated:** January 2026
