# zabbix-docker

Example

Change working directory where Dockerfile presents then:

docker build --build-arg ZBX_BRANCH=master -t zabbix-web-nginx-mysql-master .

change ZBX_BRANCH to branch you need

For ready-to-use project use docker-compose file. Change **ZBX_BRANCH** variable across the file and use docker compose up -d