import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { z } from "zod";
import { config, getTableName } from "./config";
import { query, queryOne, execute, closeConnection } from "./database";

// Create MCP Server
const server = new McpServer({
    name: "perfex-crm",
    version: "1.0.0",
});

// ============================================
// DATABASE TOOLS
// ============================================

server.tool(
    "execute_sql",
    "Execute a raw SQL query on the Perfex CRM database. Use with caution for modifications.",
    {
        sql: z.string().describe("The SQL query to execute"),
        params: z.array(z.any()).optional().describe("Query parameters for prepared statements"),
    },
    async ({ sql, params }) => {
        try {
            const results = await query(sql, params);
            return {
                content: [{ type: "text", text: JSON.stringify(results, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

server.tool(
    "list_tables",
    "List all database tables in the Perfex CRM database with row counts",
    {},
    async () => {
        try {
            const tables = await query(`
        SELECT 
          TABLE_NAME as name,
          TABLE_ROWS as row_count,
          ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = ?
        ORDER BY TABLE_NAME
      `, [config.db.database]);
            return {
                content: [{ type: "text", text: JSON.stringify(tables, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

server.tool(
    "describe_table",
    "Get the schema/structure of a specific database table",
    {
        table: z.string().describe("Table name (with or without prefix)"),
    },
    async ({ table }) => {
        try {
            // Add prefix if not present
            const tableName = table.startsWith(config.db.prefix) ? table : getTableName(table);
            const columns = await query(`DESCRIBE ${tableName}`);
            return {
                content: [{ type: "text", text: JSON.stringify(columns, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

// ============================================
// LEADS TOOLS
// ============================================

server.tool(
    "list_leads",
    "List leads from Perfex CRM with optional filters",
    {
        status: z.number().optional().describe("Filter by status ID"),
        source: z.number().optional().describe("Filter by source ID"),
        assigned: z.number().optional().describe("Filter by assigned staff ID"),
        limit: z.number().optional().default(50).describe("Maximum results to return"),
    },
    async ({ status, source, assigned, limit }) => {
        try {
            let sql = `
        SELECT l.*, 
          s.name as status_name, 
          src.name as source_name,
          CONCAT(st.firstname, ' ', st.lastname) as assigned_name
        FROM ${getTableName("leads")} l
        LEFT JOIN ${getTableName("leads_status")} s ON l.status = s.id
        LEFT JOIN ${getTableName("leads_sources")} src ON l.source = src.id
        LEFT JOIN ${getTableName("staff")} st ON l.assigned = st.staffid
        WHERE 1=1
      `;
            const params: any[] = [];

            if (status !== undefined) {
                sql += " AND l.status = ?";
                params.push(status);
            }
            if (source !== undefined) {
                sql += " AND l.source = ?";
                params.push(source);
            }
            if (assigned !== undefined) {
                sql += " AND l.assigned = ?";
                params.push(assigned);
            }

            sql += ` ORDER BY l.dateadded DESC LIMIT ?`;
            params.push(limit);

            const leads = await query(sql, params);
            return {
                content: [{ type: "text", text: JSON.stringify(leads, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

server.tool(
    "create_lead",
    "Create a new lead in Perfex CRM",
    {
        name: z.string().describe("Lead name"),
        email: z.string().optional().describe("Lead email"),
        phonenumber: z.string().optional().describe("Phone number"),
        company: z.string().optional().describe("Company name"),
        source: z.number().optional().describe("Source ID"),
        status: z.number().optional().default(1).describe("Status ID"),
        assigned: z.number().optional().describe("Assigned staff ID"),
        description: z.string().optional().describe("Lead description"),
    },
    async ({ name, email, phonenumber, company, source, status, assigned, description }) => {
        try {
            const result = await execute(
                `INSERT INTO ${getTableName("leads")} 
        (name, email, phonenumber, company, source, status, assigned, description, dateadded, lastcontact)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())`,
                [name, email || "", phonenumber || "", company || "", source || 0, status, assigned || 0, description || ""]
            );
            return {
                content: [{ type: "text", text: JSON.stringify({ success: true, id: result.insertId }, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

server.tool(
    "update_lead",
    "Update an existing lead in Perfex CRM",
    {
        id: z.number().describe("Lead ID to update"),
        name: z.string().optional().describe("Lead name"),
        email: z.string().optional().describe("Lead email"),
        phonenumber: z.string().optional().describe("Phone number"),
        company: z.string().optional().describe("Company name"),
        status: z.number().optional().describe("Status ID"),
        assigned: z.number().optional().describe("Assigned staff ID"),
    },
    async ({ id, ...updates }) => {
        try {
            const fields: string[] = [];
            const values: any[] = [];

            for (const [key, value] of Object.entries(updates)) {
                if (value !== undefined) {
                    fields.push(`${key} = ?`);
                    values.push(value);
                }
            }

            if (fields.length === 0) {
                return { content: [{ type: "text", text: "No fields to update" }] };
            }

            values.push(id);
            await execute(
                `UPDATE ${getTableName("leads")} SET ${fields.join(", ")} WHERE id = ?`,
                values
            );
            return {
                content: [{ type: "text", text: JSON.stringify({ success: true, id }, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

// ============================================
// CLIENTS TOOLS
// ============================================

server.tool(
    "list_clients",
    "List clients/companies from Perfex CRM",
    {
        active: z.boolean().optional().describe("Filter by active status"),
        limit: z.number().optional().default(50).describe("Maximum results"),
    },
    async ({ active, limit }) => {
        try {
            let sql = `
        SELECT c.*, 
          (SELECT COUNT(*) FROM ${getTableName("contacts")} WHERE userid = c.userid) as contacts_count,
          (SELECT COUNT(*) FROM ${getTableName("projects")} WHERE clientid = c.userid) as projects_count
        FROM ${getTableName("clients")} c
        WHERE 1=1
      `;
            const params: any[] = [];

            if (active !== undefined) {
                sql += " AND c.active = ?";
                params.push(active ? 1 : 0);
            }

            sql += ` ORDER BY c.company LIMIT ?`;
            params.push(limit);

            const clients = await query(sql, params);
            return {
                content: [{ type: "text", text: JSON.stringify(clients, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

server.tool(
    "create_client",
    "Create a new client/company in Perfex CRM",
    {
        company: z.string().describe("Company name"),
        vat: z.string().optional().describe("VAT number"),
        phonenumber: z.string().optional().describe("Phone number"),
        website: z.string().optional().describe("Website URL"),
        address: z.string().optional().describe("Address"),
        city: z.string().optional().describe("City"),
        state: z.string().optional().describe("State"),
        zip: z.string().optional().describe("ZIP code"),
        country: z.number().optional().describe("Country ID"),
    },
    async ({ company, vat, phonenumber, website, address, city, state, zip, country }) => {
        try {
            const result = await execute(
                `INSERT INTO ${getTableName("clients")} 
        (company, vat, phonenumber, website, address, city, state, zip, country, datecreated, active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)`,
                [company, vat || "", phonenumber || "", website || "", address || "", city || "", state || "", zip || "", country || 0]
            );
            return {
                content: [{ type: "text", text: JSON.stringify({ success: true, userid: result.insertId }, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

// ============================================
// PROJECTS TOOLS
// ============================================

server.tool(
    "list_projects",
    "List projects from Perfex CRM",
    {
        clientid: z.number().optional().describe("Filter by client ID"),
        status: z.number().optional().describe("Filter by status (1-6)"),
        limit: z.number().optional().default(50).describe("Maximum results"),
    },
    async ({ clientid, status, limit }) => {
        try {
            let sql = `
        SELECT p.*, 
          c.company as client_name,
          (SELECT COUNT(*) FROM ${getTableName("tasks")} WHERE rel_type = 'project' AND rel_id = p.id) as tasks_count
        FROM ${getTableName("projects")} p
        LEFT JOIN ${getTableName("clients")} c ON p.clientid = c.userid
        WHERE 1=1
      `;
            const params: any[] = [];

            if (clientid !== undefined) {
                sql += " AND p.clientid = ?";
                params.push(clientid);
            }
            if (status !== undefined) {
                sql += " AND p.status = ?";
                params.push(status);
            }

            sql += ` ORDER BY p.start_date DESC LIMIT ?`;
            params.push(limit);

            const projects = await query(sql, params);
            return {
                content: [{ type: "text", text: JSON.stringify(projects, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

server.tool(
    "create_project",
    "Create a new project in Perfex CRM",
    {
        name: z.string().describe("Project name"),
        clientid: z.number().describe("Client ID"),
        billing_type: z.number().optional().default(1).describe("Billing type (1=Fixed, 2=Hourly, 3=Task hours)"),
        status: z.number().optional().default(1).describe("Status (1=Not Started, 2=In Progress, etc.)"),
        start_date: z.string().optional().describe("Start date (YYYY-MM-DD)"),
        deadline: z.string().optional().describe("Deadline (YYYY-MM-DD)"),
        description: z.string().optional().describe("Project description"),
    },
    async ({ name, clientid, billing_type, status, start_date, deadline, description }) => {
        try {
            const startDateValue = start_date || new Date().toISOString().split('T')[0];
            const result = await execute(
                `INSERT INTO ${getTableName("projects")} 
        (name, clientid, billing_type, status, start_date, deadline, description, datecreated)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())`,
                [name, clientid, billing_type, status, startDateValue, deadline || null, description || ""]
            );
            return {
                content: [{ type: "text", text: JSON.stringify({ success: true, id: result.insertId }, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

// ============================================
// TASKS TOOLS
// ============================================

server.tool(
    "list_tasks",
    "List tasks from Perfex CRM",
    {
        rel_type: z.string().optional().describe("Related to type (project, lead, customer, etc.)"),
        rel_id: z.number().optional().describe("Related entity ID"),
        status: z.number().optional().describe("Status (1=Not Started, 2=Awaiting, 3=Testing, 4=In Progress, 5=Complete)"),
        assignee: z.number().optional().describe("Assigned staff ID"),
        limit: z.number().optional().default(50).describe("Maximum results"),
    },
    async ({ rel_type, rel_id, status, assignee, limit }) => {
        try {
            let sql = `
        SELECT t.*,
          (SELECT GROUP_CONCAT(CONCAT(s.firstname, ' ', s.lastname)) 
           FROM ${getTableName("task_assigned")} ta 
           JOIN ${getTableName("staff")} s ON ta.staffid = s.staffid 
           WHERE ta.taskid = t.id) as assignees
        FROM ${getTableName("tasks")} t
        WHERE 1=1
      `;
            const params: any[] = [];

            if (rel_type !== undefined) {
                sql += " AND t.rel_type = ?";
                params.push(rel_type);
            }
            if (rel_id !== undefined) {
                sql += " AND t.rel_id = ?";
                params.push(rel_id);
            }
            if (status !== undefined) {
                sql += " AND t.status = ?";
                params.push(status);
            }
            if (assignee !== undefined) {
                sql += ` AND t.id IN (SELECT taskid FROM ${getTableName("task_assigned")} WHERE staffid = ?)`;
                params.push(assignee);
            }

            sql += ` ORDER BY t.duedate ASC LIMIT ?`;
            params.push(limit);

            const tasks = await query(sql, params);
            return {
                content: [{ type: "text", text: JSON.stringify(tasks, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

server.tool(
    "create_task",
    "Create a new task in Perfex CRM",
    {
        name: z.string().describe("Task name"),
        rel_type: z.string().optional().describe("Related to type (project, lead, customer, etc.)"),
        rel_id: z.number().optional().describe("Related entity ID"),
        assignees: z.array(z.number()).optional().describe("Array of staff IDs to assign"),
        startdate: z.string().optional().describe("Start date (YYYY-MM-DD)"),
        duedate: z.string().optional().describe("Due date (YYYY-MM-DD)"),
        priority: z.number().optional().default(2).describe("Priority (1=Low, 2=Medium, 3=High, 4=Urgent)"),
        description: z.string().optional().describe("Task description"),
    },
    async ({ name, rel_type, rel_id, assignees, startdate, duedate, priority, description }) => {
        try {
            const startDateValue = startdate || new Date().toISOString().split('T')[0];
            const result = await execute(
                `INSERT INTO ${getTableName("tasks")} 
        (name, rel_type, rel_id, startdate, duedate, priority, description, dateadded, status, addedfrom)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 1, 1)`,
                [name, rel_type || "", rel_id || 0, startDateValue, duedate || null, priority, description || ""]
            );

            const taskId = result.insertId;

            // Assign staff members
            if (assignees && assignees.length > 0) {
                for (const staffId of assignees) {
                    await execute(
                        `INSERT INTO ${getTableName("task_assigned")} (taskid, staffid, assigned_from, is_assigned_from_contact)
             VALUES (?, ?, 1, 0)`,
                        [taskId, staffId]
                    );
                }
            }

            return {
                content: [{ type: "text", text: JSON.stringify({ success: true, id: taskId }, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

server.tool(
    "update_task_status",
    "Update task status in Perfex CRM",
    {
        taskid: z.number().describe("Task ID"),
        status: z.number().describe("New status (1=Not Started, 2=Awaiting, 3=Testing, 4=In Progress, 5=Complete)"),
    },
    async ({ taskid, status }) => {
        try {
            await execute(
                `UPDATE ${getTableName("tasks")} SET status = ? WHERE id = ?`,
                [status, taskid]
            );
            return {
                content: [{ type: "text", text: JSON.stringify({ success: true, taskid, status }, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

// ============================================
// INVOICES TOOLS
// ============================================

server.tool(
    "list_invoices",
    "List invoices from Perfex CRM",
    {
        clientid: z.number().optional().describe("Filter by client ID"),
        status: z.number().optional().describe("Status (1=Unpaid, 2=Paid, 3=Partially Paid, 4=Overdue, 5=Cancelled, 6=Draft)"),
        limit: z.number().optional().default(50).describe("Maximum results"),
    },
    async ({ clientid, status, limit }) => {
        try {
            let sql = `
        SELECT i.*, 
          c.company as client_name,
          (SELECT SUM(amount) FROM ${getTableName("invoicepaymentrecords")} WHERE invoiceid = i.id) as amount_paid
        FROM ${getTableName("invoices")} i
        LEFT JOIN ${getTableName("clients")} c ON i.clientid = c.userid
        WHERE 1=1
      `;
            const params: any[] = [];

            if (clientid !== undefined) {
                sql += " AND i.clientid = ?";
                params.push(clientid);
            }
            if (status !== undefined) {
                sql += " AND i.status = ?";
                params.push(status);
            }

            sql += ` ORDER BY i.date DESC LIMIT ?`;
            params.push(limit);

            const invoices = await query(sql, params);
            return {
                content: [{ type: "text", text: JSON.stringify(invoices, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

// ============================================
// STAFF TOOLS
// ============================================

server.tool(
    "list_staff",
    "List staff members from Perfex CRM",
    {
        active: z.boolean().optional().describe("Filter by active status"),
    },
    async ({ active }) => {
        try {
            let sql = `
        SELECT staffid, firstname, lastname, email, role, 
          last_activity, admin, active, profile_image
        FROM ${getTableName("staff")}
        WHERE 1=1
      `;
            const params: any[] = [];

            if (active !== undefined) {
                sql += " AND active = ?";
                params.push(active ? 1 : 0);
            }

            sql += ` ORDER BY firstname, lastname`;

            const staff = await query(sql, params);
            return {
                content: [{ type: "text", text: JSON.stringify(staff, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

// ============================================
// COMMUNICATION TOOLS
// ============================================

server.tool(
    "add_note",
    "Add an internal note to any entity in Perfex CRM",
    {
        rel_type: z.string().describe("Entity type (lead, customer, project, task, invoice, estimate, etc.)"),
        rel_id: z.number().describe("Entity ID"),
        description: z.string().describe("Note content"),
    },
    async ({ rel_type, rel_id, description }) => {
        try {
            const result = await execute(
                `INSERT INTO ${getTableName("notes")} 
        (rel_id, rel_type, description, date_contacted, addedfrom, dateadded)
        VALUES (?, ?, ?, NOW(), 1, NOW())`,
                [rel_id, rel_type, description]
            );
            return {
                content: [{ type: "text", text: JSON.stringify({ success: true, id: result.insertId }, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

server.tool(
    "get_activity_log",
    "Get recent activity log for Perfex CRM",
    {
        limit: z.number().optional().default(50).describe("Maximum results"),
    },
    async ({ limit }) => {
        try {
            const activities = await query(
                `SELECT * FROM ${getTableName("activity_log")} 
         ORDER BY date DESC LIMIT ?`,
                [limit]
            );
            return {
                content: [{ type: "text", text: JSON.stringify(activities, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

// ============================================
// MODULE DISCOVERY TOOLS
// ============================================

server.tool(
    "list_installed_modules",
    "List all installed Perfex CRM modules",
    {},
    async () => {
        try {
            const modules = await query(
                `SELECT * FROM ${getTableName("modules")} ORDER BY module_name`
            );
            return {
                content: [{ type: "text", text: JSON.stringify(modules, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

server.tool(
    "list_module_tables",
    "List database tables for a specific module (by prefix pattern)",
    {
        module_prefix: z.string().describe("Module table prefix to search for"),
    },
    async ({ module_prefix }) => {
        try {
            const tables = await query(`
        SELECT TABLE_NAME as name, TABLE_ROWS as row_count
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME LIKE ?
        ORDER BY TABLE_NAME
      `, [config.db.database, `${config.db.prefix}${module_prefix}%`]);
            return {
                content: [{ type: "text", text: JSON.stringify(tables, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

// ============================================
// DASHBOARD / STATS TOOLS
// ============================================

server.tool(
    "get_dashboard_stats",
    "Get dashboard statistics from Perfex CRM",
    {},
    async () => {
        try {
            const stats: Record<string, any> = {};

            // Leads stats
            const leadsTotal = await queryOne<{ count: number }>(
                `SELECT COUNT(*) as count FROM ${getTableName("leads")}`
            );
            stats.leads_total = leadsTotal ? leadsTotal.count : 0;

            // Clients stats
            const clientsTotal = await queryOne<{ count: number }>(
                `SELECT COUNT(*) as count FROM ${getTableName("clients")} WHERE active = 1`
            );
            stats.clients_active = clientsTotal ? clientsTotal.count : 0;

            // Projects stats
            const projectsActive = await queryOne<{ count: number }>(
                `SELECT COUNT(*) as count FROM ${getTableName("projects")} WHERE status IN (1, 2, 3, 4)`
            );
            stats.projects_active = projectsActive ? projectsActive.count : 0;

            // Tasks stats
            const tasksOpen = await queryOne<{ count: number }>(
                `SELECT COUNT(*) as count FROM ${getTableName("tasks")} WHERE status != 5`
            );
            stats.tasks_open = tasksOpen ? tasksOpen.count : 0;

            // Invoices stats
            const invoicesUnpaid = await queryOne<{ count: number; total: number }>(
                `SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total 
         FROM ${getTableName("invoices")} WHERE status IN (1, 4)`
            );
            stats.invoices_unpaid_count = invoicesUnpaid ? invoicesUnpaid.count : 0;
            stats.invoices_unpaid_total = invoicesUnpaid ? invoicesUnpaid.total : 0;

            return {
                content: [{ type: "text", text: JSON.stringify(stats, null, 2) }],
            };
        } catch (error: any) {
            return {
                content: [{ type: "text", text: `Error: ${error.message}` }],
                isError: true,
            };
        }
    }
);

// ============================================
// START SERVER
// ============================================

async function main() {
    const transport = new StdioServerTransport();
    await server.connect(transport);
    console.error("Perfex CRM MCP Server running on stdio");
}

main().catch((error) => {
    console.error("Fatal error:", error);
    process.exit(1);
});

// Handle cleanup
process.on("SIGINT", async () => {
    await closeConnection();
    process.exit(0);
});

process.on("SIGTERM", async () => {
    await closeConnection();
    process.exit(0);
});
