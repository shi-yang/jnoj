# 第一阶段：构建
FROM node:latest as build
WORKDIR /app
COPY package.json yarn.lock ./
ENV NODE_ENV $NODE_ENV
RUN yarn config set sharp_binary_host "https://npmmirror.com/mirrors/sharp"
RUN yarn config set sharp_libvips_binary_host "https://npmmirror.com/mirrors/sharp-libvips"
RUN yarn config set registry 'https://registry.npmmirror.com'
RUN yarn install
COPY . .
RUN yarn build

# 第二阶段：运行
FROM node:alpine
WORKDIR /app
COPY --from=build /app/package.json ./package.json
COPY --from=build /app/next.config.js ./
COPY --from=build /app/public ./public
COPY --from=build /app/.next ./.next
COPY --from=build /app/node_modules ./node_modules

# 复制启动脚本到容器内
COPY start.sh /app/start.sh

# 设置启动脚本为可执行
RUN chmod +x /app/start.sh

EXPOSE 3000

CMD ["./start.sh"]
