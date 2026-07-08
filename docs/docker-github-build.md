# Docker 部署

镜像地址：

```text
ghcr.io/inspoaibox/lksypro-mc:latest
```

## 首次安装

先在 GitHub Actions 手动运行 `Docker Image`，或推送代码触发构建。构建完成后，在服务器执行：

```bash
cp .env.docker.example .env.docker
openssl rand -base64 32
openssl rand -base64 32
openssl rand -base64 32
```

编辑 `.env.docker`，填入数据库密码、Redis 密码和站点地址：

```env
APP_URL=https://你的域名
SESSION_DRIVER=redis
REDIS_PASSWORD=随机密码
MYSQL_DATABASE=lsky_prod
MYSQL_USER=lsky_app
MYSQL_PASSWORD=随机密码
MYSQL_ROOT_PASSWORD=另一个随机密码
```

私有镜像先登录 GHCR：

```bash
docker login ghcr.io -u <github-user>
```

启动：

```bash
docker compose --env-file .env.docker pull
docker compose --env-file .env.docker up -d
```

访问 `APP_URL`，安装页会自动带出数据库配置，正常只需要填写管理员邮箱和密码。

如果数据库字段没有自动带出，手动填写：

```text
Host: db
Port: 3306
Database: lsky_prod
Username: lsky_app
Password: .env.docker 里的 MYSQL_PASSWORD
```

## 更新

GitHub Actions 构建新镜像后，在服务器执行：

```bash
git pull
docker compose --env-file .env.docker pull
docker compose --env-file .env.docker up -d --force-recreate
docker compose --env-file .env.docker exec app php artisan migrate --force
docker compose --env-file .env.docker exec app php artisan optimize:clear
```

可选清理旧镜像：

```bash
docker image prune -f
```

## 注意

数据保存在 Docker volumes：数据库、`storage`、`.env`、`installed.lock`。

不要执行：

```bash
docker compose down -v
```

这个命令会删除数据库和上传文件。
