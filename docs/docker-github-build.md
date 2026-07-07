# Docker GitHub 构建说明

这套配置的目标是：GitHub Actions 负责构建镜像并推送到 GHCR，本地服务器只拉取镜像运行，避免在慢机器上编译 PHP 扩展和前端资源。

## 1. GitHub 构建镜像

推送到 `dev`、`main`、`master` 或创建 `v*` tag 后，会触发 `.github/workflows/docker-image.yml`。

镜像默认推送到：

```bash
ghcr.io/inspoaibox/lsky-pro:latest
```

也可以在 GitHub Actions 页面手动运行 `Docker Image` workflow。

## 2. 服务器运行

复制环境示例：

```bash
cp .env.docker.example .env.docker
```

编辑 `.env.docker`，至少替换数据库密码：

```bash
MYSQL_DATABASE=lsky_prod
MYSQL_USER=lsky_app
MYSQL_PASSWORD=<long-random-password>
MYSQL_ROOT_PASSWORD=<different-long-random-password>
```

Linux 服务器可以用下面的命令生成密码：

```bash
openssl rand -base64 32
```

如果仓库或 GHCR package 是私有的，先登录：

```bash
docker login ghcr.io -u <github-user>
```

拉取并启动：

```bash
docker compose --env-file .env.docker pull
docker compose --env-file .env.docker up -d
```

默认访问地址：

```text
http://localhost:8080
```

安装页面会自动带出 `.env.docker` 中的数据库配置。正常情况下只需要填写管理员邮箱和密码即可。

如果页面没有自动带出，数据库信息可手动使用：

```text
Host: db
Port: 3306
Database: lsky_prod
Username: lsky_app
Password: 你在 .env.docker 中设置的 MYSQL_PASSWORD
```

生产环境请务必修改 `.env.docker` 里的数据库密码和 `APP_URL`。

## 3. 更新镜像

GitHub 构建完成后，服务器执行：

```bash
docker compose --env-file .env.docker pull app
docker compose --env-file .env.docker up -d app
```

运行数据保存在 Docker volumes 中，包括数据库、`storage`、`.env` 和 `installed.lock`。
