# Lsky Pro MC

基于 Lsky Pro 的图床项目维护版本。当前部署方式以 Docker 为主，镜像由 GitHub Actions 构建，本地服务器只负责拉取和运行。

## 镜像地址

```text
ghcr.io/inspoaibox/lksypro-mc:latest
```

## 构建方式

推送代码到 `dev`、`main`、`master`，或创建 `v*` 标签后，会触发 GitHub Actions 的 `Docker Image` 工作流。

也可以在 GitHub Actions 页面手动运行 `Docker Image`。

## 首次安装

先把部署文件放到服务器。推荐直接 clone 仓库：

```bash
git clone https://github.com/inspoaibox/lksypro-mc.git
cd lksypro-mc
```

如果服务器不放完整代码，SQLite 方式至少上传：

```text
docker-compose.sqlite.yml
.env.sqlite.example
```

MySQL 方式至少上传：

```text
docker-compose.yml
.env.docker.example
```

### SQLite 快速安装

SQLite 不需要数据库服务器，最简单。数据文件保存在 Docker volume 的 `storage/runtime/database.sqlite`。

```bash
cp .env.sqlite.example .env.sqlite
```

编辑 `.env.sqlite`：

```env
LSKY_IMAGE=ghcr.io/inspoaibox/lksypro-mc:latest
APP_PORT=8080
APP_URL=http://服务器IP:8080

SQLITE_DATABASE=/var/www/html/storage/runtime/database.sqlite
```

启动：

```bash
docker compose -f docker-compose.sqlite.yml --env-file .env.sqlite pull
docker compose -f docker-compose.sqlite.yml --env-file .env.sqlite up -d
```

浏览器访问：

```text
http://服务器IP:8080
```

安装页选择 `SQLite`，正常只需要填写管理员邮箱和密码。

### MySQL 安装

MySQL 更适合长期生产使用，当前默认 `docker-compose.yml` 会内置 MySQL 8。

如果服务器不放完整代码，至少要上传：

```text
docker-compose.yml
.env.docker.example
```

`.env.docker.example` 是模板文件，复制一份作为服务器自己的配置：

```bash
cp .env.docker.example .env.docker
```

生成两个随机密码：

```bash
openssl rand -base64 32
openssl rand -base64 32
```

编辑 `.env.docker`：

```env
LSKY_IMAGE=ghcr.io/inspoaibox/lksypro-mc:latest
APP_PORT=8080
APP_URL=http://服务器IP:8080

MYSQL_DATABASE=lsky_prod
MYSQL_USER=lsky_app
MYSQL_PASSWORD=上面生成的随机密码
MYSQL_ROOT_PASSWORD=另一个随机密码
```

如果 GHCR 镜像是私有的，先登录：

```bash
docker login ghcr.io -u <github-user>
```

拉取并启动：

```bash
docker compose --env-file .env.docker pull
docker compose --env-file .env.docker up -d
```

浏览器访问：

```text
http://服务器IP:8080
```

如果要用 80 端口：

```env
APP_PORT=80
APP_URL=http://服务器IP
```

没有配置反向代理和 HTTPS 证书时，不要把 `APP_URL` 写成 `https://...`。

Docker 默认使用内置 MySQL。安装页会自动带出数据库类型、地址、端口、库名和用户名，数据库密码可以留空使用 `.env.docker` 里的 `MYSQL_PASSWORD`，正常只需要填写管理员邮箱和密码。

如果数据库字段没有自动带出，手动填写：

```text
Host: db
Port: 3306
Database: lsky_prod
Username: lsky_app
Password: 留空，或填写 .env.docker 里的 MYSQL_PASSWORD
```

镜像会检查当前 PHP 驱动后显示可用数据库：

```text
MySQL: Docker 默认，推荐使用
PostgreSQL: 需要自己准备 PostgreSQL 数据库
SQLite: 最简单，不需要数据库服务器
SQL Server: 当前 Docker 镜像未内置 pdo_sqlsrv，安装页会禁用
```

## 更新教程

先在 GitHub Actions 构建新镜像，然后服务器执行：

```bash
docker compose --env-file .env.docker pull app
docker compose --env-file .env.docker up -d app
docker compose --env-file .env.docker exec app php artisan migrate --force
docker compose --env-file .env.docker exec app php artisan optimize:clear
```

可选清理旧镜像：

```bash
docker image prune -f
```

## 常用命令

查看容器：

```bash
docker compose --env-file .env.docker ps
```

查看日志：

```bash
docker compose --env-file .env.docker logs -f app
```

进入应用容器：

```bash
docker compose --env-file .env.docker exec app bash
```

重启：

```bash
docker compose --env-file .env.docker restart app
```

## 数据持久化

数据库、上传文件、`.env`、`installed.lock` 都保存在 Docker volumes 中。

不要执行：

```bash
docker compose down -v
```

这个命令会删除数据库和上传文件。

## 无法访问排查

先在服务器本机测试：

```bash
curl -I http://127.0.0.1:8080
```

查看端口映射：

```bash
docker compose --env-file .env.docker ps
```

查看应用日志：

```bash
docker compose --env-file .env.docker logs --tail=100 app
```

如果服务器本机能访问，但外网不能访问，检查防火墙和云服务器安全组，放行 TCP `8080`。

安装页提示“网络异常”通常不是数据库类型选错，而是后端返回了 500 或连接被重置。先执行：

```bash
docker compose --env-file .env.docker logs --tail=100 app
```

如果日志里出现 `Undefined constant "STDIN"`，说明服务器还在运行旧镜像，等 GitHub Actions 构建完成后重新拉取：

```bash
docker compose --env-file .env.docker pull app
docker compose --env-file .env.docker up -d app
```

Ubuntu 防火墙示例：

```bash
ufw allow 8080/tcp
```

## 许可证

GPL-3.0
