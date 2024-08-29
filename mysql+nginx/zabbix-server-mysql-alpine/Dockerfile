FROM alpine:3.18

ARG ZBX_SOURCES=https://git.zabbix.com/scm/zbx/zabbix.git
ARG ZBX_BRANCH=release/6.0

RUN apk add \
    --no-cache \
    --clean-protected \
    bash \
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
    mysql-dev \
    net-snmp-dev \
    openipmi-dev \
    openldap-dev \
    openssl-dev \
    patch \
    pcre2-dev \
    postgresql-dev \
    openjdk8 \
    gettext \
    git \
    g++ \
    go \
    make \
    openjdk8-jre-base \
    unixodbc-dev
RUN rm -rf /var/cache/apk/*

RUN cd /tmp/ && \
    git -c advice.detachedHead=false clone ${ZBX_SOURCES} --branch ${ZBX_BRANCH} --depth 1 --single-branch /tmp/zabbix-branch && \
    cd /tmp/zabbix-branch && \
    zabbix_revision=`git rev-parse --short HEAD` && \
    sed -i "s/{ZABBIX_REVISION}/$zabbix_revision/g" include/version.h && \
    sed -i "s/{ZABBIX_REVISION}/$zabbix_revision/g" src/go/pkg/version/version.go && \
    sed -i "s/{ZABBIX_REVISION}/$zabbix_revision/g" src/zabbix_java/src/com/zabbix/gateway/GeneralInformation.java && \
    ./bootstrap.sh && \
    export CFLAGS="-fPIC -pie -Wl,-z,relro,-z,now,-z,defs" && \
    export CFLAGS="$CFLAGS -D_FORTIFY_SOURCE=2 -fexceptions -O2 -pipe" && \
    ./configure \
            --datadir=/usr/lib \
            --libdir=/usr/lib/zabbix \
            --prefix=/usr \
            --sysconfdir=/etc/zabbix \
            --enable-agent \
            --enable-agent2 \
            --enable-ipv6 \
            --enable-proxy \
            --enable-server \
            --enable-webservice \
            --with-ldap \
            --with-libcurl \
            --with-libmodbus \
            --with-libpcre2 \
            --with-libxml2 \
            --with-mysql \
            --with-net-snmp \
            --with-openipmi \
            --with-openssl \
            --with-ssh \
            --with-unixodbc \
            $additional_components \
            --silent && \
    make -j"$(nproc)" -s dbschema && \
    make -j"$(nproc)" -s && \
    make -j"$(nproc)" -s gettext && \
    cat database/mysql/schema.sql > database/mysql/create.sql && \
    cat database/mysql/images.sql >> database/mysql/create.sql && \
    cat database/mysql/data.sql >> database/mysql/create.sql && \
    gzip -c database/mysql/create.sql > database/mysql/create_server.sql.gz && \
    rm -rf database/mysql/create.sql && \
    cat database/mysql/schema.sql > database/mysql/create.sql && \
    gzip -c database/mysql/create.sql > database/mysql/create_proxy.sql.gz && \
    rm -rf database/mysql/create.sql && \
    strip /tmp/zabbix-branch/src/zabbix_agent/zabbix_agentd && \
    strip /tmp/zabbix-branch/src/zabbix_server/zabbix_server && \
    strip /tmp/zabbix-branch/src/zabbix_proxy/zabbix_proxy && \
    strip /tmp/zabbix-branch/src/go/bin/zabbix_agent2 && \
    strip /tmp/zabbix-branch/src/zabbix_get/zabbix_get && \
    strip /tmp/zabbix-branch/src/zabbix_sender/zabbix_sender && \
    strip /tmp/zabbix-branch/src/go/bin/zabbix_web_service && \
    rm -rf $(go env GOCACHE)

STOPSIGNAL SIGTERM

RUN cp /tmp/zabbix-branch/src/zabbix_server/zabbix_server /usr/sbin/zabbix_server
RUN cp /tmp/zabbix-branch/src/zabbix_get/zabbix_get /usr/bin/zabbix_get
RUN cp /tmp/zabbix-branch/src/zabbix_sender/zabbix_sender /usr/bin/zabbix_sender
RUN mkdir /etc/zabbix && cp /tmp/zabbix-branch/conf/zabbix_server.conf /etc/zabbix/zabbix_server.conf
RUN mkdir /usr/share/doc && mkdir /usr/share/doc/zabbix-server-mysql && cp /tmp/zabbix-branch/database/mysql/create_server.sql.gz /usr/share/doc/zabbix-server-mysql/create.sql.gz

RUN set -eux && \
    INSTALL_PKGS="bash \
            tini \
            fping \
            sudo \
            traceroute \
            nmap \
            tzdata \
            iputils \
            libcurl \
            libevent \
            libldap \
            libssh \
            libxml2 \
            mariadb-client \
            mariadb-connector-c \
            net-snmp-agent-libs \
            openipmi-libs \
            pcre2 \
            unixodbc" && \
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
    adduser zabbix root && \
    adduser zabbix dialout && \
    echo "zabbix ALL=(root) NOPASSWD: /usr/bin/nmap" >> /etc/sudoers.d/zabbix && \
    mkdir -p /etc/zabbix && \
    mkdir -p /var/lib/zabbix && \
    mkdir -p /usr/lib/zabbix/alertscripts && \
    mkdir -p /usr/lib/zabbix/dbscripts && \
    mkdir -p /var/lib/zabbix/dbscripts && \
    mkdir -p /var/lib/zabbix/enc && \
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
    mkdir -p /usr/share/doc/zabbix-server-mysql && \
    chown --quiet -R zabbix:root /etc/zabbix/ /var/lib/zabbix/ && \
    chgrp -R 0 /etc/zabbix/ /var/lib/zabbix/ && \
    chmod -R g=u /etc/zabbix/ /var/lib/zabbix/ && \
    rm -rf /var/cache/apk/*

EXPOSE 10051/TCP

WORKDIR /var/lib/zabbix

VOLUME ["/var/lib/zabbix/snmptraps", "/var/lib/zabbix/export"]

COPY ["docker-entrypoint.sh", "/usr/bin/"]

RUN ["chmod", "+x", "/usr/bin/docker-entrypoint.sh"]

ENTRYPOINT ["/sbin/tini", "--", "/usr/bin/docker-entrypoint.sh"]

USER 1997

CMD ["/usr/sbin/zabbix_server", "--foreground", "-c", "/etc/zabbix/zabbix_server.conf"]