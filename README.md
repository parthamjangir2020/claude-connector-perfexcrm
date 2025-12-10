# Claude Connector for Perfex CRM

AI-powered integration between Claude Desktop and Perfex CRM using Model Context Protocol (MCP).

## Overview

This module enables Claude Desktop to communicate with and control your Perfex CRM installation through:
- **Direct database access** for full control
- **Entity management** (leads, clients, projects, tasks, invoices)
- **Internal communication** (notes, activity logs, emails)
- **Module discovery** for all installed Perfex modules

## Quick Start

```bash
# 1. Install MCP Server
cd mcp-server
npm install
npm run build

# 2. Configure Claude Desktop
# Add perfex-crm server to claude_desktop_config.json

# 3. Install Perfex Module (optional)
# Copy perfex-module/modules/claude_connector to your Perfex modules/
```

## Documentation

| Document | Description |
|----------|-------------|
| [FEATURES.md](./FEATURES.md) | Complete feature list |
| [SETUP.md](./SETUP.md) | Installation guide |
| [MODULE_OVERVIEW.md](./MODULE_OVERVIEW.md) | Technical architecture |
| [VERSION.md](./VERSION.md) | Version history |

## Requirements

- Node.js 18+
- Perfex CRM 3.0+
- Claude Desktop with MCP support
- MySQL 5.7+ / MariaDB 10.3+

## License

MIT License - See LICENSE file for details.
