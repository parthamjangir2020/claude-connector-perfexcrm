import * as dotenv from "dotenv";
import * as path from "path";

dotenv.config({ path: path.join(__dirname, "..", ".env") });

export interface Config {
    db: {
        host: string;
        port: number;
        database: string;
        user: string;
        password: string;
        prefix: string;
    };
    perfexUrl?: string;
}

export const config: Config = {
    db: {
        host: process.env.DB_HOST || "localhost",
        port: parseInt(process.env.DB_PORT || "3306", 10),
        database: process.env.DB_NAME || "perfex_crm",
        user: process.env.DB_USER || "root",
        password: process.env.DB_PASSWORD || "",
        prefix: process.env.DB_PREFIX || "tbl",
    },
    perfexUrl: process.env.PERFEX_URL,
};

export function getTableName(table: string): string {
    return `${config.db.prefix}${table}`;
}
