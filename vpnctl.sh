#!/bin/bash
# VPN kontrol scripti

case "$1" in
  start)
    sudo openvpn --config "$2" --daemon
    ;;
  stop)
    sudo killall openvpn
    ;;
  *)
    echo "Kullanım: $0 {start <ovpn>|stop}"
    ;;
esac
