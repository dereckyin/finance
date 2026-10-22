# 家庭速記簿

個人記帳網站。

## 取得程式

```bash
git clone https://github.com/dereckyin/finance.git
cd finance
```

## 啟動

在專案目錄建立 `.env`，金鑰至少 32 個字元：

```
JWT_SECRET=請換成一組隨機字串
```

安裝 PHP 相依套件後，只啟動網站服務：

```bash
docker run --rm -v ./app:/app composer:2 install --working-dir=/app
docker compose up -d --no-deps php
```

瀏覽器開啟 http://localhost:8080 。
