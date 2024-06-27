
<h1 align="left"><a href="https://glass.balala.one/">玻璃行业订单管理系统</a></h1>

## 安装
```
git clone git@github.com:keyend/glass-oa.git
```

## Requirement
1. 运行环境要求PHP7.1+，兼容PHP8.0。
2. Redis
3. 添加守护进程
4. 基于tp6
```
php think queue:listen --queue JobManager_102
```

![仪表盘](/public/static/snap/01.png)
![管理中心](/public/static/snap/02.png)
![日志记录](/public/static/snap/03.png)
![客户管理](/public/static/snap/04.png)
![订单管理](/public/static/snap/05.png)
![录入订单](/public/static/snap/06.png)
![汇总](/public/static/snap/07.png)

## 扩展

如需更多支持，请联系 keyend@163.com