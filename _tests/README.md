# Tests

This directory contains the test configuration and dependencies for the project.

## Setup

Navigate to this directory and install the dependencies:

```bash
cd _tests
composer install
```

## Running Tests

Run the static analysis (PHPStan) using the composer script:

```bash
composer phpstan
```

Run the coding standards check (PHP_CodeSniffer) using the composer script:

```bash
composer phpcs
```

Run the coding standards fixer (PHP Code Beautifier and Fixer) using the composer script:

```bash
composer phpcbf
```

## Configuration

### PHPStan

The PHPStan configuration is located in `phpstan.neon`.
The `vendor` directory is excluded from analysis to avoid memory limit issues.

### PHP_CodeSniffer

The PHP_CodeSniffer configuration is located in `phpcs.xml`.
It uses the WordPress Coding Standards and checks the project root, excluding vendor directories.
