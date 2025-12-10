import * as mysql from "mysql2/promise";
import { config } from "./config";

let pool: mysql.Pool | null = null;

export async function getConnection(): Promise<mysql.Pool> {
    if (!pool) {
        pool = mysql.createPool({
            host: config.db.host,
            port: config.db.port,
            database: config.db.database,
            user: config.db.user,
            password: config.db.password,
            waitForConnections: true,
            connectionLimit: 10,
            queueLimit: 0,
        });
    }
    return pool;
}

export async function query<T = any>(
    sql: string,
    params?: any[]
): Promise<T[]> {
    const conn = await getConnection();
    const [rows] = await conn.execute(sql, params);
    return rows as T[];
}

export async function queryOne<T = any>(
    sql: string,
    params?: any[]
): Promise<T | null> {
    const results = await query<T>(sql, params);
    return results.length > 0 ? results[0] : null;
}

export async function execute(
    sql: string,
    params?: any[]
): Promise<mysql.ResultSetHeader> {
    const conn = await getConnection();
    const [result] = await conn.execute(sql, params);
    return result as mysql.ResultSetHeader;
}

export async function closeConnection(): Promise<void> {
    if (pool) {
        await pool.end();
        pool = null;
    }
}
