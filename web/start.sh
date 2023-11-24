#!/bin/sh

# 将环境变量的值写入 .env.local 文件
echo "API_BASE_URL=$API_BASE_URL" >> .env.local
echo "API_WS_URL=$API_WS_URL" >> .env.local
echo "ADMIN_API_BASE_URL=$ADMIN_API_BASE_URL" >> .env.local

# 启动应用程序
yarn start
