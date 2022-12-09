FROM ubuntu:22.04

COPY docker/sources.list /etc/apt/sources.list

RUN apt-get update && \
    apt-get install -y --no-install-recommends ca-certificates wget make gcc g++ openjdk-11-jdk python3 \
    libmysqlclient-dev libmysql++-dev
RUN mkdir -p /sandbox
WORKDIR /sandbox
