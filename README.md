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

在服务器准备环境文件：

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
APP_URL=https://你的域名

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

浏览器访问 `APP_URL`。安装页会自动带出数据库配置，正常只需要填写管理员邮箱和密码。

如果数据库字段没有自动带出，手动填写：

```text
Host: db
Port: 3306
Database: lsky_prod
Username: lsky_app
Password: .env.docker 里的 MYSQL_PASSWORD
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

## 许可证

GPL-3.0
