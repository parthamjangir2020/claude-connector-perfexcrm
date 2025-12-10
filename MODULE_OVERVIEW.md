# Module Overview

## Architecture

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  Claude Desktop │────▶│   MCP Server    │────▶│   Perfex CRM    │
│  (MCP Client)   │◀────│  (TypeScript)   │◀────│   (Database)    │
└─────────────────┘     └─────────────────┘     └─────────────────┘
         │                      │                       │
    User prompts           Tool calls              SQL queries
```

## Components

### 1. MCP Server (TypeScript/Node.js)

**Purpose**: Bridge between Claude Desktop and Perfex CRM database.

**Key Files**:
| File | Purpose |
|------|---------|
| `src/index.ts` | Server entry point, tool registration |
| `src/config.ts` | Environment configuration |
| `src/database.ts` | MySQL connection pool |
| `src/tools/*.ts` | MCP tool implementations |

**Technologies**:
- TypeScript 5.x
- @modelcontextprotocol/sdk
- mysql2 (database driver)
- dotenv (configuration)

### 2. Perfex CRM Module (PHP)

**Purpose**: Optional module for settings UI and audit logging.

**Key Files**:
| File | Purpose |
|------|---------|
| `claude_connector.php` | Module initialization |
| `install.php` | Database migrations |
| `controllers/Claude_connector.php` | Admin controller |
| `models/Claude_connector_model.php` | Database model |

**Database Tables**:
| Table | Purpose |
|-------|---------|
| `tblclaude_connector_settings` | Configuration storage |
| `tblclaude_connector_logs` | Action audit trail |

## Communication Flow

1. **User → Claude Desktop**: Natural language request
2. **Claude Desktop → MCP Server**: Tool call via STDIO
3. **MCP Server → MySQL**: SQL query execution
4. **MySQL → MCP Server**: Query results
5. **MCP Server → Claude Desktop**: Formatted response
6. **Claude Desktop → User**: Natural language answer

## MCP Primitives Used

| Primitive | Usage |
|-----------|-------|
| **Tools** | Execute actions (CRUD, queries) |
| **Resources** | Read-only data access (reports, dashboards) |

## Security Model

- Database credentials stored in environment variables
- No credentials exposed to Claude
- All actions logged for audit
- Optional Perfex module for access control

## Perfex CRM Table Prefix

All Perfex tables use a configurable prefix (default: `tbl`). The MCP server uses `db_prefix()` equivalent to construct table names dynamically.

## Supported Perfex Entities

| Entity | Table | Operations |
|--------|-------|------------|
| Leads | `tblleads` | CRUD, convert |
| Clients | `tblclients` | CRUD |
| Projects | `tblprojects` | CRUD |
| Tasks | `tbltasks` | CRUD, assign |
| Invoices | `tblinvoices` | CRUD |
| Staff | `tblstaff` | Read |
| Notes | `tblnotes` | Create, read |
