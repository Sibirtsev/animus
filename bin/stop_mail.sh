#!/usr/bin/env bash
kill `ps ax | grep python | grep DebuggingServer | awk ' { print $1 } '`