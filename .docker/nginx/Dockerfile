FROM nginx:latest

ARG SERVICE_DIR="./nginx"
COPY ./.shared/scripts/ /tmp/scripts/
RUN chmod -R 777 /tmp/scripts/

# set timezone
ARG TZ=UTC
RUN /tmp/scripts/set_timezone.sh ${TZ}

# add users
ARG APP_USER=www-data
ARG APP_GROUP=www-data
ARG APP_USER_ID=1000
ARG APP_GROUP_ID=1000

RUN /tmp/scripts/create_user.sh ${APP_USER} ${APP_GROUP} ${APP_USER_ID} ${APP_GROUP_ID}

RUN /tmp/scripts/install_software.sh

# nginx config
COPY ${SERVICE_DIR}/nginx.conf /etc/nginx/
ARG APP_USER
RUN /tmp/scripts/modify_config.sh /etc/nginx/nginx.conf \
    "__APP_USER" \
    "${APP_USER}" \
 && /tmp/scripts/modify_config.sh /etc/nginx/nginx.conf \
    "__APP_GROUP" \
    "${APP_GROUP}" \
;

# nginx app config
COPY ${SERVICE_DIR}/sites-available/* /etc/nginx/sites-available/
ARG APP_CODE_PATH
ARG APP_HOST
ARG PMA_HOST
RUN /tmp/scripts/modify_config.sh /etc/nginx/sites-available/default.conf \
    "__NGINX_ROOT" \
    "${APP_CODE_PATH}" \
 && /tmp/scripts/modify_config.sh /etc/nginx/sites-available/default.conf \
    "__NGINX_APP_HOST" \
    "${APP_HOST}" \
 && /tmp/scripts/modify_config.sh /etc/nginx/sites-available/phpmyadmin.conf \
    "__NGINX_PMA_HOST" \
    "${PMA_HOST}" \
;

# workdir
WORKDIR /etc/nginx/

# cleanup
RUN /tmp/scripts/cleanup.sh

CMD ["nginx"]

EXPOSE 80 443