FROM alpine:3.18

ARG ZBX_SOURCES=https://git.zabbix.com/scm/zbx/zabbix.git
ARG ZBX_BRANCH=release/6.0

ENV MONGODB_PLUGIN_SOURCES=https://git.zabbix.com/scm/ap/mongodb.git MONGODB_PLUGIN_VERSION=${ZBX_VERSION} \
    POSTGRESQL_PLUGIN_SOURCES=https://git.zabbix.com/scm/ap/postgresql.git POSTGRESQL_PLUGIN_VERSION=${ZBX_VERSION}

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
            --enable-agent \
            --enable-agent2 \
            --with-libcurl \
            --with-libpcre2 \
            --silent && \
    make -j"$(nproc)" -s && \
    strip /tmp/zabbix-branch/src/go/bin/zabbix_agent2 && \
    cd /tmp/ && \
    git -c advice.detachedHead=false clone ${MONGODB_PLUGIN_SOURCES} --branch ${ZBX_BRANCH} --depth 1 --single-branch /tmp/mongodb_plugin && \
    cd /tmp/mongodb_plugin && \
    make && \
    strip /tmp/mongodb_plugin/zabbix-agent2-plugin-mongodb && \
    cd /tmp/ && \
    git -c advice.detachedHead=false clone ${POSTGRESQL_PLUGIN_SOURCES} --branch ${ZBX_BRANCH} --depth 1 --single-branch /tmp/postgresql_plugin && \
    cd /tmp/postgresql_plugin && \
    make && \
    strip /tmp/postgresql_plugin/zabbix-agent2-plugin-postgresql && \
    rm -rf $(go env GOCACHE)

STOPSIGNAL SIGTERM

RUN cp /tmp/zabbix-branch/src/go/bin/zabbix_agent2 /usr/sbin/zabbix_agent2
RUN cp /tmp/zabbix-branch/src/zabbix_get/zabbix_get /usr/bin/zabbix_get
RUN cp /tmp/zabbix-branch/src/zabbix_sender/zabbix_sender /usr/bin/zabbix_sender
RUN mkdir /etc/zabbix && cp /tmp/zabbix-branch/src/go/conf/zabbix_agent2.conf /etc/zabbix/zabbix_agent2.conf
RUN mkdir /etc/zabbix/zabbix_agent2.d && mkdir /etc/zabbix/zabbix_agent2.d/plugins.d && cp -r /tmp/zabbix-branch/src/go/conf/zabbix_agent2.d/plugins.d /etc/zabbix/zabbix_agent2.d/plugins.d
RUN cp /tmp/mongodb_plugin/mongodb.conf /etc/zabbix/zabbix_agent2.d/plugins.d/mongodb.conf
RUN cp /tmp/postgresql_plugin/postgresql.conf /etc/zabbix/zabbix_agent2.d/plugins.d/postgresql.conf
RUN mkdir /usr/sbin/zabbix-agent2-plugin && mkdir /usr/sbin/zabbix-agent2-plugin/zabbix-agent2-plugin-mongodb && cp -r /tmp/mongodb_plugin/zabbix-agent2-plugin-mongodb /usr/sbin/zabbix-agent2-plugin/zabbix-agent2-plugin-mongodb
RUN mkdir /usr/sbin/zabbix-agent2-plugin/zabbix-agent2-plugin-postgresql && cp -r /tmp/postgresql_plugin/zabbix-agent2-plugin-postgresql /usr/sbin/zabbix-agent2-plugin/zabbix-agent2-plugin-postgresql

RUN set -eux && \
    INSTALL_PKGS="tini \
            tzdata \
            bash \
            pcre2 \
            coreutils \
            smartmontools \
            sudo \
            iputils" && \
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
    mkdir -p /etc/zabbix && \
    mkdir -p /etc/zabbix/zabbix_agentd.d && \
    mkdir -p /var/lib/zabbix && \
    mkdir -p /var/lib/zabbix/enc && \
    mkdir -p /var/lib/zabbix/modules && \
    mkdir -p /var/lib/zabbix/buffer && \
    chown --quiet -R zabbix:root /etc/zabbix/ /var/lib/zabbix/ /usr/sbin/zabbix-agent2-plugin/ && \
    chgrp -R 0 /etc/zabbix/ /var/lib/zabbix/ /usr/sbin/zabbix-agent2-plugin/ && \
    chmod -R g=u /etc/zabbix/ /var/lib/zabbix/ /usr/sbin/zabbix-agent2-plugin/ && \
    rm -rf /var/cache/apk/*

EXPOSE 10050/TCP 31999/TCP

WORKDIR /var/lib/zabbix

COPY ["docker-entrypoint.sh", "/usr/bin/"]

ENTRYPOINT ["/sbin/tini", "--", "/usr/bin/docker-entrypoint.sh"]

USER 1997

CMD ["/usr/sbin/zabbix_agent2", "--foreground", "-c", "/etc/zabbix/zabbix_agent2.conf"]