#!/usr/bin/env bash
python -m smtpd -n -c DebuggingServer localhost:1025 >> ./var/logs/mail.log &