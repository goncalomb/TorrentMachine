FROM alpine:3.11.6
RUN apk add transmission-daemon

ADD done.sh /root
ADD settings.json /root

CMD mkdir -p /root/.config/transmission-daemon \
    && cp /root/settings.json /root/.config/transmission-daemon/settings.json \
    && transmission-daemon -f
