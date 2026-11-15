FROM alpine:3.19

# Install PHP, Python 3, and Node.js / npm
RUN apk add --no-cache \
    php \
    php-cli \
    python3 \
    py3-pip \
    nodejs \
    npm

WORKDIR /workspace

CMD ["bash"]
