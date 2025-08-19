#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import time
from playwright.sync_api import sync_playwright, TimeoutError as PWTimeout

URLS_FILE = "/var/www/proje.amrdanismanlik.com/proje8/url.txt"
LOG_FILE = "/var/www/proje.amrdanismanlik.com/proje8/yt_clicker.log"

HEADLESS = True
CLICK_SELECTOR = ".ytp-play-button.ytp-button"

def log(msg):
    ts = time.strftime("%Y-%m-%d %H:%M:%S")
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(f"{ts} {msg}\n")

def main():
    with open(URLS_FILE, "r", encoding="utf-8") as f:
        urls = [u.strip() for u in f if u.strip()]

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=HEADLESS)
        page = browser.new_page()

        for url in urls:
            try:
                log(f"[OPEN] {url}")
                page.goto(url, timeout=30000)
                page.wait_for_timeout(3000)
                try:
                    page.click(CLICK_SELECTOR, timeout=5000)
                    log(f"[CLICK] Play button clicked at {url}")
                except PWTimeout:
                    log(f"[SKIP] Play button not found at {url}")
            except Exception as e:
                log(f"[ERROR] {url} -> {e}")
        browser.close()

if __name__ == "__main__":
    main()
