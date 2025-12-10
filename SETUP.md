# Setup Guide

## Prerequisites

- **Node.js** 18.0 or higher
- **Perfex CRM** 3.0 or higher
- **Claude Desktop** with MCP support
- **MySQL** 5.7+ or MariaDB 10.3+

---

## Step 1: Clone Repository

```bash
git clone https://github.com/yourusername/claude-connector-perfexcrm.git
cd claude-connector-perfexcrm
```

---

## Step 2: Install MCP Server

```bash
cd mcp-server
npm install
npm run build
```

---

## Step 3: Configure Database Connection

Create `.env` file in `mcp-server/`:

```env
# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_perfex_database
DB_USER=your_database_user
DB_PASSWORD=your_database_password
DB_PREFIX=tbl

# Perfex CRM URL (optional, for API calls)
PERFEX_URL=https://your-perfex-crm.com
PERFEX_API_KEY=your_api_key
```

---

## Step 4: Configure Claude Desktop

### Windows
Edit `%APPDATA%\Claude\claude_desktop_config.json`:

### macOS
Edit `~/Library/Application Support/Claude/claude_desktop_config.json`:

Add this configuration:

```json
{
  "mcpServers": {
    "perfex-crm": {
      "command": "node",
      "args": ["G:/google-antigravity-projects/claude-desktop-with-perfexcrm/mcp-server/dist/index.js"],
      "env": {
        "DB_HOST": "localhost",
        "DB_PORT": "3306",
        "DB_NAME": "your_perfex_database",
        "DB_USER": "your_database_user",
        "DB_PASSWORD": "your_database_password",
        "DB_PREFIX": "tbl"
      }
    }
  }
}
```

---

## Step 5: Restart Claude Desktop

Close and reopen Claude Desktop to load the MCP server.

---

## Step 6: Install Perfex Module (Optional)

1. Copy `perfex-module/modules/claude_connector` to your Perfex CRM `modules/` directory
2. Go to Perfex Admin → Setup → Modules
3. Find "Claude Connector" and click **Activate**
4. Configure settings in the module dashboard

---

## Step 7: Verify Installation

Open Claude Desktop and try:

```
"List all tables in my Perfex CRM database"
```

Claude should return a list of your CRM tables.

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| MCP server not connecting | Check Node.js path in config |
| Database connection failed | Verify credentials in .env |
| Module not appearing | Check Perfex module permissions |
| Permission denied | Ensure database user has full access |

---

## Security Notes

> [!WARNING]
> Store database credentials securely. Never commit `.env` files to version control.

- Use a dedicated database user for the MCP server
- Consider read-only access for production environments
- Review audit logs in Perfex module regularly
