#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os, time, json, subprocess

BASE = "/var/www/proje.amrdanismanlik.com/proje8"
VPN_MAP = os.path.join(BASE, "vpn_map.json")
URL_FILE = os.path.join(BASE, "url.txt")
COMPLETED = os.path.join(BASE, "completed_urls.txt")
LOG_FILE = os.path.join(BASE, "vpn_job.log")
YT_BOT = os.path.join(BASE, "yt_clicker.py")

def log(msg):
    ts = time.strftime("%Y-%m-%d %H:%M:%S")
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(f"{ts} {msg}\n")

def vpn_up(ovpn_file):
    cmd = ["sudo", "openvpn", "--config", ovpn_file, "--daemon"]
    return subprocess.Popen(cmd)

def vpn_down():
    subprocess.call(["sudo", "killall", "openvpn"])

def run_bot():
    subprocess.call(["python3", YT_BOT])

def main():
    with open(VPN_MAP, "r", encoding="utf-8") as f:
        vpn_map = json.load(f)

    with open(URL_FILE, "r", encoding="utf-8") as f:
        urls = [u.strip() for u in f if u.strip()]

    for country, ovpn in vpn_map.items():
        log(f"=== {country} VPN başlatılıyor ===")
        vpn_proc = vpn_up(ovpn)
        time.sleep(10)  # VPN bağlanma süresi

        run_bot()

        log(f"=== {country} VPN ile tamamlandı ===")
        vpn_down()
        time.sleep(5)

if __name__ == "__main__":
    main()
