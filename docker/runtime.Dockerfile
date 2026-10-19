FROM ubuntu:24.04

RUN apt-get update \
    && apt-get install -y --no-install-recommends python3 php-cli nodejs bash util-linux \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /workspace /run

ENV PATH=/usr/bin:/bin HOME=/tmp LANG=C.UTF-8 PYTHONIOENCODING=utf-8
USER 1000:1000
WORKDIR /workspace
