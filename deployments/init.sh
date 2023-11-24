#!/bin/bash

# 文件路径
env_file=".env"
json_file="seaweedfs-s3.json"

# 默认的 S3 credentials
default_s3_access_key="jnoj_access_key1"
default_s3_secret_key="jnoj_secret_key1"

# 生成一个随机字符串
generate_random_string() {
    cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w ${1:-32} | head -n 1
}

# 更新 .env 文件中的变量
update_env_var() {
    local var_name="$1"
    local new_value="$2"
    # 使用 sed 来更新 .env 文件
    sed -i "s|^$var_name=.*|$var_name=$new_value|" "$env_file"
}

# 使用 sed 替换 JSON 文件中的默认 credentials
update_json_credentials() {
    local new_access_key="$1"
    local new_secret_key="$2"
    # 替换 accessKey 和 secretKey
    sed -i "s/$default_s3_access_key/$new_access_key/" "$json_file"
    sed -i "s/$default_s3_secret_key/$new_secret_key/" "$json_file"
}

# 检查 JSON 文件中的 credentials 是否是默认值
credentials_are_default() {
    # 检查完整的 accessKey 和 secretKey 字符串
    if grep -q "\"accessKey\": \"$default_s3_access_key\"" "$json_file" && \
       grep -q "\"secretKey\": \"$default_s3_secret_key\"" "$json_file"; then
        return 0 # 默认值
    else
        return 1 # 已被修改
    fi
}

# 1. 随机生成 JWT_SECRET 并更新（如果未修改过）
if credentials_are_default; then
    echo "Updating credentials as they are default."

    new_jwt_secret=$(generate_random_string 32)
    new_s3_secret_id=$(generate_random_string 32)
    new_s3_secret_key=$(generate_random_string 32)

    update_env_var "JWT_SECRET" "$new_jwt_secret"
    update_env_var "S3_SECRET_ID" "$new_s3_secret_id"
    update_env_var "S3_SECRET_KEY" "$new_s3_secret_key"

    # 也更新 JSON 文件中的 credentials
    update_json_credentials "$new_s3_secret_id" "$new_s3_secret_key"
else
    echo "Credentials have been modified. No update needed."
fi

# 2. 从用户输入获取新的域名并更新 JNOJ_HOST
# 从用户输入获取新的域名并更新 JNOJ_HOST
read -p "Enter new domain for JNOJ_HOST (eg: www.jnoj.dev): " new_domain
read -p "Does your domain support HTTPS? (y/n): " use_https

# 根据用户输入决定是使用 http 还是 https
if [[ $use_https =~ ^[Yy]$ ]]; then
    protocol="https://"
    ws_protocol="wss://"
else
    protocol="http://"
    ws_protocol="ws://"
fi

# 更新 JNOJ_HOST 和 JNOJ_WS_HOST
update_env_var "JNOJ_HOST" "${protocol}${new_domain}"
update_env_var "JNOJ_WS_HOST" "${ws_protocol}${new_domain}"

echo "Updated settings as needed."
