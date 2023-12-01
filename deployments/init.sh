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
    update_env_var "S3_PRIVATE_SECRET_ID" "$new_s3_secret_id"
    update_env_var "S3_PRIVATE_SECRET_KEY" "$new_s3_secret_key"

    update_env_var "S3_PUBLIC_SECRET_ID" "$new_s3_secret_id"
    update_env_var "S3_PUBLIC_SECRET_KEY" "$new_s3_secret_key"

    # 也更新 JSON 文件中的 credentials
    update_json_credentials "$new_s3_secret_id" "$new_s3_secret_key"
else
    echo "Credentials have been modified. No update needed."
fi

# Function to validate the domain/IP input with optional port
validate_domain() {
    local input=$1
    local domain
    local port

    # Split the input into domain and port
    IFS=':' read -r domain port <<< "$input"

    # Check for "127.0.0.1" or "localhost"
    if [[ $domain =~ ^127\.0\.0\.1$ ]] || [[ $domain == "localhost" ]]; then
        echo "127.0.0.1 or localhost is not supported."
        return 1
    fi

    # Regular expression for validating IP address
    local ip_regex='^([0-9]{1,3}\.){3}[0-9]{1,3}$'

    # Regular expression for validating domain
    local domain_regex='^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$'

    # Check if the domain part is a valid IP or domain
    if ! [[ $domain =~ $ip_regex ]] && ! [[ $domain =~ $domain_regex ]]; then
        echo "Invalid domain or IP. Please enter a valid one."
        return 1
    fi

    # Validate the port number if present
    if [[ -n $port ]]; then
        # Regular expression for validating port number
        local port_regex='^([0-9]{1,5})$'

        if ! [[ $port =~ $port_regex ]]; then
            echo "Invalid port number. Please enter a valid one."
            return 1
        fi

        # Check if port number is within valid range (1-65535)
        if ((port < 1 || port > 65535)); then
            echo "Port number is out of range. Please enter a number between 1 and 65535."
            return 1
        fi
    fi

    return 0
}




# 2. 从用户输入获取新的域名并更新 JNOJ_HOST
# Prompt for domain/IP
while true; do
    echo "Enter your domain or your IP for JNOJ_HOST (127.0.0.1 or localhost is not supported)"
    # 通过检查每个接口来获取局域网 IP
    for interface in $(ip -o link show | awk -F': ' '{print $2}')
    do
        IP=$(ip -o -4 addr list $interface | awk '{print $4}' | cut -d/ -f1)
        if [[ -n $IP && $IP != "127.0.0.1" ]]; then
            echo -e "Tip: LAN IP on $interface: your IP may be: \033[0;32m$IP\033[0m"
        fi
    done

    read -p "(eg: www.jnoj.dev or $IP): " new_domain
    if validate_domain "$new_domain"; then
        break
    else
        echo "Invalid domain or IP. Please enter a valid one."
    fi
done

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
# 更新 S3
update_env_var "S3_PRIVATE_ENDPOINT" "${protocol}${new_domain}"
update_env_var "S3_PUBLIC_ENDPOINT" "${protocol}${new_domain}"

echo "Updated settings as needed."
