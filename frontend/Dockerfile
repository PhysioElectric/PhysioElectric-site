FROM nginx:alpine

# جایگزینی کانفیگ اختصاصی Nginx
COPY default.conf /etc/nginx/conf.d/default.conf

# کپی فایل‌های فرانت به مسیر استاندارد
COPY . /usr/share/nginx/html

EXPOSE 80