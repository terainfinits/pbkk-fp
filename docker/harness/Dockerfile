# Fitur: Harness tugas coding
# connect with ../../docs/HARNESS.md
FROM php:8.4-cli-alpine
WORKDIR /tmp
USER 65534:65534
ENTRYPOINT ["sh", "-ec", "cp -R /source/. /tmp/project && cd /tmp/project && exec timeout 85 php tests.php"]
