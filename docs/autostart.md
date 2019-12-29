判题机开机启动
---------

> 以下内容均需要 root 权限。非 root 用户可使用 `sudo`。

### 1. Judge
 
将以下内容保存创建为 `jnoj-judge.service`，保存为 `/etc/systemd/system/jnoj-judge.service`

```
[Unit]
Description=Start JNOJ judge
After=network.target
Wants=mysql.service

[Service]
ExecStart=-/home/judge/jnoj/judge/dispatcher
RemainAfterExit=yes
KillMode=control-group
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
```

### 2. Polygon

将以下内容保存创建为 `jnoj-polygon.service`，保存为 `/etc/systemd/system/jnoj-judge.service`

```
[Unit]
Description=Start JNOJ polygon
After=network.target
Wants=mysql.service

[Service]
ExecStart=-/home/judge/jnoj/polygon/polygon
RemainAfterExit=yes
KillMode=control-group
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
```

### 3. 执行命令
```
systemctl daemon-reload
systemctl enable jnoj-judge
systemctl enable jnoj-polygon
```
