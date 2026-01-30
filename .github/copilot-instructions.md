# OCI Driver - AI Coding Agent Instructions

## Project Overview

This is a **lightweight PHP library** for building and executing Oracle Database (OCI) queries without validation. It provides:
- **SQL Query Builders** (Select, Insert, Update, Delete) with fluent interface
- **OCI Driver** - wrapper around PHP's oci8 extension for connection/transaction management
- **Query Debugger** - performance tracking for development/production modes

**Key Constraint**: Queries are NOT validated—the library trusts developers to write correct SQL.

## Architecture

### Core Components (src/OCI/)

1. **Query/Builder/** - Fluent SQL builders (Select, Insert, Update, Delete)
   - Inherit from `AbstractBuilder` → `AbstractCommonBuilder` → specific builders
   - Pattern: `Builder::start()` or `new Builder()` → `->method()` chains → `->build()` returns string

2. **Driver/** - OCI connection wrapper
   - `Driver` implements `DriverInterface`, wraps PHP's `oci_*` functions
   - `Connection` - static factory for oci_connect/oci_pconnect
   - Auto-commits by default (OCI_COMMIT_ON_SUCCESS), use `beginTransaction()`/`commitTransaction()` for explicit control

3. **Helper/** - Utilities
   - `Factory` - singleton pattern for Driver instances (init static connection once)
   - `Environment` - dev/prod mode detection (controls DebuggerDump vs DebuggerDumb)
   - `SessionInit` - Oracle session initialization (character set, NLS settings)
   - `ClauseInParamsHelper`, `FloatUtils` - SQL helpers for IN clauses, numeric formatting

4. **Debugger/** - Performance tracking
   - `DebuggerInterface` - `start()` before query, `end($query, $params)` after
   - `DebuggerDump` (dev) - uses Symfony VarDumper to log queries
   - `DebuggerDumb` (prod) - no-op implementation

## Key Patterns & Conventions

### Query Builders
```php
// All builders follow this pattern
$sql = Select::start()
    ->column('col1', 'col2')
    ->from('table', 'alias')
    ->where('condition')
    ->orderBy('field ASC')
    ->build(); // Returns SQL string

// Manually escape using static methods
Insert::quote("O'neil")    // Handles single quotes
Update::quote(value)       // Same as Insert
```

### Connection & Driver Usage
```php
// 1. Initialize Factory once per app lifecycle
$conn = oci_connect('user', 'pass', 'db');
Factory::init($conn, 'dev'); // Or 'prod'

// 2. Get singleton instance
$driver = Factory::get();

// 3. Use for transactions
$driver->beginTransaction()
    ->execute($sql1)
    ->execute($sql2)
    ->commitTransaction();
```

### Parameter Binding
- Drivers use `:name` placeholder syntax (Oracle native)
- `Parameter` class wraps value + OCI data type (SQLT_CHR, SQLT_LBI, etc.)
- Debugger receives array of parameters for logging

## Testing & Build

### Test Setup
- **Unit tests**: `tests/units/` (no DB required)
- **Integration tests**: `tests/integration/` (requires OCI8 + Oracle DB)
- Bootstrap defines Oracle constants (SQLT_CHR, SQLT_LBI) if missing

### Commands
```bash
composer test              # Setup DB + run all tests without coverage
composer test-coverage     # Unit tests only with xdebug coverage
composer cover             # All tests + coverage report + localhost:5001 server
composer setup-tables      # Initialize test database schema
```

### CI/CD
- Runs on PHP 8.2, 8.3, 8.4, 8.5
- Unit tests always run with coverage (xdebug mode)
- Coveralls integration for 8.2 only
- Platform requirement: `ext-oci8` ignored in CI (stubbed in tests)

## Code Style & Requirements

- **PSR-4 autoloading**: `Elie\*` namespace maps to `src/`
- **PHP 8.2+ only** - strict types, typed properties, union types
- **declare(strict_types=1)** required in all files
- **No query validation** - library is intentionally permissive
- Dependencies: only `ext-oci8`, `ext-json` (core); dev uses PHPUnit 11.5, Symfony VarDumper

## Common Tasks for AI Agents

### Adding a New Query Builder Method
1. Add method to `AbstractCommonBuilder` if shared (Select, Update, Delete)
2. Add to specific builder if unique (e.g., `UNION` only in Select)
3. Follow fluent pattern: `->method(args): self`
4. Update builder unit tests in `tests/units/OCI/Query/Builder/`

### Adding Driver Functionality
1. Implement in `Driver` class + update `DriverInterface`
2. Respect transaction mode (check `$commitOption`)
3. Use `$this->debugger->start()` / `->end()` around OCI calls
4. Test with mocked `DebuggerInterface`

### Modifying Query Output
1. Test query builders by comparing `->build()` output to expected SQL strings
2. All quote/escape logic in builder static methods
3. No SQL injection protection—document if accepting user input

## Integration Points

- **oci8 extension**: https://www.php.net/manual/en/book.oci8.php
  - Driver wraps `oci_connect`, `oci_execute`, `oci_fetch_all`, `oci_commit`, `oci_rollback`
  - Parameter binding uses `:placeholder` syntax

- **Symfony VarDumper**: Dev-mode debugging only (optional dependency)

- **GitHub Actions**: CI matrix tests all PHP versions; local dev can use `composer test`
