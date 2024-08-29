FROM alpine:3.20

ARG ZBX_SOURCES=https://git.zabbix.com/scm/zbx/zabbix.git
ARG ZBX_BRANCH=release/7.0

RUN set -eux && \
    ARCH_SUFFIX="$(cat /etc/apk/arch)"; \
    case "$ARCH_SUFFIX" in \
    ppc64le) \
    openjdk_packages='openjdk11 openjdk11-jre-headless'; \
    ;; \
    *) openjdk_packages='openjdk17 openjdk17-jre-headless'; \
    esac; \
    INSTALL_PKGS="bash \
    autoconf \
    automake \
    coreutils \
    curl \
    curl-dev \
    libevent-dev \
    libssh-dev \
    sqlite-dev \
    libmodbus-dev \
    libxml2-dev \
    linux-headers \
    mysql-dev \
    net-snmp-dev \
    openipmi-dev \
    openldap-dev \
    openssl-dev \
    patch \
    pcre2-dev \
    libpq-dev \
    gettext \
    git \
    g++ \
    go \
    make \
    $openjdk_packages \
    unixodbc-dev \
    zlib-dev" && \
    apk add \
    --no-cache \
    --clean-protected \
    ${INSTALL_PKGS}

RUN set -eux && \
    cd /tmp/ && \
    mkdir -p /tmp/zabbix-output/agent/sbin/ && \
    mkdir -p /tmp/zabbix-output/agent/conf/ && \
    mkdir -p /tmp/zabbix-output/agent2/sbin/ && \
    mkdir -p /tmp/zabbix-output/agent2/conf/ && \
    mkdir -p /tmp/zabbix-output/agent2/conf/zabbix_agent2.d/plugins.d/ && \
    mkdir -p /tmp/zabbix-output/proxy/sbin/ && \
    mkdir -p /tmp/zabbix-output/proxy/conf/ && \
    mkdir -p /tmp/zabbix-output/proxy/database/postgresql/ && \
    mkdir -p /tmp/zabbix-output/server/database/postgresql/option-patches/with-compression/ && \
    mkdir -p /tmp/zabbix-output/server/database/postgresql/option-patches/without-compression/ && \
    mkdir -p /tmp/zabbix-output/server/sbin/ && \
    mkdir -p /tmp/zabbix-output/server/conf/ && \
    mkdir -p /tmp/zabbix-output/server/database/postgresql/ && \
    mkdir -p /tmp/zabbix-output/java_gateway/sbin/ && \
    mkdir -p /tmp/zabbix-output/web_service/sbin/ && \
    mkdir -p /tmp/zabbix-output/web_service/conf/ && \
    mkdir -p /tmp/zabbix-output/general/sbin/ && \
    mkdir -p /tmp/zabbix-output/general/bin/ && \
    mkdir -p /tmp/zabbix-output/general/conf/ && \
    if [ -f "/tmp/src/bootstrap.sh" ]; then \
    cp -R /tmp/src /tmp/zabbix; \
    else \
    git -c advice.detachedHead=false clone ${ZBX_SOURCES} --branch ${ZBX_BRANCH} --depth 1 --single-branch /tmp/zabbix; \
    fi && \
    cd /tmp/zabbix && \
    zabbix_revision=`git rev-parse --short HEAD` && \
    sed -i "s/{ZABBIX_REVISION}/$zabbix_revision/g" include/version.h && \
    sed -i "s/{ZABBIX_REVISION}/$zabbix_revision/g" src/go/pkg/version/version.go && \
    sed -i "s/{ZABBIX_REVISION}/$zabbix_revision/g" src/zabbix_java/src/com/zabbix/gateway/GeneralInformation.java && \
    ./bootstrap.sh && \
    ./configure \
    --datadir=/usr/lib \
    --libdir=/usr/lib/zabbix \
    --prefix=/usr \
    --sysconfdir=/etc/zabbix \
    --enable-ipv6 \
    --enable-server \
    --with-ldap \
    --with-libcurl \
    --with-libmodbus \
    --with-libpcre2 \
    --with-libxml2 \
    --with-postgresql \
    --with-net-snmp \
    --with-openipmi \
    --with-openssl \
    --with-ssh \
    --with-unixodbc \
    --silent && \
    make -j"$(nproc)" -s dbschema && \
    make -j"$(nproc)" -s && \
    make -j"$(nproc)" -s gettext && \
    cat database/postgresql/schema.sql > database/postgresql/create.sql && \
    gzip -c database/postgresql/create.sql > /tmp/zabbix-output/proxy/database/postgresql/create.sql.gz && \
    cat database/postgresql/images.sql >> database/postgresql/create.sql && \
    cat database/postgresql/data.sql >> database/postgresql/create.sql && \
    gzip -c database/postgresql/create.sql > /tmp/zabbix-output/server/database/postgresql/create.sql.gz && \
    rm -rf database/postgresql/create.sql && \
    cp -R /tmp/zabbix/database/postgresql/timescaledb/schema.sql /tmp/zabbix-output/server/database/postgresql/timescaledb.sql && \
    cp -R /tmp/zabbix/database/postgresql/timescaledb/option-patches/with-compression/*.sql /tmp/zabbix-output/server/database/postgresql/option-patches/with-compression/ && \
    cp -R /tmp/zabbix/database/postgresql/timescaledb/option-patches/without-compression/*.sql /tmp/zabbix-output/server/database/postgresql/option-patches/without-compression/ && \
    mkdir /tmp/fonts/ && \
    curl --tlsv1.2 -sSf -L "https://noto-website-2.storage.googleapis.com/pkgs/NotoSansCJKjp-hinted.zip" -o /tmp/fonts/NotoSansCJKjp-hinted.zip && \
    unzip /tmp/fonts/NotoSansCJKjp-hinted.zip -d /tmp/fonts/ && \
    cp /tmp/fonts/NotoSansCJKjp-Regular.otf /tmp/zabbix/ui/assets/fonts/NotoSansCJKjp-Regular.ttf && \
    cp /tmp/fonts/LICENSE_OFL.txt /tmp/zabbix/ui/assets/fonts/ && \
    chmod o+r /tmp/zabbix/ui/assets/fonts/* && \
    strip /tmp/zabbix/src/zabbix_server/zabbix_server && \
    cp /tmp/zabbix/src/zabbix_server/zabbix_server /tmp/zabbix-output/server/sbin/ && \
    cp /tmp/zabbix/conf/zabbix_server.conf /tmp/zabbix-output/server/conf/ && \
    make -s distclean

RUN set -eux && \
    INSTALL_PKGS="bash \
    traceroute \
    nmap \
    fping \
    tzdata \
    iputils \
    libcap \
    libcurl \
    libevent \
    libldap \
    libssh \
    libxml2 \
    net-snmp-agent-libs \
    openipmi-libs \
    pcre2 \
    postgresql16-client \
    gzip \
    unixodbc \
    zlib" && \
    apk add \
    --no-cache \
    --clean-protected \
    ${INSTALL_PKGS} && \
    addgroup \
    --system \
    --gid 1995 \
    zabbix && \
    adduser \
    --system \
    --gecos "Zabbix monitoring system" \
    --disabled-password \
    --uid 1997 \
    --ingroup zabbix \
    --shell /sbin/nologin \
    --home /var/lib/zabbix/ \
    zabbix && \
    adduser zabbix dialout && \
    chgrp zabbix /usr/bin/nmap && \
    setcap cap_net_raw+eip /usr/bin/nmap && \
    mkdir -p /etc/zabbix && \
    mkdir -p /var/lib/zabbix && \
    mkdir -p /usr/lib/zabbix/alertscripts && \
    mkdir -p /var/lib/zabbix/dbscripts && \
    mkdir -p /var/lib/zabbix/enc && \
    mkdir -p /var/lib/zabbix/enc_internal && \
    mkdir -p /var/lib/zabbix/export && \
    mkdir -p /usr/lib/zabbix/externalscripts && \
    mkdir -p /var/lib/zabbix/mibs && \
    mkdir -p /var/lib/zabbix/modules && \
    mkdir -p /var/lib/zabbix/snmptraps && \
    mkdir -p /var/lib/zabbix/ssh_keys && \
    mkdir -p /var/lib/zabbix/ssl && \
    mkdir -p /var/lib/zabbix/ssl/certs && \
    mkdir -p /var/lib/zabbix/ssl/keys && \
    mkdir -p /var/lib/zabbix/ssl/ssl_ca && \
    mkdir -p /usr/share/doc/zabbix-server-postgresql && \
    chown --quiet -R zabbix:root /etc/zabbix/ /var/lib/zabbix/ && \
    chgrp -R 0 /etc/zabbix/ /var/lib/zabbix/ && \
    chmod -R g=u /etc/zabbix/ /var/lib/zabbix/

EXPOSE 10051/TCP

WORKDIR /var/lib/zabbix

VOLUME ["/var/lib/zabbix/snmptraps", "/var/lib/zabbix/export"]

COPY ["docker-entrypoint.sh", "/usr/bin/"]

ENTRYPOINT ["/usr/bin/docker-entrypoint.sh"]

USER 1997

CMD ["/usr/sbin/zabbix_server", "--foreground", "-c", "/etc/zabbix/zabbix_server.conf"]