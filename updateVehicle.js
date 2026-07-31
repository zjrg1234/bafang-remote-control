require('dotenv').config();
const mysql = require('mysql2/promise');
const Redis = require('ioredis');

// 1. 初始化 Redis 客户端
const redis = new Redis({
    host: process.env.REDIS_HOST || '127.0.0.1',
    port: process.env.REDIS_PORT || 6379,
    password: process.env.REDIS_PASSWORD || null,
});

// 2. 初始化 MySQL 连接池 (自动复用 TCP 连接)
const pool = mysql.createPool({
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.DB_USERNAME || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_DATABASE || 'laravel',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// 封装非阻塞的 sleep 函数
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

async function main() {
    console.log('[Monitor] 车辆状态同步守护进程已启动...');
    console.log(`[Info] 当前连接数据库: ${process.env.DB_DATABASE}`);

    try {
        while (true) {
            // 1. 检查全局关闭开关 (对应 Laravel 里的 Redis::get('close'))
            const isClosed = await redis.get('close');
            if (isClosed) {
                console.log('[Monitor] 检测到 close 信号，手动结束更新车辆信息。');
                process.exit(0);
            }

            // 2. 动态获取场地 (移入循环，实现场地新增的“热更新”)
            const [venues] = await pool.query(`
                SELECT id FROM agent_venues
                WHERE agent_id IN (SELECT id FROM cuser_agents)
            `);

            const venueIds = venues.map(v => v.id);

            // 如果场地为空，挂起等待，不要退出进程
            if (venueIds.length === 0) {
                await sleep(3000);
                continue;
            }

            // 3. 获取场地下的所有车辆 (合并查询，规避 Laravel N+1 性能问题)
            const [vehicles] = await pool.query(
                'SELECT id, receiver_id, vehicle_state FROM vehicles WHERE venue_id IN (?)',
                [venueIds]
            );

            if (vehicles.length > 0) {
                // 并发执行 Redis 查询与状态比对
                const updatePromises = vehicles.map(async (vehicle) => {
                    const redisKey = `${vehicle.receiver_id}_receiver`;
                    const status = await redis.get(redisKey);

                    let targetState = vehicle.vehicle_state;

                    if (status) {
                        try {
                            const json = JSON.parse(status);
                            if (json.receiver_id && vehicle.vehicle_state === 0) {
                                targetState = 1;
                            } else if (!json.receiver_id) {
                                targetState = 0;
                            }
                        } catch (e) {
                            console.error(`[Error] 车辆 ${vehicle.id} Redis JSON 解析失败:`, e);
                        }
                    } else {
                        targetState = 0;
                    }

                    // 4. 脏检查：只有状态真正发生改变才执行 UPDATE
                    if (targetState !== vehicle.vehicle_state) {
                        await pool.query(
                            'UPDATE vehicles SET vehicle_state = ? WHERE id = ?',
                            [targetState, vehicle.id]
                        );
                        console.log(`[${new Date().toLocaleString()}] 车辆 ${vehicle.id} 状态流转: ${vehicle.vehicle_state} -> ${targetState}`);
                    }
                });

                await Promise.all(updatePromises);
            }

            // 5. 挂起 3 秒
            await sleep(3000);
        }

    } catch (error) {
        console.error('[Fatal Error] 脚本执行致命异常:', error);
        process.exit(1); // 抛出错误码，让 PM2 自动重启它
    }
}

// 优雅关闭逻辑 (处理 PM2 的 reload/stop 信号)
process.on('SIGINT', async () => {
    console.log('正在优雅关闭数据库与 Redis 连接...');
    await pool.end();
    redis.quit();
    process.exit(0);
});

// 启动
main();
