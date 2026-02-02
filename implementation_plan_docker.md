# Docker Configuration Update

To permanently enable printing support (`lp` command), we need to modify the Docker setup.

## Changes

### 1. Published Dockerfiles
Moved Dockerfiles from `vendor/` to `./docker/` to allow customization.

### 2. Modified `docker/8.4/Dockerfile`
Added installation of `cups-client`:
```dockerfile
RUN apt-get update \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python2 dnsutils librsvg2-bin fswatch ffmpeg nano cups-client  \
    && ...
```

### 3. Updated `compose.yaml`
Pointed the build context to the new local Dockerfile:
```yaml
services:
    laravel.test:
        build:
            context: './docker/8.4'
```

## How to Apply
Run the following command in your terminal to rebuild the container:
```bash
./vendor/bin/sail build --no-cache
```
Then restart the containers:
```bash
./vendor/bin/sail up -d
```
