# zabbix-docker

Example

Change working directory where Dockerfile presents then:

docker build --build-arg ZBX_BRANCH=master -t zabbix-web-nginx-mysql-master.

change ZBX_BRANCH to branch you need and name of the image.

For ready-to-use project use docker-compose file. Change **ZBX_BRANCH** variable in the file and use **docker compose up -d**

in case if you want update already created container, after changing **ZBX_BRANCH** use **docker-compose up --build**