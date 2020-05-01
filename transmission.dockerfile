FROM alpine:3.11.6
RUN apk add transmission-daemon

#RUN mkdir -p /root/.config/transmission-daemon
#RUN echo '{ "rpc-host-whitelist-enabled": false, "rpc-whitelist-enabled": false }' > /root/.config/transmission-daemon/settings.json

#ENTRYPOINT ["transmission-daemon"]
#CMD ["-f"]

CMD mkdir -p /root/.config/transmission-daemon \
    && echo '{ "rpc-host-whitelist-enabled": false, "rpc-whitelist-enabled": false }' > /root/.config/transmission-daemon/settings.json \
    && transmission-daemon -f
