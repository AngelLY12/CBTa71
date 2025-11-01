FROM nginx:alpine

# Copia la configuración de Nginx
COPY ./default.conf /etc/nginx/conf.d/default.conf

EXPOSE 8080
CMD ["nginx", "-g", "daemon off;"]
