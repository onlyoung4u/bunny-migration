# bunny-miration

## 迁移
```shell

# 创建迁移文件
php webman migrate:create create_users_table

# 执行迁移
php webman migrate:run
--database dev # 指定数据库连接
--force # 强制执行迁移
--pretend # 模拟执行迁移
--step # 强制把每个迁移文件会记录为单独的批次

# 回滚迁移
php webman migrate:rollback
--database dev # 指定数据库连接
--force # 强制回滚迁移
--pretend # 模拟回滚迁移
--step 2 # 每次迁移的步数，默认回滚上一次迁移
--batch 1 # 回滚指定的批次

# 重置迁移
php webman migrate:reset
--database dev # 指定数据库连接
--force # 强制重置迁移
--pretend # 模拟重置迁移

# 刷新迁移
php webman migrate:refresh
--database dev # 指定数据库连接
--force # 强制刷新迁移
--seed # 刷新迁移后执行数据填充
--seeder UserSeeder # 指定数据填充类

# 查看迁移状态
php webman migrate:status
--database dev # 指定数据库连接

```

## 数据填充
```shell

# 创建数据填充文件
php webman seed:create UserSeeder

# 执行数据填充
php webman seed:run
--database dev # 指定数据库连接
--force # 强制执行数据填充
--class UserSeeder # 指定数据填充类

```