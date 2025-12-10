---
description: How to work with the Claude Connector for Perfex CRM project
---

# Claude Connector for Perfex CRM - Development Rules

## Project Structure

```
claude-desktop-with-perfexcrm/
├── mcp-server/          # TypeScript MCP Server
│   ├── src/
│   │   ├── index.ts     # Entry point
│   │   ├── config.ts    # Configuration
│   │   ├── database.ts  # MySQL utilities
│   │   └── tools/       # MCP tool implementations
│   └── package.json
├── perfex-module/       # PHP Perfex CRM Module
│   └── modules/claude_connector/
└── docs/                # Documentation
```

## Development Commands

// turbo-all

### Build MCP Server
```bash
cd mcp-server
npm run build
```

### Run MCP Server (dev mode)
```bash
cd mcp-server
npm run dev
```

### Type Check
```bash
cd mcp-server
npx tsc --noEmit
```

### Test MCP Server
```bash
cd mcp-server
npm test
```

## Coding Standards

### TypeScript (MCP Server)
- Use strict TypeScript with no `any` types
- All MCP tools must have proper input schemas with Zod
- Use async/await for database operations
- Always handle database connection errors
- Use prepared statements to prevent SQL injection

### PHP (Perfex Module)
- Follow Perfex CRM coding standards
- Use `db_prefix()` for all table names
- Prefix all functions with `claude_connector_`
- Use CodeIgniter Query Builder for database ops
- Store language strings in `language/english/claude_connector_lang.php`

## MCP Tool Template

```typescript
server.tool(
  "tool_name",
  "Description of what this tool does",
  {
    param1: z.string().describe("Parameter description"),
    param2: z.number().optional().describe("Optional param"),
  },
  async ({ param1, param2 }) => {
    // Implementation
    return {
      content: [{ type: "text", text: JSON.stringify(result) }],
    };
  }
);
```

## Database Table Prefix

Always use the configured prefix (default: `tbl`) when constructing table names:

```typescript
const tableName = `${config.dbPrefix}leads`;
```

## Environment Variables

Required for MCP server:
- `DB_HOST` - Database host
- `DB_PORT` - Database port
- `DB_NAME` - Database name
- `DB_USER` - Database user
- `DB_PASSWORD` - Database password
- `DB_PREFIX` - Table prefix (default: tbl)

## Testing Changes

1. Build the MCP server: `npm run build`
2. Restart Claude Desktop
3. Test with a prompt like "List all Perfex CRM tables"

## Common Issues

| Issue | Solution |
|-------|----------|
| Module not found | Run `npm install` |
| TypeScript errors | Run `npx tsc --noEmit` to check |
| Database connection failed | Check .env credentials |
| MCP not loading | Verify claude_desktop_config.json path |
